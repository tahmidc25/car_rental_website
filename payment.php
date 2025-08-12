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

// Get booking details
$stmt = $pdo->prepare("
    SELECT b.*, c.Model, c.Brand 
    FROM booking b 
    JOIN car c ON b.Car_ID = c.Number_Plate 
    WHERE b.ID = ? AND b.Customer_ID = ? AND b.Status = 'completed'
");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    header("Location: renter-bookings.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'];
    $rating = $_POST['rating'];
    $comments = $_POST['comments'] ?? '';
    
    try {
        $pdo->beginTransaction();
        
        // Insert payment
        $offline = ($payment_method === 'offline') ? 1 : 0;
        $online = ($payment_method === 'online') ? 1 : 0;
        
        $stmt = $pdo->prepare("INSERT INTO payment (Offline, Online, Booking_ID) VALUES (?, ?, ?)");
        $stmt->execute([$offline, $online, $booking_id]);
        
        // Insert rating
        $stmt = $pdo->prepare("INSERT INTO rating (Rating_val, Comments, Customer_ID) VALUES (?, ?, ?)");
        $stmt->execute([$rating, $comments, $_SESSION['user_id']]);
        
        $pdo->commit();
        $success = "Payment and rating submitted successfully!";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Failed to process payment: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment - Car Rental System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Payment and Rating</h2>
        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <h3>Booking Details</h3>
        <p>Car: <?php echo $booking['Brand'] . ' ' . $booking['Model']; ?></p>
        <p>Number Plate: <?php echo $booking['Car_ID']; ?></p>
        
        <form method="POST">
            <div class="form-group">
                <label>Payment Method:</label>
                <select name="payment_method" required>
                    <option value="online">Online Payment</option>
                    <option value="offline">Cash Payment</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Rate your experience (1-5):</label>
                <input type="number" name="rating" min="1" max="5" required>
            </div>
            
            <div class="form-group">
                <label>Comments (optional):</label>
                <textarea name="comments"></textarea>
            </div>
            
            <button type="submit">Submit Payment and Rating</button>
        </form>
        
        <a href="renter-bookings.php" class="back">Back to My Bookings</a>
    </div>
</body>
</html>