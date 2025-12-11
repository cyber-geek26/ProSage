<?php
session_start();
require "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Find user
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // Login success
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>ProSage Login</title>
    <link rel="stylesheet" href="assets/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>
<body>

<div class="login-container">
    <h2>ProSage Login</h2>

    <?php if ($error): ?>
        <div class="error-box"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>
    </form>
</div>

</body>
</html>
