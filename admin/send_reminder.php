<?php
session_start();
include('config.php');
// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';


if (!isset($_POST['application_id'])) {
    echo "Invalid access.";
    exit;
}

$application_id = intval($_POST['application_id']);

// Get unpaid installments
$stmt = $conn->prepare("SELECT due_date, installment_amount FROM loan_repayment_schedule WHERE application_id = ? AND status = 'unpaid'");
$stmt->bind_param("i", $application_id);
$stmt->execute();
$result = $stmt->get_result();

$installments = [];
while ($row = $result->fetch_assoc()) {
    $installments[] = $row;
}

// Get user email
$stmt = $conn->prepare("SELECT users.email, users.username FROM users 
                        JOIN loan_applications ON users.id = loan_applications.user_id 
                        WHERE loan_applications.id = ?");
$stmt->bind_param("i", $application_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || empty($installments)) {
    echo "<script>alert('No unpaid installments or user not found.'); window.location.href='distribute_loan.php?application_id=$application_id';</script>";
    exit;
}

// Build email content
$message = "<h3>Dear {$user['name']},</h3>";
$message .= "<p>You have the following unpaid loan installments:</p>";
$message .= "<table border='1' cellpadding='8'><tr><th>Due Date</th><th>Amount</th></tr>";
foreach ($installments as $i) {
    $message .= "<tr><td>{$i['due_date']}</td><td>Rs. " . number_format($i['installment_amount'], 2) . "</td></tr>";
}
$message .= "</table><p>Please make the payments at your earliest convenience.Otherwise the Late Fee will be added 2% per installement.</p><p>Thank you.</p>";

// Send email using PHPMailer
$mail = new PHPMailer;
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'nasiryt.827@gmail.com'; 
$mail->Password = 'mtvp ruzp aqfu tfxt'; // Use App Password
$mail->SMTPSecure = 'tls';
$mail->Port = 587;

$mail->setFrom('nasiryt.827@gmail.com', 'Loan System');
$mail->addAddress($user['email'], $user['name']);
$mail->isHTML(true);
$mail->Subject = 'Loan Repayment Reminder';
$mail->Body    = $message;

if ($mail->send()) {
    echo "<script>alert('Reminder email sent successfully.'); window.location.href='distribute_loan.php?application_id=$application_id';</script>";
} else {
    echo "<script>alert('Failed to send email.'); window.location.href='distribute_loan.php?application_id=$application_id';</script>";
}
?>
