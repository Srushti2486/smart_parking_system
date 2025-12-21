<?php
session_start();
require_once "config.php"; // change path if inside user folder

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm = trim($_POST['confirm']);

    if ($password !== $confirm) {
        $error = "Passwords do not match!";
    } else {

        // Check if username already exists
        $check = $conn->prepare("SELECT id FROM users WHERE username=? LIMIT 1");
        $check->bind_param("s", $username);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Username already taken!";
        } else {
            // Insert new user
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");
            $stmt->bind_param("ss", $username, $hash);

            if ($stmt->execute()) {
                $success = "Registration successful! Please login.";
            } else {
                $error = "Registration failed!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>User Registration | Smart Parking</title>
<style>
body {
    font-family: Arial;
    background: #0d0d14;
    color: #fff;
}
.container {
    width: 400px;
    margin: 80px auto;
    background: #15151f;
    padding: 25px;
    border-radius: 10px;
}
input {
    width: 100%;
    padding: 12px;
    margin-top: 10px;
    border-radius: 8px;
    border: 1px solid #444;
    background: #1f1f2b;
    color: #fff;
}
button {
    margin-top: 15px;
    padding: 12px;
    width: 100%;
    background: #ffd24d;
    border: none;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
}
a { color: #ffd24d; }
.msg { margin-top: 10px; padding: 10px; border-radius: 6px; }
.error { background: #ff5252; }
.success { background: #4caf50; }
</style>
</head>
<body>

<div class="container">
    <h2>Register New User</h2>

    <?php if (!empty($error)) echo "<div class='msg error'>$error</div>"; ?>
    <?php if (!empty($success)) echo "<div class='msg success'>$success</div>"; ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Enter Username" required>
        <input type="password" name="password" placeholder="Enter Password" required>
        <input type="password" name="confirm" placeholder="Confirm Password" required>
        <button type="submit">Register</button>
    </form>

    <p style="margin-top:10px;">Already have an account?  
       <a href="login.php">Login Here</a></p>
</div>

</body>
</html>
