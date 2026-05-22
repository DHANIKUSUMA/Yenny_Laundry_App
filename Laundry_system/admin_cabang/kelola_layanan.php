<?php
include "../koneksi.php";
session_start();

// Proteksi Halaman
if (!isset($_SESSION['cabang_id']) || $_SESSION['role'] != 'admin_cabang') {
    header("location: login.php");
    exit;
}

$session_cabang_id = $_SESSION['cabang_id'];

// --- 1. PROSES SIMPAN LAYANAN BARU ---
if (isset($_POST['simpan_layanan'])) {
    $service_name = mysqli_real_escape_string($conn, $_POST['service_name']);
    $price        = mysqli_real_escape_string($conn, $_POST['price']);

    $query = "INSERT INTO layanan (cabang_id, nama_layanan, harga_per_kg) VALUES ('$session_cabang_id', '$service_name', '$price')";
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Layanan berhasil ditambahkan!'); window.location='kelola_layanan.php';</script>";
    }
}

// --- 2. PROSES UPDATE LAYANAN ---
if (isset($_POST['update_layanan'])) {
    $id           = $_POST['id'];
    $service_name = mysqli_real_escape_string($conn, $_POST['service_name']);
    $price        = mysqli_real_escape_string($conn, $_POST['price']);

    $query = "UPDATE layanan SET nama_layanan='$service_name', harga_per_kg='$price' WHERE id='$id' AND cabang_id='$session_cabang_id'";
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Layanan berhasil diperbarui!'); window.location='kelola_layanan.php';</script>";
    }
}

// --- 3. PROSES HAPUS LAYANAN ---
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Cek apakah layanan sedang digunakan di pesanan
    $cek_penggunaan = mysqli_query($conn, "SELECT id FROM order_items WHERE service_id = '$id'");
    if (mysqli_num_rows($cek_penggunaan) > 0) {
        echo "<script>alert('Gagal! Layanan tidak bisa dihapus karena sudah pernah digunakan dalam transaksi.'); window.location='kelola_layanan.php';</script>";
    } else {
        mysqli_query($conn, "DELETE FROM services WHERE id = '$id' AND cabang_id = '$cabang_id'");
        echo "<script>alert('Layanan berhasil dihapus!'); window.location='kelola_layanan.php';</script>";
    }
}

// --- 4. NONAKTIFKAN LAYANAN ---
if (isset($_GET['aksi']) && $_GET['aksi'] == 'nonaktif') {
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    mysqli_query($conn, "UPDATE layanan SET is_active = 0 WHERE id='$id' AND cabang_id='$session_cabang_id'");
    echo "<script>alert('Layanan dinonaktifkan'); window.location='kelola_layanan.php';</script>";
}

// --- 5. AKTIFKAN KEMBALI LAYANAN ---
if (isset($_GET['aksi']) && $_GET['aksi'] == 'aktifkan') {
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    mysqli_query($conn, "UPDATE layanan SET is_active = 1 WHERE id='$id' AND cabang_id='$session_cabang_id'");
    echo "<script>alert('Layanan diaktifkan kembali'); window.location='kelola_layanan.php';</script>";
}

// --- 4. AMBIL DATA LAYANAN (Filter per Cabang) ---
$result = mysqli_query($conn, "
    SELECT * FROM layanan 
    WHERE cabang_id = '$session_cabang_id'
    ORDER BY is_active DESC, nama_layanan ASC
");

// Ambil data untuk edit
$edit_data = null;
if (isset($_GET['aksi']) && $_GET['aksi'] == 'edit') {
    $id = $_GET['id'];
    $res_edit = mysqli_query($conn, "SELECT * FROM layanan WHERE id = '$id' AND cabang_id = '$session_cabang_id'");
    $edit_data = mysqli_fetch_assoc($res_edit);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Layanan - Yenny Laundry</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .nav-link.active { background: rgba(255,255,255,0.22); }
        @media (max-width: 768px) {
            aside { transform: translateX(-100%); transition: transform 0.3s ease; z-index: 100; }
            aside.open { transform: translateX(0); }
            main { margin-left: 0 !important; }
        }
    </style>
</head>
<body class="flex min-h-screen" style="background: linear-gradient(135deg, #f3f0ff 0%, #ede9fe 50%, #e0d7ff 100%);">

    <!-- Mobile Overlay -->
    <div id="overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden"></div>

    <!-- ===== SIDEBAR ===== -->
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
                <svg class="w-4 h-4 fill-current flex-shrink-0" viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                Dashboard
            </a>
            <a href="pesanan_kasir.php" class="flex items-center gap-2.5 py-2.5 px-3 rounded-xl text-white/80 hover:bg-white/15 transition">
                <svg class="w-4 h-4 fill-current flex-shrink-0" viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                Buat Pesanan
            </a>
            <a href="kelola_layanan.php" class="nav-link active flex items-center gap-2.5 py-2.5 px-3 rounded-xl text-white/85 transition">
                <svg class="w-4 h-4 fill-current flex-shrink-0" viewBox="0 0 24 24"><path d="M20 6H4V4H2v18h2v-2h16v2h2V4h-2v2zM4 8h16v8H4V8zm2 2v4h12v-4H6z"/></svg>
                Kelola Layanan
            </a>
            <a href="kelola_pelanggan.php" class="flex items-center gap-2.5 py-2.5 px-3 rounded-xl text-white/80 hover:bg-white/15 transition">
                <svg class="w-4 h-4 fill-current flex-shrink-0" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                Data Pelanggan
            </a>
            <a href="riwayat_transaksi.php" class="flex items-center gap-2.5 py-2.5 px-3 rounded-xl text-white/80 hover:bg-white/15 transition">
                <svg class="w-4 h-4 fill-current flex-shrink-0" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm7 13H5v-.23c0-.62.28-1.2.76-1.58C7.47 15.82 9.64 15 12 15s4.53.82 6.24 2.19c.48.38.76.97.76 1.58V19z"/></svg>
                Riwayat Transaksi
            </a>
        </nav>

        <div class="pt-4 border-t border-white/15">
            <a href="../logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar?')"
               class="flex items-center gap-2.5 py-2.5 px-3 rounded-xl text-sm font-semibold text-red-300 hover:bg-red-500/20 hover:text-red-200 transition">
                <svg class="w-4 h-4 fill-current flex-shrink-0" viewBox="0 0 24 24"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
                Logout
            </a>
        </div>
    </aside>

    <!-- ===== MAIN CONTENT ===== -->
    <main class="ml-60 p-6 md:p-8 w-full">

        <!-- Mobile Topbar -->
        <div class="flex md:hidden items-center justify-between mb-6 bg-white rounded-2xl px-4 py-3 border-2 border-purple-100 shadow-sm">
            <button onclick="toggleSidebar()" class="text-purple-600">
                <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24"><path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/></svg>
            </button>
            <span class="font-bold text-purple-900 text-sm">Yenny Laundry — Kasir</span>
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white" style="background: linear-gradient(135deg,#7c3aed,#a855f7);">K</div>
        </div>

        <!-- Desktop Header -->
        <div class="hidden md:flex items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-purple-900">Kelola Layanan</h1>
                <p class="text-sm text-purple-400 mt-0.5">Atur jenis layanan dan harga khusus cabang Anda.</p>
            </div>
            <button onclick="openModalTambah()"
                class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white transition hover:-translate-y-0.5 hover:shadow-lg"
                style="background: linear-gradient(135deg, #7c3aed, #a855f7);">
                <svg class="w-4 h-4 fill-white" viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                Layanan Baru
            </button>
        </div>

        <!-- Mobile Header -->
        <div class="flex md:hidden flex-col gap-3 mb-6">
            <div>
                <h1 class="text-xl font-bold text-purple-900">Kelola Layanan</h1>
                <p class="text-xs text-purple-400 mt-0.5">Atur layanan & harga cabang Anda.</p>
            </div>
            <button onclick="openModalTambah()"
                class="w-full text-center text-white px-4 py-2.5 rounded-xl font-bold text-sm transition"
                style="background: linear-gradient(135deg, #7c3aed, #a855f7);">
                + Layanan Baru
            </button>
        </div>

        <!-- Tabel Layanan -->
        <div class="bg-white rounded-2xl border-2 border-purple-100 overflow-hidden shadow-sm">
            <div class="p-6 border-b border-purple-50 bg-purple-50/30">
                <h2 class="text-lg font-bold text-purple-900">Daftar Layanan</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-purple-50/50 text-purple-500 uppercase text-[10px] font-black tracking-widest">
                            <th class="p-5">Nama Layanan</th>
                            <th class="p-5">Harga / Satuan</th>
                            <th class="p-5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-purple-50">
                        <?php if(mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr class="hover:bg-purple-50/30 transition">
                                <td class="p-5 font-bold text-purple-900"><?= $row['nama_layanan']; ?></td>
                                <td class="p-5 font-black text-purple-700">Rp <?= number_format($row['harga_per_kg'], 0, ',', '.'); ?></td>
                                <td class="p-5 text-center">
                                    <div class="flex justify-center gap-2">
                                        <div class="flex justify-center gap-2">
                                                <a href="kelola_layanan.php?aksi=edit&id=<?= $row['id']; ?>"
                                                class="bg-purple-50 text-purple-600 px-3 py-2 rounded-lg font-bold text-xs">
                                                    ✏️
                                                </a>

                                                <?php if($row['is_active'] == 1): ?>
                                                    <!-- Nonaktifkan -->
                                                    <a href="kelola_layanan.php?aksi=nonaktif&id=<?= $row['id']; ?>"
                                                    onclick="return confirm('Nonaktifkan layanan ini?')"
                                                    class="bg-yellow-50 text-yellow-600 px-3 py-2 rounded-lg font-bold text-xs">
                                                        ⛔
                                                    </a>
                                                <?php else: ?>
                                                    <!-- Aktifkan -->
                                                    <a href="kelola_layanan.php?aksi=aktifkan&id=<?= $row['id']; ?>"
                                                    onclick="return confirm('Aktifkan kembali layanan ini?')"
                                                    class="bg-green-50 text-green-600 px-3 py-2 rounded-lg font-bold text-xs">
                                                        ✔️
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="p-20 text-center text-purple-300 font-medium italic">Belum ada data layanan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- ===== MODAL TAMBAH LAYANAN ===== -->
    <div id="modalTambah" class="fixed inset-0 bg-black/50 hidden items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl w-full max-w-md p-8 shadow-2xl border-t-4 border-purple-500">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-purple-900">Tambah Layanan Baru</h2>
                <button onclick="closeModalTambah()" class="w-8 h-8 flex items-center justify-center rounded-full bg-purple-50 text-purple-400 hover:bg-purple-100 transition">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                </button>
            </div>
            <form method="POST" class="space-y-4">
                <div>
                    <label class="text-xs font-bold text-purple-400 uppercase tracking-widest block mb-1.5">Nama Layanan</label>
                    <input type="text" name="service_name" required placeholder="Contoh: Cuci Kering Setrika"
                           class="w-full border-2 border-purple-100 p-3 rounded-xl outline-none focus:ring-2 focus:ring-purple-400 focus:border-purple-300 text-sm text-purple-900 placeholder-purple-200 transition">
                </div>
                <div>
                    <label class="text-xs font-bold text-purple-400 uppercase tracking-widest block mb-1.5">Harga (Rp)</label>
                    <input type="number" name="price" required placeholder="0"
                           class="w-full border-2 border-purple-100 p-3 rounded-xl outline-none focus:ring-2 focus:ring-purple-400 focus:border-purple-300 text-sm text-purple-900 placeholder-purple-200 transition">
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeModalTambah()"
                            class="flex-1 bg-purple-50 py-3 rounded-xl font-bold text-purple-400 hover:bg-purple-100 transition text-sm">
                        Batal
                    </button>
                    <button type="submit" name="simpan_layanan"
                            class="flex-1 text-white py-3 rounded-xl font-bold text-sm transition hover:-translate-y-0.5 hover:shadow-lg"
                            style="background: linear-gradient(135deg, #7c3aed, #a855f7);">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== MODAL EDIT LAYANAN ===== -->
    <?php if ($edit_data): ?>
    <div class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl w-full max-w-md p-8 shadow-2xl border-t-4 border-purple-500">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-purple-900">Edit Layanan</h2>
                <a href="kelola_layanan.php" class="w-8 h-8 flex items-center justify-center rounded-full bg-purple-50 text-purple-400 hover:bg-purple-100 transition">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                </a>
            </div>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="id" value="<?= $edit_data['id']; ?>">
                <div>
                    <label class="text-xs font-bold text-purple-400 uppercase tracking-widest block mb-1.5">Nama Layanan</label>
                    <input type="text" name="service_name" value="<?= $edit_data['nama_layanan']; ?>" required
                           class="w-full border-2 border-purple-100 p-3 rounded-xl outline-none focus:ring-2 focus:ring-purple-400 focus:border-purple-300 text-sm text-purple-900 transition">
                </div>
                <div>
                    <label class="text-xs font-bold text-purple-400 uppercase tracking-widest block mb-1.5">Harga (Rp)</label>
                    <input type="number" name="price" value="<?= $edit_data['harga_per_kg']; ?>" required
                           class="w-full border-2 border-purple-100 p-3 rounded-xl outline-none focus:ring-2 focus:ring-purple-400 focus:border-purple-300 text-sm text-purple-900 transition">
                </div>
                <div class="flex gap-3 pt-2">
                    <a href="kelola_layanan.php"
                       class="flex-1 text-center bg-purple-50 py-3 rounded-xl font-bold text-purple-400 hover:bg-purple-100 transition text-sm">
                        Batal
                    </a>
                    <button type="submit" name="update_layanan"
                            class="flex-1 text-white py-3 rounded-xl font-bold text-sm transition hover:-translate-y-0.5 hover:shadow-lg"
                            style="background: linear-gradient(135deg, #7c3aed, #a855f7);">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            sidebar.classList.toggle('open');
            overlay.classList.toggle('hidden');
        }

        function openModalTambah() {
            const modal = document.getElementById('modalTambah');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModalTambah() {
            const modal = document.getElementById('modalTambah');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Tutup modal jika klik di luar area modal
        document.getElementById('modalTambah').addEventListener('click', function(e) {
            if (e.target === this) closeModalTambah();
        });
    </script>

</body>
</html>
