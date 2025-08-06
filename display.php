<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "your_database");

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch images from the database
$sql = "SELECT image_path FROM images";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Images</title>
</head>
<body>

<h2>Uploaded Images</h2>
<?php
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<img src='" . $row['image_path'] . "' width='200' style='margin:10px;'>";
    }
} else {
    echo "No images found.";
}
$conn->close();
?>

</body>
</html>