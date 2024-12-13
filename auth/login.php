<?php
session_start();
include('../config/db.php');

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Mencegah SQL Injection
    $email = $conn->real_escape_string($email);
    $password = $conn->real_escape_string($password);

    // Query untuk cek user
    $query = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $_SESSION['email'] = $email;
        header("Location: ../index.php");  // Arahkan ke halaman utama setelah login
    } else {
        $error_message = "Email atau Password salah!";
    }
}
?>

<!DOCTYPE html>
<html data-bs-theme="light" lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Login - Forecasting Wisata</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/Nunito.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
</head>

<body class="bg-gradient-primary">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-9 col-lg-12 col-xl-10">
                <div class="card shadow-lg o-hidden border-0 my-5">
                    <div class="card-body p-0">
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-flex">
                                <div class="flex-grow-1 bg-login-image" style="background-image: url(../assets/img/dogs/images4.jpg);"></div>
                            </div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h4 class="text-dark mb-4">Welcome Back!</h4>
                                    </div>
                                    <form class="user" method="POST">
                                        <div class="mb-3"><input name="email" class="form-control form-control-user" type="email" id="exampleInputEmail" aria-describedby="emailHelp" placeholder="Enter Email Address..." name="email"></div>
                                        <div class="mb-3"><input name="password" class="form-control form-control-user" type="password" id="exampleInputPassword" placeholder="Password" name="password"></div>
                                        <div class="mb-3">
                                            <div class="custom-checkbox small">
<!--                                                <div class="form-check"><input class="form-check-input" type="checkbox" id="formCheck-1"><label class="form-check-label" for="formCheck-1">Remember Me</label></div>-->
                                            </div>
                                        </div><button name="login" class="btn btn-primary d-block btn-user w-100" type="submit">Login</button>
                                        <?php if (isset($error_message)) echo "<div class='alert alert-danger mt-3'>$error_message</div>"; ?>
<!--                                        <hr><a class="btn btn-primary d-block btn-google btn-user w-100 mb-2" role="button"><i class="fab fa-google"></i>&nbsp; Login with Google</a><a class="btn btn-primary d-block btn-facebook btn-user w-100" role="button"><i class="fab fa-facebook-f"></i>&nbsp; Login with Facebook</a>-->
                                        <hr>
                                    </form>
<!--                                    <div class="text-center"><a class="small" href="forgot-password.html">Forgot Password?</a></div>-->
                                    <div class="text-center"><a class="small" href="register.php">Create an Account!</a></div>
                                </div>
                            </div>
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