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
    /**
     * Mengambil jenis transaksi dari route.
     */
    private function jenis(Request $request): string
    {
        $jenis = $request->route()->defaults['jenis']
            ?? $request->route('jenis');

        abort_unless(
            in_array($jenis, ['masuk', 'keluar'], true),
            404
        );

        return $jenis;
    }

    /**
     * Menampilkan daftar transaksi.
     */
    public function index(Request $request)
    {
        $jenis = $this->jenis($request);
        $q = trim((string) $request->query('q'));

        $rows = Transaction::with([
            'supplier',
            'distributor',
            'agent',
            'user',
        ])
            ->where('jenis', $jenis)
            ->when($q !== '', function ($query) use ($q) {
                $query->where('nomor', 'like', "%{$q}%");
            })
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('transaksi.index', compact(
            'rows',
            'q',
            'jenis'
        ));
    }

    /**
     * Menampilkan formulir transaksi.
     */
    public function create(Request $request)
    {
        $jenis = $this->jenis($request);
        $masuk = $jenis === 'masuk';

        $items = Item::query()
            ->when(!$masuk, function ($query) {
                $query->where('stok', '>', 0);
            })
            ->orderBy('nama')
            ->get([
                'id',
                'kode',
                'nama',
                'satuan',
                'harga',
                'stok',
            ]);

        $suppliers = Supplier::orderBy('nama')
            ->get(['id', 'nama']);

        $parties = [
            'distributor' => Distributor::orderBy('nama')
                ->get(['id', 'nama']),
            'agent' => Agent::orderBy('nama')
                ->get(['id', 'nama']),
        ];

        return view('transaksi.form', [
            'jenis' => $jenis,
            'nomor' => $this->nextNomor($jenis),
            'items' => $items,
            'suppliers' => $suppliers,
            'parties' => $parties,
        ]);
    }

    /**
     * Menyimpan transaksi dan memperbarui stok.
     */
    public function store(Request $request)
    {
        $jenis = $this->jenis($request);
        $masuk = $jenis === 'masuk';

        $rules = [
            'tanggal' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => [
                'required',
                'integer',
                'exists:items,id',
            ],
            'items.*.jumlah' => [
                'required',
                'integer',
                'min:1',
            ],
        ];

        if ($masuk) {
            $rules['pihak'] = [
                'required',
                'integer',
                'exists:suppliers,id',
            ];

            $rules['items.*.harga'] = [
                'required',
                'integer',
                'min:0',
            ];
        } else {
            $rules['jenis_pembeli'] = [
                'required',
                'in:distributor,agent',
            ];

            $tabelPembeli = $request->input('jenis_pembeli') === 'agent'
                ? 'agents'
                : 'distributors';

            $rules['pihak'] = [
                'required',
                'integer',
                "exists:{$tabelPembeli},id",
            ];
        }

        $data = $request->validate($rules, [
            'tanggal.required' => 'Tanggal wajib diisi.',
            'items.required' => 'Tambahkan minimal satu barang.',
            'items.min' => 'Tambahkan minimal satu barang.',
            'items.*.item_id.required' => 'Pilih barang pada setiap baris.',
            'items.*.jumlah.required' => 'Jumlah wajib diisi.',
            'items.*.jumlah.min' => 'Jumlah minimal 1.',
            'items.*.harga.required' => 'Harga barang masuk wajib diisi.',
            'pihak.required' => $masuk
                ? 'Pilih supplier.'
                : 'Pilih nama pembeli.',
            'pihak.exists' => $masuk
                ? 'Supplier tidak valid.'
                : 'Pembeli tidak valid.',
        ]);

        // Gabungkan jika barang yang sama dimasukkan beberapa kali.
        $lines = [];

        foreach ($data['items'] as $row) {
            $id = (int) $row['item_id'];
            $jumlah = (int) $row['jumlah'];

            if (!isset($lines[$id])) {
                $lines[$id] = ['jumlah' => 0];
            }

            $lines[$id]['jumlah'] += $jumlah;

            if ($masuk) {
                $lines[$id]['harga'] = (int) $row['harga'];
            }
        }

        $transaction = DB::transaction(function () use (
            $lines,
            $data,
            $jenis,
            $masuk
        ) {
            $items = Item::whereIn('id', array_keys($lines))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Periksa stok sebelum transaksi keluar.
            foreach ($lines as $id => $line) {
                if (!isset($items[$id])) {
                    throw ValidationException::withMessages([
                        'items' => 'Ada barang yang tidak ditemukan.',
                    ]);
                }

                $item = $items[$id];

                if (!$masuk && $item->stok < $line['jumlah']) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$item->nama} tidak cukup. "
                            . "Tersedia {$item->stok} {$item->satuan}, "
                            . "diminta {$line['jumlah']}.",
                    ]);
                }
            }

            // Hitung total transaksi.
            $total = 0;

            foreach ($lines as $id => $line) {
                $harga = $masuk
                    ? $line['harga']
                    : (int) $items[$id]->harga;

                $subtotal = $line['jumlah'] * $harga;

                $lines[$id]['harga'] = $harga;
                $lines[$id]['subtotal'] = $subtotal;
                $total += $subtotal;
            }

            $nomor = $this->nextNomor($jenis);

            $transaction = Transaction::create([
                'nomor' => $nomor,
                'tanggal' => $data['tanggal'],
                'jenis' => $jenis,

                'supplier_id' => $masuk
                    ? $data['pihak']
                    : null,

                'distributor_id' => (
                    !$masuk
                    && $data['jenis_pembeli'] === 'distributor'
                ) ? $data['pihak'] : null,

                'agent_id' => (
                    !$masuk
                    && $data['jenis_pembeli'] === 'agent'
                ) ? $data['pihak'] : null,

                'user_id' => auth()->id(),
                'total' => $total,
            ]);

            // Simpan detail dan perbarui stok.
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

        // Arahkan ke halaman detail transaksi yang baru dibuat.
        $routeName = $masuk ? 'masuk.show' : 'keluar.show';

        $params = [
            'transaction' => $transaction->id,
        ];

        if (!$masuk) {
            $params['cetak'] = 1;
        }

        return redirect()
            ->route($routeName, $params)
            ->with(
                'ok',
                ($masuk ? 'Faktur ' : 'Nota ')
                . $transaction->nomor
                . ' berhasil disimpan dan stok sudah diperbarui.'
            );
    }

    /**
     * Menampilkan detail transaksi.
     */
    public function show(Request $request)
    {
        $jenis = $this->jenis($request);
        $id = $request->route('transaction');

        $transaction = Transaction::with([
            'details.item',
            'supplier',
            'distributor',
            'agent',
            'user',
        ])->findOrFail($id);

        abort_unless($transaction->jenis === $jenis, 404);

        return view('transaksi.show', compact(
            'jenis',
            'transaction'
        ));
    }

    /**
     * Membuat nomor transaksi berikutnya.
     */
    private function nextNomor(string $jenis): string
    {
        abort_unless(
            in_array($jenis, ['masuk', 'keluar'], true),
            404
        );

        $prefix = $jenis === 'masuk' ? 'PB-' : 'PJ-';

        $last = Transaction::where('jenis', $jenis)
            ->orderByDesc('id')
            ->value('nomor');

        $nomorTerakhir = $last
            ? (int) substr($last, 3)
            : 0;

        return $prefix . str_pad(
            (string) ($nomorTerakhir + 1),
            4,
            '0',
            STR_PAD_LEFT
        );
    }
}
