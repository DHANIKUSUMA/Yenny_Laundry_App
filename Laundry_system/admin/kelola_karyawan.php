<?php
include "../koneksi.php";

// 1. TANGKAP ID CABANG DARI URL ATAU FORM
$id_cabang_pilihan = $_REQUEST['cabang_id'] ?? '';

// 2. PROSES HAPUS
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id = $_GET['id'];
    mysqli_query($conn, "DELETE FROM karyawan WHERE id = '$id'");
    echo "<script>alert('Karyawan dihapus!'); window.location='kelola_karyawan.php?cabang_id=$id_cabang_pilihan';</script>";
}

// 3. PROSES SIMPAN (TAMBAH)
if (isset($_POST['simpan_karyawan'])) {
    $br_id     = $_POST['cabang_id'];
    $nama      = mysqli_real_escape_string($conn, $_POST['nama']);
    $nik       = mysqli_real_escape_string($conn, $_POST['nik']);
    $nomor_telepon = mysqli_real_escape_string($conn, $_POST['nomor_telepon']);
    $alamat   = mysqli_real_escape_string($conn, $_POST['alamat']);

    mysqli_query($conn, "INSERT INTO karyawan(cabang_id, nama, nomor_telepon, alamat, nik) VALUES ('$br_id', '$nama', '$nomor_telepon', '$alamat','$nik')");
    echo "<script>window.location='kelola_karyawan.php?cabang_id=$br_id';</script>";
}

// 4. PROSES UPDATE (EDIT)
if (isset($_POST['update_karyawan'])) {
    $id        = $_POST['id'];
    $br_id     = $_POST['cabang_id'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $nik       = mysqli_real_escape_string($conn, $_POST['nik']);
    $nomor_telepon     = mysqli_real_escape_string($conn, $_POST['nomor_telepon']);
    $alamat   = mysqli_real_escape_string($conn, $_POST['alamat']);

    mysqli_query($conn, "UPDATE karyawan SET nama='$nama', nik='$nik', nomor_telepon='$nomor_telepon', alamat='$alamat' WHERE id='$id'");
    echo "<script>alert('Data diperbarui!'); window.location='kelola_karyawan.php?cabang_id=$br_id';</script>";
}

// 5. AMBIL DATA EDIT
$edit_data = null;
if (isset($_GET['aksi']) && $_GET['aksi'] == 'edit') {
    $id = $_GET['id'];
    $res_edit = mysqli_query($conn, "SELECT * FROM karyawan WHERE id = '$id'");
    $edit_data = mysqli_fetch_assoc($res_edit);
}

// 6. QUERY UNTUK FORM & TABEL
$query_all_cabang = mysqli_query($conn, "SELECT * FROM cabang");
$result_karyawan = [];
if ($id_cabang_pilihan) {
    $result_karyawan = mysqli_query($conn, "SELECT * FROM karyawan WHERE cabang_id = '$id_cabang_pilihan'");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Karyawan - Yenny Laundry</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .nav-link.active { background: rgba(255,255,255,0.22); }
        /* Modal animation */
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
            <a href="kelola_karyawan.php" class="nav-link active flex items-center gap-2.5 py-2.5 px-3 rounded-xl text-sm font-medium text-white/85 hover:bg-white/15 transition">
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
                <h1 class="text-2xl font-bold text-purple-900">Kelola Karyawan</h1>
                <p class="text-sm text-purple-400 mt-0.5">Manajemen data staf per cabang</p>
            </div>
            <div class="flex items-center gap-2 bg-white rounded-full px-4 py-1.5 border-2 border-purple-100">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-white"
                    style="background: linear-gradient(135deg, #7c3aed, #a855f7);">A</div>
                <span class="text-sm font-semibold text-purple-900">Admin</span>
            </div>
        </div>

        <!-- Filter Cabang -->
        <div class="bg-white rounded-2xl p-5 border-2 border-purple-100 mb-6 flex items-end gap-4">
            <form method="GET" action="" class="flex items-end gap-4 w-full">
                <div class="flex-1">
                    <label class="block text-xs font-bold text-purple-400 uppercase tracking-widest mb-1.5">Pilih Cabang</label>
                    <select name="cabang_id" onchange="this.form.submit()"
                        class="w-full border-2 border-purple-100 bg-purple-50/40 px-3 py-2.5 rounded-xl text-sm text-purple-900 outline-none focus:ring-2 focus:ring-purple-400 transition">
                        <option value="">-- Pilih Cabang --</option>
                        <?php while($cb = mysqli_fetch_assoc($query_all_cabang)): ?>
                            <option value="<?= $cb['id']; ?>" <?= ($id_cabang_pilihan == $cb['id']) ? 'selected' : ''; ?>>
                                <?= $cb['nama_cabang']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <?php if($id_cabang_pilihan): ?>
                <button type="button" onclick="openModal('modalTambah')"
                    class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white transition hover:-translate-y-0.5 hover:shadow-lg"
                    style="background: linear-gradient(135deg, #7c3aed, #a855f7);">
                    <svg class="w-4 h-4 fill-white" viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                    Tambah Karyawan
                </button>
                <?php endif; ?>
            </form>
        </div>

        <!-- Tabel Karyawan -->
        <?php if($id_cabang_pilihan): ?>
        <div class="bg-white rounded-2xl border-2 border-purple-100 overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr style="background: linear-gradient(90deg, #f5f3ff, #ede9fe);">
                        <th class="px-5 py-3.5 text-xs font-bold text-purple-500 uppercase tracking-widest">Nama Karyawan</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-purple-500 uppercase tracking-widest">NIK</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-purple-500 uppercase tracking-widest">WhatsApp</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-purple-500 uppercase tracking-widest">Alamat</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-purple-500 uppercase tracking-widest text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-purple-50">
                    <?php if(mysqli_num_rows($result_karyawan) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result_karyawan)): ?>
                        <tr class="hover:bg-purple-50/40 transition">
                            <td class="px-5 py-3.5 font-semibold text-purple-900"><?= $row['nama']; ?></td>
                            <td class="px-5 py-3.5 text-gray-500 font-mono text-xs"><?= $row['nik']; ?></td>
                            <td class="px-5 py-3.5 text-gray-500"><?= $row['nomor_telepon']; ?></td>
                            <td class="px-5 py-3.5 text-gray-400 text-xs"><?= $row['alamat']; ?></td>
                            <td class="px-5 py-3.5 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="kelola_karyawan.php?aksi=edit&id=<?= $row['id']; ?>&cabang_id=<?= $id_cabang_pilihan ?>"
                                        class="px-3 py-1.5 rounded-lg text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 transition">Edit</a>
                                    <a href="kelola_karyawan.php?aksi=hapus&id=<?= $row['id']; ?>&cabang_id=<?= $id_cabang_pilihan ?>"
                                        onclick="return confirm('Hapus karyawan ini?')"
                                        class="px-3 py-1.5 rounded-lg text-xs font-bold text-red-500 bg-red-50 hover:bg-red-100 transition">Hapus</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-10 h-10 text-purple-200 fill-current" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                                    <p class="text-sm text-purple-300 font-medium">Belum ada karyawan di cabang ini.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php else: ?>
        <div class="bg-white rounded-2xl border-2 border-dashed border-purple-200 p-20 text-center">
            <svg class="w-12 h-12 fill-current text-purple-200 mx-auto mb-3" viewBox="0 0 24 24"><path d="M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V9h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10zm-2-8h-2v2h2v-2zm0 4h-2v2h2v-2z"/></svg>
            <p class="text-purple-300 font-medium text-sm">Pilih cabang terlebih dahulu untuk melihat data karyawan.</p>
        </div>
        <?php endif; ?>

    </main>

    <!-- Modal Tambah Karyawan -->
    <div id="modalTambah" class="fixed inset-0 bg-black/40 backdrop-blur-sm hidden items-center justify-center p-4 z-50">
        <div class="modal-enter bg-white rounded-2xl w-full max-w-md p-8 shadow-2xl border border-purple-100">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #7c3aed, #a855f7);">
                    <svg class="w-4 h-4 fill-white" viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                </div>
                <h2 class="text-lg font-bold text-purple-900">Tambah Karyawan</h2>
            </div>
            <form method="POST" class="space-y-3">
                <input type="hidden" name="cabang_id" value="<?= $id_cabang_pilihan ?>">
                <div>
                    <label class="text-xs font-bold text-purple-400 uppercase tracking-widest block mb-1">Nama Lengkap</label>
                    <input type="text" name="nama" placeholder="Nama Lengkap" required
                        class="w-full border-2 border-purple-100 bg-purple-50/30 px-3 py-2.5 rounded-xl text-sm outline-none focus:ring-2 focus:ring-purple-400 focus:border-purple-300 transition">
                </div>
                <div>
                    <label class="text-xs font-bold text-purple-400 uppercase tracking-widest block mb-1">NIK (KTP)</label>
                    <input type="text" name="nik" placeholder="NIK (KTP)" required
                        class="w-full border-2 border-purple-100 bg-purple-50/30 px-3 py-2.5 rounded-xl text-sm outline-none focus:ring-2 focus:ring-purple-400 focus:border-purple-300 transition">
                </div>
                <div>
                    <label class="text-xs font-bold text-purple-400 uppercase tracking-widest block mb-1">No. WhatsApp</label>
                    <input type="text" name="nomor_telepon" placeholder="No. WhatsApp" required
                        class="w-full border-2 border-purple-100 bg-purple-50/30 px-3 py-2.5 rounded-xl text-sm outline-none focus:ring-2 focus:ring-purple-400 focus:border-purple-300 transition">
                </div>
                <div>
                    <label class="text-xs font-bold text-purple-400 uppercase tracking-widest block mb-1">Alamat</label>
                    <textarea name="alamat" placeholder="Alamat" rows="2"
                        class="w-full border-2 border-purple-100 bg-purple-50/30 px-3 py-2.5 rounded-xl text-sm outline-none focus:ring-2 focus:ring-purple-400 focus:border-purple-300 transition resize-none"></textarea>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeModal('modalTambah')"
                        class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-purple-400 bg-purple-50 hover:bg-purple-100 transition">Batal</button>
                    <button type="submit" name="simpan_karyawan"
                        class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white transition hover:opacity-90"
                        style="background: linear-gradient(135deg, #7c3aed, #a855f7);">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Karyawan -->
    <?php if ($edit_data): ?>
    <div class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4 z-50">
        <div class="modal-enter bg-white rounded-2xl w-full max-w-md p-8 shadow-2xl border border-blue-100">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-gradient-to-br from-blue-500 to-blue-400">
                    <svg class="w-4 h-4 fill-white" viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1 1 0 000-1.41l-2.34-2.34a1 1 0 00-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                </div>
                <h2 class="text-lg font-bold text-blue-800">Edit Data Karyawan</h2>
            </div>
            <form method="POST" class="space-y-3">
                <input type="hidden" name="id" value="<?= $edit_data['id']; ?>">
                <input type="hidden" name="cabang_id" value="<?= $id_cabang_pilihan ?>">
                <div>
                    <label class="text-xs font-bold text-blue-400 uppercase tracking-widest block mb-1">Nama Lengkap</label>
                    <input type="text" name="nama" value="<?= $edit_data['nama']; ?>" required
                        class="w-full border-2 border-blue-100 bg-blue-50/30 px-3 py-2.5 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-400 transition">
                </div>
                <div>
                    <label class="text-xs font-bold text-blue-400 uppercase tracking-widest block mb-1">NIK</label>
                    <input type="text" name="nik" value="<?= $edit_data['nik']; ?>" required
                        class="w-full border-2 border-blue-100 bg-blue-50/30 px-3 py-2.5 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-400 transition">
                </div>
                <div>
                    <label class="text-xs font-bold text-blue-400 uppercase tracking-widest block mb-1">No. WhatsApp</label>
                    <input type="text" name="nomor_telepon" value="<?= $edit_data['nomor_telepon']; ?>" required
                        class="w-full border-2 border-blue-100 bg-blue-50/30 px-3 py-2.5 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-400 transition">
                </div>
                <div>
                    <label class="text-xs font-bold text-blue-400 uppercase tracking-widest block mb-1">Alamat</label>
                    <textarea name="alamat" rows="2"
                        class="w-full border-2 border-blue-100 bg-blue-50/30 px-3 py-2.5 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-400 transition resize-none"><?= $edit_data['alamat']; ?></textarea>
                </div>
                <div class="flex gap-3 pt-2">
                    <a href="kelola_karyawan.php?cabang_id=<?= $id_cabang_pilihan ?>"
                        class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-blue-400 bg-blue-50 hover:bg-blue-100 transition text-center">Batal</a>
                    <button type="submit" name="update_karyawan"
                        class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-blue-500 to-blue-600 hover:opacity-90 transition">Update Data</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

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
        // Close modal on backdrop click
        document.querySelectorAll('[id^="modal"]').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) closeModal(this.id);
            });
        });
    </script>

</body>
</html>
