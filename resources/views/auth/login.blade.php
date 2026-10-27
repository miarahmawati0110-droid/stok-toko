<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - {{ config('app.name') }}</title>
  <style>
    *{box-sizing:border-box}
    body{margin:0;background:#F3F5F7;color:#1B2733;font:14px/1.5 system-ui,sans-serif}
    .box{max-width:420px;margin:8vh auto;padding:0 16px}
    h1{text-align:center;margin:0}
    .sub{text-align:center;color:#667085;margin:4px 0 20px}
    form{background:#fff;border:1px solid #D5DBE2;border-radius:6px;padding:20px;display:grid;gap:14px}
    label{display:flex;flex-direction:column;gap:4px;font-weight:500}
    input{font:inherit;padding:8px 10px;border:1px solid #D5DBE2;border-radius:4px}
    button{font:inherit;padding:9px;border:0;border-radius:4px;background:#2D3E50;color:#fff;cursor:pointer}
    .err{color:#B42318;margin:0}
    small{color:#667085}
  </style>
</head>
<body>
  <div class="box">
    <h1>{{ config('app.name') }}</h1>
    <p class="sub">Sistem Informasi Pengelolaan Stok dan Transaksi pada Toko Berbasis Web</p>
    <form method="POST" action="{{ url('/login') }}">
      @csrf
      <label>Username
        <input name="username" value="{{ old('username') }}" autofocus>
      </label>
      <label>Password
        <input name="password" type="password">
      </label>
      @error('username') <p class="err">{{ $message }}</p> @enderror
      <button>Masuk</button>
      <small>Akun contoh: admin / admin123, kasir1 / kasir123, gudang1 / gudang123</small>
    </form>
  </div>
</body>
</html>