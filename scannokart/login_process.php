<?php
session_start();
//Database connection file 
require 'db.php'; 

//Check if form is submitted POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

//Ensure fields are filled 
    if (empty($email) || empty($password)) {
        die("Please fill in all fields.");
    }

//Prepare a SQL statemnt to prvent SQL injection 
    $stmt = $conn->prepare("SELECT id, Full_Name, password_hash FROM users WHERE email = ?");

//Check if statement is prepared successfully 
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
//Binds email parameter and execute the query 
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
//Check if exactly one matching user was found 
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
//Verify password matches hashed password
        if (password_verify($password, $user['password_hash'])) {

//Set session variables to log users
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['Full_Name'];
            $_SESSION['user_email'] = $email;

	        echo "<pre>";
	        print_r($_SESSION);
	        echo "</pre>";

            header("Location: dashboard.php"); 
            exit();
            
        } else {
//Handles incorrect Passwords
            echo "<script>alert('Incorrect password. Please try again.'); window.history.back();</script>";
        }
    } else {
//Handle unregistered emails 
        echo "<script>alert('No account found with that email address.'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: index.html");
    exit();
}
?>
