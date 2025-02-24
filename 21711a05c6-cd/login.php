<?php
session_start();
require 'config.php';

// Redirect to index page if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validate inputs
    if (empty($username) || empty($password)) {
        $error = 'Username and password are required.';
    } else {
        // Check if the user exists
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id, $hashed_password);
            $stmt->fetch();

            // Verify password
            if (verifyPassword($password, $hashed_password)) {
                // Set session variables
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                $_SESSION['login_time'] = time();

                // Set cookies with 1-hour expiration
                setcookie('user_id', $user_id, time() + 3600, "/");
                setcookie('username', $username, time() + 3600, "/");

                // Redirect to the index page
                header('Location: index.php');
                exit;
            } else {
                $error = 'Invalid password.';
            }
        } else {
            $error = 'User not found.';
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
    <title>Login - Core Banking</title>

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

.form-group {
    margin-bottom: 15px;
}

label {
    font-weight: bold;
}

input[type="text"],
input[type="password"] {
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
    <link rel="stylesheet" href="styles.css"> <!-- Link to your CSS file -->
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="login.php" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register</a></p>
    </div>
</body>
</html>
