{{-- Partial field kategori. Variabel: $mode ('create' atau 'edit') --}}
@php
    $isActive = old('form_mode') === $mode;
    $value = fn (string $field, $default = null) => $isActive ? old($field) : $default;
    $hasError = fn (string $field) => $isActive && $errors->has($field);
@endphp

@if ($isActive && $errors->any())
  <div class="alert alert-danger" role="alert">
    Periksa kembali isian Anda.
  </div>
@endif

<div class="mb-1">
  <label for="{{ $mode }}_name" class="form-label">Nama Kategori</label>
  <input type="text" id="{{ $mode }}_name" name="name"
         @class(['form-control', 'is-invalid' => $hasError('name')])
         value="{{ $value('name') }}">
  @if ($hasError('name'))
    <div class="invalid-feedback">{{ $errors->first('name') }}</div>
  @endif
</div>