<?php
session_start();
include('config.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';

// Initialize variables
$email = "";
$email_err = "";
$success_msg = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);
        
        // Check if the email exists in the database
        $sql = "SELECT id FROM users WHERE email = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) == 1) {
                // Generate a unique token
                $token = bin2hex(random_bytes(50));

                // Save the token to the database
                $update_sql = "UPDATE users SET reset_token = ? WHERE email = ?";
                if ($update_stmt = mysqli_prepare($conn, $update_sql)) {
                    mysqli_stmt_bind_param($update_stmt, "ss", $token, $email);
                    mysqli_stmt_execute($update_stmt);

                    // Send the password reset email
                    $mail = new PHPMailer(true);

                    try {
                        // SMTP Configuration
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'nasiryt.827@gmail.com'; // Your Gmail address
                        $mail->Password = "YOUR_OWN_API_KEY"; // App password from Gmail
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = 587;

                        // Email Settings
                        $mail->setFrom('nasiryt.827@gmail.com', 'ELoan Management');
                        $mail->addAddress($email);
                        $mail->isHTML(true);
                        $mail->Subject = "Password Reset Request";
                        $mail->Body = "
                            <p>Click the link below to reset your password.</p>
                            <p><a href='http://localhost/fall24/eloan/reset_password.php?token=$token'>Reset Password</a></p>
                        ";

                        $mail->send();
                        $success_msg = "Password reset link has been sent to your email.";
                    } catch (Exception $e) {
                        $email_err = "Error sending email. Please try again later.";
                    }
                }
            } else {
                $email_err = "No account found with that email.";
            }
        }
    }

    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="login-container mt-5 mb-5">
<div class="card mx-auto" style="max-width: 600px;">
<div class="card-body">
    <h2 class="text-center">Forgot Password</h2>
    <p class="text-center">Enter your email address to receive a password reset link.</p>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
            <span class="invalid-feedback"><?php echo $email_err; ?></span>
        </div>
        <div class="form-group text-center">
            <input type="submit" value="Send Reset Link" class="btn btn-primary btn-block">
        </div>
    </form>
    <?php if (!empty($success_msg)) echo "<p class='text-success text-center'>$success_msg</p>"; ?>
</div>
</div>
</div>
</body>
</html>
