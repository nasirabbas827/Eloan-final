<?php
session_start();
include('config.php');

// Check if the user is logged in as an admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';

// Handle application status update
if (isset($_POST['update_status'])) {
    $application_id = $_POST['application_id'];
    $new_status = $_POST['application_status'];
    $review = $_POST['review'];

    $update_sql = "UPDATE loan_applications SET application_status = ?, review = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($stmt, "ssi", $new_status, $review, $application_id);
    mysqli_stmt_execute($stmt);

    $query = "SELECT u.email, u.username FROM loan_applications l JOIN users u ON l.user_id = u.id WHERE l.id = ?";
    $stmt2 = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt2, "i", $application_id);
    mysqli_stmt_execute($stmt2);
    $result2 = mysqli_stmt_get_result($stmt2);
    $user = mysqli_fetch_assoc($result2);

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'nasiryt.827@gmail.com';
        $mail->Password = 'mtvp ruzp aqfu tfxt';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('nasiryt.827@gmail.com', 'Loan System');
        $mail->addAddress($user['email'], $user['username']);
        $mail->isHTML(true);
        $mail->Subject = 'Loan Application Status Updated';
        $mail->Body = "
            <h3>Hello {$user['username']},</h3>
            <p>Your loan application (ID: {$application_id}) has been updated.</p>
            <p><strong>Status:</strong> " . ucfirst($new_status) . "</p>
            <p><strong>Review:</strong> {$review}</p>
            <br><p>Thank you,<br>Loan Management Team</p>
        ";
        $mail->send();
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
    }

    header("location: view_applications.php");
    exit;
}

// Handle filters
$filter_status = $_GET['status'] ?? '';
$filter_type = $_GET['type'] ?? '';

$conditions = ["application_status != 'draft'"];
$params = [];

if ($filter_status !== '') {
    $conditions[] = "application_status = ?";
    $params[] = $filter_status;
}

if ($filter_type !== '') {
    $conditions[] = "loan_type = ?";
    $params[] = $filter_type;
}

$min_amount = $_GET['min_amount'] ?? '';
$max_amount = $_GET['max_amount'] ?? '';

if ($min_amount !== '') {
    $conditions[] = "loan_amount >= ?";
    $params[] = $min_amount;
}
if ($max_amount !== '') {
    $conditions[] = "loan_amount <= ?";
    $params[] = $max_amount;
}


$sql = "SELECT * FROM loan_applications WHERE " . implode(" AND ", $conditions);
$stmt = mysqli_prepare($conn, $sql);

if ($params) {
    $types = str_repeat("s", count(array_filter([$filter_status, $filter_type]))) .
         str_repeat("d", count(array_filter([$min_amount, $max_amount])));
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-5 mb-5">
    <h1 class="text-center mb-4">Loan Applications</h1>

    <!-- Filter Form -->
    <form method="get" class="row g-3 mb-4">
        <div class="col-md-4">
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                <option value="submitted" <?= $filter_status == 'submitted' ? 'selected' : '' ?>>Submitted</option>
                <option value="under_review" <?= $filter_status == 'under_review' ? 'selected' : '' ?>>Under Review</option>
                <option value="approved" <?= $filter_status == 'approved' ? 'selected' : '' ?>>Approved</option>
                <option value="rejected" <?= $filter_status == 'rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
        </div>
        <div class="col-md-2">
    <input type="number" name="min_amount" class="form-control" placeholder="Min Amount" value="<?= htmlspecialchars($_GET['min_amount'] ?? '') ?>">
</div>
<div class="col-md-2">
    <input type="number" name="max_amount" class="form-control" placeholder="Max Amount" value="<?= htmlspecialchars($_GET['max_amount'] ?? '') ?>">
</div>

        <div class="col-md-4">
            <input type="text" name="type" class="form-control" placeholder="Loan Type (e.g., personal)" value="<?= htmlspecialchars($filter_type) ?>">
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="view_applications.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <table class="table table-bordered">
        <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>User ID</th>
            <th>Amount</th>
            <th>Type</th>
            <th>Purpose</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($loan = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td><?= $loan['id'] ?></td>
                <td><?= $loan['user_id'] ?></td>
                <td><?= $loan['loan_amount'] ?></td>
                <td><?= $loan['loan_type'] ?></td>
                <td><?= $loan['purpose'] ?></td>
                <td><?= ucfirst($loan['application_status']) ?></td>
                <td>
                    <a href="view_details.php?id=<?= $loan['id'] ?>" class="btn btn-info btn-sm">View</a>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#updateStatusModal<?= $loan['id'] ?>">Update</button>
                    <?php if ($loan['application_status'] === 'approved') { ?>
                        <button class="btn btn-warning btn-sm mt-1" onclick="generatePDF('<?= $loan['id'] ?>', '<?= $loan['loan_amount'] ?>', '<?= $loan['loan_type'] ?>', '<?= $loan['purpose'] ?>', '<?= $loan['user_id'] ?>')">Approval Letter</button>
                        <a href="distribute_loan.php?application_id=<?= $loan['id'] ?>" class="btn btn-success btn-sm mt-1">Distribute</a>
                    <?php } ?>
                </td>
            </tr>

<!-- Modal -->
<div class="modal fade" id="updateStatusModal<?php echo $loan['id']; ?>" tabindex="-1" aria-labelledby="updateStatusLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="updateStatusLabel">Update Loan Application Status</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="post" action="view_applications.php">
                                        <input type="hidden" name="application_id" value="<?php echo $loan['id']; ?>">

                                        <div class="mb-3">
                                            <label class="form-label">Application Status</label>
                                            <select name="application_status" class="form-control">
                                                <option value="submitted" <?php echo $loan['application_status'] == 'submitted' ? 'selected' : ''; ?>>Submitted</option>
                                                <option value="under_review" <?php echo $loan['application_status'] == 'under_review' ? 'selected' : ''; ?>>Under Review</option>
                                                <option value="approved" <?php echo $loan['application_status'] == 'approved' ? 'selected' : ''; ?>>Approved</option>
                                                <option value="rejected" <?php echo $loan['application_status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                                <option value="partially_completed" <?php echo $loan['application_status'] == 'partially_completed' ? 'selected' : ''; ?>>Partially Completed</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Review</label>
                                            <textarea name="review" class="form-control" rows="4"><?php echo $loan['review']; ?></textarea>
                                        </div>

                                        <button type="submit" name="update_status" class="btn btn-primary">Save Changes</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<script>
    async function generatePDF(loanId, loanAmount, loanType, purpose, userId) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        const content = `
Loan Approval Letter

Dear User ID: ${userId},

We are pleased to inform you that your loan application (ID: ${loanId}) has been approved.

Loan Details:
- Loan Amount: PKR ${loanAmount}
- Loan Type: ${loanType}
- Purpose: ${purpose}

Please contact us to finalize the agreement and receive the disbursement.

Thank you,
Loan Management Team`;
        doc.setFont("Times", "normal");
        doc.setFontSize(12);
        doc.text(content, 15, 20);
        doc.save(`loan_${loanId}_approval_letter.pdf`);
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
