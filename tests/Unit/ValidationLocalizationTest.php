<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ValidationLocalizationTest extends TestCase
{
    public function test_violation_validation_messages_are_translated_to_indonesian(): void
    {
        $validator = Validator::make(
            ['chronology' => 'singkat', 'instrument_ids' => []],
            ['chronology' => 'required|string|min:10', 'instrument_ids' => 'required|array|min:1'],
        );

        $this->assertSame(
            'Kronologi harus berisi sedikitnya 10 karakter agar kejadian tercatat dengan jelas.',
            $validator->errors()->first('chronology'),
        );
        $this->assertSame(
            'Pilih sedikitnya satu instrumen pelanggaran.',
            $validator->errors()->first('instrument_ids'),
        );
        $this->assertStringNotContainsString('validation.', implode(' ', $validator->errors()->all()));
    }
}
