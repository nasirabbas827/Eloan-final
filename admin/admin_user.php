<?php
session_start();
include 'config.php';

// Redirect if not logged in as admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Handle delete action
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $delete_sql = "DELETE FROM users WHERE id = $delete_id";
    mysqli_query($conn, $delete_sql);
    header("Location: admin_user.php"); // Reload the page after deletion
    exit;
}

// Fetch all users
$sql = "SELECT id, username, email, phone, usertype FROM users";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Admin Panel - Manage Users</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <?php include('admin_navbar.php'); ?>

    <div class="container mt-5">
        <h2 class="text-center mb-4">Manage Users</h2>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>User Type</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td><?php echo ucfirst($row['usertype']); ?></td>
                            <td>
    <a href="admin_user.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm"
       onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
    <a href="user_history.php?user_id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">Generate History</a>
</td>

                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

</body>

</html>
