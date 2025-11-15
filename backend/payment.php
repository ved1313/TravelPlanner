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

$stmt = $conn->prepare("SELECT package_name, price FROM packages WHERE package_id = ?");
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
  <title>Payment | Travel Planner</title>
  <link rel="stylesheet" href="../css/payment.css">
</head>
<body>
  <header>
    <h1>Complete Your Payment</h1>
  </header>

  <div class="container">
    <h2><?= htmlspecialchars($package['package_name']); ?></h2>
    <p><b>Total Amount:</b> ₹<?= htmlspecialchars($package['price']); ?></p>

    <form action="bookpackage.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="package_id" value="<?= $package_id; ?>">

      <label for="payment_image"><b>Upload Payment Proof:</b></label><br>
      <input type="file" name="payment_image" id="payment_image" accept="image/*" required><br><br>

      <button type="submit" name="confirm_payment" class="pay-btn">✅ Confirm Payment</button>
      <a href="dashboard.php" class="pay-later">Pay Later</a>
      <a href="packages.php" class="cancel-btn">❌ Cancel</a>
    </form>
  </div>
</body>
</html>
