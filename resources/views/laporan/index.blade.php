
@extends('layouts.app')

@section('title', 'Laporan Transaksi')

@section('content')
<style>
    .laporan-page {
        color: #35234d;
        animation: laporanMasuk .4s ease;
    }

    @keyframes laporanMasuk {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .laporan-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 26px;
        margin-bottom: 22px;
        color: white;
        border-radius: 20px;
        background: linear-gradient(120deg, #6d28d9, #9333ea, #c084fc);
        box-shadow: 0 9px 25px #7c3aed24;
    }

    .laporan-hero h2 {
        margin: 0 0 8px;
        font-size: 24px;
        font-weight: 800;
    }

    .laporan-hero p {
        margin: 0;
        font-size: 13px;
        line-height: 1.7;
        opacity: .92;
    }

    .laporan-icon {
        display: grid;
        place-items: center;
        min-width: 62px;
        height: 62px;
        border-radius: 18px;
        background: #ffffff29;
        font-size: 30px;
    }

    .laporan-cards {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 22px;
    }

    .laporan-stat {
        padding: 22px;
        background: white;
        border: 1px solid #eee7fa;
        border-radius: 17px;
        box-shadow: 0 5px 20px #35205b09;
        transition: transform .2s, box-shadow .2s;
    }

    .laporan-stat:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px #7c3aed17;
    }

    .laporan-stat span {
        color: #88779b;
        font-size: 12px;
        font-weight: 600;
    }

    .laporan-stat h3 {
        margin: 12px 0 7px;
        color: #7c3aed;
        font-size: 25px;
        font-weight: 800;
        overflow-wrap: anywhere;
    }

    .laporan-stat p {
        margin: 0;
        color: #a092b0;
        font-size: 12px;
    }

    .laporan-panel {
        margin-bottom: 22px;
        overflow: hidden;
        background: white;
        border: 1px solid #eee7fa;
        border-radius: 18px;
        box-shadow: 0 5px 20px #35205b09;
    }

    .laporan-panel-head {
        padding: 21px 23px;
        border-bottom: 1px solid #f0eaf7;
    }

    .laporan-panel-head h3 {
        margin: 0 0 6px;
        color: #48266d;
        font-size: 17px;
        font-weight: 800;
    }

    .laporan-panel-head p {
        margin: 0;
        color: #9485a5;
        font-size: 12px;
    }

    .laporan-filter {
        display: flex;
        align-items: end;
        flex-wrap: wrap;
        gap: 14px;
        padding: 22px;
    }

    .laporan-field {
        display: flex;
        flex: 1;
        min-width: 150px;
        flex-direction: column;
        gap: 8px;
    }

    .laporan-field label {
        color: #59466e;
        font-size: 12px;
        font-weight: 700;
    }

    .laporan-field input,
    .laporan-field select {
        box-sizing: border-box;
        width: 100%;
        min-width: 0;
        padding: 11px 12px;
        border: 1px solid #e5d9f2;
        border-radius: 10px;
        outline: none;
        background: #fdfbff;
        color: #38264d;
        font-size: 13px;
    }

    .laporan-field input:focus,
    .laporan-field select:focus {
        border-color: #a855f7;
        box-shadow: 0 0 0 3px #a855f71b;
    }

    .laporan-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 11px 15px;
        border: none;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        transition: transform .2s, filter .2s;
        white-space: nowrap;
    }

    .laporan-btn:hover {
        transform: translateY(-2px);
        filter: brightness(.97);
    }

    .laporan-primary {
        color: white;
        background: linear-gradient(120deg, #7c3aed, #a855f7);
        box-shadow: 0 4px 12px #7c3aed25;
    }

    .laporan-light {
        color: #6d28d9;
        background: #f3e8ff;
    }

    .laporan-pdf {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin: 18px 22px;
    }

    .laporan-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .laporan-table {
        width: 100%;
        border-collapse: collapse;
        white-space: nowrap;
    }

    .laporan-table th {
        padding: 14px 16px;
        text-align: left;
        color: #766588;
        background: #faf7ff;
        border-top: 1px solid #f0eaf7;
        border-bottom: 1px solid #f0eaf7;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .45px;
    }

    .laporan-table td {
        padding: 15px 16px;
        color: #59486b;
        border-bottom: 1px solid #f2edf8;
        font-size: 13px;
    }

    .laporan-table tbody tr {
        transition: background .2s;
    }

    .laporan-table tbody tr:hover {
        background: #fcf9ff;
    }

    .laporan-table tbody tr:last-child td {
        border-bottom: none;
    }

    .laporan-nomor {
        color: #7c3aed !important;
        font-weight: 800;
    }

    .laporan-masuk,
    .laporan-keluar {
        display: inline-block;
        padding: 6px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
    }

    .laporan-masuk {
        color: #166534;
        background: #dcfce7;
    }

    .laporan-keluar {
        color: #6d28d9;
        background: #f3e8ff;
    }

    .laporan-total {
        color: #6d28d9 !important;
        font-weight: 800;
    }

    .laporan-empty {
        padding: 38px 20px !important;
        text-align: center;
        color: #9586a6 !important;
    }

    .laporan-pagination {
        padding: 18px 22px;
        border-top: 1px solid #f0eaf7;
        color: #8c7d9c;
        font-size: 12px;
    }

    @media (max-width: 640px) {
        .laporan-hero {
            padding: 20px;
        }

        .laporan-hero h2 {
            font-size: 20px;
        }

        .laporan-icon {
            min-width: 48px;
            height: 48px;
            font-size: 23px;
        }

        .laporan-cards {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .laporan-stat {
            padding: 18px;
        }

        .laporan-stat h3 {
            font-size: 22px;
        }

        .laporan-filter {
            padding: 16px;
        }

        .laporan-field {
            flex-basis: 100%;
        }

        .laporan-pdf {
            margin: 16px;
        }

        .laporan-panel-head {
            padding: 18px;
        }
    }
</style>

<div class="laporan-page">

    {{-- Banner laporan --}}
    <div class="laporan-hero">
        <div>
            <h2>Laporan Transaksi</h2>
            <p>
                Pantau riwayat pembelian dan penjualan barang
                berdasarkan periode dan jenis transaksi.
            </p>
        </div>

        <div class="laporan-icon">📊</div>
    </div>

    {{-- Ringkasan transaksi --}}
    <div class="laporan-cards">
        <div class="laporan-stat">
            <span>Total Pembelian Barang</span>
            <h3>Rp {{ number_format($totalMasuk, 0, ',', '.') }}</h3>
            <p>Total nilai transaksi barang masuk sesuai filter.</p>
        </div>

        <div class="laporan-stat">
            <span>Total Penjualan Barang</span>
            <h3>Rp {{ number_format($totalKeluar, 0, ',', '.') }}</h3>
            <p>Total nilai transaksi barang keluar sesuai filter.</p>
        </div>
    </div>

    {{-- Filter laporan --}}
    <div class="laporan-panel">
        <div class="laporan-panel-head">
            <h3>Filter Laporan</h3>
            <p>Tentukan rentang tanggal dan jenis transaksi yang ingin dilihat.</p>
        </div>

        <form method="GET"
              action="{{ route('laporan.index') }}"
              class="laporan-filter">

            <div class="laporan-field">
                <label for="mulai">Dari Tanggal</label>
                <input
                    type="date"
                    id="mulai"
                    name="mulai"
                    value="{{ request('mulai') }}"
                >
            </div>

            <div class="laporan-field">
                <label for="sampai">Sampai Tanggal</label>
                <input
                    type="date"
                    id="sampai"
                    name="sampai"
                    value="{{ request('sampai') }}"
                >
            </div>

            <div class="laporan-field">
                <label for="jenis">Jenis Transaksi</label>
                <select id="jenis" name="jenis">
                    <option value="">Semua Transaksi</option>
                    <option value="masuk" @selected(request('jenis') === 'masuk')>
                        Barang Masuk
                    </option>
                    <option value="keluar" @selected(request('jenis') === 'keluar')>
                        Barang Keluar
                    </option>
                </select>
            </div>

            <button type="submit" class="laporan-btn laporan-primary">
                🔎 Terapkan Filter
            </button>

            <a href="{{ route('laporan.index') }}"
               class="laporan-btn laporan-light">
                Reset
            </a>
        </form>
    </div>

    {{-- Tabel laporan --}}
    <div class="laporan-panel">
        <div class="laporan-panel-head">
            <h3>Riwayat Transaksi</h3>
            <p>Daftar transaksi sesuai filter yang dipilih.</p>
        </div>

        <a
            href="{{ route('laporan.pdf', request()->query()) }}"
            class="laporan-btn laporan-primary laporan-pdf"
        >
            📄 Unduh Laporan PDF
        </a>

        <div class="laporan-table-wrap">
            <table class="laporan-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nomor Transaksi</th>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th>Total</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($transaksi as $i => $row)
                        <tr>
                            <td>{{ $transaksi->firstItem() + $i }}</td>

                            <td class="laporan-nomor">
                                {{ $row->nomor }}
                            </td>

                            <td>
                                {{ \Illuminate\Support\Carbon::parse($row->tanggal)->format('d/m/Y') }}
                            </td>

                            <td>
                                @if ($row->jenis === 'masuk')
                                    <span class="laporan-masuk">Barang Masuk</span>
                                @else
                                    <span class="laporan-keluar">Barang Keluar</span>
                                @endif
                            </td>

                            <td class="laporan-total">
                                Rp {{ number_format((float) $row->total, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="laporan-empty">
                                <div style="font-size: 30px; margin-bottom: 10px;">
                                    📂
                                </div>
                                <strong>Belum ada data transaksi</strong>
                                <p style="margin: 7px 0 0;">
                                    Coba ubah rentang tanggal atau jenis transaksi.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="laporan-pagination">
            <div style="margin-bottom: 12px;">
                Menampilkan {{ $transaksi->count() }}
                dari {{ $transaksi->total() }} data transaksi
            </div>

            {{ $transaksi->appends(request()->query())->links() }}
        </div>
    </div>

</div>
@endsection
