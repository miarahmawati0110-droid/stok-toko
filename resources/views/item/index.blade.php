
@extends('layouts.app')

@section('content')
<div class="container" style="max-width:1400px; margin:25px auto; padding:20px;">

    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:24px;">
        <div>
            <h2 style="margin:0; color:#4c1d95;">Data Barang</h2>
            <p style="color:#666; margin-top:6px;">
                Sistem Informasi Pengelolaan Stok dan Transaksi Toko
            </p>
        </div>

        <a href="{{ route('items.create') }}"
           style="background:#6d28d9; color:white; text-decoration:none; padding:11px 18px; border-radius:8px;">
            + Tambah Barang
        </a>
    </div>

    @if (session('success'))
        <div style="padding:12px; margin-bottom:16px; background:#dcfce7; color:#166534; border-radius:8px;">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div style="padding:12px; margin-bottom:16px; background:#fee2e2; color:#991b1b; border-radius:8px;">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div style="padding:12px; margin-bottom:16px; background:#fee2e2; color:#991b1b; border-radius:8px;">
            <ul style="margin:0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:16px; margin-bottom:22px;">

        <div style="padding:20px; background:#f3e8ff; border-radius:12px;">
            <div style="color:#6b21a8;">Total Jenis Barang</div>
            <h2 style="margin-bottom:0;">{{ $items->total() }}</h2>
        </div>

        <div style="padding:20px; background:#ede9fe; border-radius:12px;">
            <div style="color:#5b21b6;">Barang per Halaman</div>
            <h2 style="margin-bottom:0;">{{ $items->count() }}</h2>
        </div>
    </div>

    <div style="margin-bottom:18px;">
        <input
            type="text"
            id="cariBarang"
            placeholder="Cari kode atau nama barang..."
            style="width:100%; max-width:420px; box-sizing:border-box; padding:12px; border:1px solid #ddd; border-radius:8px;"
        >
    </div>

    <div style="overflow-x:auto; background:white; border:1px solid #e5e7eb; border-radius:12px;">
        <table style="width:100%; border-collapse:collapse; min-width:1150px;">
            <thead>
                <tr style="background:#6d28d9; color:white;">
                    <th style="padding:13px;">No.</th>
                    <th style="padding:13px;">Foto</th>
                    <th style="padding:13px;">Kode</th>
                    <th style="padding:13px; text-align:left;">Nama Barang</th>
                    <th style="padding:13px;">Satuan</th>
                    <th style="padding:13px;">Harga</th>
                    <th style="padding:13px;">Stok</th>
                    <th style="padding:13px;">Tanggal Masuk</th>
                    <th style="padding:13px;">Supplier</th>
                    <th style="padding:13px;">Distributor</th>
                    <th style="padding:13px;">Aksi</th>
                </tr>
            </thead>

            <tbody id="daftarBarang">
                @forelse ($items as $index => $item)
                    <tr style="border-bottom:1px solid #eee; text-align:center;">
                        <td style="padding:12px;">
                            {{ $items->firstItem() + $index }}
                        </td>

                        <td style="padding:12px;">
                            @if ($item->foto)
                                <img
                                    src="{{ asset('storage/' . $item->foto) }}"
                                    alt="Foto {{ $item->nama }}"
                                    loading="lazy"
                                    style="width:75px; height:75px; object-fit:contain; border:1px solid #ddd; border-radius:8px; padding:4px; background:white;"
                                >
                            @else
                                <div style="width:75px; height:75px; margin:auto; display:flex; align-items:center; justify-content:center; background:#f3f4f6; border:1px dashed #cbd5e1; border-radius:8px; color:#6b7280; font-size:11px;">
                                    Belum ada foto
                                </div>
                            @endif
                        </td>

                        <td style="padding:12px;">
                            {{ $item->kode }}
                        </td>

                        <td style="padding:12px; text-align:left;">
                            <strong>{{ $item->nama }}</strong>
                        </td>

                        <td style="padding:12px;">
                            {{ $item->satuan }}
                        </td>

                        <td style="padding:12px;">
                            Rp {{ number_format($item->harga, 0, ',', '.') }}
                        </td>

                        <td style="padding:12px;">
                            {{ $item->stok }}
                        </td>

                        <td style="padding:12px;">
                            {{ $item->tanggal_masuk ? \Illuminate\Support\Carbon::parse($item->tanggal_masuk)->format('d-m-Y') : '-' }}
                        </td>

                        <td style="padding:12px;">
                            {{ $item->supplier->nama ?? '-' }}
                        </td>

                        <td style="padding:12px;">
                            {{ $item->distributor->nama ?? '-' }}
                        </td>

                        <td style="padding:12px; white-space:nowrap;">
                            <a
                                href="{{ route('items.edit', $item->id) }}"
                                style="display:inline-block; background:#f59e0b; color:white; text-decoration:none; padding:7px 10px; border-radius:6px; margin-bottom:5px;"
                            >
                                Edit
                            </a>

                            <form
                                action="{{ route('items.destroy', $item->id) }}"
                                method="POST"
                                style="display:inline-block;"
                                onsubmit="return confirm('Yakin ingin menghapus barang ini?');"
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    style="background:#dc2626; color:white; border:none; padding:7px 10px; border-radius:6px; cursor:pointer;"
                                >
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" style="padding:30px; text-align:center; color:#777;">
                            Belum ada data barang. Klik Tambah Barang untuk memasukkan data.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div id="pesanPencarian" style="display:none; padding:15px; text-align:center; color:#777;">
        Barang tidak ditemukan pada halaman ini.
    </div>

    <div style="margin-top:20px;">
        {{ $items->links() }}
    </div>

</div>

<script>
    const inputCari = document.getElementById('cariBarang');
    const barisBarang = document.querySelectorAll('#daftarBarang tr');
    const pesanPencarian = document.getElementById('pesanPencarian');

    inputCari.addEventListener('input', function () {
        const kataKunci = this.value.toLowerCase().trim();
        let jumlahTampil = 0;

        barisBarang.forEach(function (baris) {
            const teks = baris.textContent.toLowerCase();

            if (teks.includes(kataKunci)) {
                baris.style.display = '';
                jumlahTampil++;
            } else {
                baris.style.display = 'none';
            }
        });

        pesanPencarian.style.display =
            jumlahTampil === 0 ? 'block' : 'none';
    });
</script>
@endsection
