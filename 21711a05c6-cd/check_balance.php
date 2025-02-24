<?php
session_start();
require 'config.php';

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user's balance
$stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($balance);
$stmt->fetch();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Check Balance - Core Banking</title>
<style>
      body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0; /* Light gray background */
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 400px;
            margin: 50px auto;
            background-color: #fff; /* White background */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #ff7f50; /* Orange */
            text-align: center;
            margin-bottom: 20px;
        }
        p {
            font-size: 18px;
            text-align: center;
            margin-bottom: 20px;
        }
        a {
            display: block;
            text-align: center;
            color: #007bff; /* Blue links */
            text-decoration: none;
            margin-top: 20px;
        }
        a:hover {
            text-decoration: underline;
        }
</style>
</head>
<body>
    <div class="container">
        <h2>Check Balance</h2>
        <p>Your current balance is: <?php echo htmlspecialchars($balance); ?></p>
        <p><a href="index.php">Back to Home</a></p>
    </div>
</body>
</html>
