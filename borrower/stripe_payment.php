<?php
session_start();
include('config.php');
require_once('stripe-php-master/init.php');

// Stripe secret key
$stripe_secret_key = 'sk_test_51PQinLRrUKhdzOsDK666N2V91NnsWKtb8mcYyrYwhPgDEheMluMygqAx0MnrgRTWyVwjMvRKsUjpxuyGvFFfuhKE00cD9K5EtD';

if (!isset($_SESSION["id"])) {
    header("location: ../login.php");
    exit;
}

// Check if required data is passed via POST
if (!isset($_POST['repayment_id'], $_POST['amount'], $_POST['application_id'])) {
    echo "Invalid request.";
    exit;
}

$repayment_id = $_POST['repayment_id'];
$amount = $_POST['amount'];
$application_id = $_POST['application_id'];

// Set Stripe secret key
\Stripe\Stripe::setApiKey($stripe_secret_key);

// Define the maximum amount (in cents) for a single payment
$max_amount = 99999999; // This is $999,999.99 in cents

// Function to create payment intent
function createPaymentIntent($amount) {
    global $stripe_secret_key;
    try {
        return \Stripe\PaymentIntent::create([
            'amount' => $amount, // amount in cents
            'currency' => 'usd',
            'metadata' => ['integration_check' => 'accept_a_payment'],
        ]);
    } catch (\Stripe\Exception\ApiErrorException $e) {
        // Log the error and display the message
        error_log('Stripe API Error: ' . $e->getMessage());
        echo "Error processing payment: " . $e->getMessage();
        exit;
    }
}

// Process the payment
$total_amount = $amount * 100; // Convert to cents

if ($total_amount > $max_amount) {
    // Split the total amount into multiple smaller payments
    $remaining_amount = $total_amount;
    while ($remaining_amount > 0) {
        $payment_amount = min($remaining_amount, $max_amount); // Split into chunks
        $payment_intent = createPaymentIntent($payment_amount);

        // After each payment is created, decrease the remaining amount
        $remaining_amount -= $payment_amount;
    }

    // If we finished processing all payments, update the repayment status
    $update_sql = "UPDATE loan_repayment_schedule SET status = 'paid' WHERE id = ? AND application_id = ?";
    if ($update_stmt = mysqli_prepare($conn, $update_sql)) {
        mysqli_stmt_bind_param($update_stmt, "ii", $repayment_id, $application_id);
        if (mysqli_stmt_execute($update_stmt)) {
            header("Location: pay_repayment.php?application_id=" . $application_id); // Redirect back to repayment page
            exit;
        } else {
            echo "Error updating repayment status.";
            exit;
        }
        mysqli_stmt_close($update_stmt);
    }
} else {
    // If the total amount is within the Stripe limit, process the payment normally
    $payment_intent = createPaymentIntent($total_amount);

    // If payment was successful, update repayment status
    if ($payment_intent->status == 'succeeded') {
        $update_sql = "UPDATE loan_repayment_schedule SET status = 'paid' WHERE id = ? AND application_id = ?";
        if ($update_stmt = mysqli_prepare($conn, $update_sql)) {
            mysqli_stmt_bind_param($update_stmt, "ii", $repayment_id, $application_id);
            if (mysqli_stmt_execute($update_stmt)) {
                header("Location: pay_repayment.php?application_id=" . $application_id); // Redirect back to repayment page
                exit;
            } else {
                echo "Error updating repayment status.";
                exit;
            }
            mysqli_stmt_close($update_stmt);
        }
    } else {
        echo "Payment failed. Please try again.";
        exit;
    }
}
?>
