<?php
include "../koneksi.php";

// --- 1. PROSES TAMBAH CABANG + AKUN ---
if (isset($_POST['buka_cabang'])) {
    $nama_cabang = mysqli_real_escape_string($conn, $_POST['nama_cabang']);
    $alamat_cabang = mysqli_real_escape_string($conn, $_POST['alamat_cabang']);
    $phone_cabang = mysqli_real_escape_string($conn, $_POST['phone_cabang']);
    
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    mysqli_begin_transaction($conn);

    try {
        $query_cabang = "INSERT INTO cabang (nama_cabang, alamat, nomor_telepon) 
                         VALUES ('$nama_cabang', '$alamat_cabang', '$phone_cabang')";
        mysqli_query($conn, $query_cabang);
        
        $cabang_id = mysqli_insert_id($conn);

        $hash_password = password_hash($password, PASSWORD_DEFAULT);

        $query_user = "INSERT INTO users (cabang_id, username, password, role) 
                       VALUES ('$cabang_id', '$username', '$password', 'admin_cabang')";
        mysqli_query($conn, $query_user);

        mysqli_commit($conn);
        echo "<script>alert('Cabang dan Akun Kasir berhasil dibuat!'); window.location='buka_cabang.php';</script>";

    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<script>alert('Gagal membuka cabang: " . $e->getMessage() . "');</script>";
    }
}

// --- 2. PROSES EDIT CABANG + PASSWORD ---
if (isset($_POST['edit_cabang'])) {
    $id_cabang   = mysqli_real_escape_string($conn, $_POST['id_cabang']);
    $nama_cabang = mysqli_real_escape_string($conn, $_POST['nama_cabang']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password    = password_hash($_POST['password'], PASSWORD_DEFAULT);

    mysqli_begin_transaction($conn);

    try {
        // Update nama cabang dan username
        mysqli_query($conn, "UPDATE cabang SET nama_cabang='$nama_cabang' WHERE id='$id_cabang'");

        // Update password kasir , username='$username'
        if (!empty($password)) {

        $hash_password = password_hash($password, PASSWORD_DEFAULT);

        mysqli_query($conn, "UPDATE users 
                            SET password='$hash_password' 
                            WHERE cabang_id='$id_cabang' 
                            AND role='admin_cabang'");
        }

        mysqli_query($conn, "UPDATE users 
                             SET username='$username' 
                             WHERE cabang_id='$id_cabang' AND role='admin_cabang'");

        mysqli_commit($conn);
        echo "<script>alert('Data cabang berhasil diperbarui!'); window.location='buka_cabang.php';</script>";

    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<script>alert('Gagal update: " . $e->getMessage() . "');</script>";
    }
}

// --- 3. PROSES HAPUS CABANG ---
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id_cabang = mysqli_real_escape_string($conn, $_GET['id']);

    mysqli_begin_transaction($conn);

    try {
       // HAPUS PESANAN DULU
        mysqli_query($conn, "
            DELETE pesanan FROM pesanan
            JOIN layanan ON pesanan.layanan_id = layanan.id
            WHERE layanan.cabang_id = '$id_cabang'
        ");

        // HAPUS LAYANAN
        mysqli_query($conn, "
            DELETE FROM layanan 
            WHERE cabang_id = '$id_cabang'
        ");

        // HAPUS USER
        mysqli_query($conn, "
            DELETE FROM users 
            WHERE cabang_id = '$id_cabang'
        ");

        // HAPUS CABANG
        mysqli_query($conn, "
            DELETE FROM cabang 
            WHERE id = '$id_cabang'
        ");

        mysqli_commit($conn);
        echo "<script>alert('Cabang dan akun terkait berhasil dihapus!'); window.location='buka_cabang.php';</script>";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<script>alert('Gagal menghapus cabang: " . $e->getMessage() . "');</script>";
    }
}

// --- 4. QUERY TAMPIL DATA ---
$query = "SELECT cabang.*, users.username 
          FROM cabang 
          JOIN users ON cabang.id = users.cabang_id 
          WHERE users.role = 'admin_cabang'";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Cabang - Yenny Laundry</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .nav-link.active { background: rgba(255,255,255,0.22); }
        .modal-enter { animation: modalIn 0.22s cubic-bezier(.4,0,.2,1); }
        @keyframes modalIn {
            from { opacity: 0; transform: translateY(24px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
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
            <a href="laporan_pendapatan.php" class="flex items-center gap-2.5 py-2.5 px-3 rounded-xl text-sm font-medium text-white/85 hover:bg-white/15 transition">
                <svg class="w-4 h-4 fill-current flex-shrink-0" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm7 13H5v-.23c0-.62.28-1.2.76-1.58C7.47 15.82 9.64 15 12 15s4.53.82 6.24 2.19c.48.38.76.97.76 1.58V19z"/></svg>
                Laporan Pendapatan
            </a>
            <a href="buka_cabang.php" class="nav-link active flex items-center gap-2.5 py-2.5 px-3 rounded-xl text-sm font-medium text-white/85 hover:bg-white/15 transition">
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
                <h1 class="text-2xl font-bold text-purple-900">Kelola Cabang</h1>
                <p class="text-sm text-purple-400 mt-0.5">Manajemen cabang dan akun kasir</p>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="openModal('modalCabang')"
                    class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white transition hover:-translate-y-0.5 hover:shadow-lg"
                    style="background: linear-gradient(135deg, #7c3aed, #a855f7);">
                    <svg class="w-4 h-4 fill-white" viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                    Buka Cabang Baru
                </button>
                <div class="flex items-center gap-2 bg-white rounded-full px-4 py-1.5 border-2 border-purple-100">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-white"
                        style="background: linear-gradient(135deg, #7c3aed, #a855f7);">A</div>
                    <span class="text-sm font-semibold text-purple-900">Admin</span>
                </div>
            </div>
        </div>

        <!-- Tabel Cabang -->
        <div class="bg-white rounded-2xl border-2 border-purple-100 overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr style="background: linear-gradient(90deg, #f5f3ff, #ede9fe);">
                        <th class="px-5 py-3.5 text-xs font-bold text-purple-500 uppercase tracking-widest">Nama Cabang</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-purple-500 uppercase tracking-widest">Alamat</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-purple-500 uppercase tracking-widest">Kontak</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-purple-500 uppercase tracking-widest">Username Login</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-purple-500 uppercase tracking-widest text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-purple-50">
                    <?php 
                    $has_rows = false;
                    while($row = mysqli_fetch_assoc($result)):
                        $has_rows = true;
                    ?>
                    <tr class="hover:bg-purple-50/40 transition">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background: linear-gradient(135deg, #7c3aed22, #a855f722);">
                                    <svg class="w-4 h-4 fill-purple-500" viewBox="0 0 24 24"><path d="M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V9h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10zm-2-8h-2v2h2v-2zm0 4h-2v2h2v-2z"/></svg>
                                </div>
                                <span class="font-semibold text-purple-900"><?= $row['nama_cabang'] ?></span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-gray-400 text-xs"><?= $row['alamat'] ?></td>
                        <td class="px-5 py-3.5 text-gray-500"><?= $row['nomor_telepon'] ?></td>
                        <td class="px-5 py-3.5">
                            <span class="font-mono text-xs font-bold text-purple-600 bg-purple-50 px-2.5 py-1 rounded-lg"><?= $row['username'] ?></span>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <div class="flex items-center justify-center gap-2">

                                <!-- Tombol Edit -->
                                <button 
                                    onclick="openEditModal('<?= $row['id'] ?>','<?= $row['nama_cabang'] ?>')"
                                    class="p-2 rounded-lg bg-blue-50 text-blue-500 hover:bg-blue-100 transition"
                                    title="Edit Cabang">
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zm2.92 2.83H5v-.92l9.06-9.06.92.92L5.92 20.08zM20.71 7.04a1.003 1.003 0 000-1.42l-2.34-2.34a1.003 1.003 0 00-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.82z"/>
                                    </svg>
                                </button>

                                <!-- Tombol Hapus -->
                                <a href="buka_cabang.php?aksi=hapus&id=<?= $row['id'] ?>"
                                    onclick="return confirm('Menghapus cabang akan menghapus akun kasir terkait. Lanjutkan?')"
                                    class="p-2 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition"
                                    title="Hapus Cabang">
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                        <path d="M6 7h12v14H6V7zm3-4h6l1 2h4v2H2V5h4l1-2z"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if (!$has_rows): ?>
                    <tr>
                        <td colspan="5" class="px-5 py-16 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-10 h-10 text-purple-200 fill-current" viewBox="0 0 24 24"><path d="M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V9h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10zm-2-8h-2v2h2v-2zm0 4h-2v2h2v-2z"/></svg>
                                <p class="text-sm text-purple-300 font-medium">Belum ada cabang terdaftar.</p>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>

    <!-- Modal Buka Cabang Baru -->
    <div id="modalCabang" class="fixed inset-0 bg-black/40 backdrop-blur-sm hidden items-center justify-center p-4 z-50">
        <div class="modal-enter bg-white rounded-2xl w-full max-w-lg p-8 shadow-2xl border border-purple-100">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #7c3aed, #a855f7);">
                    <svg class="w-4 h-4 fill-white" viewBox="0 0 24 24"><path d="M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V9h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10zm-2-8h-2v2h2v-2zm0 4h-2v2h2v-2z"/></svg>
                </div>
                <h2 class="text-lg font-bold text-purple-900">Pendaftaran Cabang Baru</h2>
            </div>
            <form method="POST" class="space-y-5">

                <!-- Informasi Cabang -->
                <div class="bg-purple-50/60 rounded-xl p-4 border border-purple-100 space-y-3">
                    <p class="text-xs font-bold text-purple-500 uppercase tracking-widest">Informasi Cabang</p>
                    <div>
                        <label class="text-xs font-bold text-purple-400 block mb-1">Nama Cabang</label>
                        <input type="text" name="nama_cabang" placeholder="Contoh: Telang" required
                            class="w-full border-2 border-purple-100 bg-white px-3 py-2.5 rounded-xl text-sm outline-none focus:ring-2 focus:ring-purple-400 transition">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-purple-400 block mb-1">No. Telepon Cabang</label>
                        <input type="text" name="phone_cabang" placeholder="No. Telp Cabang" required
                            class="w-full border-2 border-purple-100 bg-white px-3 py-2.5 rounded-xl text-sm outline-none focus:ring-2 focus:ring-purple-400 transition">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-purple-400 block mb-1">Alamat Lengkap</label>
                        <textarea name="alamat_cabang" placeholder="Alamat Lengkap Cabang" rows="2"
                            class="w-full border-2 border-purple-100 bg-white px-3 py-2.5 rounded-xl text-sm outline-none focus:ring-2 focus:ring-purple-400 transition resize-none"></textarea>
                    </div>
                </div>

                <!-- Akun Kasir -->
                <div class="bg-blue-50/60 rounded-xl p-4 border border-blue-100 space-y-3">
                    <p class="text-xs font-bold text-blue-500 uppercase tracking-widest">Akun Login Kasir Cabang</p>
                    <div>
                        <label class="text-xs font-bold text-blue-400 block mb-1">Username</label>
                        <input type="text" name="username" placeholder="Username Login" required
                            class="w-full border-2 border-blue-100 bg-white px-3 py-2.5 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-400 transition">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-blue-400 block mb-1">Password</label>
                        <input type="password" name="password" placeholder="Password Login" required minlength="8"
                            class="w-full border-2 border-blue-100 bg-white px-3 py-2.5 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-400 transition">
                    </div>
                    <p class="text-[10px] text-blue-400">*Username ini akan digunakan karyawan cabang untuk login.</p>
                </div>

                <div class="flex gap-3 pt-1">
                    <button type="button" onclick="closeModal('modalCabang')"
                        class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-purple-400 bg-purple-50 hover:bg-purple-100 transition">Batal</button>
                    <button type="submit" name="buka_cabang"
                        class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white transition hover:opacity-90"
                        style="background: linear-gradient(135deg, #7c3aed, #a855f7);">Simpan & Buka</button>
                </div>
            </form>
        </div>
    </div>

        <div id="modalEdit" class="fixed inset-0 bg-black/40 backdrop-blur-sm hidden items-center justify-center p-4 z-50">
            <div class="modal-enter bg-white rounded-2xl w-full max-w-lg p-8 shadow-2xl border border-purple-100">
                
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-blue-500">
                        <svg class="w-4 h-4 fill-white" viewBox="0 0 24 24">
                            <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25z"/>
                        </svg>
                    </div>
                    <h2 class="text-lg font-bold text-purple-900">EDIT CABANG</h2>
                </div>

                <form method="POST" class="space-y-5">
                    <input type="hidden" name="id_cabang" id="edit_id">

                    <div>
                        <label class="text-xs font-bold text-purple-400 block mb-1">Nama Cabang</label>
                        <input type="text" name="nama_cabang" id="edit_nama" required
                            class="w-full border-2 border-purple-100 px-3 py-2.5 rounded-xl text-sm">
                    </div>

                    <div>
                        <label class="text-xs font-bold text-purple-400 block mb-1">username</label>
                        <input type="text" name="username" id="edit_nama" required
                            class="w-full border-2 border-purple-100 px-3 py-2.5 rounded-xl text-sm">
                    </div>

                    <div>
                        <label class="text-xs font-bold text-purple-400 block mb-1">Password Baru</label>
                        <input type="password" name="password" placeholder="Kosongkan jika tidak diubah"
                            class="w-full border-2 border-purple-100 px-3 py-2.5 rounded-xl text-sm">
                    </div>

                    <div class="flex gap-3">
                        <button type="button" onclick="closeModal('modalEdit')"
                            class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-gray-400 bg-gray-100">
                            Batal
                        </button>

                        <button type="submit" name="edit_cabang"
                            class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white bg-blue-500">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    <script>
        function openModal(id) {
            const el = document.getElementById(id);
            el.classList.remove('hidden');
            el.classList.add('flex');
        }
        function closeModal(id) {
            const el = document.getElementById(id);
            el.classList.add('hidden');
            el.classList.remove('flex');
        }
        document.querySelectorAll('[id^="modal"]').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) closeModal(this.id);
            });
        });

        function openEditModal(id, nama) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nama').value = nama;

            const modal = document.getElementById('modalEdit');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

    </script>

</body>
</html>
