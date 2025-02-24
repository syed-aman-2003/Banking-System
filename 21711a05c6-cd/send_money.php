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
    $recipient_account_number = trim($_POST['recipient_account_number']);
    $amount = trim($_POST['amount']);

    // Validate inputs
    if (empty($recipient_account_number) || empty($amount) || !is_numeric($amount) || $amount <= 0) {
        $error = 'Please enter a valid recipient account number and amount.';
    } else {
        // Fetch sender's balance
        $stmt_balance = $conn->prepare("SELECT balance FROM users WHERE id = ?");
        if (!$stmt_balance) {
            $error = 'Database error: ' . $conn->error;
        } else {
            $stmt_balance->bind_param("i", $_SESSION['user_id']);
            
            if ($stmt_balance->execute()) {
                $stmt_balance->bind_result($balance);
                $stmt_balance->fetch();
                $stmt_balance->close();

                if ($balance < $amount) {
                    $error = 'Insufficient balance.';
                } else {
                    // Check if recipient exists
                    $stmt_recipient = $conn->prepare("SELECT id FROM users WHERE bank_account_number = ?");
                    if (!$stmt_recipient) {
                        $error = 'Database error: ' . $conn->error;
                    } else {
                        $stmt_recipient->bind_param("s", $recipient_account_number);

                        if ($stmt_recipient->execute()) {
                            $stmt_recipient->store_result();

                            if ($stmt_recipient->num_rows > 0) {
                                $stmt_recipient->bind_result($recipient_id);
                                $stmt_recipient->fetch();
                                $stmt_recipient->close();

                                // Perform the transaction
                                $conn->begin_transaction();
                                try {
                                    // Deduct from sender's balance
                                    $stmt_update_sender = $conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
                                    if (!$stmt_update_sender) {
                                        throw new Exception('Database error: ' . $conn->error);
                                    }
                                    $stmt_update_sender->bind_param("di", $amount, $_SESSION['user_id']);
                                    $stmt_update_sender->execute();
                                    $stmt_update_sender->close();

                                    // Add to recipient's balance
                                    $stmt_update_recipient = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                                    if (!$stmt_update_recipient) {
                                        throw new Exception('Database error: ' . $conn->error);
                                    }
                                    $stmt_update_recipient->bind_param("di", $amount, $recipient_id);
                                    $stmt_update_recipient->execute();
                                    $stmt_update_recipient->close();

                                    // Insert transaction record for sender
                                    $stmt_insert_sender = $conn->prepare("INSERT INTO transactions (user_id, transaction_type, amount, recipient_account_number) VALUES (?, 'debit', ?, ?)");
                                    if (!$stmt_insert_sender) {
                                        throw new Exception('Database error: ' . $conn->error);
                                    }
                                    $stmt_insert_sender->bind_param("ids", $_SESSION['user_id'], $amount, $recipient_account_number);
                                    $stmt_insert_sender->execute();
                                    $stmt_insert_sender->close();

                                    // Insert transaction record for recipient
                                    $stmt_insert_recipient = $conn->prepare("INSERT INTO transactions (user_id, transaction_type, amount, recipient_account_number) VALUES (?, 'credit', ?, ?)");
                                    if (!$stmt_insert_recipient) {
                                        throw new Exception('Database error: ' . $conn->error);
                                    }
                                    $stmt_insert_recipient->bind_param("ids", $recipient_id, $amount, $_SESSION['user_id']);
                                    $stmt_insert_recipient->execute();
                                    $stmt_insert_recipient->close();

                                    $conn->commit();
                                    $success = 'Transaction successful!';
                                } catch (Exception $e) {
                                    $conn->rollback();
                                    $error = 'Transaction failed: ' . $e->getMessage();
                                }
                            } else {
                                $error = 'Recipient account number not found.';
                            }
                        } else {
                            $error = 'Error checking recipient account.';
                        }
                    }
                }
            } else {
                $error = 'Error fetching sender balance.';
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
    <title>Send Money - Core Banking</title>
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
            display: block;
            margin-bottom: 5px;
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
        <h2>Send Money</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form action="send_money.php" method="post">
            <div class="form-group">
                <label for="recipient_account_number">Recipient Account Number</label>
                <input type="text" name="recipient_account_number" id="recipient_account_number" required>
            </div>
            <div class="form-group">
                <label for="amount">Amount</label>
                <input type="text" name="amount" id="amount" required>
            </div>
            <button type="submit">Send</button>
        </form>
        <p><a href="index.php">Back to Home</a></p>
    </div>
</body>
</html>
