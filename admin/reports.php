<?php
session_start();
include 'config.php';

// Only admin access
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Handle form submission
$where = "1";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $status = $_POST['status'];
    $from = $_POST['from'];
    $to = $_POST['to'];

    if (!empty($status)) {
        $where .= " AND application_status = '" . mysqli_real_escape_string($conn, $status) . "'";
    }
    if (!empty($from) && !empty($to)) {
        $where .= " AND DATE(created_at) BETWEEN '$from' AND '$to'";
    }
}

$query = "SELECT * FROM loan_applications WHERE $where ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Loan Report Generator</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Loan Application Reports</h2>

    <form method="POST" class="row g-3 mb-4">
        <div class="col-md-3">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="">All</option>
                <option value="submitted">Submitted</option>
                <option value="under_review">Under Review</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="partially_completed">Partially Completed</option>
                <option value="draft">Draft</option>
            </select>
        </div>
        <div class="col-md-3">
            <label>From</label>
            <input type="date" name="from" class="form-control">
        </div>
        <div class="col-md-3">
            <label>To</label>
            <input type="date" name="to" class="form-control">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Generate</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered" id="reportTable">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>User ID</th>
                    <th>Loan Amount</th>
                    <th>Loan Type</th>
                    <th>Purpose</th>
                    <th>Repayment Period</th>
                    <th>Status</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['user_id'] ?></td>
                        <td><?= $row['loan_amount'] ?></td>
                        <td><?= $row['loan_type'] ?></td>
                        <td><?= htmlspecialchars(substr($row['purpose'], 0, 30)) . '...' ?></td>
                        <td><?= $row['repayment_period'] ?> months</td>
                        <td><?= ucfirst(str_replace('_', ' ', $row['application_status'])) ?></td>
                        <td><?= $row['created_at'] ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <button onclick="downloadPDF()" class="btn btn-danger mt-3">Export as PDF</button>
</div>

<script>
function downloadPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('landscape');
    doc.text("Loan Application Report", 14, 15);

    const headers = [["ID", "User ID", "Loan Amount", "Loan Type", "Purpose", "Repayment", "Status", "Created At"]];
    const rows = Array.from(document.querySelectorAll("#reportTable tbody tr")).map(row =>
        Array.from(row.children).map(cell => cell.innerText)
    );

    doc.autoTable({
        head: headers,
        body: rows,
        startY: 20,
        styles: { fontSize: 8 }
    });

    doc.save("loan_report.pdf");
}
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
</body>
</html>
