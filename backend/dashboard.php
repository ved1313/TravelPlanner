<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

$isLoggedIn = isset($_SESSION['user_id']);
$fname = $isLoggedIn ? htmlspecialchars($_SESSION['fname']) : '';

$html = file_get_contents('../frontend/dashboard.html');

if ($html === false) {
    die("Could not load dashboard.html — check your path!");
}

if ($isLoggedIn) {
    $authButtons = "
        <span>Welcome, <b>$fname</b></span>
        <button class='logout' onclick=\"location.href='../backend/logout.php'\">Logout</button>
    ";

    $contentArea = "
        <h2>Welcome back, $fname!</h2>
        <p>Here are your personalized travel options.</p>
        <div class='btn-group'>
            <a href='bookpackage.php'>Book a Package</a>
            <a href='mybookings.php'>View My Bookings</a>
            <a href='offers.php'>Special Offers</a>
        </div>
    ";
} else {
    $authButtons = "
        <button class='signin' onclick=\"location.href='../frontend/signin.html'\">Sign In</button>
        <button class='create' onclick=\"location.href='../frontend/createaccount.html'\">Create Account</button>
    ";

    $contentArea = "";
}

$html = str_replace(
    [
        '<div class="header-actions" id="authButtons">',
        '<main id="contentArea">'
    ],
    [
        '<div class="header-actions" id="authButtons">' . $authButtons,
        '<main id="contentArea">' . $contentArea
    ],
    $html
);
echo $html;exit;
?>
