<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Supplier;
use App\Models\Distributor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    private const SATUAN = [
        'pcs',
        'unit',
        'box',
        'pack',
        'kg',
        'gram',
        'liter',
        'meter',
        'lusin',
        'botol',
        'dus',
    ];

    /**
     * Menampilkan daftar barang.
     */
    public function index()
    {
        $items = Item::with(['supplier', 'distributor'])
            ->latest()
            ->paginate(10);

        return view('item.index', compact('items'));
    }

    /**
     * Menampilkan formulir tambah barang.
     */
    public function create()
    {
        $suppliers = Supplier::all();
        $distributors = Distributor::all();
        $satuan = self::SATUAN;

        return view('item.form', compact(
            'suppliers',
            'distributors',
            'satuan'
        ));
    }

    /**
     * Menyimpan barang baru beserta foto.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => [
                'required',
                'string',
                'max:255',
                'unique:items,kode',
            ],
            'nama' => ['required', 'string', 'max:255'],
            'satuan' => ['required', Rule::in(self::SATUAN)],
            'harga' => ['required', 'integer', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
            'tanggal_masuk' => ['required', 'date'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'distributor_id' => ['required', 'exists:distributors,id'],

            // Foto wajib diisi? Tidak, boleh dikosongkan.
            'foto' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')
                ->store('foto-barang', 'public');
        }

        $validated['user_id'] = auth()->id();

        Item::create($validated);

        return redirect()
            ->route('items.index')
            ->with('success', 'Barang berhasil ditambahkan.');
    }

    /**
     * Menampilkan formulir edit barang.
     */
    public function edit(Item $item)
    {
        $suppliers = Supplier::all();
        $distributors = Distributor::all();
        $satuan = self::SATUAN;

        return view('item.form', compact(
            'item',
            'suppliers',
            'distributors',
            'satuan'
        ));
    }

    /**
     * Memperbarui data dan foto barang.
     */
    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'kode' => [
                'required',
                'string',
                'max:255',
                Rule::unique('items', 'kode')->ignore($item->id),
            ],
            'nama' => ['required', 'string', 'max:255'],
            'satuan' => ['required', Rule::in(self::SATUAN)],
            'harga' => ['required', 'integer', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
            'tanggal_masuk' => ['required', 'date'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'distributor_id' => ['required', 'exists:distributors,id'],

            // Foto baru boleh dikosongkan saat edit.
            'foto' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        if ($request->hasFile('foto')) {
            // Simpan foto baru terlebih dahulu.
            $fotoBaru = $request->file('foto')
                ->store('foto-barang', 'public');

            // Hapus foto lama jika ada.
            if ($item->foto) {
                Storage::disk('public')->delete($item->foto);
            }

            $validated['foto'] = $fotoBaru;
        }

        $item->update($validated);

        return redirect()
            ->route('items.index')
            ->with('success', 'Barang berhasil diperbarui.');
    }

    /**
     * Menghapus barang.
     */
    public function destroy(Item $item)
    {
        try {
            $item->delete();

            // Hapus foto jika barang berhasil dihapus.
            if ($item->foto) {
                Storage::disk('public')->delete($item->foto);
            }

            return redirect()
                ->route('items.index')
                ->with('success', 'Barang berhasil dihapus.');
        } catch (QueryException $e) {
            return redirect()
                ->route('items.index')
                ->with(
                    'error',
                    'Barang tidak dapat dihapus karena sudah digunakan dalam transaksi.'
                );
        }
    }
}
