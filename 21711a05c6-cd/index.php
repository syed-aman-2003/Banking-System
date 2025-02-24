<?php
session_start();
require 'config.php';

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$username = '';
$balance = 0;
$bank_account_number = ''; // Initialize bank_account_number variable

// Fetch user details including balance and bank_account_number
$stmt_user = $conn->prepare("SELECT username, balance, bank_account_number FROM users WHERE id = ?");
if ($stmt_user) {
    $stmt_user->bind_param("i", $_SESSION['user_id']);
    $stmt_user->execute();
    $stmt_user->bind_result($username, $balance, $bank_account_number);
    $stmt_user->fetch();
    $stmt_user->close();
} else {
    $error = 'Database error: ' . $conn->error;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome - Core Banking</title>
<style>
       body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0; /* Light gray background */
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
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
        .alert {
            color: #721c24; /* Dark red for alerts */
            background-color: #f8d7da; /* Light red background */
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
        }
        p {
            margin-bottom: 10px;
        }
        a {
            color: #007bff; /* Blue links */
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
</style>
</head>
<body>
    <div class="container">
        <h2>Welcome, <?php echo $username; ?>!</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <p>Your bank account number: <?php echo htmlspecialchars($bank_account_number); ?></p>
        <p>Your current balance: ₹<?php echo number_format($balance, 2); ?></p>
        
        <p><a href="deposit_money.php">Deposit Money</a></p>
        <p><a href="send_money.php">Send Money</a></p>
        <p><a href="transactions.php">View Transactions</a></p>
        <p><a href="check_balance.php">Check Balance</a></p>
        <p><a href="logout.php">Logout</a></p>
    </div>
</body>
</html>