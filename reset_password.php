<?php
include('config.php');

// Define variables and initialize with empty values
$new_password = $confirm_password = "";
$new_password_err = $confirm_password_err = "";

// Check if token is present in the URL
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];

    // Verify if the token exists in the database
    $sql = "SELECT email FROM users WHERE reset_token = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $token);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) == 1) {
            mysqli_stmt_bind_result($stmt, $email);
            mysqli_stmt_fetch($stmt);
        } else {
            die("Invalid token.");
        }

        mysqli_stmt_close($stmt);
    }
} else {
    die("No token provided.");
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["new_password"]))) {
        $new_password_err = "Please enter a new password.";
    } elseif (strlen(trim($_POST["new_password"])) < 6) {
        $new_password_err = "Password must have at least 6 characters.";
    } else {
        $new_password = trim($_POST["new_password"]);
    }

    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm the password.";
    } elseif ($new_password !== trim($_POST["confirm_password"])) {
        $confirm_password_err = "Passwords do not match.";
    }

    if (empty($new_password_err) && empty($confirm_password_err)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update the password and clear the reset token
        $sql = "UPDATE users SET password = ?, reset_token = NULL WHERE email = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ss", $hashed_password, $email);
            if (mysqli_stmt_execute($stmt)) {
                echo "<script>alert('Password reset successfully. You can now log in.');</script>";
                header("Location: login.php");
                exit();
            } else {
                echo "Something went wrong. Please try again.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="login-container mt-5 mb-5">
<div class="card mx-auto" style="max-width: 600px;">
<div class="card-body">
    <h2 class="text-center">Reset Password</h2>
    <p class="text-center">Enter a new password.</p>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?token=' . htmlspecialchars($token); ?>" method="post">
        <div class="form-group">
            <label>New Password</label>
            <input type="password" name="new_password" class="form-control <?php echo (!empty($new_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($new_password); ?>">
            <span class="invalid-feedback"><?php echo $new_password_err; ?></span>
        </div>
        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>">
            <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
        </div>
        <div class="form-group text-center">
            <input type="submit" class="btn btn-primary btn-block" value="Reset Password">
        </div>
    </form>
</div>
</div>
</div>
</body>
</html>
