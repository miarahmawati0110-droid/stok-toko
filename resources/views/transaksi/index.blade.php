
@extends('layouts.app')

@section('title', 'Data Barang ' . ($jenis === 'masuk' ? 'Masuk' : 'Keluar'))

@section('content')
<style>
    .trx-list {
        color: #35234d;
        animation: trxListEnter .4s ease;
    }

    @keyframes trxListEnter {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .trx-list-hero {
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

    .trx-list-hero h2 {
        margin: 0 0 8px;
        font-size: 24px;
        font-weight: 800;
    }

    .trx-list-hero p {
        margin: 0;
        font-size: 13px;
        line-height: 1.7;
        opacity: .92;
    }

    .trx-list-icon {
        display: grid;
        place-items: center;
        min-width: 62px;
        height: 62px;
        border-radius: 18px;
        background: #ffffff29;
        font-size: 30px;
    }

    .trx-list-stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 22px;
    }

    .trx-list-stat {
        padding: 20px;
        background: white;
        border: 1px solid #eee7fa;
        border-radius: 16px;
        box-shadow: 0 5px 20px #35205b09;
        transition: transform .2s, box-shadow .2s;
    }

    .trx-list-stat:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px #7c3aed17;
    }

    .trx-list-stat span {
        color: #88779b;
        font-size: 12px;
        font-weight: 600;
    }

    .trx-list-stat h3 {
        margin: 9px 0 5px;
        color: #7c3aed;
        font-size: 26px;
        font-weight: 800;
    }

    .trx-list-stat p {
        margin: 0;
        color: #a092b0;
        font-size: 12px;
    }

    .trx-list-panel {
        overflow: hidden;
        background: white;
        border: 1px solid #eee7fa;
        border-radius: 18px;
        box-shadow: 0 5px 20px #35205b09;
    }

    .trx-list-panel-head {
        padding: 21px 23px;
        border-bottom: 1px solid #f0eaf7;
    }

    .trx-list-panel-head h3 {
        margin: 0 0 6px;
        color: #48266d;
        font-size: 17px;
        font-weight: 800;
    }

    .trx-list-panel-head p {
        margin: 0;
        color: #9485a5;
        font-size: 12px;
    }

    .trx-list-toolbar {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        padding: 17px 22px;
    }

    .trx-list-search {
        display: flex;
        flex: 1;
        max-width: 430px;
        gap: 8px;
    }

    .trx-list-search input {
        box-sizing: border-box;
        width: 100%;
        min-width: 0;
        padding: 11px 13px;
        border: 1px solid #e5d9f2;
        border-radius: 10px;
        outline: none;
        color: #38264d;
        background: #fdfbff;
        font-size: 13px;
    }

    .trx-list-search input:focus {
        border-color: #a855f7;
        box-shadow: 0 0 0 3px #a855f71b;
    }

    .trx-list-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 10px 14px;
        border: none;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        transition: transform .2s, filter .2s;
        white-space: nowrap;
    }

    .trx-list-btn:hover {
        transform: translateY(-2px);
        filter: brightness(.97);
    }

    .trx-list-primary {
        color: white;
        background: linear-gradient(120deg, #7c3aed, #a855f7);
        box-shadow: 0 4px 12px #7c3aed25;
    }

    .trx-list-light {
        color: #6d28d9;
        background: #f3e8ff;
    }

    .trx-list-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .trx-list-table {
        width: 100%;
        border-collapse: collapse;
        white-space: nowrap;
    }

    .trx-list-table th {
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

    .trx-list-table td {
        padding: 15px 16px;
        color: #59486b;
        border-bottom: 1px solid #f2edf8;
        font-size: 13px;
    }

    .trx-list-table tbody tr {
        transition: background .2s;
    }

    .trx-list-table tbody tr:hover {
        background: #fcf9ff;
    }

    .trx-list-table tbody tr:last-child td {
        border-bottom: none;
    }

    .trx-list-number {
        color: #7c3aed !important;
        font-weight: 800;
    }

    .trx-list-type {
        display: inline-block;
        padding: 6px 10px;
        border-radius: 20px;
        color: #6d28d9;
        background: #f3e8ff;
        font-size: 11px;
        font-weight: 700;
    }

    .trx-list-total {
        color: #6d28d9 !important;
        font-weight: 800;
    }

    .trx-list-actions {
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .trx-list-empty {
        padding: 38px 20px !important;
        text-align: center;
        color: #9586a6 !important;
    }

    .trx-list-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        padding: 18px 22px;
        border-top: 1px solid #f0eaf7;
        color: #8c7d9c;
        font-size: 12px;
    }

    .trx-list-pagination-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .trx-list-btn.disabled {
        opacity: .45;
        pointer-events: none;
    }

    @media (max-width: 640px) {
        .trx-list-hero {
            padding: 20px;
        }

        .trx-list-hero h2 {
            font-size: 20px;
        }

        .trx-list-icon {
            min-width: 48px;
            height: 48px;
            font-size: 23px;
        }

        .trx-list-stats {
            gap: 10px;
        }

        .trx-list-stat {
            padding: 15px;
        }

        .trx-list-stat h3 {
            font-size: 22px;
        }

        .trx-list-toolbar {
            padding: 14px;
        }

        .trx-list-search {
            max-width: none;
            flex-basis: 100%;
        }

        .trx-list-pagination {
            padding: 15px;
        }
    }
</style>

@php
    $barangMasuk = $jenis === 'masuk';
@endphp

<div class="trx-list">

    {{-- Banner halaman --}}
    <div class="trx-list-hero">
        <div>
            <h2>
                {{ $barangMasuk ? 'Data Barang Masuk' : 'Data Barang Keluar' }}
            </h2>

            <p>
                {{ $barangMasuk
                    ? 'Pantau riwayat pembelian dan penerimaan barang dari supplier.'
                    : 'Pantau riwayat penjualan dan pengeluaran barang dari toko.' }}
            </p>
        </div>

        <div class="trx-list-icon">
            {{ $barangMasuk ? '📥' : '📤' }}
        </div>
    </div>

    {{-- Statistik --}}
    <div class="trx-list-stats">
        <div class="trx-list-stat">
            <span>Total Transaksi</span>
            <h3>{{ $rows->total() }}</h3>
            <p>Seluruh riwayat transaksi tersimpan</p>
        </div>

        <div class="trx-list-stat">
            <span>Data Halaman Ini</span>
            <h3>{{ $rows->count() }}</h3>
            <p>Transaksi yang sedang ditampilkan</p>
        </div>
    </div>

    {{-- Daftar transaksi --}}
    <div class="trx-list-panel">
        <div class="trx-list-panel-head">
            <h3>Riwayat {{ $barangMasuk ? 'Barang Masuk' : 'Barang Keluar' }}</h3>
            <p>Cari transaksi, lihat detail, atau cetak dokumen.</p>
        </div>

        <div class="trx-list-toolbar">
            <form
                method="GET"
                action="{{ route($jenis . '.index') }}"
                class="trx-list-search"
            >
                <input
                    type="text"
                    name="q"
                    value="{{ $q }}"
                    placeholder="Cari nomor transaksi..."
                    aria-label="Cari nomor transaksi"
                >

                <button type="submit" class="trx-list-btn trx-list-light">
                    🔎 Cari
                </button>

                @if ($q !== '')
                    <a
                        class="trx-list-btn trx-list-light"
                        href="{{ route($jenis . '.index') }}"
                    >
                        Reset
                    </a>
                @endif
            </form>

            <a
                class="trx-list-btn trx-list-primary"
                href="{{ route($jenis . '.create') }}"
            >
                + Transaksi Baru
            </a>
        </div>

        <div class="trx-list-table-wrap">
            <table class="trx-list-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>{{ $barangMasuk ? 'No. Faktur' : 'No. Nota' }}</th>
                        <th>Tanggal</th>

                        @if (!$barangMasuk)
                            <th>Jenis Pembeli</th>
                        @endif

                        <th>{{ $barangMasuk ? 'Supplier' : 'Nama Pembeli' }}</th>
                        <th>Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($rows as $i => $row)
                        @php
                            if ($barangMasuk) {
                                $namaPihak = $row->supplier?->nama ?? '-';
                                $jenisPembeli = '-';
                            } else {
                                $namaPihak = $row->agent?->nama
                                    ?? $row->distributor?->nama
                                    ?? '-';

                                $jenisPembeli = $row->agent
                                    ? 'Agen'
                                    : ($row->distributor ? 'Distributor' : '-');
                            }
                        @endphp

                        <tr>
                            <td>{{ $rows->firstItem() + $i }}</td>

                            <td class="trx-list-number">
                                {{ $row->nomor }}
                            </td>

                            <td>
                                {{ \Illuminate\Support\Carbon::parse($row->tanggal)->format('d/m/Y') }}
                            </td>

                            @if (!$barangMasuk)
                                <td>
                                    <span class="trx-list-type">
                                        {{ $jenisPembeli }}
                                    </span>
                                </td>
                            @endif

                            <td>{{ $namaPihak }}</td>

                            <td class="trx-list-total">
                                Rp {{ number_format((float) $row->total, 0, ',', '.') }}
                            </td>

                            <td>
                                <div class="trx-list-actions">
                                    <a
                                        class="trx-list-btn trx-list-light"
                                        href="{{ route($jenis . '.show', ['transaction' => $row->id]) }}"
                                    >
                                        Detail
                                    </a>

                                    <a
                                        class="trx-list-btn trx-list-primary"
                                        href="{{ route($jenis . '.show', ['transaction' => $row->id, 'cetak' => 1]) }}"
                                    >
                                        Cetak
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="{{ $barangMasuk ? 6 : 7 }}"
                                class="trx-list-empty"
                            >
                                <div style="font-size: 30px; margin-bottom: 10px;">
                                    📂
                                </div>

                                <strong>Belum ada transaksi</strong>

                                <p style="margin: 7px 0 0;">
                                    Tidak ada data yang cocok dengan pencarian.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="trx-list-pagination">
            <span>
                Menampilkan {{ $rows->count() }} dari {{ $rows->total() }} data
            </span>

            <div class="trx-list-pagination-actions">
                @if ($rows->previousPageUrl())
                    <a
                        class="trx-list-btn trx-list-light"
                        href="{{ $rows->previousPageUrl() }}"
                    >
                        &lsaquo; Sebelumnya
                    </a>
                @endif

                @if ($rows->nextPageUrl())
                    <a
                        class="trx-list-btn trx-list-light"
                        href="{{ $rows->nextPageUrl() }}"
                    >
                        Berikutnya &rsaquo;
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
