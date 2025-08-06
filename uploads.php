<?php
if (isset($_POST['submit'])) {
    // Database connection
    $conn = new mysqli("localhost", "root", "", "your_database");

    // Check for connection errors
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Directory to store uploaded images
    $target_dir = "uploads/";
    
    // Get the file name and create full file path
    $target_file = $target_dir . basename($_FILES["image"]["name"]);

    // Move uploaded file to the uploads folder
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        // Insert the file path into the database
        $sql = "INSERT INTO images (image_path) VALUES ('$target_file')";
        
        if ($conn->query($sql) === TRUE) {
            echo "Image uploaded successfully! <br>";
            echo "<img src='" . $target_file . "' width='200'>";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "Error uploading image.";
    }

    $conn->close();
}
?>