<?php
// Start session and check if the user is logged in as a borrower
session_start();
include('config.php');

if (!isset($_SESSION["id"])) {
    header("location: ../login.php");
    exit;
}

// Initialize variables
$loan_amount = $loan_type = $purpose = $repayment_period = "";
$loan_amount_err = $loan_type_err = $purpose_err = $repayment_period_err = "";
$document_err = "";

// Handle the form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate loan amount
    if (empty(trim($_POST["loan_amount"]))) {
        $loan_amount_err = "Please enter a loan amount.";
    } else {
        $loan_amount = trim($_POST["loan_amount"]);
    }

    // Validate loan type (user input as text)
    if (empty(trim($_POST["loan_type"]))) {
        $loan_type_err = "Please enter the loan type.";
    } else {
        $loan_type = trim($_POST["loan_type"]);
    }

    // Validate purpose
    if (empty(trim($_POST["purpose"]))) {
        $purpose_err = "Please enter the purpose of the loan.";
    } else {
        $purpose = trim($_POST["purpose"]);
    }

    // Validate repayment period
    if (empty(trim($_POST["repayment_period"]))) {
        $repayment_period_err = "Please enter the repayment period.";
    } else {
        $repayment_period = trim($_POST["repayment_period"]);
    }

    // Handle file upload (proof of income, identification, credit history)
    $documents = ['proof_of_income', 'proof_of_identification', 'credit_history'];
    $uploaded_files = [];
    foreach ($documents as $document) {
        if ($_FILES[$document]["error"] == 0 && $_FILES[$document]["size"] > 0) {
            $filename = $_FILES[$document]["name"];
            $tempname = $_FILES[$document]["tmp_name"];
            move_uploaded_file($tempname, "uploads/$filename");
            $uploaded_files[$document] = $filename;
        } else {
            $document_err = "Please upload all required documents.";
        }
    }

    // Check if Save Draft or Submit is clicked
    if (isset($_POST['submit'])) {
        $application_status = 'submitted';
    } else {
        $application_status = 'draft';
    }

    // If no errors, save the loan application to the database
    if (empty($loan_amount_err) && empty($loan_type_err) && empty($purpose_err) && empty($repayment_period_err) && empty($document_err)) {

        // Insert loan application data into the database
        $sql = "INSERT INTO loan_applications (user_id, loan_amount, loan_type, purpose, repayment_period, proof_of_income, proof_of_identification, credit_history, application_status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "issssssss", $_SESSION["id"], $loan_amount, $loan_type, $purpose, $repayment_period, 
                $uploaded_files['proof_of_income'], $uploaded_files['proof_of_identification'], $uploaded_files['credit_history'], $application_status);
            if (mysqli_stmt_execute($stmt)) {
                // Redirect to the appropriate page based on the status
                if ($application_status == 'submitted') {
                    echo "Your Application Status is Submitted Wait for Approval";
                } else {
                echo "Your Application Status is Draft";
                }
            } else {
                echo "Error: Could not submit the loan application.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    mysqli_close($conn);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Application - Borrower Dashboard</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include('navbar.php'); ?>

    <div class="container mt-5 mb-5">
        <div class="card mx-auto" style="max-width: 600px;">
            <div class="card-body">
                <h1 class="text-center mb-5">Loan Application</h1>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                    <!-- Loan Amount -->
                    <div class="mb-3">
                        <label for="loan_amount" class="form-label">Loan Amount</label>
                        <input type="text" class="form-control <?php echo (!empty($loan_amount_err)) ? 'is-invalid' : ''; ?>" id="loan_amount" name="loan_amount" value="<?php echo $loan_amount; ?>">
                        <div class="invalid-feedback"><?php echo $loan_amount_err; ?></div>
                    </div>

                    <!-- Loan Type (Text input instead of selection) -->
                    <div class="mb-3">
                        <label for="loan_type" class="form-label">Loan Type</label>
                        <input type="text" class="form-control <?php echo (!empty($loan_type_err)) ? 'is-invalid' : ''; ?>" id="loan_type" name="loan_type" value="<?php echo $loan_type; ?>">
                        <div class="invalid-feedback"><?php echo $loan_type_err; ?></div>
                    </div>

                    <!-- Purpose -->
                    <div class="mb-3">
                        <label for="purpose" class="form-label">Purpose of Loan</label>
                        <input type="text" class="form-control <?php echo (!empty($purpose_err)) ? 'is-invalid' : ''; ?>" id="purpose" name="purpose" value="<?php echo $purpose; ?>">
                        <div class="invalid-feedback"><?php echo $purpose_err; ?></div>
                    </div>

                    <!-- Repayment Period -->
                    <div class="mb-3">
                        <label for="repayment_period" class="form-label">Repayment Period (Months)</label>
                        <input type="number" class="form-control <?php echo (!empty($repayment_period_err)) ? 'is-invalid' : ''; ?>" id="repayment_period" name="repayment_period" value="<?php echo $repayment_period; ?>">
                        <div class="invalid-feedback"><?php echo $repayment_period_err; ?></div>
                    </div>

                    <!-- File Uploads -->
                    <div class="mb-3">
                        <label for="proof_of_income" class="form-label">Upload Proof of Income</label>
                        <input type="file" class="form-control <?php echo (!empty($document_err)) ? 'is-invalid' : ''; ?>" id="proof_of_income" name="proof_of_income">
                        <div class="invalid-feedback"><?php echo $document_err; ?></div>
                    </div>

                    <div class="mb-3">
                        <label for="proof_of_identification" class="form-label">Upload Proof of Identification</label>
                        <input type="file" class="form-control <?php echo (!empty($document_err)) ? 'is-invalid' : ''; ?>" id="proof_of_identification" name="proof_of_identification">
                        <div class="invalid-feedback"><?php echo $document_err; ?></div>
                    </div>

                    <div class="mb-3">
                        <label for="credit_history" class="form-label">Upload Credit History</label>
                        <input type="file" class="form-control <?php echo (!empty($document_err)) ? 'is-invalid' : ''; ?>" id="credit_history" name="credit_history">
                        <div class="invalid-feedback"><?php echo $document_err; ?></div>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn btn-primary">Save Draft</button>
                    <button type="submit" name="submit" class="btn btn-success">Submit Application</button>
                    <a class="btn btn-dark" href="view_applications.php">View Applications</a>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
