<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../frontend/signin.html");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['package_id'])) {
    header("Location: packages.php");
    exit;
}


$package_id = (int)$_POST['package_id'];

$conn = new mysqli("localhost", "root", "", "travel_planner");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get package details
$stmt = $conn->prepare("SELECT package_name, description, duration, price FROM packages WHERE package_id = ?");
$stmt->bind_param("i", $package_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Package not found.");
}

$package = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Confirm Booking | Travel Planner</title>
  <link rel="stylesheet" href="../css/confirmbooking.css">
</head>

<body>
  <header>
    <h1>Confirm Your Booking</h1>
  </header>

  <div class="container">
    <h2><?= htmlspecialchars($package['package_name']); ?></h2>
    <p><?= htmlspecialchars($package['description']); ?></p>
    <p><b>Duration:</b> <?= (int)$package['duration']; ?> days</p>
    <p><b>Price:</b> ₹<?= htmlspecialchars($package['price']); ?></p>

    <div class="actions">
      <form action="bookpackage.php" method="POST" style="display:inline;">
        <input type="hidden" name="package_id" value="<?= $package_id; ?>">
        <input type="submit" value="💳 Pay Now" class="pay-btn">
      </form>

      <a href="packages.php" class="cancel-btn">❌ Cancel</a>
    </div>
  </div>
</body>
</html>
