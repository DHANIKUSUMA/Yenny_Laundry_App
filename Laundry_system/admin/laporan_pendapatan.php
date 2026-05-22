<?php
include "../koneksi.php";
session_start();

// Proteksi: Hanya Admin yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'super_admin') {
    header("location: login.php");
    exit;
}

// Filter Tanggal & Cabang
$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-01'); 
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');    
$filter_cabang = $_GET['cabang_id'] ?? 'semua';

$query_text = "SELECT 
                pesanan.*, 
                cabang.nama_cabang as nama_cabang, 
                pelanggan.nama as nama_pelanggan 
               FROM pesanan
               JOIN cabang ON pesanan.cabang_id = cabang.id 
               JOIN pelanggan ON pesanan.pelanggan_id = pelanggan.id
               WHERE pesanan.tanggal_masuk BETWEEN '$tgl_mulai 00:00:00' AND '$tgl_akhir 23:59:59'
               AND pesanan.status = 'diambil'"; 

if ($filter_cabang != 'semua') {
    $query_text .= " AND pesanan.cabang_id = '$filter_cabang'";
}

$query_text .= " ORDER BY pesanan.tanggal_masuk DESC";
$result = mysqli_query($conn, $query_text);

if (!$result) {
    die("Error Database: " . mysqli_error($conn));
}

$total_pendapatan = 0;
$data_laporan = [];
while ($row = mysqli_fetch_assoc($result)) {
    $total_pendapatan += $row['total_harga'];
    $data_laporan[] = $row;
}

$cabang_list = mysqli_query($conn, "SELECT id, nama_cabang FROM cabang");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pendapatan - Yenny Laundry</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .nav-link.active { background: rgba(255,255,255,0.22); }
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
            <a href="beranda.php" class="flex items-center gap-2.5 py-2.5 px-3 rounded-xl text-sm font-medium text-white/85 hover:bg-white/15 transition">
                <svg class="w-4 h-4 fill-current flex-shrink-0" viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                Dashboard
            </a>
            <a href="kelola_karyawan.php" class="flex items-center gap-2.5 py-2.5 px-3 rounded-xl text-sm font-medium text-white/85 hover:bg-white/15 transition">
                <svg class="w-4 h-4 fill-current flex-shrink-0" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                Kelola Karyawan
            </a>
            <a href="laporan_pendapatan.php" class="nav-link active flex items-center gap-2.5 py-2.5 px-3 rounded-xl text-sm font-medium text-white/85 hover:bg-white/15 transition">
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
                <h1 class="text-2xl font-bold text-purple-900">Laporan Pendapatan</h1>
                <p class="text-sm text-purple-400 mt-0.5">Rekapitulasi keuangan seluruh cabang</p>
            </div>
            <div class="flex items-center gap-2 bg-white rounded-full px-4 py-1.5 border-2 border-purple-100">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-white"
                    style="background: linear-gradient(135deg, #7c3aed, #a855f7);">A</div>
                <span class="text-sm font-semibold text-purple-900">Admin</span>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="bg-white rounded-2xl p-5 border-2 border-purple-100 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                <div>
                    <label class="text-xs font-bold text-purple-400 uppercase tracking-widest block mb-1.5">Dari Tanggal</label>
                    <input type="date" name="tgl_mulai" value="<?= $tgl_mulai ?>"
                        class="w-full border-2 border-purple-100 bg-purple-50/40 px-3 py-2.5 rounded-xl text-sm text-purple-900 outline-none focus:ring-2 focus:ring-purple-400 transition">
                </div>
                <div>
                    <label class="text-xs font-bold text-purple-400 uppercase tracking-widest block mb-1.5">Sampai Tanggal</label>
                    <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>"
                        class="w-full border-2 border-purple-100 bg-purple-50/40 px-3 py-2.5 rounded-xl text-sm text-purple-900 outline-none focus:ring-2 focus:ring-purple-400 transition">
                </div>
                <div>
                    <label class="text-xs font-bold text-purple-400 uppercase tracking-widest block mb-1.5">Cabang</label>
                    <select name="cabang_id"
                        class="w-full border-2 border-purple-100 bg-purple-50/40 px-3 py-2.5 rounded-xl text-sm text-purple-900 outline-none focus:ring-2 focus:ring-purple-400 transition">
                        <option value="semua">Semua Cabang</option>
                        <?php while($cb = mysqli_fetch_assoc($cabang_list)): ?>
                            <option value="<?= $cb['id'] ?>" <?= $filter_cabang == $cb['id'] ? 'selected' : '' ?>><?= $cb['nama_cabang'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit"
                    class="flex items-center justify-center gap-2 py-2.5 px-5 rounded-xl text-sm font-bold text-white transition hover:-translate-y-0.5 hover:shadow-lg"
                    style="background: linear-gradient(135deg, #7c3aed, #a855f7);">
                    <svg class="w-4 h-4 fill-white" viewBox="0 0 24 24"><path d="M10 18h4v-2h-4v2zM3 6v2h18V6H3zm3 7h12v-2H6v2z"/></svg>
                    Filter Data
                </button>
                <a href="cetak_laporan.php?tgl_mulai=<?= $tgl_mulai ?>&tgl_akhir=<?= $tgl_akhir ?>&cabang_id=<?= $filter_cabang ?>"
                        class="py-2.5 px-5 rounded-xl text-sm font-bold text-white"
                        style="background: linear-gradient(135deg, #ef4444, #f97316);">
                        Cetak PDF
                </a>
            </form>
        </div>

        <!-- Total Omzet Card -->
        <div class="rounded-2xl p-6 mb-6 relative overflow-hidden" style="background: linear-gradient(135deg, #7c3aed 0%, #9333ea 60%, #a855f7 100%);">
            <!-- Decorative circle -->
            <div class="absolute -right-8 -top-8 w-40 h-40 rounded-full bg-white/10"></div>
            <div class="absolute -right-2 bottom-0 w-24 h-24 rounded-full bg-white/5"></div>
            <p class="text-purple-200 text-xs font-bold uppercase tracking-widest mb-1 relative z-10">Total Omzet Periode Ini</p>
            <h2 class="text-4xl font-black text-white relative z-10">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></h2>
            <p class="text-purple-300 text-xs mt-2 relative z-10"><?= date('d M Y', strtotime($tgl_mulai)) ?> &mdash; <?= date('d M Y', strtotime($tgl_akhir)) ?></p>
        </div>

        <!-- Tabel Transaksi -->
        <div class="bg-white rounded-2xl border-2 border-purple-100 overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr style="background: linear-gradient(90deg, #f5f3ff, #ede9fe);">
                        <th class="px-5 py-3.5 text-xs font-bold text-purple-500 uppercase tracking-widest">Waktu</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-purple-500 uppercase tracking-widest">Cabang</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-purple-500 uppercase tracking-widest">Pelanggan</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-purple-500 uppercase tracking-widest text-right">Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-purple-50">
                    <?php if(empty($data_laporan)): ?>
                        <tr>
                            <td colspan="4" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-10 h-10 text-purple-200 fill-current" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm7 13H5v-.23c0-.62.28-1.2.76-1.58C7.47 15.82 9.64 15 12 15s4.53.82 6.24 2.19c.48.38.76.97.76 1.58V19z"/></svg>
                                    <p class="text-sm text-purple-300 font-medium">Tidak ada transaksi pada periode ini.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($data_laporan as $row): ?>
                        <tr class="hover:bg-purple-50/40 transition">
                            <td class="px-5 py-3.5 text-gray-400 text-xs"><?= date('d/m/Y H:i', strtotime($row['tanggal_masuk'])) ?></td>
                            <td class="px-5 py-3.5">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-bold text-purple-700 bg-purple-100 uppercase"><?= $row['nama_cabang'] ?></span>
                            </td>
                            <td class="px-5 py-3.5 font-semibold text-purple-900"><?= $row['nama_pelanggan'] ?></td>
                            <td class="px-5 py-3.5 text-right font-black text-purple-700">Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>

</body>
</html>
