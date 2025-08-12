<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'renter') {
    header("Location: login.php");
    exit();
}

// Get renter's bookings
$stmt = $pdo->prepare("
    SELECT b.*, c.Model, c.Brand, cl.Location 
    FROM booking b 
    JOIN car c ON b.Car_ID = c.Number_Plate 
    LEFT JOIN car_location cl ON c.Number_Plate = cl.Number_Plate 
    WHERE b.Customer_ID = ?
");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Bookings - Car Rental System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>My Bookings</h2>
        
        <?php if (empty($bookings)): ?>
            <p>You haven't made any bookings yet.</p>
        <?php else: ?>
            <table>
            <td class="status-<?php echo strtolower($booking['Status']); ?>">
    <?php echo $booking['Status']; ?>
</td>
                <tr>
                    <th>Booking ID</th>
                    <th>Car</th>
                    <th>Model</th>
                    <th>Brand</th>
                    <th>Location</th>
                    <th>Booking Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?php echo $booking['ID']; ?></td>
                        <td><?php echo $booking['Car_ID']; ?></td>
                        <td><?php echo $booking['Model']; ?></td>
                        <td><?php echo $booking['Brand']; ?></td>
                        <td><?php echo $booking['Location']; ?></td>
                        <td><?php echo $booking['Booking_Date']; ?></td>
                        <td><?php echo $booking['Status']; ?></td>
                        <td>
                            <?php if ($booking['Status'] === 'confirmed'): ?>
                                <a href="complete-ride.php?booking_id=<?php echo $booking['ID']; ?>" class="btn">Complete Ride</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
        
        <a href="dashboard.php" class="back">Back to Dashboard</a>
    </div>
</body>
</html>