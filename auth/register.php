<?php
include('../config/db.php');  // Pastikan path ke db.php sudah benar

if (isset($_POST['register'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Mencegah SQL Injection
    $email = $conn->real_escape_string($email);
    $password = $conn->real_escape_string($password);

    // Query untuk mengecek apakah email sudah terdaftar
    $query_check = "SELECT * FROM users WHERE email = '$email'";
    $result_check = $conn->query($query_check);

    if ($result_check->num_rows > 0) {
        $error_message = "Email sudah terdaftar!";
    } else {
        // Query untuk memasukkan data user baru
        $query_insert = "INSERT INTO users (email, password) VALUES ('$email', '$password')";
        if ($conn->query($query_insert) === TRUE) {
            header("Location: ../login/login.php");  // Redirect ke halaman login setelah pendaftaran sukses
            exit();
        } else {
            $error_message = "Terjadi kesalahan saat pendaftaran: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html data-bs-theme="light" lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Register - Forecasting Wisata</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/Nunito.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
</head>

<body class="bg-gradient-primary">
<div class="container">
    <div class="card shadow-lg o-hidden border-0 my-5">
        <div class="card-body p-0">
            <div class="row">
                <div class="col-lg-5 d-none d-lg-flex">
                    <div class="flex-grow-1 bg-register-image" style="background-image: url(../assets/img/dogs/images4.jpg);"></div>
                </div>
                <div class="col-lg-7">
                    <div class="p-5">
                        <div class="text-center">
                            <h4 class="text-dark mb-4">Create an Account!</h4>
                        </div>
                        <form class="user" method="POST">
                            <div class="mb-3">
                                <input name="email" class="form-control form-control-user" type="email" id="exampleInputEmail" aria-describedby="emailHelp" placeholder="Email Address" required>
                            </div>
                            <div class="mb-3">
                                <input name="password" class="form-control form-control-user" type="password" id="examplePasswordInput" placeholder="Password" required>
                            </div>
                            <?php if (isset($error_message)) echo "<div class='alert alert-danger mt-3'>$error_message</div>"; ?>
                            <button class="btn btn-primary d-block btn-user w-100" type="submit" name="register">Register Account</button>
                            <hr>
                        </form>
                        <div class="text-center"><a class="small" href="login.php">Already have an account? Login!</a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/bs-init.js"></script>
<script src="../assets/js/theme.js"></script>
</body>

</html>
