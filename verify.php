<?php
include('config.php');

if (isset($_GET['email'])) {
    $email = $_GET['email'];

    $sql = "UPDATE users SET status = 'approved' WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "Account verified successfully. <a href='login.php'>Click here to login</a>.";
    } else {
        echo "Verification failed.";
    }
}
?>
