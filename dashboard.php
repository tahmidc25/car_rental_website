<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Car Rental System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Welcome, <?php echo $_SESSION['username']; ?>!</h2>
        <p>You are logged in as <?php echo $_SESSION['user_type']; ?></p>
        
        <?php if ($_SESSION['user_type'] === 'owner'): ?>
            <div class="dashboard-options">
                <a href="owner-post.php" class="btn">Post a Car</a>
                <a href="owner-cars.php" class="btn">View My Cars</a>
            </div>
        <?php else: ?>
            <div class="dashboard-options">
                <a href="renter-book.php" class="btn">Book a Car</a>
                <a href="renter-bookings.php" class="btn">My Bookings</a>
            </div>
        <?php endif; ?>
        
        <a href="logout.php" class="logout">Logout</a>
    </div>
</body>
</html>