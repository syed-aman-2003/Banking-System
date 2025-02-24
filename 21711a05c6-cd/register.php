<?php
session_start();
require 'config.php';

// Redirect to index page if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);

    // Validate inputs
    if (empty($username) || empty($password) || empty($email)) {
        $error = 'All fields are required.';
    } else {
        // Check if the username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'Username or email already exists.';
        } else {
            // Generate a random bank account number
            $bank_account_number = generateBankAccountNumber();
            // Hash the password
            $hashed_password = hashPassword($password);

            // Insert new user into the database
            $stmt = $conn->prepare("INSERT INTO users (username, password, email, bank_account_number) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $hashed_password, $email, $bank_account_number);

            if ($stmt->execute()) {
                $success = 'Account created successfully. You can now <a href="login.php">log in</a>.';
            } else {
                $error = 'Error creating account. Please try again.';
            }
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Core Banking</title>
 <style>
    /* styles.css */

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

.alert {
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #d9534f; /* Red border */
    border-radius: 4px;
    background-color: #f2dede; /* Light red background */
    color: #a94442; /* Dark red text */
}

.alert-success {
    border-color: #5cb85c; /* Green border for success message */
    background-color: #dff0d8; /* Light green background */
    color: #3c763d; /* Dark green text */
}

.form-group {
    margin-bottom: 15px;
}

label {
    font-weight: bold;
}

input[type="text"],
input[type="password"],
input[type="email"] {
    width: 100%;
    padding: 8px;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.btn {
    display: block;
    width: 100%;
    padding: 10px;
    font-size: 16px;
    text-align: center;
    border: none;
    border-radius: 4px;
    background-color: #ff7f50; /* Orange */
    color: #fff; /* White text */
    cursor: pointer;
}

.btn:hover {
    background-color: #ff6347; /* Darker orange on hover */
}

p {
    text-align: center;
    margin-top: 15px;
    font-size: 16px;
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
        <h2>Register</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form action="register.php" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Log in</a></p>
    </div>
</body>
</html>
