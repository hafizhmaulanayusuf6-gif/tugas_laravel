{{-- Partial field produk. Variabel: $mode ('create' atau 'edit') --}}
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
  <label for="{{ $mode }}_name" class="form-label">Nama Produk</label>
  <input type="text" id="{{ $mode }}_name" name="name"
         @class(['form-control', 'is-invalid' => $hasError('name')])
         value="{{ $value('name') }}">
  @if ($hasError('name'))
    <div class="invalid-feedback">{{ $errors->first('name') }}</div>
  @endif
</div>

<div class="mb-3">
  <label for="{{ $mode }}_description" class="form-label">Deskripsi</label>
  <textarea id="{{ $mode }}_description" name="description" rows="4"
            @class(['form-control', 'is-invalid' => $hasError('description')])>{{ $value('description') }}</textarea>
  @if ($hasError('description'))
    <div class="invalid-feedback">{{ $errors->first('description') }}</div>
  @endif
</div>

<div class="row">
  <div class="col-md-6 mb-3">
    <label for="{{ $mode }}_price" class="form-label">Harga</label>
    <div class="input-group has-validation">
      <span class="input-group-text">Rp</span>
      <input type="number" id="{{ $mode }}_price" name="price" min="0" step="1"
             @class(['form-control', 'is-invalid' => $hasError('price')])
             value="{{ $value('price') }}">
      @if ($hasError('price'))
        <div class="invalid-feedback">{{ $errors->first('price') }}</div>
      @endif
    </div>
  </div>

  <div class="col-md-6 mb-3">
    <label for="{{ $mode }}_stock" class="form-label">Stok</label>
    <input type="number" id="{{ $mode }}_stock" name="stock" min="0" step="1"
           @class(['form-control', 'is-invalid' => $hasError('stock')])
           value="{{ $value('stock', 0) }}">
    @if ($hasError('stock'))
      <div class="invalid-feedback">{{ $errors->first('stock') }}</div>
    @endif
  </div>
</div>

<div class="mb-1">
  <label for="{{ $mode }}_image" class="form-label">Gambar</label>
  <input type="file" id="{{ $mode }}_image" name="image"
         accept=".jpg,.jpeg,.png,.webp"
         @class(['form-control', 'is-invalid' => $hasError('image')])>
  <div class="form-text">
    Format JPG, PNG, atau WEBP, maksimal 2 MB.
    @if ($mode === 'edit')
      Kosongkan jika tidak ingin mengganti gambar.
    @endif
  </div>
  @if ($hasError('image'))
    <div class="invalid-feedback d-block">{{ $errors->first('image') }}</div>
  @endif
</div>