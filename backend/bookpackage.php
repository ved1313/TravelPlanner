<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("Please log in to book a package.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['package_id'])) {
    echo "<h3>Please select a travel package from the list first.</h3>";
    echo "<a href='packages.php'>View Packages</a>";
    exit;
}

$package_id = (int)$_POST['package_id'];
$user_id = $_SESSION['user_id'];

$conn = new mysqli("localhost", "root", "", "travel_planner");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// Fetch package price
$stmt = $conn->prepare("SELECT price FROM packages WHERE package_id = ?");
$stmt->bind_param("i", $package_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Error: Package not found.");
}
$package = $result->fetch_assoc();
$amount = $package['price'];

// Create booking
$book = $conn->prepare("INSERT INTO bookings (booking_date, amount, user_id, package_id)
                        VALUES (CURDATE(), ?, ?, ?)");
$book->bind_param("dii", $amount, $user_id, $package_id);
$book->execute();
$booking_id = $book->insert_id;

// Create pending payment
$pay = $conn->prepare("INSERT INTO payments (payment_method, amount, payment_status, booking_id, user_id)
                       VALUES ('Not Selected', ?, 'Pending', ?, ?)");
$pay->bind_param("dii", $amount, $booking_id, $user_id);
$pay->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Booking Confirmed</title>
  <style>
    body { font-family:Poppins; background:linear-gradient(to right,#74ebd5,#ACB6E5); text-align:center; margin-top:100px; }
    .card { background:white; width:400px; margin:auto; padding:30px; border-radius:15px; box-shadow:0 5px 15px rgba(0,0,0,0.2); }
    a { display:inline-block; margin-top:15px; background:#27ae60; color:white; padding:10px 20px; border-radius:8px; text-decoration:none; }
    a:hover { background:#1e8449; }
  </style>
</head>
<body>
  <div class="card">
    <h2>🎉 Booking Confirmed!</h2>
    <p>Your booking has been successfully created.</p>
    <p><b>Booking ID:</b> <?= $booking_id ?></p>
    <p><b>Amount:</b> ₹<?= $amount ?></p>
    <p><b>Status:</b> Pending Payment</p>
    <a href="packages.php">Book Another</a>
  </div>
</body>
</html>
