<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Process\Process;
use Throwable;
use ZipArchive;

class BackupBkData extends Command
{
    protected $signature = 'bk:backup {--disk= : Disk tujuan selain konfigurasi default} {--keep-local : Jangan hapus arsip lokal setelah unggah}';

    protected $description = 'Membuat backup terenkripsi basis data dan berkas privat BK';

    public function handle(): int
    {
        $password = (string) config('backup.password');
        if ($password === '') {
            $this->error('BACKUP_PASSWORD wajib diisi agar arsip selalu terenkripsi.');

            return self::FAILURE;
        }

        $diskName = (string) ($this->option('disk') ?: config('backup.disk'));
        $stamp = now()->format('Ymd-His');
        $work = storage_path('framework/backup-'.Str::uuid());
        $archive = storage_path('app/private/backups/bk-'.$stamp.'.zip');
        File::ensureDirectoryExists($work);
        File::ensureDirectoryExists(dirname($archive));

        try {
            $databaseFile = $this->dumpDatabase($work);
            $zip = new ZipArchive;
            throw_unless($zip->open($archive, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true, \RuntimeException::class, 'Arsip backup tidak dapat dibuat.');
            $this->addEncryptedFile($zip, $databaseFile, 'database/'.basename($databaseFile), $password);
            $this->addPrivateFiles($zip, $password);
            $zip->close();

            $tiers = ['daily'];
            if (now()->isSunday()) {
                $tiers[] = 'weekly';
            }
            if (now()->day === 1) {
                $tiers[] = 'monthly';
            }

            foreach ($tiers as $tier) {
                $remotePath = 'bk-backups/'.$tier.'/bk-'.$tier.'-'.$stamp.'.zip';
                $stream = fopen($archive, 'rb');
                throw_unless(Storage::disk($diskName)->put($remotePath, $stream), \RuntimeException::class, "Gagal mengunggah {$remotePath}.");
                if (is_resource($stream)) {
                    fclose($stream);
                }
                $this->prune($diskName, $tier, (int) config('backup.retention.'.$tier));
            }

            $status = ['finished_at' => now()->toIso8601String(), 'disk' => $diskName, 'tiers' => $tiers, 'size' => File::size($archive), 'successful' => true];
            File::put(storage_path('app/private/backup-status.json'), json_encode($status, JSON_PRETTY_PRINT));
            if (! $this->option('keep-local') && $diskName !== 'local') {
                File::delete($archive);
            }
            $this->info('Backup terenkripsi berhasil dibuat dan diverifikasi pada disk '.$diskName.'.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            File::put(storage_path('app/private/backup-status.json'), json_encode(['finished_at' => now()->toIso8601String(), 'successful' => false, 'message' => $exception->getMessage()], JSON_PRETTY_PRINT));
            $this->error($exception->getMessage());

            return self::FAILURE;
        } finally {
            if (File::isDirectory($work)) {
                File::deleteDirectory($work);
            }
        }
    }

    private function dumpDatabase(string $work): string
    {
        $connection = config('database.default');
        $database = config("database.connections.{$connection}");
        if (($database['driver'] ?? null) === 'sqlite') {
            $source = $database['database'];
            throw_unless(is_file($source), \RuntimeException::class, 'Berkas SQLite tidak ditemukan.');
            $target = $work.'/database.sqlite';
            File::copy($source, $target);

            return $target;
        }
        throw_unless(($database['driver'] ?? null) === 'mysql', \RuntimeException::class, 'Driver database belum didukung oleh backup.');
        $target = $work.'/database.sql';
        $process = new Process([
            'mysqldump', '--single-transaction', '--quick', '--routines', '--triggers',
            '--host='.$database['host'], '--port='.$database['port'], '--user='.$database['username'],
            '--result-file='.$target, $database['database'],
        ], base_path(), ['MYSQL_PWD' => (string) $database['password']], null, 1800);
        $process->mustRun();

        return $target;
    }

    private function addPrivateFiles(ZipArchive $zip, string $password): void
    {
        $root = storage_path('app/private');
        if (! File::isDirectory($root)) {
            return;
        }
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS));
        foreach ($files as $file) {
            if (! $file->isFile()) {
                continue;
            }
            $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
            if (str_starts_with($relative, 'backups/') || $relative === 'backup-status.json') {
                continue;
            }
            $this->addEncryptedFile($zip, $file->getPathname(), 'private/'.$relative, $password);
        }
    }

    private function addEncryptedFile(ZipArchive $zip, string $source, string $name, string $password): void
    {
        throw_unless($zip->addFile($source, $name), \RuntimeException::class, 'Gagal menambahkan berkas ke arsip.');
        throw_unless($zip->setEncryptionName($name, ZipArchive::EM_AES_256, $password), \RuntimeException::class, 'Enkripsi AES backup gagal.');
    }

    private function prune(string $disk, string $tier, int $keep): void
    {
        $files = collect(Storage::disk($disk)->files('bk-backups/'.$tier))->sortDesc()->values();
        foreach ($files->slice(max(1, $keep)) as $file) {
            Storage::disk($disk)->delete($file);
        }
    }
}
