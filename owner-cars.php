<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'owner') {
    header("Location: login.php");
    exit();
}

// Get owner's cars with their current status and location
$stmt = $pdo->prepare("
    SELECT 
        c.*,
        b.Status AS booking_status,
        u.Name AS renter_name,
        b.ID AS booking_id
    FROM 
        car c
    LEFT JOIN (
        SELECT 
            b1.* 
        FROM 
            booking b1
        WHERE 
            b1.Status IN ('pending', 'confirmed')
        AND 
            b1.ID = (
                SELECT 
                    MAX(b2.ID) 
                FROM 
                    booking b2 
                WHERE 
                    b2.Car_ID = b1.Car_ID
            )
    ) b ON c.Number_Plate = b.Car_ID
    LEFT JOIN 
        user u ON b.Customer_ID = u.ID
    WHERE 
        c.Owner_ID = ?
    ORDER BY 
        c.Number_Plate
");
$stmt->execute([$_SESSION['user_id']]);
$cars = $stmt->fetchAll();

// Handle booking confirmation/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'];
    $action = $_POST['action'];
    
    try {
        $new_status = ($action === 'confirm') ? 'confirmed' : 'canceled';
        $stmt = $pdo->prepare("UPDATE booking SET Status = ? WHERE ID = ?");
        $stmt->execute([$new_status, $booking_id]);
        
        if ($stmt->rowCount() > 0) {
            $success = "Booking has been " . $new_status . "!";
            // Refresh the page to show updated status
            header("Location: owner-cars.php");
            exit();
        } else {
            $error = "Failed to update booking status.";
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Cars - Car Rental System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>My Cars</h2>
        
        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <table>
            <tr>
                <th>Number Plate</th>
                <th>Model</th>
                <th>Brand</th>
                <th>Status</th>
                <th>Rented By</th>
                <th>Current Location</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($cars as $car): ?>
                <tr>
                    <td><?php echo htmlspecialchars($car['Number_Plate']); ?></td>
                    <td><?php echo htmlspecialchars($car['Model']); ?></td>
                    <td><?php echo htmlspecialchars($car['Brand']); ?></td>
                    <td class="status-<?php echo strtolower($car['booking_status'] ?? 'available'); ?>">
                        <?php echo $car['booking_status'] ?? 'Available'; ?>
                    </td>
                    <td><?php echo htmlspecialchars($car['renter_name'] ?? '-'); ?></td>
                    <td>
                        <?php if (!empty($car['current_location'])): ?>
                            <?php echo htmlspecialchars($car['current_location']); ?>
                        <?php elseif (!empty($car['booking_status'])): ?>
                            <span class="location-unknown">Location not updated</span>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($car['booking_status']) && $car['booking_status'] === 'pending'): ?>
                            <form method="POST" style="display: inline-block;">
                                <input type="hidden" name="booking_id" value="<?php echo $car['booking_id']; ?>">
                                <input type="hidden" name="action" value="confirm">
                                <button type="submit" class="btn-confirm">Confirm</button>
                            </form>
                            <form method="POST" style="display: inline-block;">
                                <input type="hidden" name="booking_id" value="<?php echo $car['booking_id']; ?>">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="btn-reject">Reject</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        
        <a href="dashboard.php" class="back">Back to Dashboard</a>
    </div>
</body>
</html>