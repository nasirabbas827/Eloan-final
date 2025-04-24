<?php
include('config.php');

// Define variables and initialize with empty values
$email = $password = "";
$email_err = $password_err = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // If no errors, check credentials and log in user
    if (empty($email_err) && empty($password_err)) {
        $sql = "SELECT id, email, password, usertype, status FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $param_email);
        $param_email = $email;
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) == 1) {
            mysqli_stmt_bind_result($stmt, $id, $email, $hashed_password, $usertype, $status);
            if (mysqli_stmt_fetch($stmt)) {
                if (password_verify($password, $hashed_password)) {
                    if ($status === 'approved') {
                        // Password is correct and user is approved, start session and log in user
                        session_start();
                        $_SESSION["id"] = $id;
                        $_SESSION["email"] = $email;
                        $_SESSION["usertype"] = $usertype;

                        // Redirect based on usertype
                        if ($usertype === 'borrower') {
                            header("Location: borrower/borrower_home.php");
                        } elseif ($usertype === 'lender') {
                            header("Location: lender/lender_home.php");
                        } else {
                            header("Location: index.php");
                        }
                        exit();
                    } else {
                        $email_err = "Your account is not approved yet. Please verify your email.";
                    }
                } else {
                    $password_err = "The password you entered is incorrect.";
                }
            }
        } else {
            $email_err = "No account found with that email.";
        }

        mysqli_stmt_close($stmt);
    }

    mysqli_close($conn);
}
?>


<!DOCTYPE html>
<html>

<head>
    <title>User Login</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>
    <?php include('navbar.php'); ?>

    <div class="login-container mt-5 mb-5">
        <div class="card mx-auto" style="max-width: 600px;">
            <div class="card-body">
                <h2 class="text-center">User Login</h2>
                <p class="text-center">Please fill in your credentials to log in.</p>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="text" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                        <span class="invalid-feedback"><?php echo $email_err; ?></span>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                        <span class="invalid-feedback"><?php echo $password_err; ?></span>
                    </div>
                    <div class="form-group text-center">
                        <input type="submit" value="Log in" class="btn btn-primary btn-block">
                    </div>
                </form>

                <p class="text-center"><a href="forgot_password.php">Forgot Password?</a></p>
                <p class="text-center">Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
