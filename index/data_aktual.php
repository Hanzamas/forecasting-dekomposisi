<?php
session_start();
include('../config/db.php');  // Pastikan path ke db.php sudah benar

if (!isset($_SESSION['email'])) {
    header("Location: auth/login.php");  // Redirect ke halaman login jika belum login
    exit();
}

// Query untuk mengambil data dari tabel users
$query = "SELECT * FROM data_aktual ORDER BY id ASC";
$result = $conn->query($query);

// Handle form submission for adding data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $tahun = $_POST['tahun'];
    $kwartal = $_POST['kwartal'];
    $kunjungan = $_POST['kunjungan'];

    $insertQuery = "INSERT INTO data_aktual (Tahun, Kwartal, Kunjungan) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("iii", $tahun, $kwartal, $kunjungan);

    if ($stmt->execute()) {
        header("Location: data_aktual.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Handle form submission for editing data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $id = $_POST['id'];
    $tahun = $_POST['tahun'];
    $kwartal = $_POST['kwartal'];
    $kunjungan = $_POST['kunjungan'];

    $updateQuery = "UPDATE data_aktual SET Tahun = ?, Kwartal = ?, Kunjungan = ? WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("iiii", $tahun, $kwartal, $kunjungan, $id);

    if ($stmt->execute()) {
        header("Location: data_aktual.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Handle form submission for deleting data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $id = $_POST['id'];

    $deleteQuery = "DELETE FROM data_aktual WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: data_aktual.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html data-bs-theme="light" lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Data Aktual - Forecasting Wisata</title>
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
                <li class="nav-item"><a class="nav-link active" href="/index/data_aktual.php"><i class="fas fa-table"></i><span>Data Aktual</span></a></li>
                <li class="nav-item"><a class="nav-link" href="/index/data_forecasting.php"><i class="fas fa-table"></i><span>Data Forecasting</span></a></li>
                <li class="nav-item"><a class="nav-link" href="/index/data_user.php"><i class="far fa-user-circle"></i><span>Data User</span></a></li>
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
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <p class="text-primary m-0 fw-bold">Data Aktual</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDataModal">Tambah Data</button>
                    </div>
                    <div class="card-body">
                        <div class="row">
                        </div>
                        <div class="table-responsive table mt-2" id="dataTable" role="grid" aria-describedby="dataTable_info">
                            <table class="table my-0" id="dataTable">
                                <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tahun</th>
                                    <th>Kuartal</th>
                                    <th>Kunjungan</th>
                                    <th>Action</th>
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
                                                    <td>" . $row['Tahun'] . "</td>
                                                    <td>" . $row['Kwartal'] . "</td>
                                                    <td>" . $row['Kunjungan'] . "</td>
                                                    <td>
                                                        <button class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editDataModal' data-id='" . $row['id'] . "' data-tahun='" . $row['Tahun'] . "' data-kwartal='" . $row['Kwartal'] . "' data-kunjungan='" . $row['Kunjungan'] . "'>Edit</button>
                                                        <form method='POST' action='' style='display:inline-block;'>
                                                            <input type='hidden' name='delete' value='1'>
                                                            <input type='hidden' name='id' value='" . $row['id'] . "'>
                                                            <button type='submit' class='btn btn-danger btn-sm'>Hapus</button>
                                                        </form>
                                                    </td>
                                                  </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5'>No data found</td></tr>";
                                }
                                ?>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <th>No</th>
                                    <th>Tahun</th>
                                    <th>Kuartal</th>
                                    <th>Kunjungan</th>
                                    <th>Action</th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="row">
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

<!-- Modal for Adding Data -->
<div class="modal fade" id="addDataModal" tabindex="-1" aria-labelledby="addDataModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addDataModalLabel">Tambah Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <input type="hidden" name="add" value="1">
                    <div class="mb-3">
                        <label for="tahun" class="form-label">Tahun</label>
                        <input type="number" class="form-control" id="tahun" name="tahun" required>
                    </div>
                    <div class="mb-3">
                        <label for="kwartal" class="form-label">Kuartal</label>
                        <input type="number" class="form-control" id="kwartal" name="kwartal" required>
                    </div>
                    <div class="mb-3">
                        <label for="kunjungan" class="form-label">Kunjungan</label>
                        <input type="number" class="form-control" id="kunjungan" name="kunjungan" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Editing Data -->
<div class="modal fade" id="editDataModal" tabindex="-1" aria-labelledby="editDataModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editDataModalLabel">Edit Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <input type="hidden" name="edit" value="1">
                    <input type="hidden" id="edit-id" name="id">
                    <div class="mb-3">
                        <label for="edit-tahun" class="form-label">Tahun</label>
                        <input type="number" class="form-control" id="edit-tahun" name="tahun" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-kwartal" class="form-label">Kuartal</label>
                        <input type="number" class="form-control" id="edit-kwartal" name="kwartal" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-kunjungan" class="form-label">Kunjungan</label>
                        <input type="number" class="form-control" id="edit-kunjungan" name="kunjungan" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/bs-init.js"></script>
<script src="../assets/js/theme.js"></script>
<script>
    var editDataModal = document.getElementById('editDataModal');
    editDataModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');
        var tahun = button.getAttribute('data-tahun');
        var kwartal = button.getAttribute('data-kwartal');
        var kunjungan = button.getAttribute('data-kunjungan');

        var modalTitle = editDataModal.querySelector('.modal-title');
        var modalBodyInputId = editDataModal.querySelector('#edit-id');
        var modalBodyInputTahun = editDataModal.querySelector('#edit-tahun');
        var modalBodyInputKwartal = editDataModal.querySelector('#edit-kwartal');
        var modalBodyInputKunjungan = editDataModal.querySelector('#edit-kunjungan');

        modalTitle.textContent = 'Edit Data ' + id;
        modalBodyInputId.value = id;
        modalBodyInputTahun.value = tahun;
        modalBodyInputKwartal.value = kwartal;
        modalBodyInputKunjungan.value = kunjungan;
    });
</script>
</body>

</html>