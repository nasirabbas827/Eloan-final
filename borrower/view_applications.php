<?php
// Start session and check if the user is logged in as a borrower
session_start();
include('config.php');

if (!isset($_SESSION["id"])) {
    header("location: ../login.php");
    exit;
}

// Fetch all applications for the logged-in user
$sql = "SELECT * FROM loan_applications WHERE user_id = ? ORDER BY created_at DESC";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $_SESSION["id"]);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
    } else {
        echo "Error fetching applications.";
        exit;
    }
    mysqli_stmt_close($stmt);
}

// Check if repayment terms exist for each application
$loan_with_repayment = [];
while ($application = mysqli_fetch_assoc($result)) {
    $application_id = $application['id'];
    $repayment_sql = "SELECT * FROM loan_repayment_schedule WHERE application_id = ?";
    if ($repayment_stmt = mysqli_prepare($conn, $repayment_sql)) {
        mysqli_stmt_bind_param($repayment_stmt, "i", $application_id);
        if (mysqli_stmt_execute($repayment_stmt)) {
            $repayment_result = mysqli_stmt_get_result($repayment_stmt);
            if (mysqli_num_rows($repayment_result) > 0) {
                $loan_with_repayment[$application_id] = true;
            }
        } else {
            echo "Error checking repayment schedule.";
        }
        mysqli_stmt_close($repayment_stmt);
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View All Loan Applications - Borrower Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include('navbar.php'); ?>

    <div class="container mt-5 mb-5">
        <h1 class="text-center mb-4">Your Loan Applications</h1>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">Application ID</th>
                        <th scope="col">Loan Amount</th>
                        <th scope="col">Loan Type</th>
                        <th scope="col">Review</th>
                        <th scope="col">Status</th>
                        <th scope="col">View Details</th>
                        <th scope="col">Repayment</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Move the pointer back to the start of the result set for re-iterating
                    mysqli_data_seek($result, 0);
                    
                    while ($application = mysqli_fetch_assoc($result)): 
                        $application_id = $application['id'];
                    ?>
                        <tr>
                            <td><?php echo $application['id']; ?></td>
                            <td><?php echo $application['loan_amount']; ?></td>
                            <td><?php echo $application['loan_type']; ?></td>
                            <td><?php echo $application['review']; ?></td>
                            <td>
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
                            </td>
                            <td>
                                <a href="view_loan_application.php?id=<?php echo $application['id']; ?>" class="btn btn-info">View</a>
                                
                                <?php if ($application['application_status'] == 'draft'): ?>
                                    <a href="edit_loan_application.php?id=<?php echo $application['id']; ?>" class="btn btn-warning">Edit</a>
                                <?php endif; ?>

                                <?php if ($application['application_status'] == 'approved'): ?>
                                    <a href="add_disbursement.php?application_id=<?php echo $application['id']; ?>" class="btn btn-success ">Add Bank Details</a>
                                    <button class="btn btn-success mt-2" 
                        onclick="generatePDF('<?php echo $application['id']; ?>', '<?php echo $application['loan_amount']; ?>', '<?php echo $application['loan_type']; ?>', '<?php echo $application['purpose']; ?>', '<?php echo $_SESSION['id']; ?>')">
                        Generate Approval Letter
                    </button>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (isset($loan_with_repayment[$application_id])): ?>
                                    <a href="pay_repayment.php?application_id=<?php echo $application['id']; ?>" class="btn btn-primary">Pay Repayment</a>
                                <?php else: ?>
                                    <span class="text-muted">No Repayment Terms</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center">No loan applications found.</p>
        <?php endif; ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
    function generatePDF(applicationId, loanAmount, loanType, purpose, userId) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        const content = `
Loan Approval Letter

Dear User ID: ${userId},

We are pleased to inform you that your loan application (ID: ${applicationId}) has been approved.

Loan Details:
- Loan Amount: PKR ${loanAmount}
- Loan Type: ${loanType}
- Purpose: ${purpose}

Please contact us to finalize the agreement and receive the disbursement.

Thank you,
Loan Management Team
        `;

        doc.setFont("Times", "normal");
        doc.setFontSize(12);
        doc.text(content, 15, 20);

        doc.save(`loan_${applicationId}_approval_letter.pdf`);
    }
</script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
