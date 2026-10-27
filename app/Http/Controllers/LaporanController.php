<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Item;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::query();

        if ($request->filled('mulai')) {
            $query->whereDate('tanggal', '>=', $request->mulai);
        }

        if ($request->filled('sampai')) {
            $query->whereDate('tanggal', '<=', $request->sampai);
        }

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }

        $transaksi = $query
            ->orderBy('tanggal', 'desc')
            ->paginate(10)
            ->withQueryString();

        $totalMasuk = Transaction::where('jenis', 'masuk')
            ->when($request->filled('mulai'), fn ($q) =>
                $q->whereDate('tanggal', '>=', $request->mulai))
            ->when($request->filled('sampai'), fn ($q) =>
                $q->whereDate('tanggal', '<=', $request->sampai))
            ->sum('total');

        $totalKeluar = Transaction::where('jenis', 'keluar')
            ->when($request->filled('mulai'), fn ($q) =>
                $q->whereDate('tanggal', '>=', $request->mulai))
            ->when($request->filled('sampai'), fn ($q) =>
                $q->whereDate('tanggal', '<=', $request->sampai))
            ->sum('total');

        $items = Item::orderBy('nama')->get();

        return view('laporan.index', compact(
            'transaksi',
            'totalMasuk',
            'totalKeluar',
            'items'
        ));
    }

    public function pdf(Request $request)
    {
        $query = Transaction::query();

        if ($request->filled('mulai')) {
            $query->whereDate('tanggal', '>=', $request->mulai);
        }

        if ($request->filled('sampai')) {
            $query->whereDate('tanggal', '<=', $request->sampai);
        }

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }

        $transaksi = $query->orderBy('tanggal', 'desc')->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'laporan.pdf',
            compact('transaksi')
        );

        return $pdf->download('laporan-transaksi.pdf');
    }
}
