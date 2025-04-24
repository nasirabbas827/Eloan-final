<?php
session_start();
include('config.php');

// Check if user is logged in, if not, redirect to login page
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sender_id = $_SESSION["id"];
    $message_text = $_POST["message"];

    $sql_insert = "INSERT INTO messages (sender_id, message_text) VALUES (?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("is", $sender_id, $message_text);
    $stmt_insert->execute();
    $stmt_insert->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Contact Support</title>
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
    <h2 class="text-center">Contact Support</h2>
    <form method="post">
        <div class="form-group">
            <label for="message">Write your message:</label>
            <textarea name="message" class="form-control" rows="5" placeholder="Write your message..." required></textarea>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Send Message</button>
    </form>
</div>
</div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

