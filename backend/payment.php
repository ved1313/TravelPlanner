<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../frontend/signin.html");
    exit;
}

$conn = new mysqli("localhost", "root", "", "travel_planner");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$user_id = $_SESSION['user_id'];

if (isset($_POST['booking_id'])) {

    $booking_id = (int)$_POST['booking_id'];

    $stmt = $conn->prepare("
        SELECT b.package_id, p.package_name, p.price
        FROM bookings b
        JOIN packages p ON b.package_id = p.package_id
        WHERE b.booking_id = ?
    ");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();

    $package_id = $data['package_id'];
    $package_name = $data['package_name'];
    $price = $data['price'];

} else if (isset($_POST['package_id'])) {

    $package_id = (int)$_POST['package_id'];

    $stmt = $conn->prepare("SELECT package_name, price FROM packages WHERE package_id = ?");
    $stmt->bind_param("i", $package_id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();

    $package_name = $data['package_name'];
    $price = $data['price'];

} else {
    die("Invalid request.");
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Payment</title>
<link rel="stylesheet" href="../css/payment.css">
</head>

<body>
<header>
  <h1>Complete Your Payment</h1>
</header>

<div class="container">

  <h2><?= htmlspecialchars($package_name) ?></h2>
  <p><b>Amount:</b> ₹<?= $price ?></p>

  <!-- PAY NOW -->
  <form action="bookpackage.php" method="POST" enctype="multipart/form-data">
      <?php if (isset($booking_id)): ?>
        <input type="hidden" name="booking_id" value="<?= $booking_id ?>">
      <?php endif; ?>

      <input type="hidden" name="package_id" value="<?= $package_id ?>">
      <input type="hidden" name="action" value="pay_now">

      <label>Upload Payment Proof:</label><br>
      <input type="file" name="payment_image" accept="image/*" required><br><br>

      <button class="pay-btn">Pay Now</button>
  </form>

  <hr><br>

  <!-- PAY LATER -->
  <form action="bookpackage.php" method="POST">
      <?php if (isset($booking_id)): ?>
          <input type="hidden" name="booking_id" value="<?= $booking_id ?>">
      <?php endif; ?>

      <input type="hidden" name="package_id" value="<?= $package_id ?>">
      <input type="hidden" name="action" value="pay_later">

      <button class="pay-later-btn">Pay Later</button>
  </form>

  <a href="mybookings.php" class="cancel-btn">Cancel</a>

</div>

</body>
</html>
