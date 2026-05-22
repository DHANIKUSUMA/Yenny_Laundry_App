<?php
include "../koneksi.php";

require_once "../vendor/autoload.php";
use Dompdf\Dompdf;

$dompdf = new Dompdf();

// ambil filter
$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-01');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');
$filter_cabang = $_GET['cabang_id'] ?? 'semua';

$query = "SELECT pesanan.*, cabang.nama_cabang, pelanggan.nama AS nama_pelanggan
          FROM pesanan
          JOIN cabang ON pesanan.cabang_id = cabang.id
          JOIN pelanggan ON pesanan.pelanggan_id = pelanggan.id
          WHERE pesanan.tanggal_masuk 
          BETWEEN '$tgl_mulai 00:00:00' AND '$tgl_akhir 23:59:59'
          AND pesanan.status = 'diambil'";

if ($filter_cabang != 'semua') {
    $query .= " AND pesanan.cabang_id = '$filter_cabang'";
}

$query .= " ORDER BY pesanan.tanggal_masuk DESC";

$result = mysqli_query($conn, $query);

$total = 0;
$html = "
<h2 style='text-align:center;'>Laporan Pendapatan Laundry</h2>
<p style='text-align:center;'>Periode: $tgl_mulai - $tgl_akhir</p>
<hr>
<table border='1' width='100%' cellpadding='5' cellspacing='0'>
<tr>
<th>Waktu</th>
<th>Cabang</th>
<th>Pelanggan</th>
<th>Nominal</th>
</tr>
";

while ($row = mysqli_fetch_assoc($result)) {
    $total += $row['total_harga'];

    $html .= "
    <tr>
        <td>".date('d/m/Y H:i', strtotime($row['tanggal_masuk']))."</td>
        <td>{$row['nama_cabang']}</td>
        <td>{$row['nama_pelanggan']}</td>
        <td>Rp ".number_format($row['total_harga'],0,',','.')."</td>
    </tr>";
}

$html .= "
<tr>
    <td colspan='3'><b>Total</b></td>
    <td><b>Rp ".number_format($total,0,',','.')."</b></td>
</tr>
</table>
";

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("laporan_pendapatan.pdf", ["Attachment" => true]);