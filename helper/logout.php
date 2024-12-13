<?php
session_start();
session_destroy();
header("Location: ../auth/login.php");  // Redirect ke halaman login setelah logout
exit();
?>
