<?php
include "../koneksi.php";
session_start();

// Proteksi Halaman
if (!isset($_SESSION['cabang_id']) || $_SESSION['role'] != 'admin_cabang') {
    header("location: login.php");
    exit;
}

// Ambil ID Cabang dari sesi
$session_cabang_id = $_SESSION['cabang_id'];

// --- 1. PROSES SIMPAN (Tambah Baru) ---
if (isset($_POST['simpan_pelanggan'])) {
    $name    = mysqli_real_escape_string($conn, $_POST['name']);
    $phone   = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    // Pengecekan apakah nomor HP sudah ada DI CABANG INI (Agar cabang lain bisa punya nomor sama jika perlu, atau tetap unik secara global)
    $cek_hp = mysqli_query($conn, "SELECT nomor_telepon FROM pelanggan WHERE nomor_telepon = '$phone' AND cabang_id = '$session_cabang_id'");
    
    if (mysqli_num_rows($cek_hp) > 0) {
        echo "<script>alert('Gagal! Nomor HP $phone sudah terdaftar di cabang ini.'); window.history.back();</script>";
    } else {
        // PERBAIKAN: Masukkan cabang_id saat simpan
        mysqli_query($conn, "INSERT INTO pelanggan (nama, nomor_telepon, alamat, cabang_id) VALUES ('$name', '$phone', '$address', '$cabang_id')");
        echo "<script>alert('Pelanggan berhasil ditambahkan!'); window.location='kelola_pelanggan.php';</script>";
    }
}

// --- 2. PROSES UPDATE (Edit Data) ---
if (isset($_POST['update_pelanggan'])) {
    $id      = $_POST['id'];
    $name    = mysqli_real_escape_string($conn, $_POST['name']);
    $phone   = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    // Cek HP apakah sudah dipakai orang LAIN di cabang yang sama
    $cek_hp = mysqli_query($conn, "SELECT nomor_telepon FROM pelanggan WHERE nomor_telepon = '$phone' AND id != '$id' AND cabang_id = '$session_cabang_id'");
    
    if (mysqli_num_rows($cek_hp) > 0) {
        echo "<script>alert('Gagal Update! Nomor HP sudah digunakan.'); window.history.back();</script>";
    } else {
        mysqli_query($conn, "UPDATE pelanggan SET nama='$name', nomor_telepon='$phone', alamat='$address' WHERE id='$id' AND cabang_id='$session_cabang_id'");
        echo "<script>alert('Data pelanggan diperbarui!'); window.location='kelola_pelanggan.php';</script>";
    }
}

// --- 3. PROSES HAPUS ---
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Pastikan hanya bisa hapus pelanggan milik cabangnya sendiri
    $cek = mysqli_query($conn, "SELECT id FROM pelanggan WHERE id = '$id' AND cabang_id = '$session_cabang_id'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Gagal! Pelanggan memiliki riwayat transaksi di cabang ini.'); window.location='kelola_pelanggan.php';</script>";
    } else {
        mysqli_query($conn, "DELETE FROM pelanggan WHERE id = '$id' AND cabang_id = '$session_cabang_id'");
        echo "<script>alert('Data pelanggan berhasil dihapus!'); window.location='kelola_pelanggan.php';</script>";
    }
}

// --- 4. LOGIKA PENCARIAN & TAMPIL DATA (FILTER CABANG) ---
$search = $_GET['search'] ?? '';

// PERBAIKAN: Tambahkan WHERE cabang_id agar data cabang lain tidak bocor
$query = "SELECT * FROM pelanggan WHERE cabang_id = '$session_cabang_id'";
          
if ($search != '') {
    $query .= " AND (nama LIKE '%$search%' OR nomor_telepon LIKE '%$search%')";
}
$query .= " ORDER BY nama ASC";
$result = mysqli_query($conn, $query);

// --- 5. AMBIL DATA EDIT ---
$edit_data = null;
if (isset($_GET['aksi']) && $_GET['aksi'] == 'edit') {
    $id = $_GET['id'];
    // Pastikan data yang diedit memang milik cabang tersebut
    $res_edit = mysqli_query($conn, "SELECT * FROM pelanggan WHERE id = '$id' AND cabang_id = '$session_cabang_id'");
    $edit_data = mysqli_fetch_assoc($res_edit);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pelanggan - Yenny Laundry</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .nav-link-active { background: rgba(255,255,255,0.22); }
        .modal-enter { animation: modalIn 0.22s cubic-bezier(.4,0,.2,1); }
        @keyframes modalIn { from { opacity:0; transform: translateY(20px) scale(0.97); } to { opacity:1; transform: translateY(0) scale(1); } }
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
            <span class="font-bold text-purple-900 text-sm">Data Pelanggan</span>
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white" style="background: linear-gradient(135deg,#7c3aed,#a855f7);">K</div>
        </div>

        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-purple-900">Data Pelanggan</h1>
                <p class="text-sm text-purple-400 mt-0.5">Total di cabang ini: <span class="font-bold"><?= mysqli_num_rows($result) ?></span> pelanggan.</p>
            </div>
            <button onclick="openModal('modalTambah')"
                class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white transition hover:-translate-y-0.5 hover:shadow-lg"
                style="background: linear-gradient(135deg, #7c3aed, #a855f7);">
                <svg class="w-4 h-4 fill-white" viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                Pelanggan Baru
            </button>
        </div>

        <div class="bg-white p-4 rounded-2xl border-2 border-purple-100 shadow-sm mb-6">
            <form method="GET" action="" class="flex gap-2">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                       placeholder="Cari nama atau no. HP..." 
                       class="flex-1 border-2 border-purple-100 p-3 rounded-xl text-sm outline-none focus:ring-2 focus:ring-purple-400 transition">
                <button type="submit" class="px-6 py-2 rounded-xl font-bold text-sm text-white transition" style="background: linear-gradient(135deg,#7c3aed,#a855f7);">Cari</button>
                <?php if($search): ?>
                    <a href="kelola_pelanggan.php" class="bg-purple-50 text-purple-500 px-4 py-2 rounded-xl font-bold text-sm flex items-center hover:bg-purple-100 transition">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="bg-white rounded-2xl border-2 border-purple-100 overflow-hidden shadow-sm">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr style="background: linear-gradient(90deg, #f5f3ff, #ede9fe);">
                        <th class="px-5 py-3.5 text-xs font-bold text-purple-500 uppercase tracking-widest">Info Pelanggan</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-purple-500 uppercase tracking-widest">No. Telepon</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-purple-500 uppercase tracking-widest text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-purple-50">
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr class="hover:bg-purple-50/40 transition">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0" style="background: linear-gradient(135deg,#7c3aed,#a855f7);">
                                        <?= strtoupper(substr($row['nama'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-purple-900"><?= $row['nama']; ?></p>
                                        <p class="text-xs text-gray-400 italic"><?= $row['alamat'] ?: 'Alamat tidak diisi'; ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-mono text-xs font-bold text-purple-600 bg-purple-50 px-2.5 py-1 rounded-lg"><?= $row['nomor_telepon']; ?></span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <div class="flex justify-center gap-2">
                                    <a href="kelola_pelanggan.php?aksi=edit&id=<?= $row['id']; ?>" 
                                       class="px-3 py-1.5 rounded-lg text-xs font-bold text-purple-600 bg-purple-50 hover:bg-purple-100 transition">Edit</a>
                                    <a href="kelola_pelanggan.php?aksi=hapus&id=<?= $row['id']; ?>" 
                                       onclick="return confirm('Hapus pelanggan ini?')"
                                       class="px-3 py-1.5 rounded-lg text-xs font-bold text-red-500 bg-red-50 hover:bg-red-100 transition">Hapus</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="px-5 py-16 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-10 h-10 fill-current text-purple-200" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                                <p class="text-sm text-purple-300 font-medium">Belum ada pelanggan di cabang ini.</p>
                            </div>
                        </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal Tambah -->
    <div id="modalTambah" class="fixed inset-0 bg-black/40 backdrop-blur-sm hidden items-center justify-center p-4 z-50">
        <div class="modal-enter bg-white rounded-2xl w-full max-w-md p-8 shadow-2xl border border-purple-100">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg,#7c3aed,#a855f7);">
                    <svg class="w-4 h-4 fill-white" viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                </div>
                <h2 class="text-lg font-bold text-purple-900">Tambah Pelanggan</h2>
            </div>
            <form method="POST" class="space-y-4">
                <div>
                    <label class="text-xs font-bold text-purple-400 uppercase block mb-1">Nama Lengkap</label>
                    <input type="text" name="name" required placeholder="Nama pelanggan" class="w-full border-2 border-purple-100 px-3 py-2.5 rounded-xl text-sm outline-none focus:ring-2 focus:ring-purple-400 transition">
                </div>
                <div>
                    <label class="text-xs font-bold text-purple-400 uppercase block mb-1">No. WhatsApp / Telepon</label>
                    <input type="text" name="phone" required placeholder="08xxx" class="w-full border-2 border-purple-100 px-3 py-2.5 rounded-xl text-sm outline-none focus:ring-2 focus:ring-purple-400 transition">
                </div>
                <div>
                    <label class="text-xs font-bold text-purple-400 uppercase block mb-1">Alamat</label>
                    <textarea name="address" rows="3" class="w-full border-2 border-purple-100 px-3 py-2.5 rounded-xl text-sm outline-none focus:ring-2 focus:ring-purple-400 transition resize-none"></textarea>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeModal('modalTambah')" class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-purple-400 bg-purple-50 hover:bg-purple-100 transition">Batal</button>
                    <button type="submit" name="simpan_pelanggan" class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white transition hover:opacity-90" style="background: linear-gradient(135deg,#7c3aed,#a855f7);">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($edit_data): ?>
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50">
        <div class="modal-enter bg-white rounded-2xl w-full max-w-md p-8 shadow-2xl border-t-4 border-purple-500">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-purple-100">
                    <svg class="w-4 h-4 fill-purple-600" viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1 1 0 000-1.41l-2.34-2.34a1 1 0 00-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                </div>
                <h2 class="text-lg font-bold text-purple-900">Edit Pelanggan</h2>
            </div>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="id" value="<?= $edit_data['id']; ?>">
                <div>
                    <label class="text-xs font-bold text-purple-400 uppercase block mb-1">Nama</label>
                    <input type="text" name="name" value="<?= $edit_data['nama']; ?>" required class="w-full border-2 border-purple-100 px-3 py-2.5 rounded-xl text-sm outline-none focus:ring-2 focus:ring-purple-400 transition">
                </div>
                <div>
                    <label class="text-xs font-bold text-purple-400 uppercase block mb-1">No. Telepon</label>
                    <input type="text" name="phone" value="<?= $edit_data['nomor_telepon']; ?>" required class="w-full border-2 border-purple-100 px-3 py-2.5 rounded-xl text-sm outline-none focus:ring-2 focus:ring-purple-400 transition">
                </div>
                <div>
                    <label class="text-xs font-bold text-purple-400 uppercase block mb-1">Alamat</label>
                    <textarea name="address" rows="3" class="w-full border-2 border-purple-100 px-3 py-2.5 rounded-xl text-sm outline-none focus:ring-2 focus:ring-purple-400 transition resize-none"><?= $edit_data['alamat']; ?></textarea>
                </div>
                <div class="flex gap-3 pt-2">
                    <a href="kelola_pelanggan.php" class="flex-1 text-center py-2.5 rounded-xl text-sm font-semibold text-purple-400 bg-purple-50 hover:bg-purple-100 transition">Batal</a>
                    <button type="submit" name="update_pelanggan" class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white transition hover:opacity-90" style="background: linear-gradient(135deg,#7c3aed,#a855f7);">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script>
        function openModal(id) { const el = document.getElementById(id); el.classList.remove('hidden'); el.classList.add('flex'); }
        function closeModal(id) { const el = document.getElementById(id); el.classList.add('hidden'); el.classList.remove('flex'); }
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('overlay').classList.toggle('hidden');
        }
        document.querySelectorAll('[id^="modal"]').forEach(modal => {
            modal.addEventListener('click', function(e) { if (e.target === this) closeModal(this.id); });
        });
    </script>
</body>
</html>