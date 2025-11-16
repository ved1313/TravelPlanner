<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("Please log in to continue.");
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request.");
}

$action = $_POST['action']; // "pay_now" or "pay_later"

$conn = new mysqli("localhost", "root", "", "travel_planner");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$isPending = isset($_POST['booking_id']);


// =============================
// PAY LATER
// =============================
if ($action === "pay_later") {

    if ($isPending) {
        $booking_id = (int)$_POST['booking_id'];

        $update = $conn->prepare("
            UPDATE payments 
            SET payment_method='None', payment_status='Pending'
            WHERE booking_id=? AND user_id=?
        ");
        $update->bind_param("ii", $booking_id, $user_id);
        $update->execute();
    } 
    else {
        $package_id = (int)$_POST['package_id'];

        $stmt = $conn->prepare("SELECT price FROM packages WHERE package_id=?");
        $stmt->bind_param("i", $package_id);
        $stmt->execute();
        $price = $stmt->get_result()->fetch_assoc()['price'];

        $book = $conn->prepare("
            INSERT INTO bookings (booking_date, amount, user_id, package_id)
            VALUES (CURDATE(), ?, ?, ?)
        ");
        $book->bind_param("dii", $price, $user_id, $package_id);
        $book->execute();

        $booking_id = $book->insert_id;

        $pay = $conn->prepare("
            INSERT INTO payments (payment_method, amount, payment_status, booking_id, user_id)
            VALUES ('None', ?, 'Pending', ?, ?)
        ");
        $pay->bind_param("dii", $price, $booking_id, $user_id);
        $pay->execute();
    }

    echo "
    <html><body style='text-align:center;margin-top:80px;font-family:Poppins;'>
        <h2>📌 Booking Saved</h2>
        <p>Your booking has been created with <b>Pending Payment</b>.</p>
        <a href='mybookings.php'>View My Bookings</a>
    </body></html>";
    exit;
}


// =============================
// PAY NOW
// =============================

$upload_dir = "../uploads/";
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

$img = $_FILES['payment_image'];
if ($img['error'] !== 0) die("Upload failed.");

$filename = time() . "_" . basename($img['name']);
move_uploaded_file($img['tmp_name'], $upload_dir . $filename);

if ($isPending) {

    $booking_id = (int)$_POST['booking_id'];

    $update = $conn->prepare("
        UPDATE payments 
        SET payment_method='Online', payment_status='Completed', proof_image=? 
        WHERE booking_id=? AND user_id=?
    ");
    $update->bind_param("sii", $filename, $booking_id, $user_id);
    $update->execute();

}
else {

    $package_id = (int)$_POST['package_id'];

    $stmt = $conn->prepare("SELECT price FROM packages WHERE package_id=?");
    $stmt->bind_param("i", $package_id);
    $stmt->execute();
    $price = $stmt->get_result()->fetch_assoc()['price'];

    $book = $conn->prepare("
        INSERT INTO bookings (booking_date, amount, user_id, package_id)
        VALUES (CURDATE(), ?, ?, ?)
    ");
    $book->bind_param("dii", $price, $user_id, $package_id);
    $book->execute();

    $booking_id = $book->insert_id;

    $pay = $conn->prepare("
        INSERT INTO payments (payment_method, amount, payment_status, booking_id, user_id, proof_image)
        VALUES ('Online', ?, 'Completed', ?, ?, ?)
    ");
    $pay->bind_param("diis", $price, $booking_id, $user_id, $filename);
    $pay->execute();
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Payment Successful</title>
<style>
body { font-family:Poppins; background:linear-gradient(to right,#74ebd5,#ACB6E5); text-align:center; padding-top:80px; }
.card { background:white; width:420px; margin:auto; padding:30px; border-radius:14px; box-shadow:0 6px 20px rgba(0,0,0,0.2); }
img { width:90%; border-radius:8px; }
a { margin-top:15px; display:inline-block; padding:10px 20px; background:#27ae60; color:white; border-radius:8px; text-decoration:none; }
a:hover { background:#1e8449; }
</style>
</head>

<body>
<div class="card">
  <h2>🎉 Payment Successful!</h2>
  <p>Your booking has been updated.</p>
  <img src="../uploads/<?= $filename ?>">
  <a href="mybookings.php">View My Bookings</a>
</div>
</body>
</html>
