<?php
// start session and check if user is logged in as a borrower
session_start();
include('config.php');

if (!isset($_SESSION["id"])) {
    header("location: ../login.php");
    exit;
}

$user_id = $_SESSION["id"];



// Get user's loan applications
$applications_query = "SELECT * FROM loan_applications WHERE user_id = ?";
$stmt = $conn->prepare($applications_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$applications_result = $stmt->get_result();
$applications = $applications_result->fetch_all(MYSQLI_ASSOC);

// Get statistics
$total_applications = count($applications);

// Count approved applications
$approved_query = "SELECT COUNT(*) as count FROM loan_applications WHERE user_id = ? AND application_status = 'approved'";
$stmt = $conn->prepare($approved_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$approved_result = $stmt->get_result();
$approved_count = $approved_result->fetch_assoc()['count'];

// Get total installments
$installments_query = "SELECT COUNT(*) as count FROM loan_repayment_schedule 
                      WHERE application_id IN (SELECT id FROM loan_applications WHERE user_id = ?)";
$stmt = $conn->prepare($installments_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$installments_result = $stmt->get_result();
$total_installments = $installments_result->fetch_assoc()['count'];

// Get total pending loan amount
$pending_amount_query = "SELECT SUM(installment_amount) as total FROM loan_repayment_schedule 
                        WHERE status = 'unpaid' AND application_id IN 
                        (SELECT id FROM loan_applications WHERE user_id = ?)";
$stmt = $conn->prepare($pending_amount_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pending_result = $stmt->get_result();
$pending_amount = $pending_result->fetch_assoc()['total'] ?: 0;

// Get notifications
// 1. Upcoming repayments (due in the next 7 days)
$upcoming_repayments_query = "SELECT lrs.*, la.loan_type 
                             FROM loan_repayment_schedule lrs
                             JOIN loan_applications la ON lrs.application_id = la.id
                             WHERE la.user_id = ? AND lrs.status = 'unpaid' 
                             AND lrs.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                             ORDER BY lrs.due_date ASC";
$stmt = $conn->prepare($upcoming_repayments_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$upcoming_repayments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// 2. Recent application status changes
$recent_status_query = "SELECT * FROM loan_applications 
                       WHERE user_id = ? AND 
                       (application_status = 'approved' OR application_status = 'rejected' OR 
                        application_status = 'under_review')
                       AND updated_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                       ORDER BY updated_at DESC";
$stmt = $conn->prepare($recent_status_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_status_changes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// 3. Incomplete applications
$incomplete_query = "SELECT * FROM loan_applications 
                    WHERE user_id = ? AND 
                    (application_status = 'draft' OR application_status = 'partially_completed')";
$stmt = $conn->prepare($incomplete_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$incomplete_applications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Function to dismiss notification (would be handled by AJAX in a real application)
if (isset($_GET['dismiss']) && isset($_GET['type']) && isset($_GET['id'])) {
    // In a real application, you would store dismissed notifications in a separate table
    // For this example, we'll just redirect back to remove the GET parameters
    header("Location: borrower_home.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrower Dashboard</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
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
        .notification-area {
            max-height: 400px;
            overflow-y: auto;
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.4rem 0.6rem;
        }
        .table-responsive {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .fade-out {
            opacity: 0;
            transition: opacity 0.5s;
        }
    </style>
</head>
<body>
    <?php include('navbar.php'); ?>

    <div class="container mt-5 mb-5">
        <h1 class="text-center mb-4">Borrower Dashboard</h1>
        
        <!-- Notifications Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-bell me-2"></i> Notifications</h5>
                    </div>
                    <div class="card-body notification-area">
                        <?php if (empty($upcoming_repayments) && empty($recent_status_changes) && empty($incomplete_applications)): ?>
                            <p class="text-center">No notifications at this time.</p>
                        <?php else: ?>
                            <!-- Upcoming Repayments -->
                            <?php foreach ($upcoming_repayments as $repayment): ?>
                                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                    <strong>Upcoming Payment!</strong> You have a payment of RS: <?= number_format($repayment['installment_amount'], 2) ?> due on <?= date('d M Y', strtotime($repayment['due_date'])) ?> for your <?= htmlspecialchars($repayment['loan_type']) ?> loan.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" onclick="window.location.href='borrower_home.php?dismiss=1&type=repayment&id=<?= $repayment['id'] ?>'"></button>
                                </div>
                            <?php endforeach; ?>
                            
                            <!-- Recent Status Changes -->
                            <?php foreach ($recent_status_changes as $change): ?>
                                <div class="alert <?= ($change['application_status'] == 'approved') ? 'alert-success' : (($change['application_status'] == 'rejected') ? 'alert-danger' : 'alert-info') ?> alert-dismissible fade show" role="alert">
                                    <strong>Application Update!</strong> Your loan application for RS: <?= number_format($change['loan_amount'], 2) ?> has been <strong><?= str_replace('_', ' ', $change['application_status']) ?></strong>.
                                    <?php if ($change['application_status'] == 'rejected' && !empty($change['review'])): ?>
                                        <br>Reason: <?= htmlspecialchars($change['review']) ?>
                                    <?php endif; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" onclick="window.location.href='borrower_home.php?dismiss=1&type=status&id=<?= $change['id'] ?>'"></button>
                                </div>
                            <?php endforeach; ?>
                            
                            <!-- Incomplete Applications -->
                            <?php foreach ($incomplete_applications as $incomplete): ?>
                                <div class="alert alert-secondary alert-dismissible fade show" role="alert">
                                    <strong>Incomplete Application!</strong> Your <?= htmlspecialchars($incomplete['loan_type']) ?> loan application for RS: <?= number_format($incomplete['loan_amount'], 2) ?> is incomplete. Please complete it to proceed.
                                    <a href="complete_application.php?id=<?= $incomplete['id'] ?>" class="alert-link">Complete now</a>.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" onclick="window.location.href='borrower_home.php?dismiss=1&type=incomplete&id=<?= $incomplete['id'] ?>'"></button>
                                </div>
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
                        <h1 class="display-4"><?= $approved_count ?></h1>
                        <p class="card-text"><i class="fas fa-check-circle me-2"></i> Approved Loans</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card dashboard-card bg-info text-white h-100">
                    <div class="card-body text-center">
                        <h1 class="display-4"><?= $total_installments ?></h1>
                        <p class="card-text"><i class="fas fa-calendar-alt me-2"></i> Total Installments</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card dashboard-card bg-warning text-dark h-100">
                    <div class="card-body text-center">
                        <h1 class="display-4">RS: <?= number_format($pending_amount, 0) ?></h1>
                        <p class="card-text"><i class="fas fa-money-bill-wave me-2"></i> Pending Amount</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Loan Applications Table -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i> Your Loan Applications</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Loan Type</th>
                                        <th>Amount</th>
                                        <th>Repayment Period</th>
                                        <th>Status</th>
                                        <th>Applied On</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($applications)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No loan applications found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($applications as $app): ?>
                                            <tr>
                                                <td><?= $app['id'] ?></td>
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
                                                    <a href="view_loan_application.php?id=<?= $app['id'] ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>

                                                    <?php if ($app['application_status'] == 'draft' || $app['application_status'] == 'partially_completed'): ?>
                                                        <a href="edit_loan_application.php?id=<?= $app['id'] ?>" class="btn btn-sm btn-warning">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
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
        
        <!-- Upcoming Repayments Section -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i> Upcoming Repayments</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Loan ID</th>
                                        <th>Due Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Get upcoming repayments for all loans
                                    $repayments_query = "SELECT lrs.*, la.loan_type 
                                                        FROM loan_repayment_schedule lrs
                                                        JOIN loan_applications la ON lrs.application_id = la.id
                                                        WHERE la.user_id = ? AND lrs.status = 'unpaid'
                                                        ORDER BY lrs.due_date ASC
                                                        LIMIT 5";
                                    $stmt = $conn->prepare($repayments_query);
                                    $stmt->bind_param("i", $user_id);
                                    $stmt->execute();
                                    $repayments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                                    
                                    if (empty($repayments)):
                                    ?>
                                        <tr>
                                            <td colspan="5" class="text-center">No upcoming repayments found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($repayments as $repayment): ?>
                                            <tr>
                                                <td><?= $repayment['application_id'] ?></td>
                                                <td><?= date('d M Y', strtotime($repayment['due_date'])) ?></td>
                                                <td>RS: <?= number_format($repayment['installment_amount'], 2) ?></td>
                                                <td>
                                                    <span class="badge <?= ($repayment['status'] == 'paid') ? 'bg-success' : 'bg-warning text-dark' ?>">
                                                        <?= ucfirst($repayment['status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($repayment['status'] == 'unpaid'): ?>
                                                        <a href="view_applications.php" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-credit-card me-1"></i> Pay Now
                                                        </a>
                                                    <?php else: ?>
                                                        <button class="btn btn-sm btn-success" disabled>
                                                            <i class="fas fa-check me-1"></i> Paid
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="view_applicatoins.php" class="btn btn-outline-primary">View All Repayments</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="row mt-4">
            <div class="col-12 text-center">
                <a href="loan_application.php" class="btn btn-primary btn-lg me-2">
                    <i class="fas fa-plus-circle me-2"></i> Apply for New Loan
                </a>
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