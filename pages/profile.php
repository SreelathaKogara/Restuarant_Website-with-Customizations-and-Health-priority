<?php
include '../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user basic details
$stmt = $conn->prepare("SELECT username, email, phone_number, address FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $phone_number, $address);
$stmt->fetch();
$stmt->close();

// Fetch health profile details
$stmt = $conn->prepare("SELECT age, weight, allergies, fitness_goals, health_conditions FROM health_profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($age, $weight, $allergies, $fitness_goals, $health_conditions);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Profile</title>
    <style>
        /* Profile Page Styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            font-size: 36px;
            color: #333;
            margin-top: 20px;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            max-width: 800px;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .container p {
            font-size: 18px;
            margin: 10px 0;
            color: #555;
        }

        .label {
            font-weight: bold;
            color: #333;
        }

        .value {
            color: #666;
        }

        .profile-info {
            margin-top: 20px;
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .profile-info p {
            font-size: 16px;
            margin-bottom: 8px;
        }

        .container a button {
            display: block;
            margin: 15px auto;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 60%;
            text-align: center;
        }

        .container a button:hover {
            background-color: #45a049;
        }

        .logout-btn {
            background-color: #e74c3c !important;
        }

        hr {
            border: none;
            border-top: 1px solid #ccc;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <h1>Your Profile</h1>
    <div class="container">
        <div class="profile-info">
            <p><span class="label">Name:</span> <span class="value"><?= htmlspecialchars($name) ?></span></p>
            <p><span class="label">Email:</span> <span class="value"><?= htmlspecialchars($email) ?></span></p>
            <p><span class="label">Phone:</span> <span class="value"><?= htmlspecialchars($phone_number) ?></span></p>
            <p><span class="label">Address:</span> <span class="value"><?= htmlspecialchars($address) ?></span></p>

            <hr>

            <p><span class="label">Age:</span> <span class="value"><?= $age ?></span></p>
            <p><span class="label">Weight:</span> <span class="value"><?= $weight ?> kg</span></p>
            <p><span class="label">Allergies:</span> <span class="value"><?= $allergies ?></span></p>
            <p><span class="label">Fitness Goals:</span> <span class="value"><?= $fitness_goals ?></span></p>
            <p><span class="label">Health Conditions:</span> <span class="value"><?= $health_conditions ?></span></p>

            <hr>

            <p><span class="label">My Vouchers:</span> <span class="value">No vouchers available</span></p>
            <p><span class="label">Bank Accounts:</span> <span class="value">Not linked</span></p>
        </div>

        <a href="update_health.php"><button>Edit Health Profile</button></a>
        <a href="edit_profile.php"><button>Edit Profile</button></a>
        <a href="logout.php"><button class="logout-btn">Logout</button></a>
    </div>
</body>
</html>
