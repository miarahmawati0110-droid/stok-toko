
@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="dashboard">
    <div class="dashboard-heading">
        <div>
            <h1>Dashboard</h1>
            <p>Ringkasan stok dan transaksi toko.</p>
        </div>

        <button type="button" id="dashboardThemeToggle" class="theme-button">
            🌙 Mode Gelap
        </button>
    </div>

    <div class="stat-grid">
        <div class="stat-card">
            <span>Total Jenis Barang</span>
            <h2>{{ number_format($totalBarang) }}</h2>
        </div>

        <div class="stat-card">
            <span>Total Stok Barang</span>
            <h2>{{ number_format($totalStok) }}</h2>
        </div>

        <div class="stat-card">
            <span>Stok Menipis (≤ 5)</span>
            <h2>{{ number_format($stokMenipis) }}</h2>
        </div>

        <div class="stat-card">
            <span>Transaksi Masuk Hari Ini</span>
            <h2>{{ number_format($transaksiMasuk) }}</h2>
        </div>

        <div class="stat-card">
            <span>Transaksi Keluar Hari Ini</span>
            <h2>{{ number_format($transaksiKeluar) }}</h2>
        </div>
    </div>

    <div class="chart-grid">
        <section class="dashboard-card">
            <h3>Barang Masuk dan Keluar</h3>
            <p>Jumlah transaksi selama tujuh hari terakhir.</p>
            <div class="chart-container">
                <canvas id="transactionChart"></canvas>
            </div>
        </section>

        <section class="dashboard-card">
            <h3>Stok Barang Terbanyak</h3>
            <p>Tujuh barang dengan jumlah stok terbesar.</p>
            <div class="chart-container">
                <canvas id="stockChart"></canvas>
            </div>
        </section>

        <section class="dashboard-card chart-full">
            <h3>Nilai Transaksi</h3>
            <p>Perbandingan nilai transaksi selama enam bulan terakhir.</p>
            <div class="chart-container">
                <canvas id="valueChart"></canvas>
            </div>
        </section>
    </div>

    <section class="dashboard-card transaction-section">
        <h3>Transaksi Terbaru</h3>

        <div class="table-responsive">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nomor Transaksi</th>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th>Pihak Terkait</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transaksiTerbaru as $transaksi)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $transaksi->nomor }}</td>
                            <td>
                                {{ $transaksi->tanggal
                                    ? \Illuminate\Support\Carbon::parse($transaksi->tanggal)->format('d-m-Y')
                                    : '-' }}
                            </td>
                            <td>
                                {{ $transaksi->jenis === 'masuk'
                                    ? 'Barang Masuk'
                                    : 'Barang Keluar' }}
                            </td>
                            <td>{{ $transaksi->pihak_nama }}</td>
                            <td>Rp {{ number_format((float) $transaksi->total, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-data">
                                Belum ada transaksi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="dashboard-actions">
        <a href="{{ route('items.index') }}">Data Barang</a>
        <a href="{{ route('masuk.index') }}">Barang Masuk</a>
        <a href="{{ route('keluar.index') }}">Barang Keluar</a>
        <a href="{{ route('laporan.index') }}">Laporan</a>
    </div>
</div>

<style>
.dashboard {
    color: var(--text, #302544);
}

.dashboard-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.dashboard-heading h1 {
    margin: 0 0 6px;
    font-size: 28px;
}

.dashboard-heading p,
.dashboard-card p {
    color: var(--muted, #777);
    margin: 0 0 16px;
}

.theme-button {
    padding: 10px 16px;
    border: 1px solid var(--border, #ddd);
    border-radius: 10px;
    background: var(--surface, white);
    color: var(--text, #302544);
    cursor: pointer;
}

.stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
    gap: 16px;
    margin-bottom: 22px;
}

.stat-card,
.dashboard-card {
    background: var(--surface, white);
    border: 1px solid var(--border, #e5e0ec);
    border-radius: 14px;
    padding: 20px;
    box-shadow: 0 3px 12px rgba(0,0,0,.04);
}

.stat-card span {
    color: var(--muted, #777);
    font-size: 13px;
}

.stat-card h2 {
    font-size: 27px;
    margin: 12px 0 0;
    overflow-wrap: anywhere;
}

.chart-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
    margin-bottom: 22px;
}

.dashboard-card h3 {
    margin: 0 0 10px;
    font-size: 17px;
}

.chart-full {
    grid-column: 1 / -1;
}

.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
}

.table-responsive {
    overflow-x: auto;
}

.dashboard-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 650px;
}

.dashboard-table th,
.dashboard-table td {
    text-align: left;
    padding: 13px 12px;
    border-bottom: 1px solid var(--border, #e5e0ec);
    white-space: nowrap;
}

.dashboard-table th {
    background: var(--table-head, #f5f1fa);
}

.dashboard-table tbody tr:hover {
    background: var(--hover, #faf8fd);
}

.empty-data {
    text-align: center !important;
    color: var(--muted, #777);
}

.dashboard-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 20px;
}

.dashboard-actions a {
    text-decoration: none;
    padding: 11px 16px;
    border-radius: 9px;
    background: #6d4bb3;
    color: white;
}

html[data-theme="dark"] .dashboard {
    --text: #f0eafa;
    --muted: #c0b5d0;
    --surface: #241d30;
    --border: #453750;
    --table-head: #352941;
    --hover: #30243c;
}

html[data-theme="dark"] .stat-card,
html[data-theme="dark"] .dashboard-card {
    box-shadow: none;
}

@media (max-width: 800px) {
    .chart-grid {
        grid-template-columns: 1fr;
    }

    .chart-full {
        grid-column: auto;
    }

    .chart-container {
        height: 260px;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
(function () {
    const root = document.documentElement;
    const themeButton = document.getElementById('dashboardThemeToggle');

    function applyTheme(theme) {
        root.dataset.theme = theme;
        themeButton.textContent =
            theme === 'dark' ? '☀️ Mode Terang' : '🌙 Mode Gelap';

        if (window.dashboardCharts) {
            window.dashboardCharts.forEach(function (chart) {
                chart.options.color = theme === 'dark' ? '#f0eafa' : '#302544';
                if (chart.options.scales) {
                    Object.values(chart.options.scales).forEach(function (scale) {
                        scale.ticks = scale.ticks || {};
                        scale.ticks.color = theme === 'dark' ? '#d8cde5' : '#666';
                        scale.grid = scale.grid || {};
                        scale.grid.color = theme === 'dark' ? '#453750' : '#e8e2ef';
                    });
                }
                chart.update();
            });
        }
    }

    let savedTheme = 'light';

    try {
        savedTheme = localStorage.getItem('stoktoko-theme') || 'light';
    } catch (error) {}

    applyTheme(savedTheme);

    themeButton.addEventListener('click', function () {
        const nextTheme = root.dataset.theme === 'dark' ? 'light' : 'dark';
        applyTheme(nextTheme);

        try {
            localStorage.setItem('stoktoko-theme', nextTheme);
        } catch (error) {}
    });

    if (typeof Chart === 'undefined') {
        return;
    }

    const labels = @json($tanggalLabels);
    const chartTextColor = savedTheme === 'dark' ? '#f0eafa' : '#302544';
    const gridColor = savedTheme === 'dark' ? '#453750' : '#e8e2ef';

    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: { color: chartTextColor }
            }
        },
        scales: {
            x: {
                ticks: { color: chartTextColor },
                grid: { color: gridColor }
            },
            y: {
                beginAtZero: true,
                ticks: { color: chartTextColor },
                grid: { color: gridColor }
            }
        }
    };

    const transactionChart = new Chart(
        document.getElementById('transactionChart'),
        {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Barang Masuk',
                        data: @json($masukHarian),
                        backgroundColor: '#8b5cf6',
                        borderRadius: 5
                    },
                    {
                        label: 'Barang Keluar',
                        data: @json($keluarHarian),
                        backgroundColor: '#f59e0b',
                        borderRadius: 5
                    }
                ]
            },
            options: commonOptions
        }
    );

    const stockChart = new Chart(
        document.getElementById('stockChart'),
        {
            type: 'doughnut',
            data: {
                labels: @json($stokBarang->pluck('nama')),
                datasets: [{
                    label: 'Jumlah Stok',
                    data: @json($stokBarang->pluck('stok')),
                    backgroundColor: [
                        '#8b5cf6', '#06b6d4', '#f59e0b',
                        '#10b981', '#f43f5e', '#6366f1', '#84cc16'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: chartTextColor }
                    }
                }
            }
        }
    );

    const valueChart = new Chart(
        document.getElementById('valueChart'),
        {
            type: 'line',
            data: {
                labels: @json($bulanLabels),
                datasets: [
                    {
                        label: 'Barang Masuk (Rp)',
                        data: @json($nilaiMasuk),
                        borderColor: '#8b5cf6',
                        backgroundColor: '#8b5cf6',
                        tension: 0.3
                    },
                    {
                        label: 'Barang Keluar (Rp)',
                        data: @json($nilaiKeluar),
                        borderColor: '#f59e0b',
                        backgroundColor: '#f59e0b',
                        tension: 0.3
                    }
                ]
            },
            options: {
                ...commonOptions,
                scales: {
                    ...commonOptions.scales,
                    y: {
                        ...commonOptions.scales.y,
                        ticks: {
                            color: chartTextColor,
                            callback: function (value) {
                                return 'Rp ' + Number(value).toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        }
    );

    window.dashboardCharts = [
        transactionChart,
        stockChart,
        valueChart
    ];
})();
</script>
@endsection
