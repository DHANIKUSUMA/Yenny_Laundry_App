<?php
session_start();
include "koneksi.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = mysqli_real_escape_string($conn, $_POST["username"]);
    $password = $_POST["password"];

    $sql = "SELECT * FROM users WHERE username='$username'";
    $cek = mysqli_query($conn, $sql);

    if (mysqli_num_rows($cek) == 1) {

        $data = mysqli_fetch_assoc($cek);

        if (password_verify($password, $data['password'])) {

            $_SESSION["user_id"]   = $data["id"];
            $_SESSION["username"]  = $data["username"];
            $_SESSION["role"]      = $data["role"];
            $_SESSION["cabang_id"] = $data["cabang_id"];

            if ($data["role"] == "super_admin") {
                header("Location: admin/beranda.php");
            } else {
                header("Location: admin_cabang/beranda_kasir.php");
            }

            exit();

        } else {
            echo "<script>alert('Password salah!'); window.history.back();</script>";
        }

    } else {
        echo "<script>alert('Username tidak ditemukan!'); window.history.back();</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yenny Laundry</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .input-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #a78bfa; width: 18px; height: 18px; pointer-events: none; }
        .toggle-pw { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #a78bfa; padding: 0; display: flex; align-items: center; }

        html, body {
            overflow-x: hidden;
        }

        /* Kartu login: padding & lebar menyesuaikan layar */
        @media (max-width: 480px) {
            /* Kurangi padding body agar kartu tidak terpotong */
            body {
                padding: 16px !important;
                align-items: flex-start;
                padding-top: 32px !important;
            }

            /* Kartu menjadi full-width di HP kecil */
            .bg-white.rounded-2xl {
                padding: 24px 20px !important;
                border-radius: 16px !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            /* Logo sedikit lebih kecil */
            .w-16.h-16 {
                width: 52px !important;
                height: 52px !important;
            }

            /* Judul lebih proporsional */
            h1 {
                font-size: 1rem !important;
            }

            /* Input lebih mudah diketuk di layar sentuh */
            input[type="text"],
            input[type="password"] {
                font-size: 16px !important; /* cegah auto-zoom di iOS */
                padding-top: 10px !important;
                padding-bottom: 10px !important;
            }

            /* Tombol submit lebih mudah ditekan */
            button[type="submit"] {
                padding-top: 12px !important;
                padding-bottom: 12px !important;
                font-size: 0.95rem !important;
            }
        }

        /* Tablet: sedikit penyesuaian margin */
        @media (min-width: 481px) and (max-width: 768px) {
            body {
                padding: 24px !important;
            }

            .bg-white.rounded-2xl {
                padding: 36px 32px !important;
            }

            input[type="text"],
            input[type="password"] {
                font-size: 16px !important; /* cegah auto-zoom di iOS */
            }
        }

        /* Fokus lebih terlihat di semua ukuran layar (aksesibilitas) */
        input:focus {
            outline: none;
        }

        /* Smooth transition untuk resize */
        .bg-white.rounded-2xl {
            transition: padding 0.2s ease, max-width 0.2s ease;
        }

        /* Tombol submit: area klik nyaman di semua layar */
        button[type="submit"] {
            min-height: 44px;
            touch-action: manipulation;
        }

        /* Input: area ketuk nyaman */
        input[type="text"],
        input[type="password"] {
            min-height: 44px;
            touch-action: manipulation;
        }

        /* Toggle password button: lebih mudah ditekan di sentuh */
        .toggle-pw {
            min-width: 32px;
            min-height: 32px;
            right: 8px;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-purple-50 via-violet-100 to-purple-200 p-8">

    <div class="bg-white rounded-2xl p-10 w-full max-w-sm" style="box-shadow: 0 4px 40px rgba(99,60,180,0.10);">

        <!-- Logo & Brand -->
        <div class="flex flex-col items-center mb-7">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-3 bg-gradient-to-br from-purple-600 to-fuchsia-500">
                <svg class="w-8 h-8 fill-white" viewBox="0 0 24 24">
                    <path d="M5 3a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2H5zm7 2a5 5 0 110 10A5 5 0 0112 5zm0 2a3 3 0 100 6 3 3 0 000-6zm0 1a2 2 0 110 4 2 2 0 010-4z"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-purple-900 tracking-tight">Yenny Laundry</h1>
            <p class="text-xs text-purple-400 mt-0.5">Sistem Manajemen Laundry</p>
        </div>

        <hr class="border-purple-100 mb-6">

        <!-- Form -->
        <form method="POST" class="space-y-4">

            <div>
                <label class="block text-xs font-semibold text-purple-700 uppercase tracking-wide mb-1.5">Username</label>
                <div class="relative">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
                    </svg>
                    <input type="text" name="username" placeholder="Masukkan username" required
                        class="w-full pl-10 pr-4 py-2.5 border-2 border-purple-200 rounded-xl text-sm text-purple-900 bg-purple-50 placeholder-purple-300 focus:outline-none focus:border-purple-500 focus:bg-white focus:ring-2 focus:ring-purple-100 transition-all">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-purple-700 uppercase tracking-wide mb-1.5">Password</label>
                <div class="relative">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M18 8h-1V6c0-2.8-2.2-5-5-5S7 3.2 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.7 1.4-3.1 3.1-3.1 1.7 0 3.1 1.4 3.1 3.1v2z"/>
                    </svg>
                    <input type="password" name="password" id="pwField" placeholder="Masukkan password" required minlength="8"
                        class="w-full pl-10 pr-10 py-2.5 border-2 border-purple-200 rounded-xl text-sm text-purple-900 bg-purple-50 placeholder-purple-300 focus:outline-none focus:border-purple-500 focus:bg-white focus:ring-2 focus:ring-purple-100 transition-all">
                    <button type="button" class="toggle-pw" onclick="togglePw()" title="Tampilkan password">
                        <svg id="eyeIcon" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit"
                class="w-full py-3 bg-gradient-to-r from-purple-600 to-fuchsia-500 hover:from-purple-700 hover:to-fuchsia-600 text-white font-semibold text-sm rounded-xl transition-all hover:-translate-y-0.5 hover:shadow-lg hover:shadow-purple-200 mt-2">
                Masuk
            </button>
        </form>

        <p class="text-center text-xs text-gray-400 mt-5">&copy; 2026 <span class="text-purple-600 font-medium">Yenny Laundry</span> &mdash; All rights reserved</p>
    </div>

    <script>
        function togglePw() {
            const f = document.getElementById('pwField');
            const icon = document.getElementById('eyeIcon');
            if (f.type === 'password') {
                f.type = 'text';
                icon.innerHTML = '<path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/>';
            } else {
                f.type = 'password';
                icon.innerHTML = '<path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>';
            }
        }
    </script>
</body>
</html>
