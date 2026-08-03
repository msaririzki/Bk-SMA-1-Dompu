<?php

namespace Tests\Unit;

use App\Services\StudentIdentityService;
use PHPUnit\Framework\TestCase;

class StudentIdentityServiceTest extends TestCase
{
    public function test_it_preserves_leading_zero_when_splitting_nisn_and_nis(): void
    {
        $result = (new StudentIdentityService)->parseCombinedIdentifier('0103300574 / 18967');
        $this->assertSame('0103300574', $result['nisn']);
        $this->assertSame('18967', $result['nis']);
    }

    public function test_it_normalizes_spacing_case_and_punctuation(): void
    {
        $this->assertSame('ST ROFIAH', (new StudentIdentityService)->normalizeName("  St.  Rofi'ah "));
    }
}
