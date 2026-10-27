
<!doctype html>
<html lang="id" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title') - {{ config('app.name') }}</title>

  <script>
    (() => {
      try {
        const theme = localStorage.getItem('stoktoko-theme');
        document.documentElement.dataset.theme =
          theme === 'dark' ? 'dark' : 'light';
      } catch (error) {
        document.documentElement.dataset.theme = 'light';
      }
    })();
  </script>

  <style>
    :root {
      color-scheme: light;
      --purple: #7c3aed;
      --purple-dark: #5b21b6;
      --purple-light: #a78bfa;
      --text: #29213d;
      --muted: #756b8a;
      --border: #e8e0f5;
      --surface: #ffffff;
      --background: #f7f4ff;
      --input-bg: #ffffff;
      --table-head: #f3e8ff;
      --table-hover: #faf7ff;
      --success: #15803d;
      --danger: #dc2626;
    }

    html[data-theme="dark"] {
      color-scheme: dark;
      --text: #f1eafa;
      --muted: #b9accd;
      --border: #49385f;
      --surface: #211a2d;
      --background: #17121f;
      --input-bg: #2b2239;
      --table-head: #38284b;
      --table-hover: #30233e;
      --success: #86efac;
      --danger: #fca5a5;
    }

    * {
      box-sizing: border-box;
    }

    html {
      scroll-behavior: smooth;
    }

    body {
      margin: 0;
      color: var(--text);
      font: 14px/1.6 system-ui, -apple-system, BlinkMacSystemFont,
            "Segoe UI", sans-serif;
      background:
        radial-gradient(ellipse at top left, #ede9fe 0, transparent 42%),
        linear-gradient(135deg, #faf8ff, #f3edff 55%, #fdfbff);
      min-height: 100vh;
      transition: background .25s ease, color .25s ease;
    }

    html[data-theme="dark"] body {
      background:
        radial-gradient(ellipse at top left, #302143 0, transparent 42%),
        linear-gradient(135deg, #17121f, #241932 55%, #191421);
    }

    h1, h2, h3 {
      color: var(--text);
      line-height: 1.3;
    }

    h2 {
      margin: 0 0 16px;
    }

    a {
      transition: color .2s ease, background .25s ease,
                  transform .2s ease, box-shadow .25s ease;
    }

    /* Header */
    .top {
      position: sticky;
      top: 0;
      z-index: 100;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 16px;
      padding: 14px 24px;
      color: #fff;
      background: linear-gradient(110deg, #4c1d95, #7c3aed, #9333ea);
      box-shadow: 0 5px 20px rgba(91, 33, 182, .2);
    }

    .top > b {
      font-size: 18px;
      letter-spacing: .3px;
    }

    .top span {
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      gap: 8px;
    }

    .lk {
      background: transparent;
      border: 0;
      padding: 4px;
      color: inherit;
      text-decoration: underline;
      cursor: pointer;
      font: inherit;
    }

    .top .lk {
      padding: 6px 12px;
      border: 1px solid rgba(255,255,255,.45);
      border-radius: 8px;
      text-decoration: none;
      white-space: nowrap;
    }

    .top .lk:hover {
      background: rgba(255,255,255,.18);
    }

    /* Layout utama */
    .app {
      display: grid;
      grid-template-columns: 230px minmax(0, 1fr);
      min-height: calc(100vh - 58px);
    }

    /* Sidebar */
    .side {
      padding: 20px 12px;
      background: linear-gradient(180deg, #4c1d95, #6d28d9 55%, #7e22ce);
      min-height: calc(100vh - 58px);
      box-shadow: 5px 0 22px rgba(76, 29, 149, .08);
    }

    .side a {
      position: relative;
      display: block;
      margin: 5px 0;
      padding: 11px 14px;
      color: #ede9fe;
      text-decoration: none;
      border: 1px solid transparent;
      border-radius: 10px;
      overflow: hidden;
      transition: background .25s ease, transform .25s ease,
                  border-color .25s ease;
    }

    .side a:hover {
      background: rgba(255,255,255,.13);
      border-color: rgba(255,255,255,.15);
      transform: translateX(4px);
      color: #fff;
    }

    .side a.on {
      color: #fff;
      font-weight: 700;
      background: linear-gradient(110deg, #a78bfa, #8b5cf6);
      border-color: rgba(255,255,255,.25);
      box-shadow: 0 5px 15px rgba(30, 10, 70, .18);
    }

    /* Area konten */
    main {
      min-width: 0;
      padding: 28px;
      animation: pageIn .45s ease both;
    }

    @keyframes pageIn {
      from {
        opacity: 0;
        transform: translateY(9px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Kartu */
    .card {
      background: rgba(255,255,255,.94);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 20px;
      box-shadow: 0 8px 28px rgba(91, 33, 182, .07);
      transition: box-shadow .25s ease, transform .25s ease,
                  background .25s ease;
    }

    html[data-theme="dark"] .card {
      background: var(--surface);
      box-shadow: 0 8px 28px rgba(0,0,0,.12);
    }

    .card:hover {
      box-shadow: 0 12px 32px rgba(91, 33, 182, .11);
    }

    /* Baris tombol dan pencarian */
    .bar {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      align-items: center;
      margin-bottom: 16px;
    }

    .sp {
      flex: 1;
    }

    /* Input */
    input, select, textarea {
      font: inherit;
      color: var(--text);
      background: var(--input-bg);
      border: 1px solid #d9cdee;
      border-radius: 9px;
      padding: 9px 12px;
      min-width: 0;
      max-width: 100%;
      transition: border-color .2s ease, box-shadow .2s ease,
                  background .25s ease, color .25s ease;
    }

    html[data-theme="dark"] input,
    html[data-theme="dark"] select,
    html[data-theme="dark"] textarea {
      border-color: var(--border);
    }

    input:focus, select:focus, textarea:focus {
      outline: none;
      border-color: var(--purple);
      box-shadow: 0 0 0 3px rgba(124, 58, 237, .12);
    }

    input[type="checkbox"],
    input[type="radio"] {
      accent-color: var(--purple);
    }

    td input[type="number"] {
      width: 110px;
    }

    td select {
      width: 100%;
      min-width: 160px;
    }

    /* Tombol */
    .b {
      display: inline-block;
      font: inherit;
      font-weight: 600;
      padding: 9px 15px;
      border: 1px solid #ded4ef;
      border-radius: 9px;
      background: var(--input-bg);
      color: var(--text);
      text-decoration: none;
      text-align: center;
      cursor: pointer;
      transition: transform .2s ease, box-shadow .2s ease,
                  background .2s ease, color .2s ease;
    }

    .b:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 13px rgba(91, 33, 182, .12);
    }

    .b.pr {
      color: #fff;
      border-color: transparent;
      background: linear-gradient(110deg, #6d28d9, #9333ea);
      box-shadow: 0 4px 12px rgba(124, 58, 237, .2);
    }

    .b.pr:hover {
      background: linear-gradient(110deg, #5b21b6, #7e22ce);
    }

    .b.dis {
      opacity: .4;
      pointer-events: none;
    }

    button:disabled {
      opacity: .55;
      cursor: not-allowed;
    }

    /* Tabel */
    .tw {
      overflow-x: auto;
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 13px;
      box-shadow: 0 5px 20px rgba(91, 33, 182, .05);
    }

    table {
      border-collapse: collapse;
      width: 100%;
    }

    th, td {
      padding: 12px 14px;
      text-align: left;
      border-bottom: 1px solid #eee8f7;
      white-space: nowrap;
    }

    html[data-theme="dark"] th,
    html[data-theme="dark"] td {
      border-bottom-color: var(--border);
    }

    th {
      color: #5b21b6;
      background: linear-gradient(110deg, #f3e8ff, #ede9fe);
      font-weight: 700;
    }

    html[data-theme="dark"] th {
      color: #e9d5ff;
      background: var(--table-head);
    }

    tbody tr {
      transition: background .2s ease;
    }

    tbody tr:hover {
      background: #faf7ff;
    }

    html[data-theme="dark"] tbody tr:hover {
      background: var(--table-hover);
    }

    tr:last-child td {
      border-bottom: 0;
    }

    .em {
      text-align: center;
      color: var(--muted);
      padding: 22px;
    }

    .pgr {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      align-items: center;
      gap: 10px;
      margin-top: 14px;
      color: var(--muted);
    }

    .pgr a {
      color: var(--purple-dark);
    }

    html[data-theme="dark"] .pgr a,
    html[data-theme="dark"] .tot {
      color: #c4b5fd;
    }

    /* Form */
    .fg {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 16px;
      max-width: 800px;
    }

    label {
      display: flex;
      flex-direction: column;
      gap: 6px;
      font-weight: 600;
    }

    label input,
    label select,
    label textarea {
      width: 100%;
      font-weight: 400;
    }

    .acts {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 20px;
    }

    .err {
      color: var(--danger);
      font-size: 12px;
      font-weight: 400;
    }

    /* Notifikasi */
    .flash {
      padding: 12px 16px;
      border-radius: 10px;
      margin-bottom: 18px;
      animation: pageIn .35s ease both;
    }

    .flash.ok {
      background: #ecfdf3;
      color: #166534;
      border: 1px solid #bbf7d0;
    }

    .flash.bad {
      background: #fef2f2;
      color: #b42318;
      border: 1px solid #fecaca;
    }

    html[data-theme="dark"] .flash.ok {
      background: #153323;
      color: #bbf7d0;
      border-color: #286342;
    }

    html[data-theme="dark"] .flash.bad {
      background: #3b1d25;
      color: #fecaca;
      border-color: #71313c;
    }

    /* Laporan */
    .tot {
      text-align: right;
      font-weight: 700;
      font-size: 16px;
      margin: 14px 0;
      color: var(--purple-dark);
    }

    .meta, .sig {
      display: flex;
      justify-content: space-between;
      gap: 16px;
      margin: 14px 0;
    }

    .sig {
      margin-top: 28px;
      text-align: center;
    }

    .ctr {
      text-align: center;
      margin: 0 0 5px;
    }

    .sheet {
      max-width: 820px;
    }

    /* Responsif untuk tablet dan HP */
    @media (max-width: 800px) {
      .top {
        padding: 12px 16px;
      }

      .top > b {
        font-size: 16px;
      }

      .app {
        grid-template-columns: 1fr;
      }

      .side {
        display: flex;
        gap: 4px;
        overflow-x: auto;
        min-height: 0;
        padding: 8px;
        position: relative;
      }

      .side a {
        flex: 0 0 auto;
        white-space: nowrap;
        margin: 0;
        padding: 9px 12px;
      }

      .side a:hover {
        transform: translateY(-2px);
      }

      main {
        padding: 18px;
      }

      .card {
        padding: 15px;
      }
    }

    @media (max-width: 480px) {
      .top {
        align-items: flex-start;
        flex-direction: column;
        gap: 8px;
      }

      .top span {
        font-size: 12px;
      }

      main {
        padding: 12px;
      }

      .fg {
        grid-template-columns: 1fr;
      }

      .b {
        padding: 8px 11px;
      }
    }

    /* Tampilan cetak laporan tetap bersih */
    @media print {
      body {
        background: #fff !important;
        color: #000 !important;
      }

      .top, .side, .noprint, .flash {
        display: none !important;
      }

      .app {
        display: block;
      }

      main {
        padding: 0;
        animation: none;
      }

      .card, .tw {
        border: 0;
        box-shadow: none;
        background: #fff !important;
        color: #000 !important;
      }

      th {
        background: #eee !important;
        color: #000 !important;
      }

      td, h1, h2, h3 {
        color: #000 !important;
      }
    }

    /* Menghormati pengaturan pengurangan animasi perangkat */
    @media (prefers-reduced-motion: reduce) {
      *, *::before, *::after {
        scroll-behavior: auto !important;
        animation-duration: .01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: .01ms !important;
      }
    }
  </style>
</head>

<body>
  <header class="top">
    <b>{{ config('app.name') }}</b>

    <span>
      <button type="button" id="themeToggle" class="lk"
              aria-label="Ganti tema">
        🌙 Mode Gelap
      </button>

      {{ auth()->user()->nama }} ({{ auth()->user()->role }}) |

      <form method="POST" action="{{ route('logout') }}" style="display:inline">
        @csrf
        <button type="submit" class="lk">Keluar</button>
      </form>
    </span>
  </header>

  @php
    $role = auth()->user()->role;

    $menu = [
      ['Dashboard',        'dashboard',     ['Admin','Kasir','Gudang']],
      ['Data User',        'users',         ['Admin']],
      ['Data Supplier',    'suppliers',     ['Admin']],
      ['Data Distributor', 'distributors',  ['Admin']],
      ['Data Agen',        'agents',        ['Admin']],
      ['Data Barang',      'items',         ['Admin','Kasir','Gudang']],
      ['Barang Masuk',     'barang-masuk',  ['Admin','Gudang']],
      ['Barang Keluar',    'barang-keluar', ['Admin','Kasir']],
      ['Laporan',          'laporan',       ['Admin','Kasir','Gudang']],
    ];
  @endphp

  <div class="app">
    <nav class="side">
      @foreach ($menu as [$label, $path, $roles])
        @if (in_array($role, $roles))
          <a
            href="{{ url($path) }}"
            class="{{ request()->is($path.'*') ? 'on' : '' }}"
          >{{ $label }}</a>
        @endif
      @endforeach
    </nav>

    <main>
      @if (session('ok'))
        <div class="flash ok">{{ session('ok') }}</div>
      @endif

      @if (session('err'))
        <div class="flash bad">{{ session('err') }}</div>
      @endif

      @yield('content')
    </main>
  </div>

  <script>
    (() => {
      const root = document.documentElement;
      const button = document.getElementById('themeToggle');

      function setTheme(theme) {
        root.dataset.theme = theme;

        button.textContent = theme === 'dark'
          ? '☀️ Mode Terang'
          : '🌙 Mode Gelap';

        button.setAttribute(
          'aria-label',
          theme === 'dark'
            ? 'Aktifkan mode terang'
            : 'Aktifkan mode gelap'
        );
      }

      let savedTheme = 'light';

      try {
        savedTheme = localStorage.getItem('stoktoko-theme') || 'light';
      } catch (error) {
        savedTheme = 'light';
      }

      setTheme(savedTheme === 'dark' ? 'dark' : 'light');

      button.addEventListener('click', () => {
        const nextTheme =
          root.dataset.theme === 'dark' ? 'light' : 'dark';

        setTheme(nextTheme);

        try {
          localStorage.setItem('stoktoko-theme', nextTheme);
        } catch (error) {
          // Tema tetap bisa diganti jika penyimpanan tidak tersedia.
        }
      });
    })();
  </script>

  @stack('scripts')
</body>
</html>
