<?php
// Include database connection
include('../includes/db.php');
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in first!'); window.location.href = 'login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];
$age = $weight = $allergies = $fitness_goals = $health_conditions = "";

// Fetch existing health profile
$query = $conn->prepare("SELECT age, weight, allergies, fitness_goals, health_conditions FROM health_profiles WHERE user_id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$query->bind_result($age, $weight, $allergies, $fitness_goals, $health_conditions);
$query->fetch();
$query->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $age = $_POST['age'];
    $weight = $_POST['weight'];
    $allergies = $_POST['allergies'];
    $fitness_goals = $_POST['fitness_goals'];
    $health_conditions = $_POST['health_conditions'];

    // Update health profile
    $stmt = $conn->prepare("UPDATE health_profiles SET age = ?, weight = ?, allergies = ?, fitness_goals = ?, health_conditions = ? WHERE user_id = ?");
    $stmt->bind_param("iisssi", $age, $weight, $allergies, $fitness_goals, $health_conditions, $user_id);

    if ($stmt->execute()) {
        echo "<script>alert('Health profile updated successfully!'); window.location.href = 'profile.php';</script>";
    } else {
        echo "<script>alert('Error updating health profile.');</script>";
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
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
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
            <label for="age">Age:</label>
            <input type="number" name="age" value="<?= htmlspecialchars($age) ?>" required>

            <label for="weight">Weight (kg):</label>
            <input type="number" name="weight" value="<?= htmlspecialchars($weight) ?>" required>

            <label for="allergies">Allergies:</label>
            <input type="text" name="allergies" value="<?= htmlspecialchars($allergies) ?>" placeholder="Comma-separated (e.g., Peanuts, Dairy)">

            <label for="fitness_goals">Fitness Goals:</label>
            <select name="fitness_goals">
                <option value="Weight Loss" <?= ($fitness_goals == "Weight Loss") ? "selected" : "" ?>>Weight Loss</option>
                <option value="Muscle Gain" <?= ($fitness_goals == "Muscle Gain") ? "selected" : "" ?>>Muscle Gain</option>
                <option value="General Fitness" <?= ($fitness_goals == "General Fitness") ? "selected" : "" ?>>General Fitness</option>
            </select>

            <label for="health_conditions">Health Conditions:</label>
            <input type="text" name="health_conditions" value="<?= htmlspecialchars($health_conditions) ?>" placeholder="Comma-separated (e.g., Diabetes, High BP)">

            <button type="submit">Update</button>
        </form>
    </div>
</body>
</html>