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
    b.package_id,
    p.package_name,
    pay.payment_status
FROM bookings b
JOIN packages p ON b.package_id = p.package_id
LEFT JOIN payments pay ON b.booking_id = pay.booking_id
WHERE b.user_id = ?
ORDER BY b.booking_id DESC
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

    <?php if (isset($_GET['deleted'])): ?>
        <p style="color:red; font-weight:bold;">Booking cancelled successfully.</p>
    <?php endif; ?>

    <?php if ($result->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Booking ID</th>
                <th>Package Name</th>
                <th>Booking Date</th>
                <th>Amount</th>
                <th>Payment</th>
                <th>Cancel</th>
            </tr>
        </thead>

        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['booking_id'] ?></td>
                <td><?= htmlspecialchars($row['package_name']) ?></td>
                <td><?= $row['booking_date'] ?></td>
                <td>₹<?= number_format($row['amount'], 2) ?></td>

                <td>
                <?php if (strtolower($row['payment_status']) === 'pending'): ?>
                    <form action="payment.php" method="POST">
                        <input type="hidden" name="booking_id" value="<?= $row['booking_id'] ?>">
                        <button class="pay-now-btn">Pay Now</button>
                    </form>
                <?php else: ?>
                    <span class="status completed">Completed</span>
                <?php endif; ?>
                </td>

                <td>
                    <form action="delete.php" method="POST" 
                          onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                        <input type="hidden" name="booking_id" value="<?= $row['booking_id'] ?>">
                        <button class="cancel-btn-sm">Cancel</button>
                    </form>
                </td>

            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <?php else: ?>
        <div class="no-bookings">
            <p>No bookings yet.</p>
            <a href="packages.php">Book a Package</a>
        </div>
    <?php endif; ?>

    <div class="btn-group">
        <a href="dashboard.php">Back to Dashboard</a>
        <a href="packages.php">Book Another Package</a>
    </div>
</div>
</body>
</html>
