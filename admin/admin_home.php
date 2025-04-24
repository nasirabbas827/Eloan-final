<?php
session_start();
include('config.php');

// Check if the user is logged in as an admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}


// Get total applications count
$total_applications_query = "SELECT COUNT(*) as count FROM loan_applications";
$result = $conn->query($total_applications_query);
$total_applications = $result->fetch_assoc()['count'];

// Get applications by status
$status_query = "SELECT application_status, COUNT(*) as count FROM loan_applications GROUP BY application_status";
$status_result = $conn->query($status_query);
$status_counts = [];
while ($row = $status_result->fetch_assoc()) {
    $status_counts[$row['application_status']] = $row['count'];
}

// Get total disbursed amount
$disbursed_query = "SELECT SUM(la.loan_amount) as total FROM loan_applications la 
                    JOIN loan_disbursement ld ON la.id = ld.application_id 
                    WHERE la.application_status = 'approved'";
$result = $conn->query($disbursed_query);
$total_disbursed = $result->fetch_assoc()['total'] ?: 0;

// Get total repaid amount
$repaid_query = "SELECT SUM(installment_amount) as total FROM loan_repayment_schedule 
                WHERE status = 'paid'";
$result = $conn->query($repaid_query);
$total_repaid = $result->fetch_assoc()['total'] ?: 0;

// Get total pending amount
$pending_query = "SELECT SUM(installment_amount) as total FROM loan_repayment_schedule 
                 WHERE status = 'unpaid'";
$result = $conn->query($pending_query);
$total_pending = $result->fetch_assoc()['total'] ?: 0;

// Get recent applications
$recent_applications_query = "SELECT la.*, u.username, u.email, u.phone 
                             FROM loan_applications la
                             JOIN users u ON la.user_id = u.id
                             ORDER BY la.created_at DESC LIMIT 10";
$recent_applications_result = $conn->query($recent_applications_query);
$recent_applications = [];
while ($row = $recent_applications_result->fetch_assoc()) {
    $recent_applications[] = $row;
}

// Get recent disbursements
$recent_disbursements_query = "SELECT ld.*, la.loan_amount, la.loan_type, u.username, u.email 
                              FROM loan_disbursement ld
                              JOIN loan_applications la ON ld.application_id = la.id
                              JOIN users u ON la.user_id = u.id
                              ORDER BY ld.submitted_at DESC LIMIT 5";
$recent_disbursements_result = $conn->query($recent_disbursements_query);
$recent_disbursements = [];
while ($row = $recent_disbursements_result->fetch_assoc()) {
    $recent_disbursements[] = $row;
}

// Get upcoming repayments
$upcoming_repayments_query = "SELECT lrs.*, la.loan_type, u.username, u.email 
                             FROM loan_repayment_schedule lrs
                             JOIN loan_applications la ON lrs.application_id = la.id
                             JOIN users u ON la.user_id = u.id
                             WHERE lrs.status = 'unpaid' AND lrs.due_date >= CURDATE()
                             ORDER BY lrs.due_date ASC LIMIT 5";
$upcoming_repayments_result = $conn->query($upcoming_repayments_query);
$upcoming_repayments = [];
while ($row = $upcoming_repayments_result->fetch_assoc()) {
    $upcoming_repayments[] = $row;
}

// Get borrower statistics
$borrower_stats_query = "SELECT u.id, u.username, u.email, u.phone, 
                        COUNT(la.id) as total_applications,
                        SUM(CASE WHEN la.application_status = 'approved' THEN 1 ELSE 0 END) as approved_loans,
                        SUM(CASE WHEN la.application_status = 'approved' THEN la.loan_amount ELSE 0 END) as total_loan_amount
                        FROM users u
                        LEFT JOIN loan_applications la ON u.id = la.user_id
                        WHERE u.usertype = 'borrower'
                        GROUP BY u.id
                        ORDER BY total_loan_amount DESC
                        LIMIT 5";
$borrower_stats_result = $conn->query($borrower_stats_query);
$borrower_stats = [];
while ($row = $borrower_stats_result->fetch_assoc()) {
    $borrower_stats[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <link rel="stylesheet" href="../css/style.css">
    <style>
        .dashboard-card {
            transition: transform 0.3s;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .table-responsive {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.4rem 0.6rem;
        }
        .notification-area {
            max-height: 400px;
            overflow-y: auto;
        }
        .fade-out {
            opacity: 0;
            transition: opacity 0.5s;
        }
    </style>
</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-5 mb-5">
    <h1 class="text-center mb-4">Admin Dashboard</h1>
    
    <!-- Notifications Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-bell me-2"></i> Notifications</h5>
                </div>
                <div class="card-body notification-area">
                    <?php if (empty($upcoming_repayments)): ?>
                        <p class="text-center">No notifications at this time.</p>
                    <?php else: ?>
                        <?php foreach ($upcoming_repayments as $repayment): ?>
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <strong>Upcoming Repayment!</strong> Payment of RS: <?= number_format($repayment['installment_amount'], 2) ?> due on <?= date('d M Y', strtotime($repayment['due_date'])) ?> for <?= htmlspecialchars($repayment['username']) ?>'s <?= htmlspecialchars($repayment['loan_type']) ?> loan.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php foreach ($recent_applications as $app): ?>
                            <?php if ($app['application_status'] == 'submitted'): ?>
                                <div class="alert alert-info alert-dismissible fade show" role="alert">
                                    <strong>New Application!</strong> <?= htmlspecialchars($app['username']) ?> has submitted a new <?= htmlspecialchars($app['loan_type']) ?> loan application for RS: <?= number_format($app['loan_amount'], 2) ?>.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card dashboard-card bg-primary text-white h-100">
                <div class="card-body text-center">
                    <h1 class="display-4"><?= $total_applications ?></h1>
                    <p class="card-text"><i class="fas fa-file-alt me-2"></i> Total Applications</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card dashboard-card bg-success text-white h-100">
                <div class="card-body text-center">
                    <h1 class="display-4"><?= $status_counts['approved'] ?? 0 ?></h1>
                    <p class="card-text"><i class="fas fa-check-circle me-2"></i> Approved Loans</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card dashboard-card bg-info text-white h-100">
                <div class="card-body text-center">
                    <h1 class="display-4">RS: <?= number_format($total_disbursed, 0) ?></h1>
                    <p class="card-text"><i class="fas fa-money-bill-wave me-2"></i> Total Disbursed</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card dashboard-card bg-warning text-dark h-100">
                <div class="card-body text-center">
                    <h1 class="display-4">RS: <?= number_format($total_pending, 0) ?></h1>
                    <p class="card-text"><i class="fas fa-hourglass-half me-2"></i> Pending Repayments</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Additional Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card dashboard-card bg-danger text-white h-100">
                <div class="card-body text-center">
                    <h1 class="display-4"><?= $status_counts['under_review'] ?? 0 ?></h1>
                    <p class="card-text"><i class="fas fa-search me-2"></i> Under Review</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card dashboard-card bg-secondary text-white h-100">
                <div class="card-body text-center">
                    <h1 class="display-4"><?= $status_counts['rejected'] ?? 0 ?></h1>
                    <p class="card-text"><i class="fas fa-times-circle me-2"></i> Rejected Applications</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card dashboard-card bg-dark text-white h-100">
                <div class="card-body text-center">
                    <h1 class="display-4">RS: <?= number_format($total_repaid, 0) ?></h1>
                    <p class="card-text"><i class="fas fa-hand-holding-usd me-2"></i> Total Repaid</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Applications Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i> Recent Loan Applications</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Borrower</th>
                                    <th>Contact</th>
                                    <th>Loan Type</th>
                                    <th>Amount</th>
                                    <th>Period</th>
                                    <th>Status</th>
                                    <th>Applied On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_applications)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center">No loan applications found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recent_applications as $app): ?>
                                        <tr>
                                            <td><?= $app['id'] ?></td>
                                            <td><?= htmlspecialchars($app['username']) ?></td>
                                            <td>
                                                <small><?= htmlspecialchars($app['email']) ?></small><br>
                                                <small><?= htmlspecialchars($app['phone']) ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($app['loan_type']) ?></td>
                                            <td>RS: <?= number_format($app['loan_amount'], 2) ?></td>
                                            <td><?= $app['repayment_period'] ?> months</td>
                                            <td>
                                                <span class="badge status-badge <?php 
                                                    echo match($app['application_status']) {
                                                        'submitted' => 'bg-secondary',
                                                        'under_review' => 'bg-info',
                                                        'approved' => 'bg-success',
                                                        'rejected' => 'bg-danger',
                                                        'partially_completed' => 'bg-warning text-dark',
                                                        'draft' => 'bg-light text-dark',
                                                        default => 'bg-secondary'
                                                    };
                                                ?>">
                                                    <?= ucwords(str_replace('_', ' ', $app['application_status'])) ?>
                                                </span>
                                            </td>
                                            <td><?= date('d M Y', strtotime($app['created_at'])) ?></td>
                                            <td>
                                                <a href="view_applications.php" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="view_applications.php" class="btn btn-outline-primary">View All Applications</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Disbursements Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i> Recent Loan Disbursements</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Borrower</th>
                                    <th>Loan Type</th>
                                    <th>Amount</th>
                                    <th>Bank Details</th>
                                    <th>Disbursed On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_disbursements)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No disbursements found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recent_disbursements as $disbursement): ?>
                                        <tr>
                                            <td><?= $disbursement['id'] ?></td>
                                            <td>
                                                <?= htmlspecialchars($disbursement['username']) ?><br>
                                                <small><?= htmlspecialchars($disbursement['email']) ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($disbursement['loan_type']) ?></td>
                                            <td>RS: <?= number_format($disbursement['loan_amount'], 2) ?></td>
                                            <td>
                                                <small><strong>Bank:</strong> <?= htmlspecialchars($disbursement['bank_name']) ?></small><br>
                                                <small><strong>A/C:</strong> <?= htmlspecialchars($disbursement['account_number']) ?></small><br>
                                                <small><strong>IFSC:</strong> <?= htmlspecialchars($disbursement['ifsc_code']) ?></small>
                                            </td>
                                            <td><?= date('d M Y', strtotime($disbursement['submitted_at'])) ?></td>
                                            <td>
                                                <a href="view_applications.php" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="view_applications.php" class="btn btn-outline-success">View All Disbursements</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Upcoming Repayments Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i> Upcoming Repayments</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Borrower</th>
                                    <th>Loan Type</th>
                                    <th>Due Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($upcoming_repayments)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No upcoming repayments found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($upcoming_repayments as $repayment): ?>
                                        <tr>
                                            <td><?= $repayment['id'] ?></td>
                                            <td>
                                                <?= htmlspecialchars($repayment['username']) ?><br>
                                                <small><?= htmlspecialchars($repayment['email']) ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($repayment['loan_type']) ?></td>
                                            <td><?= date('d M Y', strtotime($repayment['due_date'])) ?></td>
                                            <td>RS: <?= number_format($repayment['installment_amount'], 2) ?></td>
                                            <td>
                                                <span class="badge <?= ($repayment['status'] == 'paid') ? 'bg-success' : 'bg-warning text-dark' ?>">
                                                    <?= ucfirst($repayment['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="view_applications.php" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="view_applications.php" class="btn btn-outline-warning">View All Repayments</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Top Borrowers Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i> Top Borrowers</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Borrower</th>
                                    <th>Contact</th>
                                    <th>Total Applications</th>
                                    <th>Approved Loans</th>
                                    <th>Total Loan Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($borrower_stats)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No borrowers found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($borrower_stats as $borrower): ?>
                                        <tr>
                                            <td><?= $borrower['id'] ?></td>
                                            <td><?= htmlspecialchars($borrower['username']) ?></td>
                                            <td>
                                                <small><?= htmlspecialchars($borrower['email']) ?></small><br>
                                                <small><?= htmlspecialchars($borrower['phone']) ?></small>
                                            </td>
                                            <td><?= $borrower['total_applications'] ?></td>
                                            <td><?= $borrower['approved_loans'] ?></td>
                                            <td>RS: <?= number_format($borrower['total_loan_amount'], 2) ?></td>
                                            
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row mt-4">
            <div class="col-12 text-center">
                <a href="view_applications.php" class="btn btn-outline-primary btn-lg me-2">
                    <i class="fas fa-list me-2"></i> View All Applications
                </a>
                <a href="update_profile.php" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-user me-2"></i> Update Profile
                </a>
            </div>
        </div>
</div>

<!-- Footer -->
<footer class="bg-dark text-white text-center py-3 mt-5">
    <p class="mb-0">&copy; <?= date('Y') ?> Loan Management System. All rights reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // JavaScript for dismissing notifications
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize all tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Auto-dismiss alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.classList.add('fade-out');
                setTimeout(() => alert.remove(), 500);
            }, 5000);
        });
    });
</script>
</body>
</html>