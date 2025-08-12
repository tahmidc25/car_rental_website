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
    WHERE b.ID = ? AND b.Customer_ID = ?
");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    header("Location: renter-bookings.php");
    exit();
}

// Update booking status to completed
$stmt = $pdo->prepare("UPDATE booking SET Status = 'completed' WHERE ID = ?");
$stmt->execute([$booking_id]);

header("Location: payment.php?booking_id=" . $booking_id);
exit();
?>