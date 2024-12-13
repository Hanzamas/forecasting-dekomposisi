<?php
session_start();
include('../config/db.php');  // Pastikan path ke db.php sudah benar

if (!isset($_SESSION['email'])) {
    header("Location: auth/login.php");  // Redirect ke halaman login jika belum login
    exit();
}


// Query untuk mengambil data dari tabel users
$query = "SELECT * FROM users ORDER BY id ASC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html data-bs-theme="light" lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Data User - Forecasting Wisata</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/Nunito.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
</head>

<body id="page-top">
<div id="wrapper">
    <nav class="navbar align-items-start sidebar sidebar-dark accordion bg-gradient-primary p-0 navbar-dark">
        <div class="container-fluid d-flex flex-column p-0"><a class="navbar-brand d-flex justify-content-center align-items-center sidebar-brand m-0" href="#"><span>Forecasting</span>
                <div class="sidebar-brand-icon rotate-n-15"></div>
                <div class="sidebar-brand-text mx-3"></div>
            </a>
            <hr class="sidebar-divider my-0">
            <ul class="navbar-nav text-light" id="accordionSidebar">
                <li class="nav-item"><a class="nav-link" href="../index.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
                <li class="nav-item"><a class="nav-link" href="/index/data_aktual.php"><i class="fas fa-table"></i><span>Data Aktual</span></a></li>
                <li class="nav-item"><a class="nav-link" href="/index/data_forecasting.php"><i class="fas fa-table"></i><span>Data Forecasting</span></a></li>
                <li class="nav-item"><a class="nav-link active" href="/index/data_user.php"><i class="far fa-user-circle"></i><span>Data User</span></a></li>
                <li class="nav-item"><a class="nav-link" href="/index/profile.php"><i class="fas fa-user"></i><span>Profile</span></a></li>
                <li class="nav-item"><a class="nav-link" href="../helper/logout.php"><i class="fas fa-exclamation-circle"></i><span>Logout</span></a></li>
            </ul>
            <div class="text-center d-none d-md-inline"><button class="btn rounded-circle border-0" id="sidebarToggle" type="button"></button></div>
        </div>
    </nav>
    <div class="d-flex flex-column" id="content-wrapper">
        <div id="content">
            <nav class="navbar navbar-expand bg-white shadow mb-4 topbar">
            </nav>
            <div class="container-fluid">
                <h3 class="text-dark mb-4">Data</h3>
                <div class="card shadow">
                    <div class="card-header py-3">
                        <p class="text-primary m-0 fw-bold">Data User</p>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive table mt-2" id="dataTable" role="grid" aria-describedby="dataTable_info">
                            <table class="table my-0" id="dataTable">
                                <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Email</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                // Mengecek apakah hasil query ada
                                if ($result->num_rows > 0) {
                                    // Variabel untuk nomor urut
                                    $no = 1;
                                    // Loop untuk menampilkan data
                                    while($row = $result->fetch_assoc()) {
                                        echo "<tr>
                                                    <td>" . $no++ . "</td>
                                                    <td>" . $row['email'] . "</td>
                                                  </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='2'>No users found</td></tr>";
                                }
                                ?>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <th>No</th>
                                    <th>Email</th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <footer class="bg-white sticky-footer">
            <div class="container my-auto">
                <div class="text-center my-auto copyright"><span>Copyright © Forecasting Wisata 2024</span></div>
            </div>
        </footer>
    </div><a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/bs-init.js"></script>
<script src="../assets/js/theme.js"></script>
</body>

</html>
