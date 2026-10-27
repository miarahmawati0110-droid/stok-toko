
@extends('layouts.app')

@section('title', 'Transaksi Barang ' . ($jenis === 'masuk' ? 'Masuk' : 'Keluar'))

@section('content')
<style>
    .trx-page {
        color: #35234d;
        animation: trxEnter .4s ease;
    }

    @keyframes trxEnter {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .trx-hero {
        padding: 25px;
        color: white;
        border-radius: 20px;
        background: linear-gradient(120deg, #6d28d9, #9333ea, #c084fc);
        box-shadow: 0 9px 25px #7c3aed24;
        margin-bottom: 22px;
    }

    .trx-hero h2 {
        margin: 0 0 8px;
        font-size: 24px;
        font-weight: 800;
    }

    .trx-hero p {
        margin: 0;
        font-size: 13px;
        opacity: .9;
    }

    .trx-badge {
        display: inline-block;
        margin-bottom: 12px;
        padding: 6px 11px;
        border-radius: 30px;
        background: #ffffff27;
        font-size: 12px;
        font-weight: 700;
    }

    .trx-card {
        margin-bottom: 20px;
        overflow: hidden;
        background: white;
        border: 1px solid #eee7fa;
        border-radius: 18px;
        box-shadow: 0 5px 20px #35205b09;
    }

    .trx-card-head {
        padding: 19px 22px;
        border-bottom: 1px solid #f0eaf7;
    }

    .trx-card-head h3 {
        margin: 0 0 5px;
        color: #48266d;
        font-size: 17px;
        font-weight: 800;
    }

    .trx-card-head p {
        margin: 0;
        color: #9485a5;
        font-size: 12px;
    }

    .trx-fields {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
        padding: 22px;
    }

    .trx-fields label {
        display: flex;
        flex-direction: column;
        gap: 8px;
        color: #59466e;
        font-size: 13px;
        font-weight: 700;
    }

    .trx-page input,
    .trx-page select {
        box-sizing: border-box;
        width: 100%;
        min-width: 0;
        padding: 11px 12px;
        border: 1px solid #e5d9f2;
        border-radius: 10px;
        outline: none;
        background: #fdfbff;
        color: #38264d;
        font: inherit;
        font-size: 13px;
        transition: border-color .2s, box-shadow .2s;
    }

    .trx-page input:focus,
    .trx-page select:focus {
        border-color: #a855f7;
        box-shadow: 0 0 0 3px #a855f71b;
    }

    .trx-page input:disabled {
        background: #f3e8ff;
        color: #7e22ce;
        font-weight: 800;
    }

    .trx-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .trx-table {
        width: 100%;
        border-collapse: collapse;
        white-space: nowrap;
    }

    .trx-table th {
        padding: 14px;
        text-align: left;
        color: #725b89;
        background: #faf7ff;
        border-bottom: 1px solid #eee5f7;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    .trx-table td {
        padding: 12px;
        border-bottom: 1px solid #f0eaf7;
        font-size: 13px;
    }

    .trx-table tbody tr:hover {
        background: #fdfaff;
    }

    .trx-table input,
    .trx-table select {
        min-width: 110px;
    }

    .trx-table .stk,
    .trx-table .hrgtxt {
        color: #7c3aed;
        font-weight: 700;
    }

    .trx-table .sub {
        color: #6d28d9;
        font-weight: 800;
    }

    .trx-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 10px 15px;
        border: 0;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        transition: transform .2s, filter .2s;
    }

    .trx-btn:hover {
        transform: translateY(-2px);
        filter: brightness(.97);
    }

    .trx-primary {
        color: white;
        background: linear-gradient(120deg, #7c3aed, #a855f7);
        box-shadow: 0 4px 12px #7c3aed25;
    }

    .trx-light {
        color: #6d28d9;
        background: #f3e8ff;
    }

    .trx-danger {
        color: #b91c1c;
        background: #fee2e2;
        padding: 8px 11px;
    }

    .trx-toolbar {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        padding: 16px 22px;
    }

    .trx-spacer {
        flex: 1;
    }

    .trx-total {
        padding: 12px 17px;
        border: 1px solid #e9d5ff;
        border-radius: 12px;
        color: #6d28d9;
        background: #faf5ff;
        font-size: 17px;
        font-weight: 800;
    }

    .trx-actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 20px;
    }

    .trx-error {
        margin-bottom: 18px;
        padding: 15px 18px;
        color: #991b1b;
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 12px;
        font-size: 13px;
    }

    .trx-error div + div {
        margin-top: 5px;
    }

    @media (max-width: 600px) {
        .trx-hero {
            padding: 20px;
        }

        .trx-hero h2 {
            font-size: 20px;
        }

        .trx-fields {
            grid-template-columns: 1fr;
            padding: 16px;
            gap: 15px;
        }

        .trx-card-head {
            padding: 17px;
        }

        .trx-toolbar {
            padding: 14px;
        }

        .trx-total {
            width: 100%;
            box-sizing: border-box;
            text-align: center;
        }

        .trx-actions .trx-btn {
            flex: 1;
        }
    }
</style>

<div class="trx-page">

    <div class="trx-hero">
        <span class="trx-badge">
            {{ $jenis === 'masuk' ? '📥 TRANSAKSI PEMBELIAN' : '📤 TRANSAKSI PENJUALAN' }}
        </span>

        <h2>
            {{ $jenis === 'masuk' ? 'Barang Masuk' : 'Barang Keluar' }}
        </h2>

        <p>
            {{ $jenis === 'masuk'
                ? 'Catat penerimaan barang dari supplier dan perbarui stok toko.'
                : 'Catat barang yang keluar dan pastikan jumlahnya sesuai stok.' }}
        </p>
    </div>

    @if ($errors->any())
        <div class="trx-error">
            <strong>Periksa kembali data berikut:</strong>
            @foreach ($errors->all() as $pesan)
                <div>• {{ $pesan }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route($jenis . '.store') }}">
        @csrf

        <div class="trx-card">
            <div class="trx-card-head">
                <h3>Informasi Transaksi</h3>
                <p>Lengkapi nomor dokumen, tanggal, dan pihak terkait.</p>
            </div>

            <div class="trx-fields">
                <label>
                    {{ $jenis === 'masuk' ? 'Nomor Faktur' : 'Nomor Nota' }}
                    <input value="{{ $nomor }}" disabled>
                </label>

                <label>
                    Tanggal Transaksi
                    <input
                        type="date"
                        name="tanggal"
                        value="{{ old('tanggal', now()->toDateString()) }}"
                        required
                    >
                </label>

                @if ($jenis === 'masuk')
                    <label>
                        Supplier
                        <select name="pihak" required>
                            <option value="">Pilih supplier</option>
                            @foreach ($suppliers as $s)
                                <option
                                    value="{{ $s->id }}"
                                    @selected(old('pihak') == $s->id)
                                >
                                    {{ $s->nama }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                @else
                    <label>
                        Jenis Pembeli
                        <select name="jenis_pembeli" id="jp">
                            <option
                                value="distributor"
                                @selected(old('jenis_pembeli', 'distributor') === 'distributor')
                            >
                                Distributor
                            </option>
                            <option
                                value="agent"
                                @selected(old('jenis_pembeli') === 'agent')
                            >
                                Agen
                            </option>
                        </select>
                    </label>

                    <label>
                        Nama Pembeli
                        <select name="pihak" id="pihak" required>
                            <option value="">Pilih pembeli</option>
                        </select>
                    </label>
                @endif
            </div>
        </div>

        <div class="trx-card">
            <div class="trx-card-head">
                <h3>Detail Barang</h3>
                <p>Pilih barang dan isi jumlah sesuai transaksi.</p>
            </div>

            <div class="trx-table-wrap">
                <table class="trx-table">
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

            <div class="trx-toolbar">
                <button type="button" class="trx-btn trx-light" id="add">
                    + Tambah Barang
                </button>

                <span class="trx-spacer"></span>

                <div class="trx-total" id="total">Total: Rp 0</div>
            </div>
        </div>

        <div class="trx-actions">
            <a class="trx-btn trx-light" href="{{ route($jenis . '.index') }}">
                Batal
            </a>

            <button type="submit" class="trx-btn trx-primary">
                {{ $jenis === 'masuk' ? '✓ Simpan Transaksi' : '✓ Simpan dan Cetak Nota' }}
            </button>
        </div>
    </form>
</div>

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
            + '<td><button type="button" class="trx-btn trx-danger rm">Hapus</button></td>';
    }

    function calc(tr) {
        const select = tr.querySelector('.it');
        const it = items.find(x => String(x.id) === select.value);
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
        tbody.querySelectorAll('tr').forEach(tr => {
            t += calc(tr);
        });
        document.getElementById('total').textContent = 'Total: ' + fmt(t);
    }

    function addRow(d) {
        d = d || {};

        const tr = document.createElement('tr');
        tr.innerHTML = rowHtml(idx++);

        const sel = tr.querySelector('.it');
        sel.add(new Option('Pilih barang', ''));

        items.forEach(x => {
            sel.add(new Option(x.kode + ' - ' + x.nama, x.id));
        });

        if (d.item_id) sel.value = d.item_id;
        if (d.jumlah) tr.querySelector('.qty').value = d.jumlah;

        if (jenis === 'masuk' && d.harga !== undefined) {
            tr.querySelector('.hrg').value = d.harga;
        }

        tbody.appendChild(tr);
        total();
    }

    tbody.addEventListener('input', total);

    tbody.addEventListener('change', function (e) {
        if (jenis === 'masuk' && e.target.classList.contains('it')) {
            const it = items.find(x => String(x.id) === e.target.value);
            if (it) {
                e.target.closest('tr').querySelector('.hrg').value = it.harga;
            }
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

        const fill = selected => {
            pihak.innerHTML = '';
            pihak.add(new Option('Pilih pembeli', ''));

            (parties[jp.value] || []).forEach(p => {
                pihak.add(new Option(p.nama, p.id));
            });

            if (selected) pihak.value = selected;
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
