<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'owner') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $number_plate = $_POST['number_plate'];
    $model = $_POST['model'];
    $brand = $_POST['brand'];
    $car_type = $_POST['car_type'];
    $hourly_cost = $_POST['hourly_cost'];
    $location = $_POST['location'];
    
    try {
        $pdo->beginTransaction();
        
        // Insert into car table
        $stmt = $pdo->prepare("INSERT INTO car (Number_Plate, Model, Brand, Owner_ID) VALUES (?, ?, ?, ?)");
        $stmt->execute([$number_plate, $model, $brand, $_SESSION['user_id']]);
        
        // Insert into car_location table
        $stmt = $pdo->prepare("INSERT INTO car_location (Number_Plate, Location) VALUES (?, ?)");
        $stmt->execute([$number_plate, $location]);
        
        // Insert into appropriate car type table
        if ($car_type === 'luxury') {
            $stmt = $pdo->prepare("INSERT INTO luxury_car (Number_Plate, Additional_Fee) VALUES (?, ?)");
            $stmt->execute([$number_plate, $hourly_cost]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO normal_car (Number_Plate, SUV, Van, Pickup, Sedan) VALUES (?, ?, ?, ?, ?)");
            $suv = ($car_type === 'suv') ? 1 : 0;
            $van = ($car_type === 'van') ? 1 : 0;
            $pickup = ($car_type === 'pickup') ? 1 : 0;
            $sedan = ($car_type === 'sedan') ? 1 : 0;
            $stmt->execute([$number_plate, $suv, $van, $pickup, $sedan]);
        }
        
        $pdo->commit();
        $success = "Car posted successfully!";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Failed to post car: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Post a Car - Car Rental System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Post Your Car for Rent</h2>
        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Number Plate:</label>
                <input type="text" name="number_plate" required>
            </div>
            <div class="form-group">
                <label>Model:</label>
                <input type="text" name="model" required>
            </div>
            <div class="form-group">
                <label>Brand:</label>
                <input type="text" name="brand" required>
            </div>
            <div class="form-group">
                <label>Car Type:</label>
                <select name="car_type" required>
                    <option value="luxury">Luxury Car</option>
                    <option value="suv">SUV</option>
                    <option value="van">Van</option>
                    <option value="pickup">Pickup</option>
                    <option value="sedan">Sedan</option>
                </select>
            </div>
            <div class="form-group">
                <label>Hourly Cost (BDT):</label>
                <input type="number" name="hourly_cost" step="0.01" required>
            </div>
            <div class="form-group">
                <label>Location:</label>
                <input type="text" name="location" required>
            </div>
            <button type="submit">Post Car</button>
        </form>
        <a href="dashboard.php" class="back">Back to Dashboard</a>
    </div>
</body>
</html>
