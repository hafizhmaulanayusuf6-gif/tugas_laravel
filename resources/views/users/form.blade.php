{{-- Partial field user. Variabel: $mode ('create' atau 'edit') --}}
@php
    $isActive = old('form_mode') === $mode;
    $value = fn (string $field, $default = null) => $isActive ? old($field) : $default;
    $hasError = fn (string $field) => $isActive && $errors->has($field);
@endphp

@if ($isActive && $errors->any())
  <div class="alert alert-danger" role="alert">
    Periksa kembali isian Anda. Ada {{ $errors->count() }} kesalahan.
  </div>
@endif

<div class="mb-3">
  <label for="{{ $mode }}_name" class="form-label">User Name</label>
  <input type="text" id="{{ $mode }}_name" name="name" autocomplete="off"
         @class(['form-control', 'is-invalid' => $hasError('name')])
         value="{{ $value('name') }}">
  @if ($hasError('name'))
    <div class="invalid-feedback">{{ $errors->first('name') }}</div>
  @endif
</div>

<div class="mb-3">
  <label for="{{ $mode }}_email" class="form-label">Email</label>
  <input type="email" id="{{ $mode }}_email" name="email" autocomplete="off"
         @class(['form-control', 'is-invalid' => $hasError('email')])
         value="{{ $value('email') }}">
  @if ($hasError('email'))
    <div class="invalid-feedback">{{ $errors->first('email') }}</div>
  @endif
</div>

<div class="mb-3">
  <label for="{{ $mode }}_password" class="form-label">Password</label>
  <input type="password" id="{{ $mode }}_password" name="password" autocomplete="new-password"
         @class(['form-control', 'is-invalid' => $hasError('password')])>
  <div class="form-text">
    Minimal 8 karakter.
    @if ($mode === 'edit')
      Kosongkan jika tidak ingin mengubah password.
    @endif
  </div>
  @if ($hasError('password'))
    <div class="invalid-feedback">{{ $errors->first('password') }}</div>
  @endif
</div>

<div class="mb-1">
  <label for="{{ $mode }}_role" class="form-label">Role</label>
  <select id="{{ $mode }}_role" name="role"
          @class(['form-select', 'is-invalid' => $hasError('role')])>
    <option value="">-- Pilih Role --</option>
    @foreach (\App\Models\User::ROLES as $key => $label)
      <option value="{{ $key }}" @selected((string) $value('role') === $key)>{{ $label }}</option>
    @endforeach
  </select>
  @if ($hasError('role'))
    <div class="invalid-feedback">{{ $errors->first('role') }}</div>
  @endif
</div>