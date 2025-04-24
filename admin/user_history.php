<?php
session_start();
include 'config.php';

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

$user_id = intval($_GET['user_id']);

// Fetch user info
$user_sql = "SELECT * FROM users WHERE id = $user_id";
$user_result = mysqli_query($conn, $user_sql);
$user = mysqli_fetch_assoc($user_result);

// Fetch loan applications
$app_sql = "SELECT * FROM loan_applications WHERE user_id = $user_id";
$app_result = mysqli_query($conn, $app_sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>User History</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <a class="btn btn-dark float-right" href="" onclick="window.print()">Print</a>
    <h3>User Details</h3>
    <table class="table table-bordered">
        <tr><th>ID</th><td><?= $user['id'] ?></td></tr>
        <tr><th>Username</th><td><?= htmlspecialchars($user['username']) ?></td></tr>
        <tr><th>Email</th><td><?= htmlspecialchars($user['email']) ?></td></tr>
        <tr><th>Phone</th><td><?= htmlspecialchars($user['phone']) ?></td></tr>
        <tr><th>User Type</th><td><?= ucfirst($user['usertype']) ?></td></tr>
    </table>

    <h4 class="mt-4">Loan Applications</h4>
    <?php while ($app = mysqli_fetch_assoc($app_result)) {
        $app_id = $app['id'];

        // Get disbursement
        $disb_query = "SELECT * FROM loan_disbursement WHERE application_id = $app_id";
        $disb_result = mysqli_query($conn, $disb_query);
        $disb = mysqli_fetch_assoc($disb_result);

        // Get repayment schedule
        $repay_query = "SELECT * FROM loan_repayment_schedule WHERE application_id = $app_id";
        $repay_result = mysqli_query($conn, $repay_query);
    ?>
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">Application ID: <?= $app_id ?></div>
        <div class="card-body">
            <p><strong>Loan Amount:</strong> <?= $app['loan_amount'] ?> | <strong>Type:</strong> <?= $app['loan_type'] ?> | <strong>Status:</strong> <?= ucfirst(str_replace('_',' ',$app['application_status'])) ?></p>
            <p><strong>Purpose:</strong> <?= $app['purpose'] ?></p>
            <p><strong>Repayment Period:</strong> <?= $app['repayment_period'] ?> months</p>
            <p><strong>Submitted At:</strong> <?= $app['created_at'] ?></p>

            <?php if ($disb): ?>
            <h6 class="mt-3">Disbursement Details:</h6>
            <ul>
                <li>Account Holder: <?= $disb['account_holder_name'] ?></li>
                <li>Bank: <?= $disb['bank_name'] ?></li>
                <li>Account #: <?= $disb['account_number'] ?></li>
                <li>IFSC: <?= $disb['ifsc_code'] ?></li>
                <li>Submitted At: <?= $disb['submitted_at'] ?></li>
            </ul>
            <?php endif; ?>

            <h6 class="mt-3">Repayment Schedule:</h6>
            <table class="table table-sm table-bordered">
                <thead>
                    <tr><th>Due Date</th><th>Installment</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php while ($repay = mysqli_fetch_assoc($repay_result)) { ?>
                        <tr>
                            <td><?= $repay['due_date'] ?></td>
                            <td><?= $repay['installment_amount'] ?></td>
                            <td><?= ucfirst($repay['status']) ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php } ?>
</div>
</body>
</html>
