<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $startDate = $today->copy()->subDays(6);

        // Statistik barang
        $totalBarang = Item::count();
        $totalStok = (int) Item::sum('stok');
        $stokMenipis = Item::where('stok', '<=', 5)->count();

        // Statistik transaksi hari ini
        $transaksiMasuk = Transaction::where('jenis', 'masuk')
            ->whereDate('tanggal', $today)
            ->count();

        $transaksiKeluar = Transaction::where('jenis', 'keluar')
            ->whereDate('tanggal', $today)
            ->count();

        // Grafik jumlah transaksi selama 7 hari terakhir
        $tanggalLabels = [];
        $masukHarian = [];
        $keluarHarian = [];

        for ($i = 0; $i < 7; $i++) {
            $tanggal = $startDate->copy()->addDays($i);
            $tanggalLabels[] = $tanggal->format('d M');

            $masukHarian[] = (int) Transaction::where('jenis', 'masuk')
                ->whereDate('tanggal', $tanggal)
                ->count();

            $keluarHarian[] = (int) Transaction::where('jenis', 'keluar')
                ->whereDate('tanggal', $tanggal)
                ->count();
        }

        // Barang dengan stok terbanyak
        $stokBarang = Item::orderByDesc('stok')
            ->orderBy('nama')
            ->limit(7)
            ->get(['nama', 'stok']);

        // Grafik nilai transaksi selama 6 bulan
        $bulanLabels = [];
        $nilaiMasuk = [];
        $nilaiKeluar = [];

        for ($i = 5; $i >= 0; $i--) {
            $bulan = $today->copy()->startOfMonth()->subMonths($i);

            $bulanLabels[] = $bulan->translatedFormat('M Y');

            $nilaiMasuk[] = (float) Transaction::where('jenis', 'masuk')
                ->whereYear('tanggal', $bulan->year)
                ->whereMonth('tanggal', $bulan->month)
                ->sum('total');

            $nilaiKeluar[] = (float) Transaction::where('jenis', 'keluar')
                ->whereYear('tanggal', $bulan->year)
                ->whereMonth('tanggal', $bulan->month)
                ->sum('total');
        }

        // Nilai transaksi bulan ini
        $nilaiMasukBulanIni = (float) Transaction::where('jenis', 'masuk')
            ->whereYear('tanggal', $today->year)
            ->whereMonth('tanggal', $today->month)
            ->sum('total');

        $nilaiKeluarBulanIni = (float) Transaction::where('jenis', 'keluar')
            ->whereYear('tanggal', $today->year)
            ->whereMonth('tanggal', $today->month)
            ->sum('total');

        // Tabel transaksi terbaru
        $transaksiTerbaru = Transaction::with([
            'supplier',
            'distributor',
            'agent',
            'user',
        ])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        return view('dashboard', compact(
            'totalBarang',
            'totalStok',
            'stokMenipis',
            'transaksiMasuk',
            'transaksiKeluar',
            'tanggalLabels',
            'masukHarian',
            'keluarHarian',
            'stokBarang',
            'bulanLabels',
            'nilaiMasuk',
            'nilaiKeluar',
            'nilaiMasukBulanIni',
            'nilaiKeluarBulanIni',
            'transaksiTerbaru'
        ));
    }
}
