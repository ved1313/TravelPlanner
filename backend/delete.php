<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['booking_id'])) {
    die("Invalid request.");
}

$user_id = $_SESSION['user_id'];
$booking_id = (int)$_POST['booking_id'];

$conn = new mysqli("localhost", "root", "", "travel_planner");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// Verify booking belongs to this user
$check = $conn->prepare("SELECT booking_id FROM bookings WHERE booking_id=? AND user_id=?");
$check->bind_param("ii", $booking_id, $user_id);
$check->execute();
$res = $check->get_result();

if ($res->num_rows === 0) {
    die("Error: You cannot delete this booking.");
}

// Delete payment entry first (FK safe)
$deletePay = $conn->prepare("DELETE FROM payments WHERE booking_id=?");
$deletePay->bind_param("i", $booking_id);
$deletePay->execute();

// Delete booking entry
$deleteBook = $conn->prepare("DELETE FROM bookings WHERE booking_id=?");
$deleteBook->bind_param("i", $booking_id);
$deleteBook->execute();

header("Location: mybookings.php?deleted=1");
exit;
?>
