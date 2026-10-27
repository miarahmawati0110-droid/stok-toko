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

# ---------- Model ----------
$c = @'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    protected $fillable = ['distributor_id', 'nama', 'alamat', 'telepon'];

    public function distributor()
    {
        return $this->belongsTo(Distributor::class);
    }
}
'@
Put 'app\Models\Agent.php' $c

$c = @'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
        'kode', 'nama', 'satuan', 'harga', 'stok', 'tanggal_masuk',
        'supplier_id', 'distributor_id', 'user_id',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function distributor()
    {
        return $this->belongsTo(Distributor::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
'@
Put 'app\Models\Item.php' $c

# ---------- Controller Agen ----------
$c = @'
<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Distributor;
use Illuminate\Database\Eloquent\Model;

class AgentController extends MasterController
{
    protected string $model = Agent::class;
    protected string $route = 'agents';
    protected string $title = 'Agen';
    protected array $columns = [
        'nama' => 'Nama Agen',
        'alamat' => 'Alamat',
        'telepon' => 'Telepon',
        'distributor_id' => 'Distributor',
    ];
    protected array $search = ['nama', 'alamat', 'telepon'];
    protected array $with = ['distributor'];

    public function cell(Model $row, string $col)
    {
        return $col === 'distributor_id' ? $row->distributor?->nama : parent::cell($row, $col);
    }

    protected function fields(?Model $row = null): array
    {
        return [
            ['name' => 'nama', 'label' => 'Nama Agen', 'type' => 'text', 'rules' => 'required|string|max:255'],
            ['name' => 'alamat', 'label' => 'Alamat', 'type' => 'text', 'rules' => 'required|string|max:255'],
            ['name' => 'telepon', 'label' => 'Telepon', 'type' => 'text', 'rules' => 'required|string|max:30'],
            [
                'name' => 'distributor_id',
                'label' => 'Distributor (membawahi)',
                'type' => 'select',
                'options' => Distributor::orderBy('nama')->pluck('nama', 'id')->all(),
                'rules' => 'required|exists:distributors,id',
            ],
        ];
    }
}
'@
Put 'app\Http\Controllers\AgentController.php' $c

# ---------- Controller User ----------
$c = @'
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserController extends MasterController
{
    protected string $model = User::class;
    protected string $route = 'users';
    protected string $title = 'User';
    protected array $columns = [
        'nama' => 'Nama Lengkap',
        'username' => 'Username',
        'role' => 'Role',
    ];
    protected array $search = ['nama', 'username', 'role'];

    protected function fields(?Model $row = null): array
    {
        return [
            ['name' => 'nama', 'label' => 'Nama Lengkap', 'type' => 'text', 'rules' => 'required|string|max:255'],
            [
                'name' => 'username',
                'label' => 'Username',
                'type' => 'text',
                'rules' => 'required|string|max:50|unique:users,username'.($row ? ','.$row->id : ''),
            ],
            [
                'name' => 'password',
                'label' => 'Password',
                'type' => 'password',
                'hint' => $row ? 'Kosongkan jika password tidak diubah.' : 'Minimal 6 karakter.',
                'rules' => $row ? 'nullable|string|min:6' : 'required|string|min:6',
            ],
            [
                'name' => 'role',
                'label' => 'Role',
                'type' => 'select',
                'options' => ['Admin' => 'Admin', 'Kasir' => 'Kasir', 'Gudang' => 'Gudang'],
                'rules' => 'required|in:Admin,Kasir,Gudang',
            ],
        ];
    }

    protected function beforeSave(array $data): array
    {
        if (empty($data['password'])) {
            unset($data['password']);
        }
        return $data;
    }

    public function destroy($id)
    {
        if ((int) $id === auth()->id()) {
            return back()->with('err', 'Akun yang sedang dipakai tidak bisa dihapus.');
        }

        return parent::destroy($id);
    }
}
'@
Put 'app\Http\Controllers\UserController.php' $c

# ---------- Controller Barang ----------
$c = @'
<?php

namespace App\Http\Controllers;

use App\Models\Distributor;
use App\Models\Item;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Model;

class ItemController extends MasterController
{
    private const SATUAN = ['karung', 'kg', 'botol', 'pak', 'dus', 'pcs', 'liter'];

    protected string $model = Item::class;
    protected string $route = 'items';
    protected string $title = 'Barang';
    protected array $columns = [
        'kode' => 'Kode Barang',
        'nama' => 'Nama Barang',
        'satuan' => 'Satuan',
        'harga' => 'Harga',
        'stok' => 'Stok',
        'supplier_id' => 'Supplier',
        'distributor_id' => 'Distributor',
        'user_id' => 'Dikelola Oleh',
    ];
    protected array $search = ['kode', 'nama', 'satuan'];
    protected array $with = ['supplier', 'distributor', 'user'];

    public function cell(Model $row, string $col)
    {
        return match ($col) {
            'harga' => 'Rp '.number_format($row->harga, 0, ',', '.'),
            'supplier_id' => $row->supplier?->nama,
            'distributor_id' => $row->distributor?->nama,
            'user_id' => $row->user?->nama,
            default => parent::cell($row, $col),
        };
    }

    protected function fields(?Model $row = null): array
    {
        return [
            [
                'name' => 'kode',
                'label' => 'Kode Barang',
                'type' => 'text',
                'rules' => 'required|string|max:50|unique:items,kode'.($row ? ','.$row->id : ''),
            ],
            ['name' => 'nama', 'label' => 'Nama Barang', 'type' => 'text', 'rules' => 'required|string|max:255'],
            [
                'name' => 'satuan',
                'label' => 'Satuan',
                'type' => 'select',
                'options' => array_combine(self::SATUAN, self::SATUAN),
                'rules' => 'required|in:'.implode(',', self::SATUAN),
            ],
            ['name' => 'harga', 'label' => 'Harga', 'type' => 'number', 'rules' => 'required|integer|min:0'],
            ['name' => 'stok', 'label' => 'Jumlah Stok', 'type' => 'number', 'rules' => 'required|integer|min:0'],
            ['name' => 'tanggal_masuk', 'label' => 'Tanggal Masuk', 'type' => 'date', 'rules' => 'required|date'],
            [
                'name' => 'supplier_id',
                'label' => 'Supplier (memasok)',
                'type' => 'select',
                'options' => Supplier::orderBy('nama')->pluck('nama', 'id')->all(),
                'rules' => 'required|exists:suppliers,id',
            ],
            [
                'name' => 'distributor_id',
                'label' => 'Distributor (menyalurkan)',
                'type' => 'select',
                'options' => Distributor::orderBy('nama')->pluck('nama', 'id')->all(),
                'rules' => 'required|exists:distributors,id',
            ],
        ];
    }

    protected function beforeSave(array $data): array
    {
        $data['user_id'] = auth()->id();
        return $data;
    }
}
'@
Put 'app\Http\Controllers\ItemController.php' $c

# ---------- Form (password tidak ditampilkan ulang, ada petunjuk) ----------
$c = @'
@extends('layouts.app')
@section('title', ($row ? 'Ubah ' : 'Tambah ').$title)
@section('content')
  <h2>{{ $row ? 'Ubah' : 'Tambah' }} {{ $title }}</h2>

  <form method="POST" action="{{ $row ? route($route.'.update', $row->id) : route($route.'.store') }}">
    @csrf
    @if ($row)
      @method('PUT')
    @endif

    <div class="card fg">
      @foreach ($fields as $f)
        @php $type = $f['type'] ?? 'text'; @endphp
        <label>{{ $f['label'] }}
          @if ($type === 'select')
            <select name="{{ $f['name'] }}">
              <option value="">Pilih</option>
              @foreach ($f['options'] as $val => $text)
                <option value="{{ $val }}" @selected(old($f['name'], $row?->{$f['name']}) == $val)>{{ $text }}</option>
              @endforeach
            </select>
          @else
            <input type="{{ $type }}" name="{{ $f['name'] }}" value="{{ $type === 'password' ? '' : old($f['name'], $row?->{$f['name']}) }}" placeholder="Masukkan {{ strtolower($f['label']) }}">
          @endif
          @if (!empty($f['hint']))
            <small style="color:#667085;font-weight:400">{{ $f['hint'] }}</small>
          @endif
          @error($f['name'])
            <span class="err">{{ $message }}</span>
          @enderror
        </label>
      @endforeach
    </div>

    <div class="acts">
      <a class="b" href="{{ route($route.'.index') }}">Batal</a>
      <button class="b pr">Simpan</button>
    </div>
  </form>
@endsection
'@
Put 'resources\views\master\form.blade.php' $c

# ---------- Rute ----------
$c = @'
<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DistributorController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

    // Semua peran yang sudah login
    Route::resource('items', ItemController::class)->except('show');

    // Hanya Admin
    Route::middleware('role:Admin')->group(function () {
        Route::resource('users', UserController::class)->except('show');
        Route::resource('suppliers', SupplierController::class)->except('show');
        Route::resource('distributors', DistributorController::class)->except('show');
        Route::resource('agents', AgentController::class)->except('show');
    });
});
'@
Put 'routes\web.php' $c

php artisan optimize:clear

Write-Host ''
Write-Host 'Selesai. Muat ulang browser (F5).' -ForegroundColor Green