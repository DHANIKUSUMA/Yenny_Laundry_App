<?php
include "../koneksi.php";
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'super_admin') {
    header("location: login.php");
    exit;
}

// ambil data admin login
$id = $_SESSION['user_id'];

$data = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT * FROM users WHERE id='$id'")
);

// proses update
if (isset($_POST['update'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (!empty($password)) {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $query = "UPDATE users SET username='$username', password='$password' WHERE id='$id'";
    } else {
        $query = "UPDATE users SET username='$username' WHERE id='$id'";
    }

    mysqli_query($conn, $query);

    echo "<script>alert('Akun berhasil diperbarui'); window.location='beranda.php';</script>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ubah Akun</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-purple-50 flex items-center justify-center min-h-screen">

    <div class="modal-enter bg-white rounded-2xl w-full max-w-md p-8 shadow-2xl border border-purple-100">

        <!-- Header -->
        <div class="flex items-center gap-3 mb-6">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center"
                style="background: linear-gradient(135deg, #7c3aed, #a855f7);">
                <svg class="w-4 h-4 fill-white" viewBox="0 0 24 24">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
            </div>
            <h2 class="text-lg font-bold text-purple-900">Ubah Akun Super Admin</h2>
        </div>

        <!-- Form -->
        <form method="POST" class="space-y-3">

            <!-- Username -->
            <div>
                <label class="text-xs font-bold text-purple-400 uppercase tracking-widest block mb-1">
                    Username
                </label>
                <input type="text" name="username" id="username" value="<?= $data['username'] ?>"
                    class="w-full border-2 border-purple-100 bg-purple-50/30 px-3 py-2.5 rounded-xl text-sm outline-none focus:ring-2 focus:ring-purple-400 focus:border-purple-300 transition">
            </div>

            <!-- Password -->
            <div>
                <label class="text-xs font-bold text-purple-400 uppercase tracking-widest block mb-1">
                    Password Baru
                </label>
                <input type="password" name="password" id="password"
                    placeholder="Kosongkan jika tidak diubah"
                    class="w-full border-2 border-purple-100 bg-purple-50/30 px-3 py-2.5 rounded-xl text-sm outline-none focus:ring-2 focus:ring-purple-400 focus:border-purple-300 transition">
            </div>

            <!-- Button -->
            <div class="flex gap-3 pt-2" >
                <button type="button" onclick="window.location.href='beranda.php'"
                    class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-purple-400 bg-purple-50 hover:bg-purple-100 transition">
                    Batal
                </button>

                <button type="submit" name="update" 
                    class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white transition hover:opacity-90"
                    style="background: linear-gradient(135deg, #7c3aed, #a855f7);">
                    Simpan
                </button>
            </div>

        </form>

    </div>
</div>

</body>
</html>