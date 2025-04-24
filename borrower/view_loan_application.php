<?php
// Start session and check if the user is logged in as a borrower
session_start();
include('config.php');

if (!isset($_SESSION["id"])) {
    header("location: ../login.php");
    exit;
}

// Get the application ID from the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $application_id = $_GET['id'];

    // Fetch loan application details from the database
    $sql = "SELECT * FROM loan_applications WHERE id = ? AND user_id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $application_id, $_SESSION["id"]);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            if (mysqli_num_rows($result) == 1) {
                $application = mysqli_fetch_assoc($result);
            } else {
                echo "Application not found.";
                exit;
            }
        } else {
            echo "Error fetching application details.";
            exit;
        }
        mysqli_stmt_close($stmt);
    }

    mysqli_close($conn);
} else {
    echo "Invalid application ID.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Loan Application - Borrower Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include('navbar.php'); ?>

    <div class="container mt-5 mb-5">
        <h1 class="text-center mb-4">Loan Application Details</h1>

        <div class="card mx-auto" style="max-width: 800px;">
            <div class="card-body">
                <h4 class="card-title">Application ID: <?php echo $application['id']; ?></h4>

                <p><strong>Loan Amount:</strong> <?php echo $application['loan_amount']; ?></p>
                <p><strong>Loan Type:</strong> <?php echo $application['loan_type']; ?></p>
                <p><strong>Purpose:</strong> <?php echo $application['purpose']; ?></p>
                <p><strong>Repayment Period (Months):</strong> <?php echo $application['repayment_period']; ?></p>
                <p><strong>Review:</strong> <?php echo $application['review']; ?></p>

                <p><strong>Status:</strong>
                    <span class="badge 
                        <?php 
                            switch ($application['application_status']) {
                                case 'submitted':
                                    echo 'bg-primary';
                                    break;
                                case 'under_review':
                                    echo 'bg-warning';
                                    break;
                                case 'approved':
                                    echo 'bg-success';
                                    break;
                                case 'rejected':
                                    echo 'bg-danger';
                                    break;
                                case 'partially_completed':
                                    echo 'bg-info';
                                    break;
                                case 'draft':
                                    echo 'bg-secondary';
                                    break;
                            }
                        ?>">
                        <?php echo ucfirst($application['application_status']); ?>
                    </span>
                </p>

                <p><strong>Submitted On:</strong> <?php echo date("F j, Y, g:i a", strtotime($application['created_at'])); ?></p>

                <!-- Document Links -->
                <h5>Uploaded Documents:</h5>
                <ul>
                    <?php if (!empty($application['proof_of_income'])): ?>
                        <li><a href="uploads/<?php echo $application['proof_of_income']; ?>" target="_blank">Proof of Income</a></li>
                    <?php endif; ?>
                    <?php if (!empty($application['proof_of_identification'])): ?>
                        <li><a href="uploads/<?php echo $application['proof_of_identification']; ?>" target="_blank">Proof of Identification</a></li>
                    <?php endif; ?>
                    <?php if (!empty($application['credit_history'])): ?>
                        <li><a href="uploads/<?php echo $application['credit_history']; ?>" target="_blank">Credit History</a></li>
                    <?php endif; ?>
                </ul>

                <!-- Action Buttons -->
                <div class="mt-4">
                    <?php if ($application['application_status'] == 'draft'): ?>
                        <a href="edit_loan_application.php?id=<?php echo $application['id']; ?>" class="btn btn-warning">Edit Application</a>
                    <?php endif; ?>
                    <a href="view_applications.php" class="btn btn-secondary">Back to Applications</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
