<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"])) {
    header("location: ../login.php");
    exit;
}

if (!isset($_GET['application_id'])) {
    echo "Invalid request.";
    exit;
}

$application_id = intval($_GET['application_id']);

// Fetch bank details
$stmt = $conn->prepare("SELECT * FROM loan_disbursement WHERE application_id = ?");
$stmt->bind_param("i", $application_id);
$stmt->execute();
$bank = $stmt->get_result()->fetch_assoc();

// Fetch loan application details
$stmt = $conn->prepare("SELECT * FROM loan_applications WHERE id = ?");
$stmt->bind_param("i", $application_id);
$stmt->execute();
$loan = $stmt->get_result()->fetch_assoc();

if (!$bank || !$loan) {
    echo "<script>alert('Invalid or missing data.'); window.location.href='view_applications.php';</script>";
    exit;
}

// Check if repayment schedule already exists
$stmt = $conn->prepare("SELECT * FROM loan_repayment_schedule WHERE application_id = ?");
$stmt->bind_param("i", $application_id);
$stmt->execute();
$repayment_result = $stmt->get_result();
$schedule_exists = $repayment_result->num_rows > 0;

// Handle repayment generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['interest_rate']) && !$schedule_exists) {
    $interest_rate = floatval($_POST['interest_rate']);
    $loan_amount = floatval($loan['loan_amount']);
    $months = intval($loan['repayment_period']);
    
    $total_repay = $loan_amount + ($loan_amount * ($interest_rate / 100));
    $monthly_payment = round($total_repay / $months, 2);
    
    for ($i = 1; $i <= $months; $i++) {
        $due_date = date('Y-m-d', strtotime("+$i month"));
        $stmt = $conn->prepare("INSERT INTO loan_repayment_schedule (application_id, due_date, installment_amount, status) VALUES (?, ?, ?, 'unpaid')");
        $stmt->bind_param("isd", $application_id, $due_date, $monthly_payment);
        $stmt->execute();
    }
    echo "<script>alert('Repayment schedule generated successfully.'); window.location.href='distribute_loan.php?application_id=$application_id';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Distribute Loan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>
<div class="container mt-5 mb-5">
    <div class="row">
        <!-- Loan Details -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="card-title">Loan Application Details</h4>
                    <p><strong>User ID:</strong> <?php echo $loan['user_id']; ?></p>
                    <p><strong>Amount:</strong> RS: <?php echo $loan['loan_amount']; ?></p>
                    <p><strong>Loan Type:</strong> <?php echo $loan['loan_type']; ?></p>
                    <p><strong>Purpose:</strong> <?php echo $loan['purpose']; ?></p>
                    <p><strong>Repayment Period (months):</strong> <?php echo $loan['repayment_period']; ?></p>
                    <p><strong>Credit History:</strong> <?php echo $loan['credit_history']; ?></p>
                    <p><strong>Status:</strong> <?php echo ucfirst($loan['application_status']); ?></p>
                    <p><strong>Review:</strong> <?php echo $loan['review']; ?></p>
                </div>
            </div>
        </div>

        <!-- Bank + Repayment Form -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="card-title">Bank Details</h4>
                    <ul class="list-group">
                        <li class="list-group-item"><strong>Account Holder:</strong> <?php echo $bank['account_holder_name']; ?></li>
                        <li class="list-group-item"><strong>Bank Name:</strong> <?php echo $bank['bank_name']; ?></li>
                        <li class="list-group-item"><strong>Account Number:</strong> <?php echo $bank['account_number']; ?></li>
                        <li class="list-group-item"><strong>IFSC Code:</strong> <?php echo $bank['ifsc_code']; ?></li>
                    </ul>
                    <div class="mt-4">
                        <?php if (!$schedule_exists): ?>
                            <form method="post">
                                <div class="mb-3">
                                    <label for="interest_rate" class="form-label">Enter Interest Rate (%)</label>
                                    <input type="number" name="interest_rate" step="0.01" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-success">Add Repayment Terms</button>
                            </form>
                        <?php else: ?>
                            <h5 class="mt-4">Repayment Schedule</h5>
                            <form action="send_reminder.php" method="post" class="mt-3">
    <input type="hidden" name="application_id" value="<?php echo $application_id; ?>">
    <button type="submit" class="btn btn-warning">Send Reminder Email</button>
</form>

                            <table class="table table-bordered mt-2">
                                <thead>
                                    <tr>
                                        <th>Installment #</th>
                                        <th>Due Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $count = 1;
                                    while ($row = $repayment_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $count++ ?></td>
                                            <td><?= htmlspecialchars($row['due_date']) ?></td>
                                            <td>Rs: <?= number_format($row['installment_amount'], 2) ?></td>
                                            <td><?= ucfirst($row['status']) ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                        <a href="view_applications.php" class="btn btn-secondary mt-3">Back</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
