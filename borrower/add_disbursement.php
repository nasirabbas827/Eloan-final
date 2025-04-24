<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['application_id'])) {
    echo "Invalid request.";
    exit;
}

$application_id = intval($_GET['application_id']);
$disbursement = null;

// Check for existing disbursement
$check_sql = "SELECT * FROM loan_disbursement WHERE application_id = ?";
$stmt = mysqli_prepare($conn, $check_sql);
mysqli_stmt_bind_param($stmt, "i", $application_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($row = mysqli_fetch_assoc($result)) {
    $disbursement = $row;
}
mysqli_stmt_close($stmt);

// Handle form submission (insert or update)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $account_holder_name = trim($_POST["account_holder_name"]);
    $bank_name = trim($_POST["bank_name"]);
    $account_number = trim($_POST["account_number"]);
    $ifsc_code = trim($_POST["ifsc_code"]);

    if ($disbursement) {
        // Update existing record
        $update_sql = "UPDATE loan_disbursement SET account_holder_name = ?, bank_name = ?, account_number = ?, ifsc_code = ? WHERE application_id = ?";
        $stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($stmt, "ssssi", $account_holder_name, $bank_name, $account_number, $ifsc_code, $application_id);
        $message = "Bank details updated successfully.";
    } else {
        // Insert new record
        $insert_sql = "INSERT INTO loan_disbursement (application_id, account_holder_name, bank_name, account_number, ifsc_code) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_sql);
        mysqli_stmt_bind_param($stmt, "issss", $application_id, $account_holder_name, $bank_name, $account_number, $ifsc_code);
        $message = "Bank details submitted successfully.";
    }

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('$message'); window.location.href = 'view_loan_application.php?id=$application_id';</script>";
        exit;
    } else {
        echo "Something went wrong. Please try again later.";
    }
    mysqli_stmt_close($stmt);
}
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $disbursement ? 'Update' : 'Add'; ?> Bank Details - Loan Disbursement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5 mb-5">
    <div class="card mx-auto" style="max-width: 600px;">
        <div class="card-body">
            <h2 class="text-center mb-4"><?php echo $disbursement ? 'Update' : 'Enter'; ?> Bank Details</h2>
            <form method="post">
                <div class="mb-3">
                    <label for="account_holder_name" class="form-label">Account Holder Name</label>
                    <input type="text" name="account_holder_name" class="form-control" required value="<?php echo $disbursement['account_holder_name'] ?? ''; ?>">
                </div>
                <div class="mb-3">
                    <label for="bank_name" class="form-label">Bank Name</label>
                    <input type="text" name="bank_name" class="form-control" required value="<?php echo $disbursement['bank_name'] ?? ''; ?>">
                </div>
                <div class="mb-3">
                    <label for="account_number" class="form-label">Account Number</label>
                    <input type="text" name="account_number" class="form-control" required value="<?php echo $disbursement['account_number'] ?? ''; ?>">
                </div>
                <div class="mb-3">
                    <label for="ifsc_code" class="form-label">IFSC Code</label>
                    <input type="text" name="ifsc_code" class="form-control" required value="<?php echo $disbursement['ifsc_code'] ?? ''; ?>">
                </div>
                <button type="submit" class="btn btn-primary"><?php echo $disbursement ? 'Update' : 'Submit'; ?> Bank Details</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
