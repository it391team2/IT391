<?php
// Include your database connection script
require 'db.php'; 

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve data from the POST request
    $fullName = trim($_POST['Full_Name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Basic validation
    if (empty($fullName) || empty($email) || empty($password)) {
        die("Please fill in all required fields.");
    }

    // Securely hash the password. NEVER store plain-text passwords!
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    /* * Prepare the SQL statement. 
     * IMPORTANT: I am assuming your table is named 'users'. 
     * If your table is named differently, change 'users' in the query below!
     */
    $stmt = $conn->prepare("INSERT INTO users (email, password_hash, Full_Name, created_at) VALUES (?, ?, ?, NOW())");

    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    // Bind the variables to the parameters (s = string)
    $stmt->bind_param("sss", $email, $passwordHash, $fullName);

    // Execute the query
    if ($stmt->execute()) {
        // Success! Redirect the user back to the login page
        header("Location: index.html?registered=true");
        exit();
    } else {
        // Handle duplicate email errors (MySQL Error 1062)
        if ($conn->errno == 1062) {
            echo "<script>alert('An account with this email already exists!'); window.history.back();</script>";
        } else {
            echo "An error occurred: " . $stmt->error;
        }
    }

    // Close connections
    $stmt->close();
    $conn->close();
} else {
    // If someone tries to access this file directly without submitting the form
    header("Location: index.html");
    exit();
}
?>