<?php
require_once 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $user_type = $_POST['user_type'];

    try {
        // Insert into user table
        $stmt = $pdo->prepare("INSERT INTO user (Name, Email, Contact_No, username, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $contact, $username, $password]);
        $user_id = $pdo->lastInsertId();

        // Insert into owner or customer table
        if ($user_type === 'owner') {
            $stmt = $pdo->prepare("INSERT INTO owner (ID) VALUES (?)");
        } else {
            $stmt = $pdo->prepare("INSERT INTO customer (ID, Discount) VALUES (?, 0.00)");
        }
        $stmt->execute([$user_id]);

        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_type'] = $user_type;
        $_SESSION['username'] = $username;
        
        header("Location: dashboard.php");
        exit();
    } catch (PDOException $e) {
        $error = "Registration failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Car Rental System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Name:</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Contact No:</label>
                <input type="text" name="contact" required>
            </div>
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Register as:</label>
                <select name="user_type" required>
                    <option value="owner">Car Owner</option>
                    <option value="renter">Renter</option>
                </select>
            </div>
            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>