<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Str;

class StudentIdentityService
{
    public function normalizeName(?string $name): string
    {
        $name = Str::upper(preg_replace('/\s+/u', ' ', trim((string) $name)));

        return preg_replace('/[^A-Z0-9 ]/', '', Str::ascii($name));
    }

    public function parseCombinedIdentifier(mixed $value): array
    {
        preg_match_all('/\d+/', trim((string) $value), $matches);
        $numbers = $matches[0] ?? [];
        $nisn = collect($numbers)->first(fn ($number) => strlen($number) === 10);
        $nis = collect($numbers)->first(fn ($number) => strlen($number) >= 4 && strlen($number) <= 6);

        return ['nisn' => $nisn, 'nis' => $nis];
    }

    public function nextTemporaryId(string $academicYear, string $className): string
    {
        $year = preg_replace('/\D/', '', $academicYear);
        $year = substr($year, 2, 2).substr($year, -2);
        $class = preg_replace('/[^A-Z0-9]/', '', Str::upper($className));
        $prefix = "TMP-{$year}-{$class}";
        $last = Student::withTrashed()->where('temporary_id', 'like', $prefix.'-%')->orderByDesc('temporary_id')->value('temporary_id');
        $sequence = $last ? ((int) Str::afterLast($last, '-')) + 1 : 1;

        return sprintf('%s-%03d', $prefix, $sequence);
    }
}
