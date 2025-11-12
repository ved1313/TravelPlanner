<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("<h3>Please log in to view available packages.</h3>");
}

$conn = new mysqli("localhost", "root", "", "travel_planner");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$result = $conn->query("SELECT package_id, package_name, description, duration, price FROM packages");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Available Packages | Travel Planner</title>
  <link rel="stylesheet" href="../frontend/style.css">
</head>
<body style="font-family:Poppins; background:linear-gradient(to right,#74ebd5,#ACB6E5);">
   <button  top="0" left="0" class="home-btn" position="relative" onclick="location.href='../backend/dashboard.php'">🏠</button>
  <header style="background:gold; text-align:center; padding:10px 0;">
     
    <h1>🌍 Available Travel Packages</h1>
  </header>

  <div class="container" style="display:flex; flex-wrap:wrap; justify-content:center; gap:20px; padding:20px;">
    <?php if ($result && $result->num_rows > 0): ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <div style="background:white; width:300px; padding:20px; border-radius:15px; 
                    box-shadow:0 5px 15px rgba(0,0,0,0.1); text-align:center;">
          <h2><?= htmlspecialchars($row['package_name']) ?></h2>
          <p><?= htmlspecialchars($row['description']) ?></p>
          <p><b>Duration:</b> <?= (int)$row['duration'] ?> days</p>
          <p><b>Price:</b> ₹<?= htmlspecialchars($row['price']) ?></p>

          <form action="confirmbooking.php" method="POST">
            <input type="hidden" name="package_id" value="<?= (int)$row['package_id'] ?>">
            <input type="submit" value="Book Now" 
              style="background:#27ae60; color:white; border:none; padding:10px 20px; 
                     border-radius:8px; cursor:pointer; font-size:16px;">
          </form>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p>No packages available at the moment.</p>
    <?php endif; ?>
  </div>
</body>
</html>
