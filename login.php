<?php
require_once 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM user WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['ID'];
            $_SESSION['username'] = $user['username'];
            
            // Check if user is owner or customer
            $stmt = $pdo->prepare("SELECT * FROM owner WHERE ID = ?");
            $stmt->execute([$user['ID']]);
            if ($stmt->fetch()) {
                $_SESSION['user_type'] = 'owner';
            } else {
                $_SESSION['user_type'] = 'renter';
            }
            
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid username or password";
        }
    } catch (PDOException $e) {
        $error = "Login failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Car Rental System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>