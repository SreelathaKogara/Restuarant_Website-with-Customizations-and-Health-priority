<?php
// Database connection details
$servername = "localhost"; // Use '127.0.0.1' if 'localhost' doesn't work
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$database = "restuarant"; // Replace with your database name

// Create a connection
$conn = new mysqli($servername, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Database connection successful!";
$conn->close();
?>