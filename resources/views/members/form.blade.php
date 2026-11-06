{{-- Partial field member. Variabel: $mode ('create' atau 'edit') --}}
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
  <label for="{{ $mode }}_name" class="form-label">Nama</label>
  <input type="text" id="{{ $mode }}_name" name="name"
         @class(['form-control', 'is-invalid' => $hasError('name')])
         value="{{ $value('name') }}">
  @if ($hasError('name'))
    <div class="invalid-feedback">{{ $errors->first('name') }}</div>
  @endif
</div>

<div class="mb-3">
  <label for="{{ $mode }}_address" class="form-label">Alamat</label>
  <textarea id="{{ $mode }}_address" name="address" rows="3"
            @class(['form-control', 'is-invalid' => $hasError('address')])>{{ $value('address') }}</textarea>
  @if ($hasError('address'))
    <div class="invalid-feedback">{{ $errors->first('address') }}</div>
  @endif
</div>

<div class="row">
  <div class="col-md-6 mb-3">
    <label for="{{ $mode }}_phone" class="form-label">No. Telepon</label>
    <input type="text" id="{{ $mode }}_phone" name="phone" inputmode="tel"
           @class(['form-control', 'is-invalid' => $hasError('phone')])
           value="{{ $value('phone') }}">
    @if ($hasError('phone'))
      <div class="invalid-feedback">{{ $errors->first('phone') }}</div>
    @endif
  </div>

  <div class="col-md-6 mb-3">
    <label for="{{ $mode }}_email" class="form-label">Email</label>
    <input type="email" id="{{ $mode }}_email" name="email"
           @class(['form-control', 'is-invalid' => $hasError('email')])
           value="{{ $value('email') }}">
    @if ($hasError('email'))
      <div class="invalid-feedback">{{ $errors->first('email') }}</div>
    @endif
  </div>
</div>

<div class="mb-1">
  <label for="{{ $mode }}_status" class="form-label">Status</label>
  <select id="{{ $mode }}_status" name="status"
          @class(['form-select', 'is-invalid' => $hasError('status')])>
    @foreach (\App\Models\Member::STATUSES as $key => $label)
      <option value="{{ $key }}" @selected($value('status', 'aktif') === $key)>{{ $label }}</option>
    @endforeach
  </select>
  @if ($hasError('status'))
    <div class="invalid-feedback">{{ $errors->first('status') }}</div>
  @endif
</div>