<?php
include "../koneksi.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pastikan hanya kasir yang bisa akses
if (!isset($_SESSION['cabang_id']) || $_SESSION['role'] != 'admin_cabang') {
    header("location: index.php");
    exit;
}

$session_cabang_id = $_SESSION['cabang_id'];

// --- 1. PROSES SIMPAN PELANGGAN ---
if (isset($_POST['simpan_pelanggan'])) {
    $name    = mysqli_real_escape_string($conn, $_POST['name']);
    $phone   = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    // Cek apakah nomor HP sudah ada di database
    $cek_hp = mysqli_query($conn, "SELECT id FROM pelanggan WHERE nomor_telepon = '$phone'");
    
    if (mysqli_num_rows($cek_hp) > 0) {
        echo "<script>alert('Gagal! Nomor HP $phone sudah terdaftar.'); window.history.back();</script>";
    } else {
        // Simpan dengan branch_id admin_cabang yang sedang login
        $query = "INSERT INTO pelanggan(nama, nomor_telepon, alamat, cabang_id) 
                  VALUES ('$name', '$phone', '$address', '$session_cabang_id')";
        
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Pelanggan berhasil ditambahkan!'); window.location='beranda_kasir.php';</script>";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pelanggan - Yenny Laundry</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .nav-link-active { background: rgba(255,255,255,0.22); }
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
            <a href="kelola_pelanggan.php" class="nav-link-active flex items-center gap-2.5 py-2.5 px-3 rounded-xl text-white/85 transition">
                <svg class="w-4 h-4 fill-current flex-shrink-0" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>Data Pelanggan
            </a>
            <a href="riwayat_transaksi.php" class="flex items-center gap-2.5 py-2.5 px-3 rounded-xl text-white/80 hover:bg-white/15 transition">
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

    <main class="main-content ml-60 p-6 md:p-8 w-full">
        <!-- Mobile Topbar -->
        <div class="flex md:hidden items-center justify-between mb-6 bg-white rounded-2xl px-4 py-3 border-2 border-purple-100 shadow-sm">
            <button onclick="toggleSidebar()" class="text-purple-600">
                <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24"><path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/></svg>
            </button>
            <span class="font-bold text-purple-900 text-sm">Tambah Pelanggan</span>
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white" style="background: linear-gradient(135deg,#7c3aed,#a855f7);">K</div>
        </div>

        <div class="max-w-xl mx-auto">
            <div class="mb-6">
                <a href="beranda_kasir.php" class="inline-flex items-center gap-1.5 text-purple-500 font-bold text-sm hover:text-purple-700 transition mb-3">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
                    Kembali ke Dashboard
                </a>
                <h1 class="text-2xl font-bold text-purple-900">Registrasi Pelanggan Baru</h1>
                <p class="text-sm text-purple-400 mt-0.5">Daftarkan pelanggan agar bisa mulai bertransaksi.</p>
            </div>

            <div class="bg-white rounded-2xl border-2 border-purple-100 shadow-sm overflow-hidden">
                <div class="px-8 py-5 border-b border-purple-50" style="background: linear-gradient(90deg, #f5f3ff, #ede9fe);">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg,#7c3aed,#a855f7);">
                            <svg class="w-4 h-4 fill-white" viewBox="0 0 24 24"><path d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-9-2V7H4v3H1v2h3v3h2v-3h3v-2H6zm9 4c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                        </div>
                        <span class="font-bold text-purple-900">Data Pelanggan</span>
                    </div>
                </div>

                <form method="POST" class="p-8 space-y-5">
                    <div>
                        <label class="text-xs font-bold text-purple-400 uppercase block mb-1.5">Nama Lengkap</label>
                        <input type="text" name="name" placeholder="Contoh: Budi Santoso" required 
                               class="w-full border-2 border-purple-100 px-3 py-2.5 rounded-xl text-sm outline-none focus:ring-2 focus:ring-purple-400 transition">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-purple-400 uppercase block mb-1.5">Nomor WhatsApp / HP</label>
                        <input type="number" name="phone" placeholder="081234567XXX" required 
                               class="w-full border-2 border-purple-100 px-3 py-2.5 rounded-xl text-sm outline-none focus:ring-2 focus:ring-purple-400 transition">
                        <p class="text-[10px] text-purple-300 mt-1">*Nomor HP menjadi identitas unik pelanggan.</p>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-purple-400 uppercase block mb-1.5">Alamat <span class="text-purple-300 normal-case font-normal">(Opsional)</span></label>
                        <textarea name="address" rows="3" placeholder="Alamat lengkap pelanggan..." 
                                  class="w-full border-2 border-purple-100 px-3 py-2.5 rounded-xl text-sm outline-none focus:ring-2 focus:ring-purple-400 transition resize-none"></textarea>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="reset" class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-purple-400 bg-purple-50 hover:bg-purple-100 transition">Reset</button>
                        <button type="submit" name="simpan_pelanggan" class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white transition hover:opacity-90" style="background: linear-gradient(135deg,#7c3aed,#a855f7);">Simpan Pelanggan</button>
                    </div>
                </form>
            </div>

            <div class="mt-4 bg-purple-50 p-4 rounded-2xl border border-purple-100 flex items-start gap-3">
                <svg class="w-4 h-4 fill-purple-400 flex-shrink-0 mt-0.5" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                <p class="text-xs text-purple-600 leading-relaxed">Setelah terdaftar, Anda dapat langsung membuat pesanan untuk pelanggan ini melalui menu <strong>Buat Pesanan</strong>.</p>
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