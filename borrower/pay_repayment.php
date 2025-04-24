<?php
// Start session and check if the user is logged in as a borrower
session_start();
include('config.php');

require_once('stripe-php-master/init.php');

// Stripe API keys
$stripe_public_key = 'pk_test_51PQinLRrUKhdzOsDnpHkYJbi0HZIsF9xOVIcPZtsAr4nbH5h1p3o1jblMCPoB0glvFG3o1pbxQZLSiKRHgvuZRMt009qg1bTkq';
$stripe_secret_key = 'sk_test_51PQinLRrUKhdzOsDK666N2V91NnsWKtb8mcYyrYwhPgDEheMluMygqAx0MnrgRTWyVwjMvRKsUjpxuyGvFFfuhKE00cD9K5EtD';

if (!isset($_SESSION["id"])) {
    header("location: ../login.php");
    exit;
}

// Check if application_id is passed via the URL
if (!isset($_GET['application_id']) || empty($_GET['application_id'])) {
    echo "Invalid application ID.";
    exit;
}

$application_id = $_GET['application_id'];

// Fetch loan details
$sql = "SELECT * FROM loan_applications WHERE id = ? AND user_id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "ii", $application_id, $_SESSION["id"]);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) == 0) {
            echo "Application not found.";
            exit;
        }
        $application = mysqli_fetch_assoc($result);
    } else {
        echo "Error fetching application.";
        exit;
    }
    mysqli_stmt_close($stmt);
}

// Fetch repayment schedule for the given application_id
$repayment_sql = "SELECT * FROM loan_repayment_schedule WHERE application_id = ?";
if ($repayment_stmt = mysqli_prepare($conn, $repayment_sql)) {
    mysqli_stmt_bind_param($repayment_stmt, "i", $application_id);
    if (mysqli_stmt_execute($repayment_stmt)) {
        $repayment_result = mysqli_stmt_get_result($repayment_stmt);
    } else {
        echo "Error fetching repayment schedule.";
        exit;
    }
    mysqli_stmt_close($repayment_stmt);
}

mysqli_close($conn);

// Handle repayment status update (not used here as we're using Stripe for payment)
if (isset($_POST['pay_repayment'])) {
    $repayment_id = $_POST['repayment_id'];

    $update_sql = "UPDATE loan_repayment_schedule SET status = 'paid' WHERE id = ? AND application_id = ?";
    if ($update_stmt = mysqli_prepare($conn, $update_sql)) {
        mysqli_stmt_bind_param($update_stmt, "ii", $repayment_id, $application_id);
        if (mysqli_stmt_execute($update_stmt)) {
            header("Location: pay_repayment.php?application_id=" . $application_id); // Refresh page
            exit;
        } else {
            echo "Error updating repayment status.";
        }
        mysqli_stmt_close($update_stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Repayment Schedule - Pay Repayment</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include('navbar.php'); ?>

    <div class="container mt-5 mb-5">
        <h1 class="text-center mb-4">Repayment Schedule for Application ID: <?php echo $application_id; ?></h1>

        <?php if (mysqli_num_rows($repayment_result) > 0): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">Repayment ID</th>
                        <th scope="col">Due Date</th>
                        <th scope="col">Installment Amount</th>
                        <th scope="col">Status</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($repayment = mysqli_fetch_assoc($repayment_result)): ?>
                        <tr>
                            <td><?php echo $repayment['id']; ?></td>
                            <td><?php echo $repayment['due_date']; ?></td>
                            <td><?php echo number_format($repayment['installment_amount'], 2); ?></td>
                            <td>
                                <span class="badge <?php echo ($repayment['status'] == 'paid') ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo ucfirst($repayment['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($repayment['status'] == 'unpaid'): ?>
                                    <form action="stripe_payment.php" method="POST">
                                        <input type="hidden" name="repayment_id" value="<?php echo $repayment['id']; ?>">
                                        <input type="hidden" name="amount" value="<?php echo $repayment['installment_amount'] * 100; ?>"> <!-- Amount in cents -->
                                        <input type="hidden" name="application_id" value="<?php echo $application_id; ?>">
                                        <script
                                            src="https://checkout.stripe.com/checkout.js"
                                            class="stripe-button"
                                            data-key="<?php echo $stripe_public_key; ?>"
                                            data-amount="<?php echo $repayment['installment_amount'] * 100; ?>"
                                            data-name="Loan Repayment"
                                            data-description="Payment for Repayment ID: <?php echo $repayment['id']; ?>"
                                            data-currency="usd">
                                        </script>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted">Paid</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center">No repayment schedules found for this loan.</p>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
