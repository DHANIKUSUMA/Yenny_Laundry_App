<?php 
$host = "localhost"; 
$user = "root"; 
$pass = ""; 
$db = "db_laundry"; 
$conn = mysqli_connect($host, $user, $pass, $db); if (!$conn) { die("Koneksi gagal"); } ?>