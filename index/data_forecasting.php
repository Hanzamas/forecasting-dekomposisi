<?php
session_start();
include('../config/db.php'); // Pastikan path ke db.php sudah benar

if (!isset($_SESSION['email'])) {
    header("Location: auth/login.php"); // Redirect ke halaman login jika belum login
    exit();
}

// Query untuk mengambil data dari tabel data_aktual
$query = "SELECT * FROM data_aktual ORDER BY id ASC";
$result = $conn->query($query);

// Ambil data dari database untuk perhitungan
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Perhitungan Moving Average (MA), CMA, dan komponen lainnya
$period = 4; // Periode untuk MA
$total_data = count($data);

$total_kunjungan = 0;
$total_ma = 0;
$total_cma = 0;
$total_x = 0;
$total_x2 = 0;
$total_xy = 0;

for ($i = 0; $i < $total_data; $i++) {
    // Hitung MA
    if ($i >= $period - 1 && $i <= 83) {
        $ma = 0;
        for ($j = 0; $j < $period; $j++) {
            $ma += $data[$i - $j]['Kunjungan'];
        }
        $data[$i]['ma'] = $ma !== null ? $ma / $period : null;
    } else {
        $data[$i]['ma'] = $i > 0 ? $data[$i - 1]['ma'] : null;
    }

    // Set MA for elements after the 84th to use the MA of the previous element
    if ($i > 84) {
        $data[$i]['ma'] = $data[$i - 1]['ma'];
    }
    // Hitung CMA
    if ($i >= $period) {
        if (isset($data[$i]['ma']) && isset($data[$i - 1]['ma'])) {
            $data[$i]['cma'] = ($data[$i]['ma'] + $data[$i - 1]['ma']) / 2;
        } else {
            $data[$i]['cma'] = null;
        }
    } else {
        $data[$i]['cma'] = null;
    }
    // Handle the 85th element using MA from the 84th element


    // Hitung X dan X²
    $data[$i]['x'] = $i; // X dimulai dari 0 kemudian ++
    $data[$i]['x2'] = pow($data[$i]['x'], 2); // X^2 adalah X dipangkatkan 2

    // Hitung XY
    if (isset($data[$i]['Kunjungan'])) {
        $data[$i]['xy'] = $data[$i]['x'] * $data[$i]['Kunjungan'];
    } else {
        $data[$i]['xy'] = null;
    }

    if ($i <= 83) {
        $total_kunjungan += $data[$i]['Kunjungan'];
        $total_ma += $data[$i]['ma'] ?? 0;
        $total_cma += $data[$i]['cma'] ?? 0;
        $total_x += $data[$i]['x'];
        $total_x2 += $data[$i]['x2'];
        $total_xy += $data[$i]['xy'] ?? 0;
    }

    // Placeholder untuk komponen lain (CMA, SI, FT, E, dll.)
    $data[$i]['si'] = null;
    $data[$i]['ft'] = null;
    $data[$i]['e'] = null;
    $data[$i]['e2'] = null;
    $data[$i]['abs_error'] = null;
}

// Hitung A, A', Hasil A, B, B', Hasil B
$a = ($total_kunjungan * $total_x2) - ($total_x * $total_xy);
$a_prime = (84 * $total_x2) - pow($total_x, 2);
$hasil_a = $a / $a_prime;

$b = (84 * $total_xy) - ($total_x * $total_kunjungan);
$b_prime = (84 * $total_x2) - pow($total_x, 2);
$hasil_b = $b / $b_prime;

// Hitung CMAT untuk setiap baris
for ($i = 0; $i < $total_data; $i++) {
    $data[$i]['cmat'] = $hasil_a + ($hasil_b * $data[$i]['x']);
}

// Hitung CF mulai dari data ke-5
for ($i = 4; $i < $total_data; $i++) {
    if (isset($data[$i]['cma']) && isset($data[$i]['cmat']) && $data[$i]['cmat'] != 0) {
        $data[$i]['cf'] = $data[$i]['cma'] / $data[$i]['cmat'];
    } else {
        $data[$i]['cf'] = null;
    }
}

// Hitung CFA untuk setiap kwartal
$cfa = [];
for ($i = 0; $i < $total_data; $i++) {
    $kwartal = $data[$i]['Kwartal'];
    if (!isset($cfa[$kwartal])) {
        $cfa[$kwartal] = 0;
    }
    $cfa[$kwartal] += $data[$i]['Kunjungan'];
}

// Hitung total CFA
$total_cfa = array_sum($cfa);

// Hitung Rasio untuk setiap kwartal
$rasio = [];
foreach ($cfa as $kwartal => $value) {
    $rasio[$kwartal] = $value / $total_cfa;
}

// Assign CFA and Rasio to each row
for ($i = 0; $i < $total_data; $i++) {
    $data[$i]['cfa'] = $cfa[$data[$i]['Kwartal']];
    $data[$i]['rasio'] = $rasio[$data[$i]['Kwartal']];
    if ($i > 83) {
        $data[$i]['si'] = $data[$i]['cf'] * 12;
    } else {
        $data[$i]['si'] = $rasio[$data[$i]['Kwartal']] * 12;
    }
}

// Hitung FT mulai dari data ke-5
for ($i = 4; $i < $total_data && $i <= 84; $i++) {
    if (isset($data[$i]['cmat']) && isset($data[$i]['cf']) && isset($data[$i]['si'])) {
        $data[$i]['ft'] = $data[$i]['cmat'] + $data[$i]['cf'] + $data[$i]['si'] + 1;
    } else {
        $data[$i]['ft'] = null;
    }
}

// Hitung E mulai dari data ke-5 sampai ke-84

$total_e2 = 0;
$total_abs_error = 0;
for ($i = 4; $i < $total_data && $i <= 83; $i++) {
    if (isset($data[$i]['Kunjungan']) && isset($data[$i]['ft'])) {
        $data[$i]['e'] = abs($data[$i]['Kunjungan'] - $data[$i]['ft']);
        $data[$i]['e2'] = pow($data[$i]['e'], 2);
        if ($data[$i]['Kunjungan'] != 0) {
            $data[$i]['abs_error'] = $data[$i]['e'] / $data[$i]['Kunjungan'];
        } else {
            $data[$i]['abs_error'] = null; // or handle as needed
        }
        $total_e2 += $data[$i]['e2'];
        $total_abs_error += $data[$i]['abs_error'];
    } else {
        $data[$i]['e'] = null;
        $data[$i]['e2'] = null;
        $data[$i]['abs_error'] = null;
    }
}

$total_mse = $total_e2 / (84 - 4);
$total_mape = $total_abs_error / (84 - 4);


?>
<!DOCTYPE html>
<html data-bs-theme="light" lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Data Forecasting - Forecasting Wisata</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/Nunito.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <li class="nav-item"><a class="nav-link active" href="/index/data_forecasting.php"><i class="fas fa-table"></i><span>Data Forecasting</span></a></li>
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
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <p class="text-primary m-0 fw-bold">Forecast Chart</p>
                    </div>
                    <div class="card-body">
                        <canvas id="forecastChart"></canvas>
                    </div>
                </div>
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <p class="text-primary m-0 fw-bold">Summary</p>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="card bg-light text-dark mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">A</h5>
                                        <p class="card-text"><?php echo $a; ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card bg-light text-dark mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">A'</h5>
                                        <p class="card-text"><?php echo $a_prime; ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card bg-light text-dark mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">Hasil A</h5>
                                        <p class="card-text"><?php echo $hasil_a; ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card bg-light text-dark mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">B</h5>
                                        <p class="card-text"><?php echo $b; ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card bg-light text-dark mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">B'</h5>
                                        <p class="card-text"><?php echo $b_prime; ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card bg-light text-dark mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">Hasil B</h5>
                                        <p class="card-text"><?php echo $hasil_b; ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <p class="text-primary m-0 fw-bold">Summary</p>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="card bg-light text-dark mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">Total Kunjungan</h5>
                                        <p class="card-text"><?php echo $total_kunjungan; ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card bg-light text-dark mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">Total MA</h5>
                                        <p class="card-text"><?php echo $total_ma; ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card bg-light text-dark mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">Total CMA</h5>
                                        <p class="card-text"><?php echo $total_cma; ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card bg-light text-dark mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">Total X</h5>
                                        <p class="card-text"><?php echo $total_x; ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card bg-light text-dark mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">Total X^2</h5>
                                        <p class="card-text"><?php echo $total_x2; ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card bg-light text-dark mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">Total XY</h5>
                                        <p class="card-text"><?php echo $total_xy; ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <p class="text-primary m-0 fw-bold">Summary</p>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="card bg-light text-dark mb-3">
                                        <div class="card-body">
                                            <h5 class="card-title">Total CFA</h5>
                                            <p class="card-text"><?php echo $total_cfa; ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card bg-light text-dark mb-3">
                                        <div class="card-body">
                                            <h5 class="card-title">Total E^2</h5>
                                            <p class="card-text"><?php echo $total_e2; ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card bg-light text-dark mb-3">
                                        <div class="card-body">
                                            <h5 class="card-title">Total ABS ERROR</h5>
                                            <p class="card-text"><?php echo $total_abs_error; ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card bg-light text-dark mb-3">
                                        <div class="card-body">
                                            <h5 class="card-title">Total MSE</h5>
                                            <p class="card-text"><?php echo $total_mse; ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card bg-light text-dark mb-3">
                                        <div class="card-body">
                                            <h5 class="card-title">Total MAPE</h5>
                                            <p class="card-text"><?php echo $total_mape; ?></p>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                <div class="card shadow">
                    <div class="card-header py-3">
                        <p class="text-primary m-0 fw-bold">Penghitungan Data Aktual</p>
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
                                    <th>MA</th>
                                    <th>CMA</th>
                                    <th>X</th>
                                    <th>X^2</th>
                                    <th>X'Y</th>
                                    <th>CMAT</th>
                                    <th>CF</th>
                                    <th>CFA</th>
                                    <th>RASIO</th>
                                    <th>SI</th>
                                    <th>FT</th>
                                    <th>E</th>
                                    <th>E^2</th>
                                    <th>ABS EROR/Y</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                // Menampilkan data
                                foreach ($data as $index => $row) {
                                    if ($index >= 84) {
                                        break;
                                    }
                                    echo "<tr>
                                            <td>" . ($index + 1) . "</td>
                                            <td>" . $row['Tahun'] . "</td>
                                            <td>" . $row['Kwartal'] . "</td>
                                            <td>" . $row['Kunjungan'] . "</td>
                                            <td>" . ($row['ma'] ?? '-') . "</td>
                                            <td>" . ($row['cma'] ?? '-') . "</td>
                                            <td>" . ($row['x'] ?? '-') . "</td>
                                            <td>" . ($row['x2'] ?? '-') . "</td>
                                            <td>" . ($row['xy'] ?? '-') . "</td>
                                            <td>" . ($row['cmat'] ?? '-') . "</td>
                                            <td>" . ($row['cf'] ?? '-') . "</td>
                                            <td>" . ($row['cfa'] ?? '-') . "</td>
                                            <td>" . ($row['rasio'] ?? '-') . "</td>
                                            <td>" . ($row['si'] ?? '-') . "</td>
                                            <td>" . ($row['ft'] ?? '-') . "</td>
                                            <td>" . ($row['e'] ?? '-') . "</td>
                                            <td>" . ($row['e2'] ?? '-') . "</td>
                                            <td>" . ($row['abs_error'] ?? '-') . "</td>
                                          </tr>";
                                }
                                ?>

                                </tbody>
                                <tfoot>
                                <tr>

                                </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="row">


                        </div>
                    </div>
                </div>
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <p class="text-primary m-0 fw-bold">Penghitungan Data Forecasting</p>
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
                                            <th>MA</th>
                                            <th>CMA</th>
                                            <th>X</th>
                                            <th>X^2</th>
                                            <th>X'Y</th>
                                            <th>CMAT</th>
                                            <th>CF</th>
                                            <th>CFA</th>
                                            <th>RASIO</th>
                                            <th>SI</th>
                                            <th>FT</th>
                                            <th>E</th>
                                            <th>E^2</th>
                                            <th>ABS EROR/Y</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        // Menampilkan data
                                        foreach ($data as $index => $row) {
                                            if ($index < 84) {
                                                continue;
                                            }
                                            echo "<tr>
                                                <td>" . ($index + 1) . "</td>
                                                <td>" . $row['Tahun'] . "</td>
                                                <td>" . $row['Kwartal'] . "</td>
                                                <td>" . $row['Kunjungan'] . "</td>
                                                <td>" . ($row['ma'] ?? '-') . "</td>
                                                <td>" . ($row['cma'] ?? '-') . "</td>
                                                <td>" . ($row['x'] ?? '-') . "</td>
                                                <td>" . ($row['x2'] ?? '-') . "</td>
                                                <td>" . ($row['xy'] ?? '-') . "</td>
                                                <td>" . ($row['cmat'] ?? '-') . "</td>
                                                <td>" . ($row['cf'] ?? '-') . "</td>
                                                <td>" . ($row['cfa'] ?? '-') . "</td>
                                                <td>" . ($row['rasio'] ?? '-') . "</td>
                                                <td>" . ($row['si'] ?? '-') . "</td>
                                                <td>" . ($row['ft'] ?? '-') . "</td>
                                                <td>" . ($row['e'] ?? '-') . "</td>
                                                <td>" . ($row['e2'] ?? '-') . "</td>
                                                <td>" . ($row['abs_error'] ?? '-') . "</td>
                                            </tr>";
                                        }
                                        ?>

                                        </tbody>
                                        <tfoot>
                                        <tr>

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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/bs-init.js"></script>
<script src="../assets/js/theme.js"></script>
        <script>
            var ctx = document.getElementById('forecastChart').getContext('2d');
            var forecastChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode(array_column($data, 'x')); ?>,
                    datasets: [{
                        label: 'Data Aktual Kunjungan',
                        data: <?php echo json_encode(array_column($data, 'Kunjungan')); ?>,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1,
                        fill: false
                    }, {
                        label: 'FT (Forecast)',
                        data: <?php echo json_encode(array_column($data, 'ft')); ?>,
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Index'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Kunjungan'
                            }
                        }
                    }
                }
            });
        </script>
</body>

</html>