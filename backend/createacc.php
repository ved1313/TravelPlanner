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

$fname = $_POST['fname'];
$lname = $_POST['lname'];
$email_id = $_POST['email_id'];
$contact_no = $_POST['contact_no'];
$password_hashed = password_hash($_POST['password'], PASSWORD_DEFAULT); 
$gender = $_POST['gender'];
$houseno = $_POST['houseno'];
$street = $_POST['street'];
$city = $_POST['city'];
$state = $_POST['state'];
$pincode = $_POST['pincode'];


$sql_check = "SELECT * FROM users WHERE email_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("s", $email_id); 
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows > 0) {

       echo       "<html> <script  type='text/javascript'>
                   alert('An account with this email address already exists. Please use a different email.');
                   window.history.back();
               </script>
       </html>";

    $stmt_check->close();
    $conn->close();
    exit; 

} else {

    $sql_insert = "INSERT INTO users (fname, lname, email_id, contact_no, password, gender, houseno, street, city, state, pincode)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt_insert = $conn->prepare($sql_insert);


    $stmt_insert->bind_param("sssssssssss",
        $fname,
        $lname,
        $email_id, 
        $contact_no,
        $password_hashed,
        $gender,
        $houseno,
        $street,
        $city,
        $state,
        $pincode
    );

    if ($stmt_insert->execute()) {
   
        $user_id = $stmt_insert->insert_id;

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
        echo "<h3 style='color:red;text-align:center;'>Error: Unable to create account.<br>" . $stmt_insert->error . "</h3>";
    }

    $stmt_insert->close();
    $conn->close();
}
?>