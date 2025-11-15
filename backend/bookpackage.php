<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("Please log in to complete payment.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['package_id'])) {
    echo "<h3>Invalid Request</h3>";
    echo "<a href='packages.php'>Back to Packages</a>";
    exit;
}

$user_id = $_SESSION['user_id'];
$package_id = (int)$_POST['package_id'];

$conn = new mysqli("localhost", "root", "", "travel_planner");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch price
$stmt = $conn->prepare("SELECT price FROM packages WHERE package_id = ?");
if (!$stmt) {
    die("Prepare failed (select price): " . $conn->error);
}
$stmt->bind_param("i", $package_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) { die("Package not found."); }

$package = $result->fetch_assoc();
$amount = $package['price'];
$stmt->close();


// ========= 1️⃣ Upload Payment Proof ========= //
$upload_dir = __DIR__ . "/../uploads/"; // absolute dir
if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0777, true)) {
        die("Failed to create upload directory.");
    }
}

$payment_image = null;
if (isset($_FILES['payment_image']) && $_FILES['payment_image']['error'] === UPLOAD_ERR_OK) {

    // Basic validation of file type (images only)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES['payment_image']['tmp_name']);
    finfo_close($finfo);

    $allowed = ['image/png','image/jpeg','image/jpg','image/webp'];
    if (!in_array($mime, $allowed)) {
        die("Invalid file type. Please upload a PNG/JPG/WEBP image.");
    }

    $origName = basename($_FILES["payment_image"]["name"]);
    $ext = pathinfo($origName, PATHINFO_EXTENSION);
    $filename = time() . "_" . preg_replace('/[^A-Za-z0-9_.-]/', '_', pathinfo($origName, PATHINFO_FILENAME)) . "." . $ext;
    $target_path = $upload_dir . $filename;

    if (!move_uploaded_file($_FILES["payment_image"]["tmp_name"], $target_path)) {
        die("Failed to move uploaded file.");
    }

    $payment_image = $filename; // store filename, not full path
} else {
    // No file uploaded or upload error
    $err = $_FILES['payment_image']['error'] ?? 'no file';
    die("Payment proof upload failed (error: {$err}). Please go back and try again.");
}


// ========= 2️⃣ Create Booking Record ========= //
$bookSql = "
    INSERT INTO bookings (booking_date, amount, user_id, package_id)
    VALUES (CURDATE(), ?, ?, ?)
";
$book = $conn->prepare($bookSql);
if (!$book) {
    die("Prepare failed (insert booking): " . $conn->error);
}
$book->bind_param("dii", $amount, $user_id, $package_id);
if (!$book->execute()) {
    die("Execute failed (insert booking): " . $book->error);
}
$booking_id = $book->insert_id;
$book->close();


// ========= 3️⃣ Insert Payment Record (Completed) ========= //
// Note: ensure `payments` table has `proof_image` column (VARCHAR)
$paySql = "
    INSERT INTO payments (payment_method, amount, payment_status, booking_id, user_id, proof_image)
    VALUES (?, ?, ?, ?, ?, ?)
";
$pay = $conn->prepare($paySql);
if (!$pay) {
    die("Prepare failed (insert payment): " . $conn->error);
}

$method = 'Online';
$status = 'Completed';
// bind types: s = string (method), d = double(amount), s = string(status) -> but placeholders order below
// Our placeholders: method (s), amount (d), status (s), booking_id (i), user_id (i), proof_image (s)
$pay->bind_param("sdsiis", $method, $amount, $status, $booking_id, $user_id, $payment_image);

// Execute and check
if (!$pay->execute()) {
    die("Execute failed (insert payment): " . $pay->error);
}
$pay->close();

$conn->close();

// ========= 4️⃣ Confirmation page ========= //
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Payment Successful</title>
  <style>
    body { font-family: Poppins, sans-serif; background: linear-gradient(to right,#74ebd5,#ACB6E5); text-align:center; margin-top:80px;}
    .card { background:white; width:480px; margin: auto; padding:30px; border-radius:12px; box-shadow:0 6px 20px rgba(0,0,0,0.15); }
    img { max-width:90%; border-radius:8px; margin-top:12px; }
    a { display:inline-block; margin-top:18px; padding:10px 18px; background:#27ae60; color:#fff; border-radius:8px; text-decoration:none;}
    a:hover { background:#1e8449; }
  </style>
</head>
<body>
  <div class="card">
    <h2>🎉 Payment Successful!</h2>
    <p>Your booking is confirmed.</p>
    <p><strong>Booking ID:</strong> <?= htmlspecialchars($booking_id) ?></p>
    <p><strong>Amount Paid:</strong> ₹<?= htmlspecialchars(number_format($amount,2)) ?></p>
    <p><strong>Status:</strong> <span style="color:green;font-weight:600;">Completed</span></p>

    <?php if ($payment_image): ?>
      <p><strong>Payment Proof:</strong></p>
      <img src="../uploads/<?= htmlspecialchars($payment_image) ?>" alt="Payment proof">
    <?php endif; ?>

    <p><a href="mybookings.php">View My Bookings</a></p>
  </div>
</body>
</html>
