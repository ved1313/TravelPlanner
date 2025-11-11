<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "travel_planner";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$email = $_POST['email_id'];
$pass = $_POST['password'];

$sql = "SELECT * FROM users WHERE email_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();

    if (password_verify($pass, $row['password'])) {
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['fname'] = $row['fname'];
        header("Location: ../backend/dashboard.php");
        exit;
    } else {
        echo "<script>alert('Incorrect password!'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('No user found with that email address!'); window.history.back();</script>";
}

$conn->close();
?>
