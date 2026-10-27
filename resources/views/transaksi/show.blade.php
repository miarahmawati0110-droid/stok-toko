
@extends('layouts.app')

@section('title', ($jenis === 'masuk' ? 'Faktur ' : 'Nota ') . $transaction->nomor)

@section('content')
    @php
        if ($jenis === 'masuk') {
            $namaPihak = $transaction->supplier?->nama ?? '-';
            $jenisPembeli = '';
        } else {
            $namaPihak = $transaction->agent?->nama
                ?? $transaction->distributor?->nama
                ?? '-';

            $jenisPembeli = $transaction->agent
                ? 'Agen'
                : ($transaction->distributor ? 'Distributor' : '-');
        }
    @endphp

    <div class="card sheet">
        <h3 class="ctr">{{ config('app.name', 'Sistem Informasi Stok Toko') }}</h3>

        <h2 class="ctr">
            {{ $jenis === 'masuk' ? 'FAKTUR PEMBELIAN' : 'NOTA PENJUALAN' }}
        </h2>

        <div class="meta">
            <div>
                {{ $jenis === 'masuk' ? 'No. Faktur' : 'No. Nota' }}:
                {{ $transaction->nomor }}
                <br>

                Tanggal:
                {{ \Illuminate\Support\Carbon::parse($transaction->tanggal)->format('d/m/Y') }}
            </div>

            <div>
                {{ $jenis === 'masuk' ? 'Supplier' : 'Pembeli' }}:
                {{ $namaPihak }}

                @if ($jenis === 'keluar')
                    ({{ $jenisPembeli }})
                @endif

                <br>

                Petugas:
                {{ $transaction->user?->nama ?? '-' }}
            </div>
        </div>

        <div class="tw">
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama Barang</th>
                        <th>Jumlah</th>
                        <th>{{ $jenis === 'masuk' ? 'Harga Beli' : 'Harga Jual' }}</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($transaction->details as $i => $d)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $d->item?->nama ?? '-' }}</td>
                            <td>
                                {{ $d->jumlah }}
                                {{ $d->item?->satuan ?? '' }}
                            </td>
                            <td>
                                Rp {{ number_format((float) $d->harga, 0, ',', '.') }}
                            </td>
                            <td>
                                Rp {{ number_format((float) $d->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="em">Belum ada detail barang.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <p class="tot">
            Total:
            Rp {{ number_format((float) $transaction->total, 0, ',', '.') }}
        </p>

        <div class="sig">
            <div>
                Dibuat oleh,
                <br><br><br>
                ( {{ $transaction->user?->nama ?? '-' }} )
            </div>

            <div>
                Diterima oleh,
                <br><br><br>
                ( ........................ )
            </div>
        </div>
    </div>

    <div class="acts noprint">
        <a class="b" href="{{ route($jenis . '.index') }}">Kembali</a>

        <button
            type="button"
            class="b pr"
            onclick="window.print()"
        >
            Cetak
        </button>
    </div>

    @if (request()->boolean('cetak'))
        @push('scripts')
            <script>
                window.addEventListener('load', function () {
                    window.print();
                });
            </script>
        @endpush
    @endif
@endsection
