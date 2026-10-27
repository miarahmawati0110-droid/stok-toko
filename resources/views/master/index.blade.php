
@extends('layouts.app')

@section('title', 'Data ' . $title)

@section('content')
@php
    $halamanSupplier = $route === 'suppliers';
    $halamanDistributor = $route === 'distributors';
    $halamanUser = $route === 'users';
@endphp

<style>
    .master-page {
        color: #30254b;
        animation: masterMasuk .5s ease;
    }

    @keyframes masterMasuk {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .master-welcome {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 28px;
        margin-bottom: 22px;
        color: white;
        border-radius: 22px;
        background: linear-gradient(120deg, #6d28d9, #9333ea, #c084fc);
        box-shadow: 0 10px 28px #7c3aed2b;
    }

    .master-welcome h2 {
        margin: 0 0 9px;
        font-size: 25px;
        font-weight: 800;
    }

    .master-welcome p {
        margin: 0;
        line-height: 1.7;
        opacity: .92;
    }

    .master-welcome-icon {
        display: grid;
        place-items: center;
        min-width: 66px;
        height: 66px;
        border-radius: 20px;
        background: #ffffff28;
        font-size: 32px;
    }

    .master-cards {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 24px;
    }

    .master-stat {
        padding: 23px;
        background: white;
        border: 1px solid #eee7fa;
        border-radius: 18px;
        box-shadow: 0 5px 20px #35205b0a;
        transition: transform .25s, box-shadow .25s;
    }

    .master-stat:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 25px #7c3aed18;
    }

    .master-stat span {
        color: #827493;
        font-size: 13px;
        font-weight: 600;
    }

    .master-stat h2 {
        color: #7c3aed;
        font-size: 30px;
        margin: 10px 0 5px;
        font-weight: 800;
    }

    .master-stat p {
        color: #9487a5;
        font-size: 12px;
        margin: 0;
    }

    .master-panel {
        background: white;
        border: 1px solid #eee7fa;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 5px 20px #35205b0a;
    }

    .master-header {
        padding: 22px 24px;
        border-bottom: 1px solid #f0eaf7;
    }

    .master-header h3 {
        margin: 0 0 6px;
        font-size: 19px;
        font-weight: 800;
        color: #382653;
    }

    .master-header p {
        margin: 0;
        color: #9285a2;
        font-size: 13px;
    }

    .master-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        padding: 18px 24px;
    }

    .master-search {
        display: flex;
        flex: 1;
        max-width: 430px;
        gap: 8px;
    }

    .master-search input {
        width: 100%;
        min-width: 0;
        padding: 11px 14px;
        border: 1px solid #e6dcef;
        border-radius: 11px;
        outline: none;
        background: #fcfaff;
        color: #35234c;
        transition: border .2s, box-shadow .2s;
    }

    .master-search input:focus {
        border-color: #a855f7;
        box-shadow: 0 0 0 3px #a855f71c;
    }

    .master-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 10px 15px;
        border: none;
        border-radius: 10px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: .2s;
        white-space: nowrap;
    }

    .master-btn:hover {
        transform: translateY(-2px);
        filter: brightness(.97);
    }

    .master-primary {
        color: white;
        background: linear-gradient(120deg, #7c3aed, #a855f7);
        box-shadow: 0 4px 12px #7c3aed2b;
    }

    .master-secondary {
        color: #6d28d9;
        background: #f3e8ff;
    }

    .master-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .master-table {
        width: 100%;
        border-collapse: collapse;
        white-space: nowrap;
    }

    .master-table th {
        padding: 15px 18px;
        text-align: left;
        color: #766588;
        background: #faf7ff;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .6px;
        border-top: 1px solid #f0eaf7;
        border-bottom: 1px solid #f0eaf7;
    }

    .master-table td {
        padding: 16px 18px;
        border-bottom: 1px solid #f3eef8;
        font-size: 13px;
        color: #554565;
    }

    .master-table tbody tr {
        transition: background .2s;
    }

    .master-table tbody tr:hover {
        background: #fcf9ff;
    }

    .master-table tbody tr:last-child td {
        border-bottom: none;
    }

    .master-actions {
        display: flex;
        gap: 7px;
        align-items: center;
    }

    .master-edit {
        background: #ede9fe;
        color: #6d28d9;
    }

    .master-delete {
        background: #fee2e2;
        color: #b91c1c;
    }

    .master-empty {
        padding: 38px 20px !important;
        text-align: center;
        color: #9b8eaa !important;
    }

    .master-pagination {
        padding: 18px 24px;
        border-top: 1px solid #f0eaf7;
        color: #857695;
        font-size: 13px;
    }

    .master-pagination nav p {
        margin-bottom: 12px;
    }

    .master-pagination nav div:first-child {
        margin-bottom: 10px;
    }

    .master-alert {
        margin-bottom: 18px;
        padding: 14px 18px;
        border-radius: 12px;
        animation: masterMasuk .3s ease;
    }

    .master-alert-success {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .master-alert-error {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    @media (max-width: 640px) {
        .master-welcome {
            padding: 20px;
        }

        .master-welcome h2 {
            font-size: 21px;
        }

        .master-welcome-icon {
            min-width: 48px;
            height: 48px;
            font-size: 23px;
        }

        .master-cards {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .master-stat {
            padding: 18px;
        }

        .master-bar {
            padding: 15px;
        }

        .master-search {
            max-width: none;
        }

        .master-header {
            padding: 18px;
        }
    }
</style>

<div class="master-page">

    {{-- Pesan sukses dan error --}}
    @if (session('ok') || session('success'))
        <div class="master-alert master-alert-success">
            ✓ {{ session('ok') ?? session('success') }}
        </div>
    @endif

    @if (session('err') || session('error'))
        <div class="master-alert master-alert-error">
            ⚠ {{ session('err') ?? session('error') }}
        </div>
    @endif

    {{-- Sambutan khusus distributor --}}
    @if ($halamanDistributor)
        <div class="master-welcome">
            <div>
                <h2>Data Distributor</h2>
                <p>
                    Kelola informasi distributor yang memasok barang
                    untuk kebutuhan toko dengan mudah.
                </p>
            </div>
            <div class="master-welcome-icon">🚚</div>
        </div>

        <div class="master-cards">
            <div class="master-stat">
                <span>Total Distributor</span>
                <h2>{{ $rows->total() }}</h2>
                <p>Seluruh data distributor tersimpan</p>
            </div>

            <div class="master-stat">
                <span>Data Ditampilkan</span>
                <h2>{{ $rows->count() }}</h2>
                <p>Jumlah data pada halaman ini</p>
            </div>
        </div>

    {{-- Sambutan khusus supplier --}}
    @elseif ($halamanSupplier)
        <div class="master-welcome">
            <div>
                <h2>Data Supplier</h2>
                <p>Kelola informasi supplier untuk mendukung persediaan barang toko.</p>
            </div>
            <div class="master-welcome-icon">📦</div>
        </div>

        <div class="master-cards">
            <div class="master-stat">
                <span>Total Supplier</span>
                <h2>{{ $rows->total() }}</h2>
                <p>Seluruh data supplier tersimpan</p>
            </div>

            <div class="master-stat">
                <span>Data Ditampilkan</span>
                <h2>{{ $rows->count() }}</h2>
                <p>Jumlah data pada halaman ini</p>
            </div>
        </div>

    {{-- Sambutan halaman lainnya --}}
    @elseif (!$halamanUser)
        <div class="master-welcome">
            <div>
                <h2>Data {{ $title }}</h2>
                <p>Kelola dan perbarui data {{ strtolower($title) }} melalui halaman ini.</p>
            </div>
            <div class="master-welcome-icon">📋</div>
        </div>

        <div class="master-cards">
            <div class="master-stat">
                <span>Total Data</span>
                <h2>{{ $rows->total() }}</h2>
                <p>Seluruh data {{ strtolower($title) }}</p>
            </div>

            <div class="master-stat">
                <span>Data Ditampilkan</span>
                <h2>{{ $rows->count() }}</h2>
                <p>Jumlah data pada halaman ini</p>
            </div>
        </div>
    @endif

    {{-- Statistik khusus pengguna --}}
    @if ($halamanUser)
        <div class="master-welcome">
            <div>
                <h2>Data Pengguna</h2>
                <p>Kelola akun dan hak akses pengguna sistem.</p>
            </div>
            <div class="master-welcome-icon">👥</div>
        </div>

        <div class="master-cards">
            <div class="master-stat">
                <span>Total Pengguna</span>
                <h2>{{ $totalUser ?? $rows->total() }}</h2>
                <p>Semua akun pengguna</p>
            </div>

            <div class="master-stat">
                <span>Admin</span>
                <h2>{{ $totalAdmin ?? 0 }}</h2>
                <p>Pengguna dengan akses admin</p>
            </div>

            <div class="master-stat">
                <span>Kasir</span>
                <h2>{{ $totalKasir ?? 0 }}</h2>
                <p>Pengguna dengan akses kasir</p>
            </div>

            <div class="master-stat">
                <span>Gudang</span>
                <h2>{{ $totalGudang ?? 0 }}</h2>
                <p>Pengguna dengan akses gudang</p>
            </div>
        </div>
    @endif

    {{-- Tabel data --}}
    <div class="master-panel">
        <div class="master-header">
            <h3>Daftar {{ $title }}</h3>
            <p>Lihat, cari, dan kelola data yang tersimpan.</p>
        </div>

        <div class="master-bar">
            <form action="{{ route($route . '.index') }}"
                  method="GET"
                  class="master-search">
                <input
                    type="text"
                    name="q"
                    value="{{ $q ?? request('q') }}"
                    placeholder="Cari {{ strtolower($title) }}..."
                    aria-label="Cari {{ $title }}"
                >

                <button type="submit" class="master-btn master-secondary">
                    Cari
                </button>
            </form>

            <a href="{{ route($route . '.create') }}"
               class="master-btn master-primary">
                + Tambah {{ $title }}
            </a>
        </div>

        <div class="master-table-wrap">
            <table class="master-table">
                <thead>
                    <tr>
                        <th>No.</th>

                        @foreach ($columns as $col => $label)
                            <th>{{ $label }}</th>
                        @endforeach

                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td>{{ $rows->firstItem() + $loop->index }}</td>

                            @foreach ($columns as $col => $label)
                                <td>{{ $ctrl->cell($row, $col) }}</td>
                            @endforeach

                            <td>
                                <div class="master-actions">
                                    <a
                                        href="{{ route($route . '.edit', $row->id) }}"
                                        class="master-btn master-edit">
                                        ✎ Edit
                                    </a>

                                    <form
                                        action="{{ route($route . '.destroy', $row->id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="master-btn master-delete">
                                            🗑 Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($columns) + 2 }}"
                                class="master-empty">
                                <div style="font-size: 32px; margin-bottom: 10px;">
                                    📂
                                </div>
                                <strong>Data belum tersedia</strong>
                                <p style="margin: 7px 0 0;">
                                    Belum ada data yang cocok dengan pencarian.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="master-pagination">
            {{ $rows->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
