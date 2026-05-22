        <?php
        include "../koneksi.php";
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Proteksi Kasir
        if (!isset($_SESSION['cabang_id']) || $_SESSION['role'] != 'admin_cabang') {
            header("location: index.php");
            exit;
        }

        $cabang_id = (int)$_SESSION['cabang_id'];

        // --- PROSES SIMPAN TRANSAKSI ---
        if (isset($_POST['buat_pesanan'])) {
        
            $customer_id = (int)$_POST['customer_id'];
            $employee_id = (int)$_POST['employee_id'];
            $service_id  = (int)$_POST['service_id'];
            $metode_pembayaran = $_POST['metode_pembayaran'];

            // Pembulatan kelipatan 5
            $qty_input = (float)$_POST['qty']; 
            $berat_kg  = ceil($qty_input / 5) * 5;

            $total_harga  = (int)$_POST['total_bayar'];


            mysqli_begin_transaction($conn);

            try {

                // 1. CEK LAYANAN MASIH AKTIF
                $cek_service = mysqli_query($conn, "
                    SELECT id FROM layanan 
                    WHERE id = '$service_id' 
                    AND cabang_id = '$cabang_id' 
                    AND is_active = 1
                ");

                if (mysqli_num_rows($cek_service) == 0) {
                    mysqli_rollback($conn);
                    echo "<script>alert('Layanan sudah tidak aktif!'); window.history.back();</script>";
                    exit;
                }

                //2. INSERT KE ORDERS
                // BUAT NOMOR NOTA
                $tanggal = date('Ymd');

                // HITUNG JUMLAH PESANAN HARI INI
                $q = mysqli_query($conn, "
                    SELECT COUNT(*) as total 
                    FROM pesanan 
                    WHERE DATE(tanggal_masuk) = CURDATE()
                ");

                $d = mysqli_fetch_assoc($q);

                $urutan = $d['total'] + 1;

                // FORMAT NOTA
                $nomor_nota = "LAU-" . $cabang_id . "-" . $tanggal . "-" . str_pad($urutan, 4, "0", STR_PAD_LEFT);


                // INSERT KE PESANAN
                $query_order = "INSERT INTO pesanan 
                (
                    nomor_nota,
                    cabang_id,
                    pelanggan_id,
                    layanan_id,
                    berat_kg,
                    total_harga,
                    metode_pembayaran,
                    status,
                    tanggal_masuk
                ) 
                VALUES 
                (
                    '$nomor_nota',
                    '$cabang_id',
                    '$customer_id',
                    '$service_id',
                    '$berat_kg',
                    '$total_harga',
                    '$metode_pembayaran',
                    'proses',
                    NOW()
                )";
            
            $insert = mysqli_query($conn, $query_order);

            if (!$insert) {
                throw new Exception(mysqli_error($conn));
            }
                //4. COMMIT
                mysqli_commit($conn);

                echo "<script>
                    alert('Pesanan Berhasil Disimpan!');
                    window.location='beranda_kasir.php';
                </script>";
                exit;

            } catch (Exception $e) {
                mysqli_rollback($conn);
                die("Gagal menyimpan pesanan: " . $e->getMessage());
            }
        }

        // --- DATA DROPDOWN ---
        $pelanggan = mysqli_query($conn, "
            SELECT * FROM pelanggan 
            WHERE cabang_id = '$cabang_id' 
            ORDER BY nama ASC
        ");

        // HANYA LAYANAN AKTIF
        $services  = mysqli_query($conn, "
            SELECT * FROM layanan
            WHERE cabang_id = '$cabang_id' 
            AND is_active = 1
            ORDER BY nama_layanan ASC
        ");

        $employees = mysqli_query($conn, "
            SELECT * FROM karyawan 
            WHERE cabang_id = '$cabang_id' 
            ORDER BY nama ASC
        ");
        ?>

        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Buat Pesanan - Yenny Laundry</title>
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
                    <a href="pesanan_kasir.php" class="nav-link-active flex items-center gap-2.5 py-2.5 px-3 rounded-xl text-white/85 transition">
                        <svg class="w-4 h-4 fill-current flex-shrink-0" viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>Buat Pesanan
                    </a>
                    <a href="kelola_layanan.php" class="flex items-center gap-2.5 py-2.5 px-3 rounded-xl text-white/80 hover:bg-white/15 transition">
                        <svg class="w-4 h-4 fill-current flex-shrink-0" viewBox="0 0 24 24"><path d="M20 6H4V4H2v18h2v-2h16v2h2V4h-2v2zM4 8h16v8H4V8zm2 2v4h12v-4H6z"/></svg>Kelola Layanan
                    </a>
                    <a href="kelola_pelanggan.php" class="flex items-center gap-2.5 py-2.5 px-3 rounded-xl text-white/80 hover:bg-white/15 transition">
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
                    <span class="font-bold text-purple-900 text-sm">Input Pesanan</span>
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white" style="background: linear-gradient(135deg,#7c3aed,#a855f7);">K</div>
                </div>

                <div class="max-w-2xl mx-auto">
                    <div class="mb-6">
                        <h1 class="text-2xl font-bold text-purple-900">Input Pesanan Baru</h1>
                        <p class="text-sm text-purple-400 mt-0.5">Isi detail pesanan laundry pelanggan.</p>
                    </div>

                    <div class="bg-white rounded-2xl border-2 border-purple-100 shadow-sm overflow-hidden">
                        <!-- Form Header -->
                        <div class="px-8 py-5 border-b border-purple-50" style="background: linear-gradient(90deg, #f5f3ff, #ede9fe);">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg,#7c3aed,#a855f7);">
                                    <svg class="w-4 h-4 fill-white" viewBox="0 0 24 24"><path d="M20 6H4V4H2v18h2v-2h16v2h2V4h-2v2zM4 8h16v8H4V8zm2 2v4h12v-4H6z"/></svg>
                                </div>
                                <span class="font-bold text-purple-900">Detail Transaksi</span>
                            </div>
                        </div>

                        <form method="POST" class="p-8 space-y-5">
                            <div>
                                <label class="text-xs font-bold text-purple-400 uppercase block mb-1.5">Pelanggan</label>
                                <select name="customer_id" required class="w-full border-2 border-purple-100 bg-white px-3 py-2.5 rounded-xl text-sm outline-none focus:ring-2 focus:ring-purple-400 transition">
                                    <option value="">-- Pilih Pelanggan --</option>
                                    <?php while($c = mysqli_fetch_assoc($pelanggan)): ?>
                                        <option value="<?= $c['id'] ?>"><?= $c['nama'] ?> (<?= $c['nomor_telepon'] ?>)</option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-xs font-bold text-purple-400 uppercase block mb-1.5">Jenis Layanan</label>
                                    <select id="service_id" name="service_id" required onchange="hitungTotal()" class="w-full border-2 border-purple-100 bg-white px-3 py-2.5 rounded-xl text-sm outline-none focus:ring-2 focus:ring-purple-400 transition">
                                        <option value="" data-price="0">-- Pilih Layanan --</option>
                                        <?php mysqli_data_seek($services, 0); while($s = mysqli_fetch_assoc($services)): ?>
                                            <option 
                                                value="<?= $s['id'] ?>" 
                                                data-price="<?= $s['harga_per_kg'] ?>">

                                                <?= $s['nama_layanan'] ?> -
                                                Rp<?= number_format($s['harga_per_kg']) ?>

                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-purple-400 uppercase block mb-1.5">Jumlah / Berat (kg)</label>
                                    <input type="number" step="0.1" id="qty" name="qty" required oninput="hitungTotal()" min="0" placeholder="0.0"
                                        class="w-full border-2 border-purple-100 px-3 py-2.5 rounded-xl text-sm outline-none focus:ring-2 focus:ring-purple-400 transition">
                                    <p class="text-[10px] text-purple-300 mt-1">* Dihitung kelipatan 5</p>
                                </div>
                            </div>

                            <div>
                                <label class="text-xs font-bold text-purple-400 uppercase block mb-1.5">Staf Pelaksana</label>
                                <select name="employee_id" required class="w-full border-2 border-purple-100 bg-white px-3 py-2.5 rounded-xl text-sm outline-none focus:ring-2 focus:ring-purple-400 transition">
                                    <option value="">-- Pilih Staf --</option>
                                    <?php while($e = mysqli_fetch_assoc($employees)): ?>
                                        <option value="<?= $e['id'] ?>"><?= $e['nama'] ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-purple-400 uppercase block mb-1.5">
                                    Metode Pembayaran
                                </label>

                                <select name="metode_pembayaran" required
                                    class="w-full border-2 border-purple-100 bg-white px-3 py-2.5 rounded-xl text-sm outline-none focus:ring-2 focus:ring-purple-400 transition">

                                    <option value="">-- Pilih Pembayaran --</option>
                                    <option value="cash">Cash</option>
                                    <option value="transfer">Transfer</option>
                                    <option value="qris">QRIS</option>
                                </select>
                            </div>

                            <!-- Total Box -->
                            <div class="rounded-2xl p-5 border-2 border-purple-100" style="background: linear-gradient(135deg, #f5f3ff, #ede9fe);">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs font-bold text-purple-400 uppercase tracking-widest">Total Harga</p>
                                        <p id="info_pembulatan" class="text-xs text-orange-500 font-bold mt-1 hidden"></p>
                                    </div>
                                    <div class="text-right">
                                        <h2 id="display_total" class="text-3xl font-black text-purple-700">Rp 0</h2>
                                    </div>
                                </div>
                                <input type="hidden" id="total_bayar" name="total_bayar" value="0">
                            </div>

                            <button type="submit" name="buat_pesanan"
                                class="w-full py-3.5 rounded-xl font-bold text-white text-sm transition hover:opacity-90 hover:-translate-y-0.5 hover:shadow-lg"
                                style="background: linear-gradient(135deg, #7c3aed, #a855f7);">
                                Simpan Pesanan
                            </button>
                        </form>
                    </div>
                </div>
            </main>

            <script>
                // 1. Pindahkan fungsi ke luar agar menjadi Global
                function hitungTotal() {
                    const serviceSelect = document.getElementById('service_id');
                    const qtyInput = document.getElementById('qty');
                    const displayTotal = document.getElementById('display_total');
                    const inputTotal = document.getElementById('total_bayar');
                    const infoPembulatan = document.getElementById('info_pembulatan');

                    // Validasi jika elemen tidak ditemukan
                    if (!serviceSelect || !qtyInput) return;

                    const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];

                    // AMBIL HARGA (Pastikan atribut data-price ada)
                    const price = parseInt(selectedOption.getAttribute('data-price')) || 0;

                    // BERAT USER
                    let qty = parseFloat(qtyInput.value) || 0;

                    // BULATKAN KELIPATAN 5
                    let berat = 0;
                    if (qty > 0) {
                        berat = Math.ceil(qty / 5) * 5;
                    }

                    // HITUNG TOTAL
                    let total = price * berat;

                    // TAMPILKAN KE USER
                    displayTotal.innerHTML = 'Rp ' + total.toLocaleString('id-ID');

                    // SIMPAN KE INPUT HIDDEN UNTUK PHP
                    inputTotal.value = total;

                    // TAMPILKAN INFO PEMBULATAN
                    if (qty > 0 && qty !== berat) {
                        infoPembulatan.innerHTML = '* Dibulatkan menjadi ' + berat + ' kg';
                        infoPembulatan.classList.remove('hidden');
                    } else {
                        infoPembulatan.classList.add('hidden');
                    }
                }

                // 2. Fungsi Sidebar tetap di luar
                function toggleSidebar() {
                    document.getElementById('sidebar').classList.toggle('open');
                    document.getElementById('overlay').classList.toggle('hidden');
                }

                // 3. (Opsional) Jalankan hitungTotal saat halaman pertama kali dimuat
                document.addEventListener('DOMContentLoaded', function () {
                    hitungTotal();
                });
            </script>
        </body>
        </html>