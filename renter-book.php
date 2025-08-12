<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'renter') {
    header("Location: login.php");
    exit();
}

// Get available cars
$stmt = $pdo->prepare("
    SELECT c.*, cl.Location 
    FROM car c 
    LEFT JOIN car_location cl ON c.Number_Plate = cl.Number_Plate 
    WHERE c.Number_Plate NOT IN (
        SELECT Car_ID FROM booking WHERE Status IN ('pending', 'confirmed')
    )
");
$stmt->execute();
$cars = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $car_id = $_POST['car_id'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO booking (Customer_ID, Car_ID, Status) VALUES (?, ?, 'pending')");
        $stmt->execute([$_SESSION['user_id'], $car_id]);
        
        $success = "Car booked successfully! The owner will confirm your booking.";
    } catch (PDOException $e) {
        $error = "Failed to book car: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book a Car - Car Rental System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Book a Car</h2>
        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <h3>Available Cars</h3>
        <?php if (empty($cars)): ?>
            <p>No cars available at the moment.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>Number Plate</th>
                    <th>Model</th>
                    <th>Brand</th>
                    <th>Location</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($cars as $car): ?>
                    <tr>
                        <td><?php echo $car['Number_Plate']; ?></td>
                        <td><?php echo $car['Model']; ?></td>
                        <td><?php echo $car['Brand']; ?></td>
                        <td><?php echo $car['Location']; ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="car_id" value="<?php echo $car['Number_Plate']; ?>">
                                <button type="submit">Book Now</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
        
        <a href="dashboard.php" class="back">Back to Dashboard</a>
    </div>
</body>
</html>