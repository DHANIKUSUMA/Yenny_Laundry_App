<?php
include "../koneksi.php";
session_start();

// 1. Proteksi Halaman (Hanya Kasir)
if (!isset($_SESSION['cabang_id']) || $_SESSION['role'] != 'admin_cabang') {
    header("location: login.php");
    exit;
}

$session_cabang_id = $_SESSION['cabang_id'];
$nama_kasir = $_SESSION['username'];

// --- Statistik Riwayat ---
// Total pesanan yang sudah selesai/diambil
$jml_selesai = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM pesanan WHERE cabang_id='$session_cabang_id' AND status = 'diambil'"));

// Menghitung TOTAL OMSET (Semua pesanan yang masuk di cabang ini)
$query_omset = mysqli_query($conn, "SELECT SUM(total_harga) as total FROM pesanan WHERE cabang_id = '$session_cabang_id' and status = 'diambil'");
$data_omset = mysqli_fetch_assoc($query_omset);
$total_omset = $data_omset['total'] ?? 0;

// --- 2. QUERY DATA RIWAYAT (Hanya status 'diambil') ---
$query_riwayat = "SELECT pesanan.*, pelanggan.nama as nama_pelanggan 
                  FROM pesanan
                  JOIN pelanggan ON pesanan.pelanggan_id = pelanggan.id 
                  WHERE pesanan.cabang_id = '$session_cabang_id' AND pesanan.status = 'diambil'
                  ORDER BY pesanan.id DESC";
$riwayat = mysqli_query($conn, $query_riwayat);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - Yenny Laundry</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .nav-link-active { background: rgba(255,255,255,0.22); }
        @media print { .no-print { display: none; } aside { display: none; } .main-content { margin-left: 0; } }
        @media (max-width: 768px) {
            aside { transform: translateX(-100%); transition: transform 0.3s ease; }
            aside.open { transform: translateX(0); }
            .main-content { margin-left: 0 !important; }
        }
    </style>
</head>
<body class="flex min-h-screen" style="background: linear-gradient(135deg, #f3f0ff 0%, #ede9fe 50%, #e0d7ff 100%);">

    <div id="overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden"></div>

    <aside id="sidebar" class="w-60 min-h-screen fixed top-0 left-0 bottom-0 flex flex-col p-5 text-white z-50"
        style="background: linear-gradient(180deg, #7c3aed 0%, #9333ea 60%, #a855f7 100%);">
        <div class="flex items-center gap-3 mb-8 pb-5 border-b border-white/15">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-white/20">
                <svg class="w-5 h-5 fill-white" viewBox="0 0 24 24"><path d="M20 6H4V4H2v18h2v-2h16v2h2V4h-2v2zM4 8h16v8H4V8zm2 2v4h12v-4H6z"/></svg>
            </div>
            <div>
                <div class="text-sm font-bold leading-tight">Yenny Laundry</div>
                <div class="text-xs opacity-60">Kasir Panel</div>
            </div>
        </div>
        <p class="text-[10px] font-bold uppercase tracking-widest opacity-50 mb-2 px-3">Menu</p>
        <nav class="space-y-0.5 flex-1 text-sm font-medium">
            <a href="beranda_kasir.php" class="flex items-center gap-2.5 py-2.5 px-3 rounded-xl text-white/80 hover:bg-white/15 transition">
                <svg class="w-4 h-4 fill-current flex-shrink-0" viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>Dashboard
            </a>
            <a href="pesanan_kasir.php" class="flex items-center gap-2.5 py-2.5 px-3 rounded-xl text-white/80 hover:bg-white/15 transition">
                <svg class="w-4 h-4 fill-current flex-shrink-0" viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>Buat Pesanan
            </a>
            <a href="kelola_layanan.php" class="flex items-center gap-2.5 py-2.5 px-3 rounded-xl text-white/80 hover:bg-white/15 transition">
                <svg class="w-4 h-4 fill-current flex-shrink-0" viewBox="0 0 24 24"><path d="M20 6H4V4H2v18h2v-2h16v2h2V4h-2v2zM4 8h16v8H4V8zm2 2v4h12v-4H6z"/></svg>Kelola Layanan
            </a>
            <a href="kelola_pelanggan.php" class="flex items-center gap-2.5 py-2.5 px-3 rounded-xl text-white/80 hover:bg-white/15 transition">
                <svg class="w-4 h-4 fill-current flex-shrink-0" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>Data Pelanggan
            </a>
            <a href="riwayat_transaksi.php" class="nav-link-active flex items-center gap-2.5 py-2.5 px-3 rounded-xl text-white/85 transition">
                <svg class="w-4 h-4 fill-current flex-shrink-0" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm7 13H5v-.23c0-.62.28-1.2.76-1.58C7.47 15.82 9.64 15 12 15s4.53.82 6.24 2.19c.48.38.76.97.76 1.58V19z"/></svg>Riwayat Transaksi
            </a>
        </nav>
        <div class="pt-4 border-t border-white/15">
            <a href="../logout.php" onclick="return confirm('Keluar dari sistem?')"
               class="flex items-center gap-2.5 py-2.5 px-3 rounded-xl text-sm font-semibold text-red-300 hover:bg-red-500/20 hover:text-red-200 transition">
                <svg class="w-4 h-4 fill-current flex-shrink-0" viewBox="0 0 24 24"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
                Logout
            </a>
        </div>
    </aside>

    <main class="main-content no-print ml-60 p-6 md:p-8 w-full">
        <!-- Mobile Topbar -->
        <div class="flex md:hidden items-center justify-between mb-6 bg-white rounded-2xl px-4 py-3 border-2 border-purple-100 shadow-sm">
            <button onclick="toggleSidebar()" class="text-purple-600">
                <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24"><path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/></svg>
            </button>
            <span class="font-bold text-purple-900 text-sm">Riwayat Transaksi</span>
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white" style="background: linear-gradient(135deg,#7c3aed,#a855f7);">K</div>
        </div>

        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-purple-900">Riwayat Transaksi</h1>
                <p class="text-sm text-purple-400 mt-0.5">Cabang #<?= $session_cabang_id ?> | Arsip pesanan selesai.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl border-2 border-purple-100 shadow-sm relative overflow-hidden">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-green-50 rounded-full"></div>
                <p class="text-xs font-bold text-green-500 uppercase tracking-widest mb-1 relative z-10">Pesanan Selesai</p>
                <h3 class="text-4xl font-bold text-purple-900 relative z-10"><?= $jml_selesai ?></h3>
            </div>
            <div class="bg-white p-6 rounded-2xl border-2 border-purple-100 shadow-sm relative overflow-hidden">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-purple-50 rounded-full"></div>
                <p class="text-xs font-bold text-purple-400 uppercase tracking-widest mb-1 relative z-10">Total Omzet</p>
                <h3 class="text-3xl font-bold text-purple-900 relative z-10">Rp <?= number_format($total_omset, 0, ',', '.') ?></h3>
            </div>
        </div>

        <div class="bg-white rounded-2xl border-2 border-purple-100 overflow-hidden shadow-sm">
            <div class="px-6 py-4 border-b border-purple-50 flex items-center justify-between" style="background: linear-gradient(90deg, #f5f3ff, #ede9fe);">
                <h2 class="font-bold text-purple-900">Arsip Pesanan</h2>
                <span class="bg-purple-100 text-purple-600 text-xs font-bold px-3 py-1 rounded-full uppercase">Status: Diambil</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr style="background: linear-gradient(90deg, #f5f3ff, #ede9fe);">
                            <th class="px-5 py-3.5 text-xs font-bold text-purple-500 uppercase tracking-widest">Nota</th>
                            <th class="px-5 py-3.5 text-xs font-bold text-purple-500 uppercase tracking-widest">Pelanggan</th>
                            <th class="px-5 py-3.5 text-xs font-bold text-purple-500 uppercase tracking-widest">Tgl. Masuk</th>
                            <th class="px-5 py-3.5 text-xs font-bold text-purple-500 uppercase tracking-widest">Total Bayar</th>
                            <th class="px-5 py-3.5 text-xs font-bold text-purple-500 uppercase tracking-widest text-center">Status</th>
                            <th class="px-5 py-3.5 text-xs font-bold text-purple-500 uppercase tracking-widest text-center no-print">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-purple-50">
                        <?php if(mysqli_num_rows($riwayat) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($riwayat)): ?>
                            <tr class="hover:bg-purple-50/40 transition">
                                <td class="px-5 py-3.5 font-mono font-bold text-purple-500">#<?= $row['nomor_nota'] ?></td>
                                <td class="px-5 py-3.5 font-semibold text-purple-900"><?= $row['nama_pelanggan'] ?></td>
                                <td class="px-5 py-3.5 text-gray-400 text-xs"><?= date('d M Y H:i', strtotime($row['tanggal_masuk'])) ?></td>
                                <td class="px-5 py-3.5 font-bold text-green-600">Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="bg-purple-50 text-purple-500 px-2.5 py-1 rounded-lg font-bold text-[10px] uppercase border border-purple-100">Diambil</span>
                                </td>
                                <td class="px-5 py-3.5 text-center no-print">
                                    <a href="cetak_nota.php?id=<?= $row['id'] ?>" target="_blank"
                                       class="px-3 py-1.5 rounded-lg text-xs font-bold text-purple-600 bg-purple-50 hover:bg-purple-100 transition">Nota</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-10 h-10 fill-current text-purple-200" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm7 13H5v-.23c0-.62.28-1.2.76-1.58C7.47 15.82 9.64 15 12 15s4.53.82 6.24 2.19c.48.38.76.97.76 1.58V19z"/></svg>
                                    <p class="text-sm text-purple-300 font-medium">Belum ada riwayat transaksi.</p>
                                </div>
                            </td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('overlay').classList.toggle('hidden');
        }
    </script>
</body>
</html>