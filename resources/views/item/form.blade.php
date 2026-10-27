
@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 850px; margin: 30px auto; padding: 25px; background: white; border: 1px solid #ddd; border-radius: 12px;">

    <h2>{{ isset($item) ? 'Edit Barang' : 'Tambah Barang' }}</h2>
    <p>Sistem Informasi Pengelolaan Stok dan Transaksi Toko</p>

    @if ($errors->any())
        <div style="color: #b91c1c; background: #fee2e2; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
            <strong>Periksa kembali data berikut:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        action="{{ isset($item) ? route('items.update', $item->id) : route('items.store') }}"
        method="POST"
        enctype="multipart/form-data"
    >
        @csrf

        @if (isset($item))
            @method('PUT')
        @endif

        <div style="margin-bottom: 16px;">
            <label for="kode"><strong>Kode Barang</strong></label>
            <input
                type="text"
                id="kode"
                name="kode"
                value="{{ old('kode', $item->kode ?? '') }}"
                required
                maxlength="255"
                style="display:block; width:100%; box-sizing:border-box; padding:10px; margin-top:6px;"
            >
        </div>

        <div style="margin-bottom: 16px;">
            <label for="nama"><strong>Nama Barang</strong></label>
            <input
                type="text"
                id="nama"
                name="nama"
                value="{{ old('nama', $item->nama ?? '') }}"
                required
                maxlength="255"
                style="display:block; width:100%; box-sizing:border-box; padding:10px; margin-top:6px;"
            >
        </div>

        <div style="margin-bottom: 16px;">
            <label for="satuan"><strong>Satuan</strong></label>
            <select
                id="satuan"
                name="satuan"
                required
                style="display:block; width:100%; box-sizing:border-box; padding:10px; margin-top:6px;"
            >
                <option value="">-- Pilih Satuan --</option>

                @foreach ($satuan as $itemSatuan)
                    <option
                        value="{{ $itemSatuan }}"
                        {{ old('satuan', $item->satuan ?? '') == $itemSatuan ? 'selected' : '' }}
                    >
                        {{ $itemSatuan }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom: 16px;">
            <label for="harga"><strong>Harga</strong></label>
            <input
                type="number"
                id="harga"
                name="harga"
                min="0"
                value="{{ old('harga', $item->harga ?? '') }}"
                required
                style="display:block; width:100%; box-sizing:border-box; padding:10px; margin-top:6px;"
            >
        </div>

        <div style="margin-bottom: 16px;">
            <label for="stok"><strong>Stok</strong></label>
            <input
                type="number"
                id="stok"
                name="stok"
                min="0"
                value="{{ old('stok', $item->stok ?? 0) }}"
                required
                style="display:block; width:100%; box-sizing:border-box; padding:10px; margin-top:6px;"
            >
        </div>

        <div style="margin-bottom: 16px;">
            <label for="tanggal_masuk"><strong>Tanggal Masuk</strong></label>
            <input
                type="date"
                id="tanggal_masuk"
                name="tanggal_masuk"
                value="{{ old('tanggal_masuk', isset($item) && $item->tanggal_masuk ? \Illuminate\Support\Carbon::parse($item->tanggal_masuk)->format('Y-m-d') : '') }}"
                required
                style="display:block; width:100%; box-sizing:border-box; padding:10px; margin-top:6px;"
            >
        </div>

        <div style="margin-bottom: 16px;">
            <label for="supplier_id"><strong>Supplier</strong></label>
            <select
                id="supplier_id"
                name="supplier_id"
                required
                style="display:block; width:100%; box-sizing:border-box; padding:10px; margin-top:6px;"
            >
                <option value="">-- Pilih Supplier --</option>

                @foreach ($suppliers as $supplier)
                    <option
                        value="{{ $supplier->id }}"
                        {{ old('supplier_id', $item->supplier_id ?? '') == $supplier->id ? 'selected' : '' }}
                    >
                        {{ $supplier->nama }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom: 16px;">
            <label for="distributor_id"><strong>Distributor</strong></label>
            <select
                id="distributor_id"
                name="distributor_id"
                required
                style="display:block; width:100%; box-sizing:border-box; padding:10px; margin-top:6px;"
            >
                <option value="">-- Pilih Distributor --</option>

                @foreach ($distributors as $distributor)
                    <option
                        value="{{ $distributor->id }}"
                        {{ old('distributor_id', $item->distributor_id ?? '') == $distributor->id ? 'selected' : '' }}
                    >
                        {{ $distributor->nama }}
                    </option>
                @endforeach
            </select>
        </div>

        <hr style="margin: 24px 0;">

        <div style="margin-bottom: 20px;">
            <label for="foto"><strong>Foto Barang</strong></label>
            <p style="color:#666; font-size:13px;">
                Format JPG, JPEG, PNG, atau WEBP. Maksimal 2 MB.
                Foto boleh dikosongkan.
            </p>

            @if (isset($item) && $item->foto)
                <div style="margin-bottom: 12px;">
                    <p>Foto saat ini:</p>
                    <img
                        src="{{ asset('storage/' . $item->foto) }}"
                        alt="Foto {{ $item->nama }}"
                        style="width:150px; height:150px; object-fit:contain; border:1px solid #ddd; border-radius:8px; padding:5px;"
                    >
                </div>
            @endif

            <input
                type="file"
                id="foto"
                name="foto"
                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                style="display:block; width:100%; margin-top:8px;"
            >

            <div style="margin-top:12px;">
                <img
                    id="preview-foto"
                    src="{{ isset($item) && $item->foto ? asset('storage/' . $item->foto) : '' }}"
                    alt="Pratinjau foto barang"
                    style="{{ isset($item) && $item->foto ? '' : 'display:none;' }} width:150px; height:150px; object-fit:contain; border:1px solid #ddd; border-radius:8px; padding:5px;"
                >
            </div>
        </div>

        <button
            type="submit"
            style="background:#6d28d9; color:white; border:none; padding:11px 20px; border-radius:7px; cursor:pointer;"
        >
            {{ isset($item) ? 'Simpan Perubahan' : 'Simpan Barang' }}
        </button>

        <a
            href="{{ route('items.index') }}"
            style="display:inline-block; margin-left:10px; padding:11px 16px; color:#333; text-decoration:none; border:1px solid #ccc; border-radius:7px;"
        >
            Kembali
        </a>
    </form>
</div>

<script>
    const inputFoto = document.getElementById('foto');
    const previewFoto = document.getElementById('preview-foto');

    inputFoto.addEventListener('change', function () {
        const file = this.files[0];

        if (file) {
            previewFoto.src = URL.createObjectURL(file);
            previewFoto.style.display = 'block';
        }
    });
</script>
@endsection
