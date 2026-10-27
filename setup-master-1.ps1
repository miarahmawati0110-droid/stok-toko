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

# ---------- 1. Controller dasar ----------
$c = @'
<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

abstract class MasterController extends Controller
{
    protected string $model;
    protected string $route;
    protected string $title;
    protected array $columns = [];
    protected array $search = [];
    protected array $with = [];

    abstract protected function fields(?Model $row = null): array;

    public function cell(Model $row, string $col)
    {
        return $row->{$col};
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $query = ($this->model)::query()->with($this->with);

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                foreach ($this->search as $col) {
                    $w->orWhere($col, 'like', "%{$q}%");
                }
            });
        }

        $rows = $query->orderBy($this->search[0] ?? 'id')->paginate(10)->withQueryString();

        return view('master.index', [
            'rows' => $rows,
            'q' => $q,
            'title' => $this->title,
            'route' => $this->route,
            'columns' => $this->columns,
            'ctrl' => $this,
        ]);
    }

    public function create()
    {
        return view('master.form', $this->formData(null));
    }

    public function edit($id)
    {
        $row = ($this->model)::findOrFail($id);
        return view('master.form', $this->formData($row));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        ($this->model)::create($this->beforeSave($data));

        return redirect()->route($this->route.'.index')->with('ok', $this->title.' berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $row = ($this->model)::findOrFail($id);
        $data = $request->validate($this->rules($row));
        $row->update($this->beforeSave($data));

        return redirect()->route($this->route.'.index')->with('ok', $this->title.' berhasil diubah.');
    }

    public function destroy($id)
    {
        $row = ($this->model)::findOrFail($id);

        try {
            $row->delete();
        } catch (QueryException $e) {
            return back()->with('err', $this->title.' tidak bisa dihapus karena masih dipakai data lain.');
        }

        return redirect()->route($this->route.'.index')->with('ok', $this->title.' berhasil dihapus.');
    }

    protected function beforeSave(array $data): array
    {
        return $data;
    }

    protected function rules(?Model $row = null): array
    {
        $rules = [];
        foreach ($this->fields($row) as $f) {
            $rules[$f['name']] = $f['rules'];
        }
        return $rules;
    }

    private function formData(?Model $row): array
    {
        return [
            'row' => $row,
            'title' => $this->title,
            'route' => $this->route,
            'fields' => $this->fields($row),
        ];
    }
}
'@
Put 'app\Http\Controllers\MasterController.php' $c

# ---------- 2. Model dan controller Supplier / Distributor ----------
$model = @'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class __CLASS__ extends Model
{
    protected $fillable = ['nama', 'alamat', 'telepon', 'email'];
}
'@

$ctrl = @'
<?php

namespace App\Http\Controllers;

use App\Models\__CLASS__;
use Illuminate\Database\Eloquent\Model;

class __CLASS__Controller extends MasterController
{
    protected string $model = __CLASS__::class;
    protected string $route = '__ROUTE__';
    protected string $title = '__CLASS__';
    protected array $columns = [
        'nama' => 'Nama __CLASS__',
        'alamat' => 'Alamat',
        'telepon' => 'Telepon',
        'email' => 'Email',
    ];
    protected array $search = ['nama', 'alamat', 'telepon', 'email'];

    protected function fields(?Model $row = null): array
    {
        return [
            ['name' => 'nama', 'label' => 'Nama __CLASS__', 'type' => 'text', 'rules' => 'required|string|max:255'],
            ['name' => 'alamat', 'label' => 'Alamat', 'type' => 'text', 'rules' => 'required|string|max:255'],
            ['name' => 'telepon', 'label' => 'Telepon', 'type' => 'text', 'rules' => 'required|string|max:30'],
            ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'rules' => 'nullable|email|max:255'],
        ];
    }
}
'@

foreach ($p in @(@('Supplier', 'suppliers'), @('Distributor', 'distributors'))) {
    Put ("app\Models\" + $p[0] + ".php") ($model.Replace('__CLASS__', $p[0]))
    Put ("app\Http\Controllers\" + $p[0] + "Controller.php") ($ctrl.Replace('__CLASS__', $p[0]).Replace('__ROUTE__', $p[1]))
}

# ---------- 3. Tampilan daftar dan form ----------
$c = @'
@extends('layouts.app')
@section('title', 'Data '.$title)
@section('content')
  <h2>Data {{ $title }}</h2>

  <form method="GET" class="bar">
    <input name="q" value="{{ $q }}" placeholder="Cari data">
    <button class="b">Cari</button>
    @if ($q)
      <a class="b" href="{{ route($route.'.index') }}">Reset</a>
    @endif
    <span class="sp"></span>
    <a class="b pr" href="{{ route($route.'.create') }}">+ Tambah {{ $title }}</a>
  </form>

  <div class="tw">
    <table>
      <thead>
        <tr>
          <th>No</th>
          @foreach ($columns as $label)
            <th>{{ $label }}</th>
          @endforeach
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($rows as $i => $row)
          <tr>
            <td>{{ $rows->firstItem() + $i }}</td>
            @foreach ($columns as $col => $label)
              <td>{{ $ctrl->cell($row, $col) }}</td>
            @endforeach
            <td>
              <a class="lk" href="{{ route($route.'.edit', $row->id) }}">Ubah</a> |
              <form method="POST" action="{{ route($route.'.destroy', $row->id) }}" style="display:inline" onsubmit="return confirm('Hapus data ini?')">
                @csrf
                @method('DELETE')
                <button class="lk">Hapus</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="{{ count($columns) + 2 }}" class="em">Belum ada data.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="pgr">
    <span>Menampilkan {{ $rows->count() }} dari {{ $rows->total() }} data</span>
    <span>
      <a class="b {{ $rows->onFirstPage() ? 'dis' : '' }}" href="{{ $rows->previousPageUrl() ?? '#' }}">&lsaquo;</a>
      <a class="b {{ $rows->hasMorePages() ? '' : 'dis' }}" href="{{ $rows->nextPageUrl() ?? '#' }}">&rsaquo;</a>
    </span>
  </div>
@endsection
'@
Put 'resources\views\master\index.blade.php' $c

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
        <label>{{ $f['label'] }}
          @if (($f['type'] ?? 'text') === 'select')
            <select name="{{ $f['name'] }}">
              <option value="">Pilih</option>
              @foreach ($f['options'] as $val => $text)
                <option value="{{ $val }}" @selected(old($f['name'], $row?->{$f['name']}) == $val)>{{ $text }}</option>
              @endforeach
            </select>
          @else
            <input type="{{ $f['type'] ?? 'text' }}" name="{{ $f['name'] }}" value="{{ old($f['name'], $row?->{$f['name']}) }}" placeholder="Masukkan {{ strtolower($f['label']) }}">
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

# ---------- 4. Layout ----------
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
    h2{margin:0 0 14px}
    .top{display:flex;justify-content:space-between;align-items:center;padding:12px 20px;background:#fff;border-bottom:1px solid #D5DBE2}
    .lk{background:none;border:0;padding:0;color:#2D3E50;text-decoration:underline;cursor:pointer;font:inherit}
    .app{display:grid;grid-template-columns:210px 1fr}
    .side{background:#2D3E50;padding:12px 0;min-height:calc(100vh - 53px)}
    .side a{display:block;padding:10px 20px;color:#C9D3DD;text-decoration:none;border-left:3px solid transparent}
    .side a.on{background:rgba(255,255,255,.1);border-left-color:#fff;color:#fff;font-weight:600}
    main{padding:24px;min-width:0}
    .card{background:#fff;border:1px solid #D5DBE2;border-radius:6px;padding:16px}
    .bar{display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin-bottom:12px}
    .sp{flex:1}
    input,select{font:inherit;color:inherit;background:#fff;border:1px solid #D5DBE2;border-radius:4px;padding:7px 10px;min-width:0}
    .b{display:inline-block;font:inherit;padding:7px 14px;border:1px solid #D5DBE2;border-radius:4px;background:#fff;color:#1B2733;text-decoration:none;cursor:pointer}
    .b.pr{background:#2D3E50;border-color:#2D3E50;color:#fff}
    .b.dis{opacity:.4;pointer-events:none}
    .tw{overflow-x:auto;background:#fff;border:1px solid #D5DBE2;border-radius:6px}
    table{border-collapse:collapse;width:100%}
    th,td{padding:9px 12px;text-align:left;border-bottom:1px solid #D5DBE2;white-space:nowrap}
    th{background:#F3F5F7;font-weight:600}
    tr:last-child td{border-bottom:0}
    .em{text-align:center;color:#667085}
    .pgr{display:flex;justify-content:space-between;align-items:center;margin-top:10px;color:#667085}
    .fg{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px;max-width:760px}
    label{display:flex;flex-direction:column;gap:4px;font-weight:500}
    label input,label select{font-weight:400}
    .acts{display:flex;gap:8px;margin-top:14px}
    .err{color:#B42318;font-weight:400}
    .flash{padding:10px 14px;border-radius:4px;margin-bottom:14px}
    .flash.ok{background:#E7F5EC;color:#166534;border:1px solid #B7E1C4}
    .flash.bad{background:#FDECEA;color:#B42318;border:1px solid #F5C2BD}
    @media(max-width:800px){.app{grid-template-columns:1fr}.side{display:flex;overflow-x:auto;min-height:0;padding:0}.side a{white-space:nowrap}main{padding:16px}}
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
    <main>
      @if (session('ok'))
        <div class="flash ok">{{ session('ok') }}</div>
      @endif
      @if (session('err'))
        <div class="flash bad">{{ session('err') }}</div>
      @endif
      @yield('content')
    </main>
  </div>
</body>
</html>
'@
Put 'resources\views\layouts\app.blade.php' $c

# ---------- 5. Rute ----------
$c = @'
<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DistributorController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

    // Hanya Admin
    Route::middleware('role:Admin')->group(function () {
        Route::resource('suppliers', SupplierController::class)->except('show');
        Route::resource('distributors', DistributorController::class)->except('show');
    });
});
'@
Put 'routes\web.php' $c

php artisan optimize:clear

Write-Host ''
Write-Host 'Selesai. Jalankan: php artisan serve' -ForegroundColor Green