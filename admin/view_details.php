<?php
session_start();
include('config.php');

// Check if the user is logged in as an admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Fetch loan details based on the loan ID passed via query string
if (isset($_GET['id'])) {
    $loan_id = $_GET['id'];

    // Fetch the loan details
    $sql = "SELECT * FROM loan_applications WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $loan_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($loan = mysqli_fetch_assoc($result)) {
        // Display the loan details
    } else {
        echo "Loan not found.";
        exit;
    }
} else {
    echo "Invalid request.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Application Details</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include('admin_navbar.php'); ?>

    <div class="container mt-5 mb-5">
        <h1 class="text-center mb-5">Loan Application Details</h1>

        <table class="table table-bordered">
            <tbody>
                <tr>
                    <th>ID</th>
                    <td><?php echo $loan['id']; ?></td>
                </tr>
                <tr>
                    <th>User ID</th>
                    <td><?php echo $loan['user_id']; ?></td>
                </tr>
                <tr>
                    <th>Loan Amount</th>
                    <td><?php echo $loan['loan_amount']; ?></td>
                </tr>
                <tr>
                    <th>Loan Type</th>
                    <td><?php echo $loan['loan_type']; ?></td>
                </tr>
                <tr>
                    <th>Purpose</th>
                    <td><?php echo $loan['purpose']; ?></td>
                </tr>
                <tr>
                    <th>Repayment Period</th>
                    <td><?php echo $loan['repayment_period']; ?> months</td>
                </tr>
                <!-- Display the file links -->
                <tr>
                    <th>Proof of Income</th>
                    <td>
                        <?php 
                            // Check if the file path is not empty and display it
                            if (!empty($loan['proof_of_income'])) {
                                echo '<a href="../borrower/uploads/' . $loan['proof_of_income'] . '" target="_blank">View Proof of Income</a>';
                            } else {
                                echo 'No file uploaded.';
                            }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>Proof of Identification</th>
                    <td>
                        <?php 
                            // Check if the file path is not empty and display it
                            if (!empty($loan['proof_of_identification'])) {
                                echo '<a href="../borrower/uploads/' . $loan['proof_of_identification'] . '" target="_blank">View Proof of Identification</a>';
                            } else {
                                echo 'No file uploaded.';
                            }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>Credit History</th>
                    <td>
                        <?php 
                            // Check if the file path is not empty and display it
                            if (!empty($loan['credit_history'])) {
                                echo '<a href="../borrower/uploads/' . $loan['credit_history'] . '" target="_blank">View Credit History</a>';
                            } else {
                                echo 'No file uploaded.';
                            }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>Application Status</th>
                    <td><?php echo ucfirst($loan['application_status']); ?></td>
                </tr>
                <tr>
                    <th>Review</th>
                    <td><?php echo $loan['review']; ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
