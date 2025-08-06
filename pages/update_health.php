<?php
// Start session and include database connection
session_start();
include('../includes/db.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in first.'); window.location.href='login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id']; // Get logged-in user ID

// Fetch user's current health data
$query = "SELECT * FROM health_profiles WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$health_data = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $age = $_POST['age'];
    $weight = $_POST['weight'];
    $allergies = $_POST['allergies'];
    $fitness_goals = $_POST['fitness_goals'];
    $health_conditions = $_POST['health_conditions'];

    // Update health profile in database
    $update_query = "UPDATE health_profiles SET age = ?, weight = ?, allergies = ?, fitness_goals = ?, health_conditions = ? WHERE user_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("iisssi", $age, $weight, $allergies, $fitness_goals, $health_conditions, $user_id);
    
    if ($stmt->execute()) {
        echo "<script>alert('Health profile updated successfully!'); window.location.href='profile.php';</script>";
    } else {
        echo "<script>alert('Error updating profile.');</script>";
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Health Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .form-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .form-container h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-container input, .form-container select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 14px;
        }
        .form-container button {
            width: 100%;
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .form-container button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Update Health Profile</h1>
        <form action="update_health.php" method="POST">
            <input type="number" name="age" placeholder="Age" value="<?= htmlspecialchars($health_data['age'] ?? '') ?>" required>
            <input type="number" name="weight" placeholder="Weight (kg)" value="<?= htmlspecialchars($health_data['weight'] ?? '') ?>" required>
            <input type="text" name="allergies" placeholder="Allergies (comma-separated)" value="<?= htmlspecialchars($health_data['allergies'] ?? '') ?>">
            <input type="text" name="fitness_goals" placeholder="Fitness Goals" value="<?= htmlspecialchars($health_data['fitness_goals'] ?? '') ?>">
            <input type="text" name="health_conditions" placeholder="Health Conditions (comma-separated)" value="<?= htmlspecialchars($health_data['health_conditions'] ?? '') ?>">
            <button type="submit">Update Profile</button>
        </form>
    </div>
</body>
</html>