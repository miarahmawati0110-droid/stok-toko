
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\DistributorController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\LaporanController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.process');

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Data master
    Route::resource('items', ItemController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::resource('distributors', DistributorController::class);
    Route::resource('agents', AgentController::class);
    Route::resource('users', UserController::class);

    // Barang masuk
    Route::get('/barang-masuk', [TransactionController::class, 'index'])
        ->defaults('jenis', 'masuk')
        ->name('masuk.index');

    Route::get('/barang-masuk/create', [TransactionController::class, 'create'])
        ->defaults('jenis', 'masuk')
        ->name('masuk.create');

    Route::post('/barang-masuk', [TransactionController::class, 'store'])
        ->defaults('jenis', 'masuk')
        ->name('masuk.store');

    Route::get('/barang-masuk/{transaction}', [TransactionController::class, 'show'])
        ->defaults('jenis', 'masuk')
        ->whereNumber('transaction')
        ->name('masuk.show');

    // Barang keluar
    Route::get('/barang-keluar', [TransactionController::class, 'index'])
        ->defaults('jenis', 'keluar')
        ->name('keluar.index');

    Route::get('/barang-keluar/create', [TransactionController::class, 'create'])
        ->defaults('jenis', 'keluar')
        ->name('keluar.create');

    Route::post('/barang-keluar', [TransactionController::class, 'store'])
        ->defaults('jenis', 'keluar')
        ->name('keluar.store');

    Route::get('/barang-keluar/{transaction}', [TransactionController::class, 'show'])
        ->defaults('jenis', 'keluar')
        ->whereNumber('transaction')
        ->name('keluar.show');

    // Laporan
    Route::get('/laporan', [LaporanController::class, 'index'])
        ->name('laporan.index');

    Route::get('/laporan/pdf', [LaporanController::class, 'pdf'])
        ->name('laporan.pdf');
});
