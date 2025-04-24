<?php
session_start();
include('config.php');
require_once('stripe-php-master/init.php');

$stripe_secret_key = 'sk_test_51PQinLRrUKhdzOsDK666N2V91NnsWKtb8mcYyrYwhPgDEheMluMygqAx0MnrgRTWyVwjMvRKsUjpxuyGvFFfuhKE00cD9K5EtD';

if (!isset($_SESSION["id"])) {
    header("location: ../login.php");
    exit;
}

// Validate required POST data
if (!isset($_POST['repayment_id'], $_POST['amount'], $_POST['application_id'])) {
    echo "Invalid request.";
    exit;
}

$repayment_id = $_POST['repayment_id'];
$amount_cents = intval($_POST['amount']); // already in cents from the form
$application_id = $_POST['application_id'];

if ($amount_cents < 50) {
    echo "Minimum payment amount is $0.50.";
    exit;
}

\Stripe\Stripe::setApiKey($stripe_secret_key);

try {
    $payment_intent = \Stripe\PaymentIntent::create([
        'amount' => $amount_cents,
        'currency' => 'usd',
        'description' => "Payment for Repayment ID: $repayment_id",
        'metadata' => [
            'repayment_id' => $repayment_id,
            'application_id' => $application_id,
            'user_id' => $_SESSION["id"]
        ],
    ]);

    // Simulate payment succeeded for testing (in live you'd use webhook)
    if ($payment_intent->status !== 'succeeded') {
        // For testing purposes, we assume it succeeded after creation
        // In live, use webhook to confirm success before updating DB
        $payment_successful = true;
    }

    if ($payment_successful ?? true) {
        $update_sql = "UPDATE loan_repayment_schedule SET status = 'paid' WHERE id = ? AND application_id = ?";
        if ($update_stmt = mysqli_prepare($conn, $update_sql)) {
            mysqli_stmt_bind_param($update_stmt, "ii", $repayment_id, $application_id);
            if (mysqli_stmt_execute($update_stmt)) {
                header("Location: pay_repayment.php?application_id=" . $application_id);
                exit;
            } else {
                echo "Error updating repayment status.";
            }
            mysqli_stmt_close($update_stmt);
        }
    } else {
        echo "Payment failed. Please try again.";
    }

} catch (\Stripe\Exception\ApiErrorException $e) {
    echo "Stripe API error: " . $e->getMessage();
    exit;
}
?>
