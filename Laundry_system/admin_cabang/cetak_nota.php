<?php
include "../koneksi.php";
session_start();

// 1. Proteksi Halaman
if (!isset($_SESSION['cabang_id'])) {
    header("location: login.php");
    exit;
}

// 2. Ambil ID dari URL
if (!isset($_GET['id'])) {
    die("ID Nota tidak ditemukan.");
}

$order_id = mysqli_real_escape_string($conn, $_GET['id']);

// 3. Query Utama (Mengambil data Order dan Customer saja agar lebih aman dari error kolom)
$query = "SELECT pesanan.*, pelanggan.nama as nama_pelanggan, pelanggan.nomor_telepon as telp_pelanggan 
          FROM pesanan
          JOIN pelanggan ON pesanan.pelanggan_id = pelanggan.id 
          WHERE pesanan.id = '$order_id' LIMIT 1";

$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Data transaksi tidak ditemukan di database.");
}

$data = mysqli_fetch_assoc($result);

// Ambil Nama Cabang dari Session (Jika ada) atau Hardcode
$nama_toko = "YENNY LAUNDRY";
$cabang_info = "Cabang ID: #" . $data['cabang_id']; 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota_<?= $data['id'] ?>_<?= $data['nama_pelanggan'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none; }
            body { background: white; padding: 0; }
            .ticket { 
                width: 100%; 
                border: none !important; 
                box-shadow: none !important; 
                margin: 0;
            }
        }
        .ticket {
            max-width: 380px;
            background: white;
            margin: 20px auto;
            padding: 25px;
            border: 1px dashed #bbb;
            font-family: 'Courier New', Courier, monospace;
        }
    </style>
</head>
<body class="bg-gray-100">

    <div class="no-print flex justify-center mt-6 gap-3">
        <button onclick="window.print()" class="bg-blue-600 text-white px-5 py-2 rounded-lg font-bold shadow-md hover:bg-blue-700 transition">
            Print Nota (CTRL+P)
        </button>
        <button onclick="window.close()" class="bg-white border border-gray-300 px-5 py-2 rounded-lg font-bold hover:bg-gray-50 transition">
            Tutup Tab
        </button>
    </div>

    <div class="ticket shadow-sm">
        <div class="text-center border-b border-dashed pb-4 mb-4">
            <h2 class="text-xl font-bold uppercase tracking-tighter"><?= $nama_toko ?></h2>
            <p class="text-[10px] font-bold text-gray-600"><?= $cabang_info ?></p>
            <p class="text-[9px] italic mt-1">"Pakaian Bersih, Hati Senang"</p>
        </div>

        <div class="text-[11px] space-y-1 mb-4">
            <div class="flex justify-between">
                <span>NO. NOTA</span>
                <span class="font-bold">#<?= $data['nomor_nota'] ?></span>
            </div>
            <div class="flex justify-between">
                <span>TANGGAL</span>
                <span><?= date('d/m/Y H:i', strtotime($data['tanggal_masuk'])) ?></span>
            </div>
            <div class="flex justify-between border-t border-dashed pt-1 mt-1">
                <span>PELANGGAN</span>
                <span class="font-bold"><?= strtoupper($data['nama_pelanggan']) ?></span>
            </div>
            <div class="flex justify-between">
                <span>TELP</span>
                <span><?= $data['telp_pelanggan'] ?></span>
            </div>
        </div>

        <table class="w-full text-[11px] mb-4">
            <thead class="border-b border-dashed">
                <tr>
                    <th class="text-left py-2">DESKRIPSI</th>
                    <th class="text-right py-2">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="py-3">
                        Jasa Laundry Kilat/Reguler<br>
                        <span class="text-[9px] text-gray-500 uppercase tracking-widest">[ Status: <?= $data['status'] ?> ]</span>
                    </td>
                    <td class="text-right py-3 font-bold">
                        Rp<?= number_format($data['total_harga'], 0, ',', '.') ?>
                    </td>
                </tr>
            </tbody>
            <tfoot class="border-t-2 border-dashed">
                <tr>
                    <th class="text-left pt-3 text-sm">TOTAL BAYAR</th>
                    <th class="text-right pt-3 text-sm font-black">Rp<?= number_format($data['total_harga'], 0, ',', '.') ?></th>
                </tr>
            </tfoot>
        </table>

        <div class="text-[10px] text-center mt-8 border-t border-dashed pt-4">
            <p class="font-bold">TERIMA KASIH</p>
            <p class="mt-1">Mohon simpan nota ini sebagai<br>bukti pengambilan cucian.</p>
            
            <div class="mt-6 flex justify-center opacity-30">
                <div class="h-6 w-32 border-x-2 border-black flex justify-around items-end pb-1 gap-1">
                    <div class="w-1 bg-black h-4"></div>
                    <div class="w-[2px] bg-black h-3"></div>
                    <div class="w-1 bg-black h-4"></div>
                    <div class="w-[2px] bg-black h-3"></div>
                    <div class="w-1 bg-black h-4"></div>
                </div>
            </div>
            <p class="mt-2 text-[8px] uppercase tracking-widest">Kasir: <?= $_SESSION['username'] ?></p>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>