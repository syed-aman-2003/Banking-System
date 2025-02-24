<?php
session_start();
require 'config.php';

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = trim($_POST['amount']);

    // Validate input
    if (empty($amount) || !is_numeric($amount) || $amount <= 0) {
        $error = 'Please enter a valid amount.';
    } else {
        // Update user's balance
        $stmt_update_balance = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        if (!$stmt_update_balance) {
            $error = 'Database error: ' . $conn->error;
        } else {
            $stmt_update_balance->bind_param("di", $amount, $_SESSION['user_id']);

            if ($stmt_update_balance->execute()) {
                $stmt_update_balance->close();

                // Insert transaction record
                $stmt_insert_transaction = $conn->prepare("INSERT INTO transactions (user_id, transaction_type, amount) VALUES (?, 'credit', ?)");
                if (!$stmt_insert_transaction) {
                    $error = 'Database error: ' . $conn->error;
                } else {
                    $stmt_insert_transaction->bind_param("id", $_SESSION['user_id'], $amount);
                    
                    if ($stmt_insert_transaction->execute()) {
                        $stmt_insert_transaction->close();
                        $success = 'Deposit successful!';
                    } else {
                        $error = 'Error inserting transaction record.';
                    }
                }
            } else {
                $error = 'Error updating balance.';
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Deposit Money - Core Banking</title>
<style>
       body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0; /* Light gray background */
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 500px;
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
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .alert-danger {
            color: #721c24; /* Dark red */
            background-color: #f8d7da; /* Light red background */
            border: 1px solid #f5c6cb;
        }
        .alert-success {
            color: #155724; /* Dark green */
            background-color: #d4edda; /* Light green background */
            border: 1px solid #c3e6cb;
        }
        form {
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            font-weight: bold;
        }
        input[type="text"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #ff7f50; /* Orange */
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #e56347; /* Darker shade of orange */
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
        <h2>Deposit Money</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form action="deposit_money.php" method="post">
            <div class="form-group">
                <label for="amount">Amount</label>
                <input type="text" name="amount" id="amount" required>
            </div>
            <button type="submit">Deposit</button>
        </form>
        <p><a href="index.php">Back to Home</a></p>
    </div>
</body>
</html>
