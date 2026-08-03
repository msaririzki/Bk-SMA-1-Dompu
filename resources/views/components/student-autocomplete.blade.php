@props([
    'name' => 'student_id',
    'student' => null,
    'label' => 'Siswa',
    'required' => true,
    'id' => null,
])

@php
    $inputId = $id ?: str_replace('_', '-', $name).'-search';
    $selectedId = old($name, $student?->id);
    $className = $student?->currentEnrollment?->schoolClass?->name;
    $identifier = $student?->nisn ?: ($student?->nis ?: $student?->temporary_id);
    $selectedLabel = $student
        ? $student->name.($className ? " — {$className}" : '').($identifier ? " ({$identifier})" : '')
        : '';
@endphp

<div
    class="student-autocomplete"
    data-student-autocomplete
    data-url="{{ route('students.search') }}"
>
    <label class="form-label" for="{{ $inputId }}">{{ $label }}</label>
    <input data-student-value type="hidden" name="{{ $name }}" value="{{ $selectedId }}">
    <div class="input-with-icon">
        <x-icon name="search" />
        <input
            id="{{ $inputId }}"
            data-student-query
            class="form-control pr-10"
            type="search"
            value="{{ $selectedLabel }}"
            placeholder="Ketik minimal 2 huruf nama siswa..."
            autocomplete="off"
            role="combobox"
            aria-autocomplete="list"
            aria-expanded="false"
            aria-controls="{{ $inputId }}-results"
            @if($required) required aria-required="true" @endif
        >
        <span data-student-spinner class="student-search-spinner hidden" aria-hidden="true"></span>
    </div>
    <div id="{{ $inputId }}-results" data-student-results class="student-search-results hidden" role="listbox"></div>
    <p data-student-help class="field-help"><x-icon name="search" class="mt-0.5 size-3.5 shrink-0" /> Ketik sebagian nama, NIS, NISN, atau ID sementara lalu pilih hasilnya.</p>
</div>
