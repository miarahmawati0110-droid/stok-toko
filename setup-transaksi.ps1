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

class Transaction extends Model
{
    protected $fillable = [
        'nomor', 'tanggal', 'jenis', 'supplier_id', 'distributor_id',
        'agent_id', 'user_id', 'total',
    ];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }

    public function details()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function distributor()
    {
        return $this->belongsTo(Distributor::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getPihakNamaAttribute(): string
    {
        if ($this->jenis === 'masuk') {
            return $this->supplier?->nama ?? '-';
        }

        return $this->agent?->nama ?? $this->distributor?->nama ?? '-';
    }

    public function getJenisPembeliAttribute(): ?string
    {
        if ($this->jenis !== 'keluar') {
            return null;
        }

        return $this->agent_id ? 'Agen' : 'Distributor';
    }
}
'@
Put 'app\Models\Transaction.php' $c

$c = @'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{
    protected $fillable = ['transaction_id', 'item_id', 'jumlah', 'harga', 'subtotal'];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
'@
Put 'app\Models\TransactionDetail.php' $c

# ---------- Controller ----------
$c = @'
<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Distributor;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function index(Request $request, string $jenis)
    {
        $q = trim((string) $request->query('q'));

        $rows = Transaction::with(['supplier', 'distributor', 'agent', 'user'])
            ->where('jenis', $jenis)
            ->when($q !== '', fn ($w) => $w->where('nomor', 'like', "%{$q}%"))
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('transaksi.index', compact('rows', 'q', 'jenis'));
    }

    public function create(string $jenis)
    {
        $masuk = $jenis === 'masuk';

        $items = Item::query()
            ->when(! $masuk, fn ($q) => $q->where('stok', '>', 0))
            ->orderBy('nama')
            ->get(['id', 'kode', 'nama', 'satuan', 'harga', 'stok']);

        return view('transaksi.form', [
            'jenis' => $jenis,
            'nomor' => $this->nextNomor($jenis),
            'items' => $items,
            'suppliers' => Supplier::orderBy('nama')->get(['id', 'nama']),
            'parties' => [
                'distributor' => Distributor::orderBy('nama')->get(['id', 'nama']),
                'agent' => Agent::orderBy('nama')->get(['id', 'nama']),
            ],
        ]);
    }

    public function store(Request $request, string $jenis)
    {
        $masuk = $jenis === 'masuk';

        $rules = [
            'tanggal' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:items,id',
            'items.*.jumlah' => 'required|integer|min:1',
        ];

        if ($masuk) {
            $rules['pihak'] = 'required|exists:suppliers,id';
            $rules['items.*.harga'] = 'required|integer|min:0';
        } else {
            $rules['jenis_pembeli'] = 'required|in:distributor,agent';
            $rules['pihak'] = 'required|exists:'.($request->input('jenis_pembeli') === 'agent' ? 'agents' : 'distributors').',id';
        }

        $data = $request->validate($rules, [
            'tanggal.required' => 'Tanggal wajib diisi.',
            'pihak.required' => $masuk ? 'Pilih supplier.' : 'Pilih nama pembeli.',
            'pihak.exists' => $masuk ? 'Supplier tidak valid.' : 'Pembeli tidak valid.',
            'items.required' => 'Tambahkan minimal satu barang.',
            'items.*.item_id.required' => 'Pilih barang pada setiap baris.',
            'items.*.jumlah.required' => 'Jumlah wajib diisi pada setiap baris.',
            'items.*.jumlah.min' => 'Jumlah minimal 1.',
        ]);

        // Gabungkan baris barang yang sama
        $lines = [];
        foreach ($data['items'] as $row) {
            $id = (int) $row['item_id'];
            $lines[$id]['jumlah'] = ($lines[$id]['jumlah'] ?? 0) + (int) $row['jumlah'];
            if ($masuk) {
                $lines[$id]['harga'] = (int) $row['harga'];
            }
        }

        // Simpan transaksi dan ubah stok dalam satu database transaction
        $transaction = DB::transaction(function () use ($lines, $data, $jenis, $masuk) {
            $items = Item::whereIn('id', array_keys($lines))->lockForUpdate()->get()->keyBy('id');

            $total = 0;
            foreach ($lines as $id => $line) {
                $item = $items[$id];

                if (! $masuk && $item->stok < $line['jumlah']) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$item->nama} tidak cukup. Tersedia {$item->stok} {$item->satuan}, diminta {$line['jumlah']}.",
                    ]);
                }

                $lines[$id]['harga'] = $masuk ? $line['harga'] : $item->harga;
                $lines[$id]['subtotal'] = $line['jumlah'] * $lines[$id]['harga'];
                $total += $lines[$id]['subtotal'];
            }

            $transaction = Transaction::create([
                'nomor' => $this->nextNomor($jenis),
                'tanggal' => $data['tanggal'],
                'jenis' => $jenis,
                'supplier_id' => $masuk ? $data['pihak'] : null,
                'distributor_id' => (! $masuk && $data['jenis_pembeli'] === 'distributor') ? $data['pihak'] : null,
                'agent_id' => (! $masuk && $data['jenis_pembeli'] === 'agent') ? $data['pihak'] : null,
                'user_id' => auth()->id(),
                'total' => $total,
            ]);

            foreach ($lines as $id => $line) {
                $transaction->details()->create([
                    'item_id' => $id,
                    'jumlah' => $line['jumlah'],
                    'harga' => $line['harga'],
                    'subtotal' => $line['subtotal'],
                ]);

                if ($masuk) {
                    $items[$id]->increment('stok', $line['jumlah']);
                } else {
                    $items[$id]->decrement('stok', $line['jumlah']);
                }
            }

            return $transaction;
        });

        $params = ['transaction' => $transaction->id];
        if (! $masuk) {
            $params['cetak'] = 1;
        }

        return redirect()->route($jenis.'.show', $params)->with(
            'ok',
            ($masuk ? 'Faktur ' : 'Nota ').$transaction->nomor.' berhasil disimpan, stok barang sudah diperbarui.'
        );
    }

    public function show(string $jenis, Transaction $transaction)
    {
        abort_unless($transaction->jenis === $jenis, 404);

        $transaction->load(['details.item', 'supplier', 'distributor', 'agent', 'user']);

        return view('transaksi.show', compact('jenis', 'transaction'));
    }

    private function nextNomor(string $jenis): string
    {
        $prefix = $jenis === 'masuk' ? 'PB-' : 'PJ-';
        $last = Transaction::where('jenis', $jenis)->orderByDesc('id')->value('nomor');
        $n = $last ? ((int) substr($last, 3)) + 1 : 1;

        return $prefix.str_pad((string) $n, 4, '0', STR_PAD_LEFT);
    }
}
'@
Put 'app\Http\Controllers\TransactionController.php' $c

# ---------- Tampilan: riwayat ----------
$c = @'
@extends('layouts.app')
@section('title', 'Riwayat Barang '.($jenis === 'masuk' ? 'Masuk' : 'Keluar'))
@section('content')
  <h2>Riwayat Barang {{ $jenis === 'masuk' ? 'Masuk' : 'Keluar' }}</h2>

  <form method="GET" class="bar">
    <input name="q" value="{{ $q }}" placeholder="Cari nomor">
    <button class="b">Cari</button>
    @if ($q)
      <a class="b" href="{{ route($jenis.'.index') }}">Reset</a>
    @endif
    <span class="sp"></span>
    <a class="b pr" href="{{ route($jenis.'.create') }}">+ Transaksi Baru</a>
  </form>

  <div class="tw">
    <table>
      <thead>
        <tr>
          <th>No</th>
          <th>{{ $jenis === 'masuk' ? 'No. Faktur' : 'No. Nota' }}</th>
          <th>Tanggal</th>
          @if ($jenis === 'keluar')
            <th>Jenis Pembeli</th>
          @endif
          <th>{{ $jenis === 'masuk' ? 'Supplier' : 'Nama Pembeli' }}</th>
          <th>Total</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($rows as $i => $row)
          <tr>
            <td>{{ $rows->firstItem() + $i }}</td>
            <td>{{ $row->nomor }}</td>
            <td>{{ $row->tanggal->format('d/m/Y') }}</td>
            @if ($jenis === 'keluar')
              <td>{{ $row->jenis_pembeli }}</td>
            @endif
            <td>{{ $row->pihak_nama }}</td>
            <td>Rp {{ number_format($row->total, 0, ',', '.') }}</td>
            <td>
              <a class="lk" href="{{ route($jenis.'.show', ['transaction' => $row->id]) }}">Detail</a> |
              <a class="lk" href="{{ route($jenis.'.show', ['transaction' => $row->id, 'cetak' => 1]) }}">Cetak</a>
            </td>
          </tr>
        @empty
          <tr><td colspan="{{ $jenis === 'keluar' ? 7 : 6 }}" class="em">Belum ada transaksi.</td></tr>
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
Put 'resources\views\transaksi\index.blade.php' $c

# ---------- Tampilan: form transaksi ----------
$c = @'
@extends('layouts.app')
@section('title', 'Transaksi Barang '.($jenis === 'masuk' ? 'Masuk' : 'Keluar'))
@section('content')
  <h2>Transaksi Barang {{ $jenis === 'masuk' ? 'Masuk (Pembelian)' : 'Keluar (Penjualan)' }}</h2>

  @if ($errors->any())
    <div class="flash bad">
      @foreach ($errors->all() as $pesan)
        <div>{{ $pesan }}</div>
      @endforeach
    </div>
  @endif

  <form method="POST" action="{{ route($jenis.'.store') }}">
    @csrf

    <div class="card fg">
      <label>{{ $jenis === 'masuk' ? 'No. Faktur' : 'No. Nota' }}
        <input value="{{ $nomor }}" disabled>
      </label>
      <label>Tanggal
        <input type="date" name="tanggal" value="{{ old('tanggal', now()->toDateString()) }}" required>
      </label>

      @if ($jenis === 'masuk')
        <label>Supplier
          <select name="pihak" required>
            <option value="">Pilih supplier</option>
            @foreach ($suppliers as $s)
              <option value="{{ $s->id }}" @selected(old('pihak') == $s->id)>{{ $s->nama }}</option>
            @endforeach
          </select>
        </label>
      @else
        <label>Jenis Pembeli
          <select name="jenis_pembeli" id="jp">
            <option value="distributor" @selected(old('jenis_pembeli', 'distributor') === 'distributor')>Distributor</option>
            <option value="agent" @selected(old('jenis_pembeli') === 'agent')>Agen</option>
          </select>
        </label>
        <label>Nama Pembeli
          <select name="pihak" id="pihak" required></select>
        </label>
      @endif
    </div>

    <h3 style="margin:18px 0 8px">Detail Barang</h3>
    <div class="tw">
      <table>
        <thead>
          <tr>
            <th>Barang</th>
            @if ($jenis === 'keluar')
              <th>Stok</th>
            @endif
            <th>Jumlah</th>
            <th>{{ $jenis === 'masuk' ? 'Harga Beli' : 'Harga Jual' }}</th>
            <th>Subtotal</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody id="rows"></tbody>
      </table>
    </div>

    <div class="bar" style="margin-top:12px">
      <button type="button" class="b" id="add">+ Tambah Barang</button>
      <span class="sp"></span>
      <b class="tot" id="total">Total: Rp 0</b>
    </div>

    <div class="acts">
      <a class="b" href="{{ route($jenis.'.index') }}">Batal</a>
      <button class="b pr">{{ $jenis === 'masuk' ? 'Simpan' : 'Simpan dan Cetak Nota' }}</button>
    </div>
  </form>

  @push('scripts')
  <script>
  (function () {
    const jenis = @json($jenis);
    const items = @json($items);
    const parties = @json($parties);
    const oldItems = Object.values(@json(old('items', [])));
    const oldPihak = @json(old('pihak'));

    const tbody = document.getElementById('rows');
    const fmt = n => 'Rp ' + Math.round(Number(n) || 0).toLocaleString('id-ID');
    let idx = 0;

    function rowHtml(i) {
      return '<td><select name="items[' + i + '][item_id]" class="it" required></select></td>'
        + (jenis === 'keluar' ? '<td class="stk">-</td>' : '')
        + '<td><input type="number" min="1" name="items[' + i + '][jumlah]" class="qty" value="1" required></td>'
        + (jenis === 'masuk'
            ? '<td><input type="number" min="0" name="items[' + i + '][harga]" class="hrg" value="0" required></td>'
            : '<td class="hrgtxt">-</td>')
        + '<td class="sub">Rp 0</td>'
        + '<td><button type="button" class="lk rm">Hapus</button></td>';
    }

    function calc(tr) {
      const it = items.find(x => String(x.id) === tr.querySelector('.it').value);
      const q = Number(tr.querySelector('.qty').value) || 0;
      let h;
      if (jenis === 'masuk') {
        h = Number(tr.querySelector('.hrg').value) || 0;
      } else {
        h = it ? Number(it.harga) : 0;
        tr.querySelector('.hrgtxt').textContent = it ? fmt(h) : '-';
        tr.querySelector('.stk').textContent = it ? it.stok + ' ' + it.satuan : '-';
      }
      tr.querySelector('.sub').textContent = fmt(q * h);
      return q * h;
    }

    function total() {
      let t = 0;
      tbody.querySelectorAll('tr').forEach(tr => { t += calc(tr); });
      document.getElementById('total').textContent = 'Total: ' + fmt(t);
    }

    function addRow(d) {
      d = d || {};
      const tr = document.createElement('tr');
      tr.innerHTML = rowHtml(idx++);
      const sel = tr.querySelector('.it');
      sel.add(new Option('Pilih barang', ''));
      items.forEach(x => sel.add(new Option(x.kode + ' - ' + x.nama, x.id)));
      if (d.item_id) sel.value = d.item_id;
      if (d.jumlah) tr.querySelector('.qty').value = d.jumlah;
      if (jenis === 'masuk' && d.harga !== undefined) tr.querySelector('.hrg').value = d.harga;
      tbody.appendChild(tr);
      total();
    }

    tbody.addEventListener('input', total);
    tbody.addEventListener('change', function (e) {
      if (jenis === 'masuk' && e.target.classList.contains('it')) {
        const it = items.find(x => String(x.id) === e.target.value);
        if (it) e.target.closest('tr').querySelector('.hrg').value = it.harga;
      }
      total();
    });
    tbody.addEventListener('click', function (e) {
      if (e.target.classList.contains('rm')) {
        e.target.closest('tr').remove();
        if (!tbody.children.length) addRow();
        total();
      }
    });
    document.getElementById('add').addEventListener('click', () => addRow());

    if (jenis === 'keluar') {
      const jp = document.getElementById('jp');
      const pihak = document.getElementById('pihak');
      const fill = sel => {
        pihak.innerHTML = '';
        pihak.add(new Option('Pilih pembeli', ''));
        (parties[jp.value] || []).forEach(p => pihak.add(new Option(p.nama, p.id)));
        if (sel) pihak.value = sel;
      };
      jp.addEventListener('change', () => fill(''));
      fill(oldPihak);
    }

    if (oldItems.length) {
      oldItems.forEach(r => addRow(r));
    } else {
      addRow();
    }
  })();
  </script>
  @endpush
@endsection
'@
Put 'resources\views\transaksi\form.blade.php' $c

# ---------- Tampilan: faktur / nota ----------
$c = @'
@extends('layouts.app')
@section('title', ($jenis === 'masuk' ? 'Faktur ' : 'Nota ').$transaction->nomor)
@section('content')
  <div class="card sheet">
    <h3 class="ctr">{{ config('app.name') }}</h3>
    <h2 class="ctr">{{ $jenis === 'masuk' ? 'FAKTUR PEMBELIAN' : 'NOTA PENJUALAN' }}</h2>

    <div class="meta">
      <div>
        {{ $jenis === 'masuk' ? 'No. Faktur' : 'No. Nota' }} : {{ $transaction->nomor }}<br>
        Tanggal : {{ $transaction->tanggal->format('d/m/Y') }}
      </div>
      <div>
        {{ $jenis === 'masuk' ? 'Supplier' : 'Pembeli' }} : {{ $transaction->pihak_nama }}@if ($jenis === 'keluar') ({{ $transaction->jenis_pembeli }})@endif<br>
        Petugas : {{ $transaction->user?->nama }}
      </div>
    </div>

    <div class="tw">
      <table>
        <thead>
          <tr>
            <th>No</th>
            <th>Nama Barang</th>
            <th>Jumlah</th>
            <th>{{ $jenis === 'masuk' ? 'Harga Beli' : 'Harga Jual' }}</th>
            <th>Subtotal</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($transaction->details as $i => $d)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td>{{ $d->item?->nama }}</td>
              <td>{{ $d->jumlah }} {{ $d->item?->satuan }}</td>
              <td>Rp {{ number_format($d->harga, 0, ',', '.') }}</td>
              <td>Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <p class="tot">Total: Rp {{ number_format($transaction->total, 0, ',', '.') }}</p>

    <div class="sig">
      <div>Dibuat oleh,<br><br><br>( {{ $transaction->user?->nama }} )</div>
      <div>Diterima oleh,<br><br><br>( ............. )</div>
    </div>
  </div>

  <div class="acts noprint">
    <a class="b" href="{{ route($jenis.'.index') }}">Kembali</a>
    <button type="button" class="b pr" onclick="window.print()">Cetak</button>
  </div>

  @if (request()->boolean('cetak'))
    @push('scripts')
      <script>window.addEventListener('load', function () { window.print(); });</script>
    @endpush
  @endif
@endsection
'@
Put 'resources\views\transaksi\show.blade.php' $c

# ---------- Layout (gaya faktur, cetak, dan @stack scripts) ----------
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
    td input[type=number]{width:110px}
    td select{width:100%;min-width:200px}
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
    .tot{text-align:right;font-weight:600;font-size:16px;margin:12px 0}
    .meta,.sig{display:flex;justify-content:space-between;gap:16px;margin:14px 0}
    .sig{margin-top:28px;text-align:center}
    .ctr{text-align:center;margin:0 0 4px}
    .sheet{max-width:820px}
    @media(max-width:800px){.app{grid-template-columns:1fr}.side{display:flex;overflow-x:auto;min-height:0;padding:0}.side a{white-space:nowrap}main{padding:16px}}
    @media print{.top,.side,.noprint,.flash{display:none!important}.app{display:block}body{background:#fff;color:#000}main{padding:0}.card{border:0}}
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

  @stack('scripts')
</body>
</html>
'@
Put 'resources\views\layouts\app.blade.php' $c

# ---------- Rute ----------
$c = @'
<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DistributorController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransactionController;
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

    // Barang masuk: Admin dan Gudang. Barang keluar: Admin dan Kasir.
    foreach (['masuk' => 'Admin,Gudang', 'keluar' => 'Admin,Kasir'] as $jenis => $roles) {
        Route::middleware('role:'.$roles)
            ->prefix('barang-'.$jenis)
            ->name($jenis.'.')
            ->group(function () use ($jenis) {
                Route::get('/', [TransactionController::class, 'index'])->defaults('jenis', $jenis)->name('index');
                Route::get('/create', [TransactionController::class, 'create'])->defaults('jenis', $jenis)->name('create');
                Route::post('/', [TransactionController::class, 'store'])->defaults('jenis', $jenis)->name('store');
                Route::get('/{transaction}', [TransactionController::class, 'show'])->defaults('jenis', $jenis)->name('show');
            });
    }
});
'@
Put 'routes\web.php' $c

php artisan optimize:clear

Write-Host ''
Write-Host 'Selesai. Muat ulang browser (F5).' -ForegroundColor Green