<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

$isLoggedIn = isset($_SESSION['user_id']);
$fname = $isLoggedIn ? htmlspecialchars($_SESSION['fname']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Travel Planner Dashboard</title>
  <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
  <header>
    <div>✈️ Travel Planner Dashboard</div>
  </header>

  <div class="auth-buttons">
    <?php if ($isLoggedIn): ?>
      <span>Welcome, <b><?= $fname ?></b></span>
      <button class="logout" onclick="location.href='../backend/logout.php'">Logout</button>
    <?php else: ?>
      
    <?php endif; ?>
  </div>

  <main>
    <?php if ($isLoggedIn): ?>
      <div class="dashboard-container">
        <h2>Welcome back, <?= $fname ?>!</h2>
        <p>Plan, book, and manage your travel experiences effortlessly.</p>

        <div class="btn-group">
          <a href="../backend/packages.php">📦 Book a Package</a>
          <a href="../backend/mybookings.php">📄 View My Bookings</a>
          <a href="../frontend/offers.html">🎁 Special Offers</a>
        </div>

        <button class="logout" onclick="location.href='../backend/logout.php'">Logout</button>
      </div>
    <?php else: ?>
      <div class="dashboard-container">
        <h2>Welcome to Travel Planner!</h2>
        <p>Sign in or create an account to start planning your adventures.</p>
        <div class="btn-group">
          <a href="../frontend/signin.html">🔑 Sign In</a>
          <a href="../frontend/createaccount.html">📝 Create Account</a>
        </div>
      </div>
    <?php endif; ?>
  </main>
</body>
</html>
