<?php
$servername = "localhost";  // Nama server database, biasanya localhost
$username = "root";         // Username untuk database
$password = "";             // Password untuk database (kosongkan jika tidak ada)
$dbname = "forecasting-mancanegara"; // Nama database yang digunakan

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Mengecek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
