<?php
session_start();
include('config.php');

// Check if user is logged in, if not, redirect to login page
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: login.php");
    exit;
}

// Delete a message
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $message_id = $_GET['delete'];

    $sql_delete = "DELETE FROM messages WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $message_id);
    $stmt_delete->execute();
    $stmt_delete->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Messages</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>

<?php
include('navbar.php');
?>

<div class="container mt-5">
<div class="card mx-auto" style="max-width: 600px;">
<div class="card-body">
    <h2 class="text-center">Your Messages</h2>
    <?php
    $user_id = $_SESSION["id"];
    $sql_user_messages = "SELECT * FROM messages WHERE sender_id = ? OR receiver_id = ?";
    $stmt_user_messages = $conn->prepare($sql_user_messages);
    $stmt_user_messages->bind_param("ii", $user_id, $user_id);
    $stmt_user_messages->execute();
    $result_user_messages = $stmt_user_messages->get_result();

    while ($row = $result_user_messages->fetch_assoc()) {
        echo '<div class="card mb-3">';
        echo '<div class="card-body">';
        echo '<h5 class="card-title">Message</h5>';
        echo '<p class="card-text">' . $row['message_text'] . '</p>';
        if ($row['reply_text']) {
            echo '<h6 class="card-subtitle mb-2 text-muted">Admin\'s Reply</h6>';
            echo '<p class="card-text">' . $row['reply_text'] . '</p>';
        }
        echo '<a href="view_messages.php?delete=' . $row['id'] . '" class="btn btn-danger">Delete Message</a>';
        echo '</div>';
        echo '</div>';
    }
    $stmt_user_messages->close();
    ?>
</div>
</div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
