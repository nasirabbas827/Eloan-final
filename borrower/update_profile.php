<?php
// Start session and check if user is logged in
session_start();
if (!isset($_SESSION["id"])) {
    header("location: ../login.php");
    exit;
}

include 'config.php';

// Get logged-in user's ID
$user_id = $_SESSION["id"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Process the form submission and update the profile

    // Get the updated values from the form
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $password = !empty($_POST["password"]) ? trim($_POST["password"]) : null;

    // Password validation: minimum length 8 characters, must contain at least one special character
    $password_err = '';
    if ($password) {
        if (strlen($password) < 8) {
            $password_err = "Password must be at least 8 characters long.";
        } elseif (!preg_match("/[A-Za-z]/", $password) || !preg_match("/\d/", $password) || !preg_match("/[\W_]/", $password)) {
            $password_err = "Password must include at least one letter, one number, and one special character.";
        } else {
            // If password is valid, hash it
            $password = password_hash($password, PASSWORD_DEFAULT);
        }
    }

    if (empty($password_err)) {
        // Prepare the SQL query
        $query = "UPDATE users SET username=?, email=?, phone=?";
        $params = [$username, $email, $phone];
        $types = "sss";

        // If password is provided, include it in the update query
        if ($password) {
            $query .= ", password=?";
            $params[] = $password;
            $types .= "s";
        }
        $query .= " WHERE id=?";
        $params[] = $user_id;
        $types .= "i";

        // Prepare and execute the statement
        if ($stmt = mysqli_prepare($conn, $query)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            if (mysqli_stmt_execute($stmt)) {
                $success_msg = "Profile updated successfully!";
            } else {
                $error_msg = "Error updating profile. Please try again.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $error_msg = "Failed to prepare the statement.";
        }
    }
}

// Fetch the user's current profile data
$query = "SELECT * FROM users WHERE id = ?";
if ($stmt = mysqli_prepare($conn, $query)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Update Profile</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <?php include('navbar.php'); ?>

    <div class="container mt-5 mb-5">
    <div class="card mx-auto" style="max-width: 600px;">
    <div class="card-body">
        <div class="form-container">
            <h2>Update Profile</h2>

            <!-- Success/Error Messages -->
            <?php if (isset($success_msg)): ?>
                <div class="alert alert-success"><?php echo $success_msg; ?></div>
            <?php endif; ?>
            <?php if (isset($error_msg)): ?>
                <div class="alert alert-danger"><?php echo $error_msg; ?></div>
            <?php endif; ?>
            <?php if (isset($password_err)): ?>
                <div class="alert alert-danger"><?php echo $password_err; ?></div>
            <?php endif; ?>

            <!-- Profile Update Form -->
            <form action="" method="POST">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($row['username']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone:</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($row['phone']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">New Password (leave blank to keep current password):</label>
                    <input type="password" class="form-control" id="password" name="password">
                    <small class="form-text text-muted">Password must be at least 8 characters long, including letters, numbers, and special characters.</small>
                </div>
                <div class="form-group text-center">
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </div>
            </form>
        </div>
    </div>
    </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
