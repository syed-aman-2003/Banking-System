<?php
session_start();
require 'config.php';

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user transactions
$stmt = $conn->prepare("SELECT id, transaction_type, amount, transaction_date, recipient_account_number FROM transactions WHERE user_id = ? ORDER BY transaction_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($transaction_id, $transaction_type, $amount, $transaction_date, $recipient_account_number);

$transactions = [];
while ($stmt->fetch()) {
    $transactions[] = [
        'id' => $transaction_id,
        'transaction_type' => $transaction_type,
        'amount' => $amount,
        'transaction_date' => $transaction_date,
        'recipient_account_number' => $recipient_account_number
    ];
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Previous Transactions - Core Banking</title>
<style>
    body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0; /* Light gray background */
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #ff7f50; /* Orange */
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2; /* Light gray */
        }
        tr:hover {
            background-color: #e0e0e0; /* Darker gray on hover */
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
       
    <h2>Previous Transactions</h2>
        <table border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Transaction Type</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Recipient Account</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($transaction['id']); ?></td>
                        <td><?php echo htmlspecialchars($transaction['transaction_type']); ?></td>
                        <td><?php echo htmlspecialchars($transaction['amount']); ?></td>
                        <td><?php echo htmlspecialchars($transaction['transaction_date']); ?></td>
                        <td><?php echo htmlspecialchars($transaction['recipient_account_number']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p><a href="index.php">Back to Home</a></p>
    </div>
</body>
</html>
