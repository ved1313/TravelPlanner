<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../frontend/signin.html");
    exit;
}

$user_id = $_SESSION['user_id'];

$conn = new mysqli("localhost", "root", "", "travel_planner");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "
SELECT 
    b.booking_id,
    b.booking_date,
    b.amount,
    p.package_name,
    pay.payment_status
FROM bookings b
JOIN packages p ON b.package_id = p.package_id
LEFT JOIN payments pay ON b.booking_id = pay.booking_id
WHERE b.user_id = ?
ORDER BY b.booking_date DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Bookings | Travel Planner</title>
  <link rel="stylesheet" href="../css/mybookings.css">
</head>

<body>
  <header>
    <div>📄 My Bookings</div>
  </header>

  <div class="container">
    <h2>Welcome back, <?= htmlspecialchars($_SESSION['fname']); ?>!</h2>
    <p>Here are your booked travel packages.</p>

    <?php if ($result->num_rows > 0): ?>
      <table>
        <thead>
          <tr>
            <th>Booking ID</th>
            <th>Package Name</th>
            <th>Booking Date</th>
            <th>Amount (₹)</th>
            <th>Payment Status</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $row['booking_id'] ?></td>
              <td><?= htmlspecialchars($row['package_name']) ?></td>
              <td><?= $row['booking_date'] ?></td>
              <td><?= number_format($row['amount'], 2) ?></td>
              <td>
                <span class="status <?= strtolower($row['payment_status']) ?>">
                  <?= htmlspecialchars($row['payment_status']) ?>
                </span>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <div class="no-bookings">
        <p>😕 You haven’t booked any packages yet.</p>
        <a href="packages.php">Book Your First Trip ✈️</a>
      </div>
    <?php endif; ?>

    <div class="btn-group">
      <a href="dashboard.php">🏠 Back to Dashboard</a>
      <a href="packages.php">➕ Book Another Package</a>
    </div>
  </div>
</body>
</html>
