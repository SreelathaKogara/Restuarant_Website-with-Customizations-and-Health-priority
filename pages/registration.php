<?php
// Include database connection
include('../includes/db.php');
session_start();

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $age = $_POST['age'];
    $weight = $_POST['weight'];
    $address = $_POST['address'];
    $phone_number = $_POST['phone_number'];
    $bank_account = $_POST['bank_account']; // Optional

    // Handle dynamic "Other" values
    $allergies = $_POST['allergies'] === "Other" ? $_POST['custom_allergy'] : $_POST['allergies'];
    $fitness_goals = $_POST['fitness_goals'] === "Other" ? $_POST['custom_fitness_goal'] : $_POST['fitness_goals'];
    $health_conditions = $_POST['health_conditions'] === "Other" ? $_POST['custom_health_condition'] : $_POST['health_conditions'];

    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($address) || empty($phone_number) || $age <= 0 || $weight <= 0) {
        echo "<script>alert('Please fill in all required fields correctly.');</script>";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo "<script>alert('Email already exists. Please use a different email.'); window.location.href = 'registration.php';</script>";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Insert user data
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, address, phone_number, bank_account) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $username, $email, $hashed_password, $address, $phone_number, $bank_account);
            $stmt->execute();

            $user_id = $stmt->insert_id;

            // Insert health profile data
            $stmt = $conn->prepare("INSERT INTO health_profiles (user_id, age, weight, allergies, fitness_goals, health_conditions) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iissss", $user_id, $age, $weight, $allergies, $fitness_goals, $health_conditions);
            $stmt->execute();

            echo "<script>alert('Registration successful!'); window.location.href = 'login.php';</script>";
        }

        // Cleanup
        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
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
    <script>
        function toggleTextbox(selectElement, textboxId) {
            var textbox = document.getElementById(textboxId);
            textbox.style.display = selectElement.value === "Other" ? "block" : "none";
        }
    </script>
</head>
<body>
    <div class="form-container">
        <h1>Create Your Profile</h1>
        <form action="registration.php" method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="number" name="age" placeholder="Age" required>
            <input type="number" name="weight" placeholder="Weight (kg)" required>
            <input type="text" name="address" placeholder="Address" required>
            <input type="text" name="phone_number" placeholder="Phone Number" required>
            <input type="text" name="bank_account" placeholder="Bank Account Number (optional)">

            <label for="allergies">Allergies:</label>
            <select name="allergies" onchange="toggleTextbox(this, 'custom_allergy')">
                <option value="None">None</option>
                <option value="Peanuts">Peanuts</option>
                <option value="Dairy">Dairy</option>
                <option value="Gluten">Gluten</option>
                <option value="Shellfish">Shellfish</option>
                <option value="Other">Other</option>
            </select>
            <input type="text" id="custom_allergy" name="custom_allergy" placeholder="Other (Specify)" style="display: none;">

            <label for="fitness_goals">Fitness Goals:</label>
            <select name="fitness_goals" onchange="toggleTextbox(this, 'custom_fitness_goal')">
                <option value="Weight Loss">Weight Loss</option>
                <option value="Muscle Gain">Muscle Gain</option>
                <option value="Endurance">Endurance</option>
                <option value="General Fitness">General Fitness</option>
                <option value="Other">Other</option>
            </select>
            <input type="text" id="custom_fitness_goal" name="custom_fitness_goal" placeholder="Other (Specify)" style="display: none;">

            <label for="health_conditions">Health Conditions:</label>
            <select name="health_conditions" onchange="toggleTextbox(this, 'custom_health_condition')">
                <option value="None">None</option>
                <option value="Diabetes">Diabetes</option>
                <option value="High Blood Pressure">High Blood Pressure</option>
                <option value="Heart Disease">Heart Disease</option>
                <option value="Other">Other</option>
            </select>
            <input type="text" id="custom_health_condition" name="custom_health_condition" placeholder="Other (Specify)" style="display: none;">

            <button type="submit">Register</button>
        </form>
    </div>
</body>
</html>