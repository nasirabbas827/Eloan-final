<?php
session_start();
include('config.php');

// Check if the user is logged in as an admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message_id = $_POST["message_id"];
    $reply_text = $_POST["reply"];

    $sql_update = "UPDATE messages SET reply_text = ?, reply_datetime = CURRENT_TIMESTAMP WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("si", $reply_text, $message_id);
    $stmt_update->execute();
    $stmt_update->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Reply</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
<div class="card mx-auto" style="max-width: 600px;">
<div class="card-body">
    <h2>Admin Reply</h2>
    <?php
    $sql_messages = "SELECT messages.id, messages.message_text, messages.sender_id, users.username FROM messages
                    INNER JOIN users ON messages.sender_id = users.id
                    WHERE messages.reply_text IS NULL";
    
    $result_messages = $conn->query($sql_messages);

    while ($row = $result_messages->fetch_assoc()) {
        echo "<div class='card mb-3'>";
        echo "<div class='card-body'>";
        echo "<h5 class='card-title'>Message from User " . $row['username'] . "</h5>";
        echo "<p class='card-text'>" . $row['message_text'] . "</p>";
        echo "<form method='post'>";
        echo "<input type='hidden' name='message_id' value='" . $row['id'] . "'>";
        echo "<div class='form-group'>";
        echo "<textarea name='reply' class='form-control' rows='3' placeholder='Reply...'></textarea>";
        echo "</div>";
        echo "<button type='submit' class='btn btn-primary mt-2'>Send Reply</button>";
        echo "</form>";
        echo "</div>";
        echo "</div>";
    }
    ?>
</div>
</div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
