<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Fetch user
    $stmt = $conn->prepare("SELECT id, role, password FROM users WHERE username=? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {

        $stmt->bind_result($id, $role, $dbPass);
        $stmt->fetch();

        // ✔ Secure password verify (supports old SHA256 + new HASH)
        if (password_verify($password, $dbPass) || hash('sha256', $password) == $dbPass) {

            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;

            if ($role == 'admin') {
                header("Location: admin/index.php");
            } else {
                header("Location: user/index.php");
            }
            exit;

        } else {
            $error = "Incorrect Password";
        }

    } else {
        $error = "User not found";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Login</title>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: "Poppins", sans-serif;
    }

    body {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        background: linear-gradient(135deg, #0d0d0d, #1a1a1a);
        color: #fff;
    }

    h2 {
        position: absolute;
        top: 40px;
        font-size: 32px;
        color: #00d4ff;
    }

    .login-box {
        background: #1b1b1b;
        padding: 40px 35px;
        width: 360px;
        border-radius: 14px;
        box-shadow: 0 0 30px rgba(0, 212, 255, 0.2);
        border: 1px solid #2d2d2d;
        text-align: center;
    }

    input {
        width: 100%;
        padding: 12px;
        margin: 15px 0;
        border: 1px solid #333;
        background: #111;
        border-radius: 8px;
        font-size: 16px;
        color: #fff;
    }

    input:focus {
        border-color: #00d4ff;
        box-shadow: 0 0 8px #00d4ff;
    }

    button {
        width: 100%;
        padding: 12px;
        background: #00d4ff;
        border: none;
        font-size: 18px;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        margin-top: 8px;
    }

    button:hover { background: #00a9cc; }

    .error {
        margin-top: 10px;
        color: #ff4c4c;
        font-weight: 600;
    }

    /* Register Button */
    .register-btn {
        margin-top: 15px;
        display: inline-block;
        color: #00d4ff;
        font-weight: 600;
        text-decoration: none;
        transition: 0.3s;
    }

    .register-btn:hover {
        color: #00a9cc;
        text-decoration: underline;
    }
</style>

</head>
<body>

<h2>Login</h2>

<div class="login-box">
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>

        <input type="password" name="password" placeholder="Password" required>

        <button type="submit">Login</button>
    </form>

    <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>

    <!-- Register Link -->
    <a class="register-btn" href="register.php">Create New Account</a>
</div>

</body>
</html>
