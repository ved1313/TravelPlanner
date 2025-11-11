<?php
// ------------------------------------------
// 🧭 Database Connection
// ------------------------------------------
$servername = "localhost";
$username = "root";   // default for XAMPP
$password = "";       // change if you have one
$dbname = "travel_planner";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ------------------------------------------
// 🧾 Collect Form Data
// ------------------------------------------
$fname = $_POST['fname'];
$lname = $_POST['lname'];
$email_id = $_POST['email_id'];
$contact_no = $_POST['contact_no'];
$password_hashed = password_hash($_POST['password'], PASSWORD_DEFAULT); // secure hash
$gender = $_POST['gender'];
$houseno = $_POST['houseno'];
$street = $_POST['street'];
$city = $_POST['city'];
$state = $_POST['state'];
$pincode = $_POST['pincode'];

// ------------------------------------------
// 🧍 Insert Data into USERS Table
// ------------------------------------------
$sql = "INSERT INTO users (fname, lname, email_id, contact_no, password, gender, houseno, street, city, state, pincode)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssssss", 
    $fname, $lname, $email_id, $contact_no, $password_hashed, 
    $gender, $houseno, $street, $city, $state, $pincode
);

if ($stmt->execute()) {
    // Get the newly created user's ID
    $user_id = $stmt->insert_id;

    // ------------------------------------------
    // ✈️ Automatically Create a Default Booking
    // ------------------------------------------
    // (Optional: You can also choose the latest or cheapest package dynamically)
    $default_package_id = 1; // assuming you have a “Welcome Package” in packages table

    // Calculate amount by fetching package price (if exists)
    $package_query = $conn->prepare("SELECT price FROM packages WHERE package_id = ?");
    $package_query->bind_param("i", $default_package_id);
    $package_query->execute();
    $package_result = $package_query->get_result();

    $amount = 0;
    if ($package_result->num_rows > 0) {
        $row = $package_result->fetch_assoc();
        $amount = $row['price'];
    }

    $package_query->close();

    // Insert into bookings table
    $booking_sql = "INSERT INTO bookings (booking_date, amount, user_id, package_id)
                    VALUES (CURDATE(), ?, ?, ?)";
    $booking_stmt = $conn->prepare($booking_sql);
    $booking_stmt->bind_param("dii", $amount, $user_id, $default_package_id);
    $booking_stmt->execute();

    // Optional: Insert into payments as pending
    $payment_sql = "INSERT INTO payments (payment_method, amount, payment_status, booking_id, user_id)
                    VALUES ('Not Selected', ?, 'Pending', ?, ?)";
    $booking_id = $booking_stmt->insert_id;
    $payment_stmt = $conn->prepare($payment_sql);
    $payment_stmt->bind_param("dii", $amount, $booking_id, $user_id);
    $payment_stmt->execute();

    // ------------------------------------------
    // ✅ Success Response
    // ------------------------------------------
    echo "
    <html>
        <head>
            <title>Account Created</title>
            <style>
                body {
                    background: linear-gradient(to right, #74ebd5, #ACB6E5);
                    font-family: Poppins, sans-serif;
                    text-align: center;
                    color: #2c3e50;
                    margin-top: 100px;
                }
                .card {
                    background: white;
                    width: 400px;
                    margin: auto;
                    padding: 30px;
                    border-radius: 15px;
                    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
                }
                a {
                    display: inline-block;
                    margin-top: 15px;
                    padding: 10px 20px;
                    background-color: #27ae60;
                    color: white;
                    border-radius: 8px;
                    text-decoration: none;
                }
                a:hover {
                    background-color: #219150;
                }
            </style>
        </head>
        <body>
            <div class='card'>
                <h2>Account Created Successfully 🎉</h2>
                <p>Welcome, <b>$fname</b>!<br>Your account has been created.</p>
                <p>Please sign in to continue.</p>
                <a href='createaccount.html'>Return to Home</a>
                <a href='../frontend/signin.html'>Sign In</a>
            </div>
        </body>
    </html>
    ";

} else {
    echo "<h3 style='color:red;text-align:center;'>Error: Unable to create account.<br>" . $stmt->error . "</h3>";
}

// ------------------------------------------
// 🧹 Cleanup
// ------------------------------------------
$stmt->close();
$conn->close();
?>
