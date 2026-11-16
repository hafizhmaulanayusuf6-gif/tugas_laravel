<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Masuk | ZieMarket</title>
    <link rel="stylesheet" href="{{ asset('assets/css/adminlte.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous">
    <style>
        body { min-height: 100vh; display: grid; place-items: center; padding: 1.25rem; background: #f3f5f8; }
        .login-card { width: min(100%, 430px); border: 1px solid #e4e8ee; border-radius: 1rem; box-shadow: 0 1rem 2.5rem rgb(25 42 70 / 8%); }
        .brand-mark { width: 3rem; height: 3rem; display: grid; place-items: center; border-radius: .9rem; background: #eaf2ff; color: #1769e0; font-size: 1.4rem; }
        .form-control { min-height: 2.9rem; }
    </style>
</head>
<body>
    <main class="card login-card shadow-sm">
        <div class="card-body p-4 p-sm-5">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="brand-mark" aria-hidden="true"><i class="bi bi-shop"></i></div>
                <div>
                    <h1 class="h4 mb-1">Masuk ke ZieMarket</h1>
                    <p class="text-secondary mb-0">Gunakan akun admin atau kasir Anda.</p>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <p class="mb-0">{{ $errors->first() }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" autocomplete="username" required autofocus>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" autocomplete="current-password" required>
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                    <label class="form-check-label" for="remember">Ingat saya</label>
                </div>
                <button class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2" type="submit">
                    <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i><span>Masuk</span>
                </button>
            </form>
        </div>
    </main>
</body>
</html>
