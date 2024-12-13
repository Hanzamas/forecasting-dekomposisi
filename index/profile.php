<?php
session_start();

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['email'])) {
    header("Location: auth/login.php");
    exit();
}

// Dapatkan email pengguna dari sesi
$email = $_SESSION['email'];

// Sertakan file konfigurasi database
include('../config/db.php');

// Pesan status untuk pemberitahuan
$statusMessage = "";

// Periksa apakah formulir dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari formulir
    $newPassword = $_POST['password'];

    // Validasi input password
    if (empty($newPassword)) {
        $statusMessage = "Password tidak boleh kosong!";
    } else {
        // Hash password baru


        // Perbarui password di database
        $query = "UPDATE users SET password = ? WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $newPassword, $email);

        if ($stmt->execute()) {
            $statusMessage = "Pengaturan berhasil diperbarui!";
        } else {
            $statusMessage = "Terjadi kesalahan saat memperbarui data.";
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html data-bs-theme="light" lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Profile - Forecasting Wisata</title>
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
                <li class="nav-item"><a class="nav-link " href="../index.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
                <li class="nav-item"><a class="nav-link " href="/index/data_aktual.php"><i class="fas fa-table"></i><span>Data Aktual</span></a></li>
                <li class="nav-item"><a class="nav-link" href="/index/data_forecasting.php"><i class="fas fa-table"></i><span>Data Forecasting</span></a></li>
                <li class="nav-item"><a class="nav-link" href="/index/data_user.php"><i class="far fa-user-circle"></i><span>Data User</span></a></li>
                <li class="nav-item"><a class="nav-link active" href="/index/profile.php"><i class="fas fa-user"></i><span>Profile</span></a></li>
                <li class="nav-item"><a class="nav-link" href="../helper/logout.php"><i class="fas fa-exclamation-circle"></i><span>Logout</span></a></li>
            </ul>
            <div class="text-center d-none d-md-inline"><button class="btn rounded-circle border-0" id="sidebarToggle" type="button"></button></div>
        </div>
    </nav>
    <div class="d-flex flex-column" id="content-wrapper">
        <div id="content">
            <nav class="navbar navbar-expand bg-white shadow mb-4 topbar"></nav>
            <div class="container-fluid">
                <h3 class="text-dark mb-4">Profile</h3>
                <?php if (!empty($statusMessage)): ?>
                    <div class="alert alert-info" role="alert">
                        <?php echo htmlspecialchars($statusMessage, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>
                <div class="row mb-5">
                    <div class="col-lg-8">
                        <div class="card shadow mb-5">
                            <div class="card-header py-3">
                                <p class="text-primary m-0 fw-bold">User Settings</p>
                            </div>
                            <div class="card-body">
                                <form action="" method="POST">
                                    <div class="row">
                                        <div class="col">
                                            <div class="mb-3">
                                                <label class="form-label" for="email"><strong>Email Address</strong></label>
                                                <input class="form-control"
                                                       type="email"
                                                       id="email"
                                                       placeholder="user@example.com"
                                                       name="email"
                                                       value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
                                                       readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col">
                                            <div class="mb-3">
                                                <label class="form-label" for="password"><strong>Password</strong></label>
                                                <input class="form-control"
                                                       type="password"
                                                       id="password"
                                                       placeholder="**********"
                                                       name="password">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <button class="btn btn-primary btn-sm" type="submit">Save Settings</button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>

                    <div class="  mb-5">
                        <div class=" py-3"></div>
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
