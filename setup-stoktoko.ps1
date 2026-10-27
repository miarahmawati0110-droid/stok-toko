$ErrorActionPreference = 'Stop'

if (-not (Test-Path .\artisan)) {
    Write-Host 'Jalankan script ini dari folder stoktoko (yang berisi file artisan).' -ForegroundColor Red
    exit 1
}

$utf8 = New-Object System.Text.UTF8Encoding $false
function Put($rel, $text) {
    $full = Join-Path (Get-Location).Path $rel
    New-Item -ItemType Directory -Force -Path (Split-Path $full) | Out-Null
    [System.IO.File]::WriteAllText($full, $text, $utf8)
    Write-Host "OK  $rel"
}

# ---------- 1. Migrasi users (nama, username, role) ----------
$c = @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('username')->unique();
            $table->string('password');
            $table->enum('role', ['Admin', 'Kasir', 'Gudang'])->default('Admin');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('users');
    }
};
'@
$m = (Get-ChildItem database\migrations -Filter *create_users_table.php | Select-Object -First 1).Name
Put "database\migrations\$m" $c

# ---------- 2. Model User ----------
$c = @'
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['nama', 'username', 'password', 'role'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }
}
'@
Put 'app\Models\User.php' $c

# ---------- 3. Seeder: tiga akun contoh ----------
$c = @'
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create(['nama' => 'Admin Toko',   'username' => 'admin',   'password' => 'admin123',  'role' => 'Admin']);
        User::create(['nama' => 'Siti Aminah',  'username' => 'kasir1',  'password' => 'kasir123',  'role' => 'Kasir']);
        User::create(['nama' => 'Budi Santoso', 'username' => 'gudang1', 'password' => 'gudang123', 'role' => 'Gudang']);
    }
}
'@
Put 'database\seeders\DatabaseSeeder.php' $c

# ---------- 4. Controller login ----------
$c = @'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        if (Auth::attempt($data)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors(['username' => 'Username atau password salah.'])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
'@
Put 'app\Http\Controllers\AuthController.php' $c

# ---------- 5. Middleware peran ----------
$c = @'
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! in_array($request->user()->role, $roles)) {
            abort(403, 'Anda tidak punya akses ke halaman ini.');
        }

        return $next($request);
    }
}
'@
Put 'app\Http\Middleware\RoleMiddleware.php' $c

# ---------- 6. bootstrap/app.php (daftar alias role) ----------
$c = @'
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
'@
Put 'bootstrap\app.php' $c

# ---------- 7. Rute ----------
$c = @'
<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

    // Hanya Admin: user, supplier, distributor, agen (diisi di langkah berikutnya)
    Route::middleware('role:Admin')->group(function () {
    });

    // Admin, Kasir, Gudang: data barang dan laporan (langkah berikutnya)
    // Admin, Gudang: barang masuk
    // Admin, Kasir: barang keluar
});
'@
Put 'routes\web.php' $c

# ---------- 8. Tampilan ----------
$c = @'
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
'@
Put 'resources\views\auth\login.blade.php' $c

$c = @'
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title') - {{ config('app.name') }}</title>
  <style>
    *{box-sizing:border-box}
    body{margin:0;background:#F3F5F7;color:#1B2733;font:14px/1.5 system-ui,sans-serif}
    .top{display:flex;justify-content:space-between;align-items:center;padding:12px 20px;background:#fff;border-bottom:1px solid #D5DBE2}
    .lk{background:none;border:0;color:#2D3E50;text-decoration:underline;cursor:pointer;font:inherit}
    .app{display:grid;grid-template-columns:210px 1fr}
    .side{background:#2D3E50;padding:12px 0;min-height:calc(100vh - 53px)}
    .side a{display:block;padding:10px 20px;color:#C9D3DD;text-decoration:none;border-left:3px solid transparent}
    .side a.on{background:rgba(255,255,255,.1);border-left-color:#fff;color:#fff;font-weight:600}
    main{padding:24px}
    .card{background:#fff;border:1px solid #D5DBE2;border-radius:6px;padding:16px}
    @media(max-width:800px){.app{grid-template-columns:1fr}.side{display:flex;overflow-x:auto;min-height:0;padding:0}.side a{white-space:nowrap}}
  </style>
</head>
<body>
  <header class="top">
    <b>{{ config('app.name') }}</b>
    <span>{{ auth()->user()->nama }} ({{ auth()->user()->role }}) |
      <form method="POST" action="{{ route('logout') }}" style="display:inline">@csrf
        <button class="lk">Keluar</button>
      </form>
    </span>
  </header>

  @php
    $role = auth()->user()->role;
    $menu = [
      ['Dashboard',        'dashboard',     ['Admin','Kasir','Gudang']],
      ['Data User',        'users',         ['Admin']],
      ['Data Supplier',    'suppliers',     ['Admin']],
      ['Data Distributor', 'distributors',  ['Admin']],
      ['Data Agen',        'agents',        ['Admin']],
      ['Data Barang',      'items',         ['Admin','Kasir','Gudang']],
      ['Barang Masuk',     'barang-masuk',  ['Admin','Gudang']],
      ['Barang Keluar',    'barang-keluar', ['Admin','Kasir']],
      ['Laporan',          'laporan',       ['Admin','Kasir','Gudang']],
    ];
  @endphp

  <div class="app">
    <nav class="side">
      @foreach ($menu as [$label, $path, $roles])
        @if (in_array($role, $roles))
          <a href="{{ url($path) }}" class="{{ request()->is($path.'*') ? 'on' : '' }}">{{ $label }}</a>
        @endif
      @endforeach
    </nav>
    <main>@yield('content')</main>
  </div>
</body>
</html>
'@
Put 'resources\views\layouts\app.blade.php' $c

$c = @'
@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
  <h2>Dashboard</h2>
  <div class="card">Selamat datang, {{ auth()->user()->nama }}. Anda masuk sebagai <b>{{ auth()->user()->role }}</b>.</div>
@endsection
'@
Put 'resources\views\dashboard.blade.php' $c

# ---------- 9. Zona waktu WIB ----------
$cfg = Join-Path (Get-Location).Path 'config\app.php'
$t = [System.IO.File]::ReadAllText($cfg)
$t = $t.Replace("'timezone' => 'UTC'", "'timezone' => 'Asia/Jakarta'")
[System.IO.File]::WriteAllText($cfg, $t, $utf8)
Write-Host 'OK  config\app.php (timezone Asia/Jakarta)'

# ---------- 10. Database ----------
php artisan config:clear
php artisan migrate:fresh --seed

Write-Host ''
Write-Host 'Selesai. Sekarang jalankan: php artisan serve' -ForegroundColor Green
