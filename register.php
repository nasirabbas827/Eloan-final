<?php
include('config.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// define variables and initialize with empty values
$username = $password = $email = $phone = $usertype = "";
$username_err = $password_err = $email_err = $phone_err = $usertype_err = "";
$success_message = "";

// check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } else {
        $sql = "SELECT id FROM users WHERE username = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $param_username);
        $param_username = trim($_POST["username"]);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) == 1) {
            $username_err = "This username is already taken.";
        } else {
            $username = trim($_POST["username"]);
        }
    }

    // validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } else {
        $password = trim($_POST["password"]);
        if (!preg_match('/^(?=.*[!@#$%^&*(),.?":{}|<>])(?=.*[A-Z])(?=.*\d)[A-Za-z\d!@#$%^&*(),.?":{}|<>]{8,}$/', $password)) {
            $password_err = "Password must be at least 8 characters long, include a special character, an uppercase letter, and a number.";
        }
    }

    // validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email address.";
    } else {
        $email = trim($_POST["email"]);
        if (!preg_match('/@gmail\.com$/', $email)) {
            $email_err = "Please enter a valid Gmail address.";
        }
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $param_email);
        $param_email = $email;
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) == 1) {
            $email_err = "This email address is already taken.";
        }
    }

    // validate phone
    if (empty(trim($_POST["phone"]))) {
        $phone_err = "Please enter a phone number.";
    } else {
        $phone = trim($_POST["phone"]);
        if (!preg_match('/^\d{10,11}$/', $phone)) {
            $phone_err = "Please enter a valid phone number.";
        }
        $sql = "SELECT id FROM users WHERE phone = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $param_phone);
        $param_phone = $phone;
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) == 1) {
            $phone_err = "This phone number is already taken.";
        }
    }

    // validate usertype
    if (empty($_POST["usertype"])) {
        $usertype_err = "Please select user type.";
    } else {
        $usertype = $_POST["usertype"];
        if (!in_array($usertype, ['borrower', 'lender'])) {
            $usertype_err = "Invalid user type selected.";
        }
    }

    // insert into database
    if (empty($username_err) && empty($password_err) && empty($email_err) && empty($phone_err) && empty($usertype_err)) {
        $sql = "INSERT INTO users (username, password, email, phone, usertype, status) VALUES (?, ?, ?, ?, ?, 'pending')";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssss", $param_username, $param_password, $param_email, $param_phone, $param_usertype);
        $param_username = $username;
        $param_password = password_hash($password, PASSWORD_DEFAULT);
        $param_email = $email;
        $param_phone = $phone;
        $param_usertype = $usertype;

        if (mysqli_stmt_execute($stmt)) {
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'nasiryt.827@gmail.com';
                $mail->Password   = 'mtvp ruzp aqfu tfxt';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('nasiryt.827@gmail.com', 'Loan System');
                $mail->addAddress($email, $username);

                $mail->isHTML(true);
                $mail->Subject = 'Verify Your Account';
                $mail->Body    = "Hi $username,<br><br>Please click the link below to verify your account:<br>
                <a href='http://localhost/fall24/eloan/verify.php?email=$email'>Verify Account</a>";

                $mail->send();
                $success_message = "User registered! Check your email to verify your account.";
            } catch (Exception $e) {
                $success_message = "User registered, but verification email failed. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $success_message = "Something went wrong. Please try again.";
        }

        mysqli_stmt_close($stmt);
        mysqli_close($conn);
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>
<div class="registration-container mt-5 mb-5">
    <div class="card mx-auto" style="max-width: 600px;">
        <div class="card-body">
            <h2 class="text-center">User Registration</h2>
            <p class="text-center">Please fill in your details to register.</p>

            <?php if (!empty($success_message)) : ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                    <span class="invalid-feedback"><?php echo $username_err; ?></span>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                    <span class="invalid-feedback"><?php echo $email_err; ?></span>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>">
                    <span class="invalid-feedback"><?php echo $password_err; ?></span>
                </div>

                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="number" name="phone" class="form-control <?php echo (!empty($phone_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $phone; ?>">
                    <span class="invalid-feedback"><?php echo $phone_err; ?></span>
                </div>

                <div class="form-group">
                    <label>User Type</label>
                    <select name="usertype" class="form-control <?php echo (!empty($usertype_err)) ? 'is-invalid' : ''; ?>">
                        <option value="">Select Type</option>
                        <option value="borrower" <?php echo ($usertype == 'borrower') ? 'selected' : ''; ?>>Borrower</option>
                        <option value="lender" <?php echo ($usertype == 'lender') ? 'selected' : ''; ?>>Lender</option>
                    </select>
                    <span class="invalid-feedback"><?php echo $usertype_err; ?></span>
                </div>

                <div class="form-group text-center">
                    <input type="submit" class="btn btn-primary btn-block" value="Register">
                </div>
            </form>

            <p class="text-center">Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
