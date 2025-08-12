<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'renter') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['booking_id'])) {
    header("Location: renter-bookings.php");
    exit();
}

$booking_id = $_GET['booking_id'];

// Get payment details
$stmt = $pdo->prepare("
    SELECT p.*, c.Model, c.Brand
    FROM payment p
    JOIN booking b ON p.Booking_ID = b.ID
    JOIN car c ON b.Car_ID = c.Number_Plate
    WHERE p.Booking_ID = ?
");
$stmt->execute([$booking_id]);
$payment = $stmt->fetch();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Successful - Car Rental System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Payment Successful</h2>
        
        <div class="receipt">
            <h3>Rental Receipt</h3>
            <p><strong>Car:</strong> <?php echo htmlspecialchars($payment['Brand'] . ' ' . $payment['Model']); ?></p>
            <p><strong>Booking ID:</strong> <?php echo htmlspecialchars($booking_id); ?></p>
            <p><strong>Payment Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($payment['Payment_Date'])); ?></p>
            <p><strong>Payment Method:</strong> <?php echo ucfirst(str_replace('_', ' ', $payment['Payment_Method'])); ?></p>
            <hr>
            <p><strong>Duration:</strong> <?php echo number_format($payment['Hours'], 2); ?> hours</p>
            <p><strong>Hourly Rate:</strong> $<?php echo number_format($payment['Hourly_Rate'], 2); ?></p>
            <p><strong>Total Amount:</strong> $<?php echo number_format($payment['Amount'], 2); ?></p>
        </div>
        
        <a href="renter-bookings.php" class="btn">Back to My Bookings</a>
    </div>
</body>
</html>