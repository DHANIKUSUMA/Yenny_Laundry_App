<?php
    include "../koneksi.php";

    // ambil 7 hari terakhir
    $query = "SELECT 
                DATE(tanggal_masuk) as tgl,
                SUM(total_harga) as total
            FROM pesanan
            WHERE status='diambil'
            AND tanggal_masuk >= DATE(NOW() - INTERVAL 7 DAY)
            GROUP BY DATE(tanggal_masuk)
            ORDER BY tgl ASC";

    $result = mysqli_query($conn, $query);

    $labels = [];
    $data = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $labels[] = $row['tgl'];
        $data[] = $row['total'];
    }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Yenny Laundry</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .nav-link.active { background: rgba(255,255,255,0.22); }
        .card-arrow { opacity: 0.2; transition: opacity 0.2s; }
        .card:hover .card-arrow { opacity: 0.6; }
    </style>
</head>
<body class="flex min-h-screen" style="background: linear-gradient(135deg, #f3f0ff 0%, #ede9fe 50%, #e0d7ff 100%);">

    <!-- Sidebar -->
    <aside class="w-60 min-h-screen fixed top-0 left-0 bottom-0 flex flex-col p-5 text-white"
        style="background: linear-gradient(180deg, #7c3aed 0%, #9333ea 60%, #a855f7 100%);">

        <!-- Brand -->
        <div class="flex items-center gap-3 mb-8 pb-5 border-b border-white/15">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-white/20">
                <svg class="w-5 h-5 fill-white" viewBox="0 0 24 24">
                    <path d="M5 3a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2H5zm7 2a5 5 0 110 10A5 5 0 0112 5zm0 2a3 3 0 100 6 3 3 0 000-6zm0 1a2 2 0 110 4 2 2 0 010-4z"/>
                </svg>
            </div>
            <div>
                <div class="text-sm font-bold leading-tight">Yenny Laundry</div>
                <div class="text-xs opacity-60 mt-0.5">Admin Panel</div>
            </div>
        </div>

        <!-- Nav -->
        <p class="text-[10px] font-bold uppercase tracking-widest opacity-50 mb-2 px-3">Menu</p>
        <nav class="space-y-0.5 flex-1">
            <a href="beranda.php" class="nav-link active flex items-center gap-2.5 py-2.5 px-3 rounded-xl text-sm font-medium text-white/85 hover:bg-white/15 transition">
                <svg class="w-4 h-4 fill-current flex-shrink-0" viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                Dashboard
            </a>
            <a href="kelola_karyawan.php" class="flex items-center gap-2.5 py-2.5 px-3 rounded-xl text-sm font-medium text-white/85 hover:bg-white/15 transition">
                <svg class="w-4 h-4 fill-current flex-shrink-0" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                Kelola Karyawan
            </a>
            <a href="laporan_pendapatan.php" class="flex items-center gap-2.5 py-2.5 px-3 rounded-xl text-sm font-medium text-white/85 hover:bg-white/15 transition">
                <svg class="w-4 h-4 fill-current flex-shrink-0" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm7 13H5v-.23c0-.62.28-1.2.76-1.58C7.47 15.82 9.64 15 12 15s4.53.82 6.24 2.19c.48.38.76.97.76 1.58V19z"/></svg>
                Laporan Pendapatan
            </a>
            <a href="buka_cabang.php" class="flex items-center gap-2.5 py-2.5 px-3 rounded-xl text-sm font-medium text-white/85 hover:bg-white/15 transition">
                <svg class="w-4 h-4 fill-current flex-shrink-0" viewBox="0 0 24 24"><path d="M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V9h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10zm-2-8h-2v2h2v-2zm0 4h-2v2h2v-2z"/></svg>
                Kelola Cabang
            </a>

            <a href="ubah_akun.php" class="flex items-center gap-2.5 py-2.5 px-3 rounded-xl text-sm font-medium text-white/85 hover:bg-white/15 transition">
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
                Ubah Akun
            </a>
        </nav>

        <!-- Logout -->
        <div class="pt-4 border-t border-white/15">
            <a href="../logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar?')"
                class="flex items-center gap-2.5 py-2.5 px-3 rounded-xl text-sm font-semibold text-red-300 hover:bg-red-500/20 hover:text-red-200 transition">
                <svg class="w-4 h-4 fill-current flex-shrink-0" viewBox="0 0 24 24"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
                Logout
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="ml-60 p-8 w-full">

        <!-- Top Bar -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-purple-900">Dashboard</h1>
                <p class="text-sm text-purple-400 mt-0.5">Ringkasan informasi sistem laundry</p>
            </div>
            <div class="flex items-center gap-2 bg-white rounded-full px-4 py-1.5 border-2 border-purple-100">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-white"
                    style="background: linear-gradient(135deg, #7c3aed, #a855f7);">A</div>
                <span class="text-sm font-semibold text-purple-900">Admin</span>
            </div>
        </div>

        <!-- Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

            <a href="kelola_karyawan.php" class="card group bg-white rounded-2xl p-6 border-2 border-purple-100 hover:border-blue-200 hover:-translate-y-1 hover:shadow-xl transition-all duration-200 relative overflow-hidden no-underline block">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center mb-4" style="background: linear-gradient(135deg, #3b82f6, #60a5fa);">
                    <svg class="w-5 h-5 fill-white" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                </div>
                <h2 class="text-sm font-bold text-blue-700 mb-1">Kelola Karyawan</h2>
                <p class="text-xs text-gray-400 leading-relaxed">Atur informasi karyawan.</p>
                <div class="card-arrow absolute right-5 bottom-5 w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="#1d4ed8" stroke-width="2.5" stroke-linecap="round"><path d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>

            <a href="laporan_pendapatan.php" class="card group bg-white rounded-2xl p-6 border-2 border-purple-100 hover:border-purple-300 hover:-translate-y-1 hover:shadow-xl transition-all duration-200 relative overflow-hidden no-underline block">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center mb-4" style="background: linear-gradient(135deg, #7c3aed, #a855f7);">
                    <svg class="w-5 h-5 fill-white" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm7 13H5v-.23c0-.62.28-1.2.76-1.58C7.47 15.82 9.64 15 12 15s4.53.82 6.24 2.19c.48.38.76.97.76 1.58V19z"/></svg>
                </div>
                <h2 class="text-sm font-bold text-purple-700 mb-1">Laporan Pendapatan</h2>
                <p class="text-xs text-gray-400 leading-relaxed">Pantau pendapatan laundry.</p>
                <div class="card-arrow absolute right-5 bottom-5 w-7 h-7 rounded-full bg-purple-100 flex items-center justify-center">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="#5b21b6" stroke-width="2.5" stroke-linecap="round"><path d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>

            <a href="buka_cabang.php" class="card group bg-white rounded-2xl p-6 border-2 border-purple-100 hover:border-indigo-300 hover:-translate-y-1 hover:shadow-xl transition-all duration-200 relative overflow-hidden no-underline block">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center mb-4" style="background: linear-gradient(135deg, #4f46e5, #818cf8);">
                    <svg class="w-5 h-5 fill-white" viewBox="0 0 24 24"><path d="M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V9h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10zm-2-8h-2v2h2v-2zm0 4h-2v2h2v-2z"/></svg>
                </div>
                <h2 class="text-sm font-bold text-indigo-700 mb-1">Tambah Cabang</h2>
                <p class="text-xs text-gray-400 leading-relaxed">Membuka Cabang Baru.</p>
                <div class="card-arrow absolute right-5 bottom-5 w-7 h-7 rounded-full bg-indigo-100 flex items-center justify-center">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="#3730a3" stroke-width="2.5" stroke-linecap="round"><path d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>

        </div>
        <div class="bg-white rounded-2xl border-2 border-purple-100 p-5 mb-6 mt-3">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-bold text-purple-700">
                    Grafik Pendapatan 7 Hari Terakhir
                </h2>
            </div>

            <!-- Chart Container -->
            <div class="w-full h-[300px] sm:h-[350px] md:h-[400px]">
                <canvas id="pendapatanChart"></canvas>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
    const ctx = document.getElementById('pendapatanChart');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: <?= json_encode($data) ?>,
                borderColor: '#7c3aed',
                backgroundColor: 'rgba(124, 58, 237, 0.15)',
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointBackgroundColor: '#a855f7'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
    </script>
</body>
</html>