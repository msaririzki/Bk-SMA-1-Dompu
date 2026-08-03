<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\User;
use App\Models\ViolationCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class AttachmentService
{
    public function store(UploadedFile $file, ViolationCase $case, User $user): Attachment
    {
        $mime = (string) $file->getMimeType();
        $isImage = in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true);
        $extension = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'application/pdf' => 'pdf',
            default => throw new RuntimeException('Jenis berkas tidak didukung.'),
        };

        $directory = 'evidence/'.$case->id;
        $basename = (string) Str::uuid();
        $path = $directory.'/'.$basename.'.'.$extension;
        $thumbnailPath = null;

        if ($isImage) {
            $image = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));
            if (! $image) {
                throw new RuntimeException('Berkas gambar tidak dapat dibaca.');
            }

            // Encoding ulang membuang EXIF/GPS dan metadata sensitif lainnya.
            Storage::disk('local')->put($path, $this->encode($image, $mime));
            $thumbnail = $this->thumbnail($image, 480, 360);
            $thumbnailPath = $directory.'/'.$basename.'-thumb.'.$extension;
            Storage::disk('local')->put($thumbnailPath, $this->encode($thumbnail, $mime, 82));
            imagedestroy($thumbnail);
            imagedestroy($image);
        } else {
            Storage::disk('local')->putFileAs($directory, $file, $basename.'.pdf');
        }

        return Attachment::create([
            'case_id' => $case->id,
            'student_id' => $case->student_id,
            'uploaded_by' => $user->id,
            'disk' => 'local',
            'path' => $path,
            'thumbnail_path' => $thumbnailPath,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $mime,
            'size' => Storage::disk('local')->size($path),
        ]);
    }

    private function thumbnail(\GdImage $source, int $maxWidth, int $maxHeight): \GdImage
    {
        $width = imagesx($source);
        $height = imagesy($source);
        $ratio = min($maxWidth / $width, $maxHeight / $height, 1);
        $targetWidth = max(1, (int) round($width * $ratio));
        $targetHeight = max(1, (int) round($height * $ratio));
        $target = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($target, false);
        imagesavealpha($target, true);
        $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
        imagefill($target, 0, 0, $transparent);
        imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        return $target;
    }

    private function encode(\GdImage $image, string $mime, int $quality = 90): string
    {
        ob_start();
        match ($mime) {
            'image/jpeg' => imagejpeg($image, null, $quality),
            'image/png' => imagepng($image, null, 7),
            'image/webp' => imagewebp($image, null, $quality),
        };

        return (string) ob_get_clean();
    }
}
