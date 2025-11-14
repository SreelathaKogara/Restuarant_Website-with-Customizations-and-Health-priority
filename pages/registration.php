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

// Insert health profile data (custom values form)
$stmt = $conn->prepare("INSERT INTO health_profiles (user_id, age, weight, allergies, fitness_goals, health_conditions) 
VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iissss", $user_id, $age, $weight, $allergies, $fitness_goals, $health_conditions);
$stmt->execute();

// Insert DEFAULT values into user_health_profile for tracking
$defaultHealth = $conn->prepare("INSERT INTO user_health_profile 
(user_id, goal, daily_calorie_target, protein_target, sugar_limit, sodium_limit, diabetes, bp, heart, allergy_peanut, allergy_dairy, allergy_gluten, allergy_shellfish, updated_at)
VALUES (?, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NOW())");
$defaultHealth->bind_param("i", $user_id);
$defaultHealth->execute();


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
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');
body {
    font-family: "Poppins", sans-serif;
    background: #fafafa;
    margin: 0;
    overflow-x: hidden;
}

.background {
    position: fixed;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #ff914d, #ff5e62);
    z-index: -1;
}

.form-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding: 20px;
}

.form-card {
    background: #fff;
    padding: 40px;
    width: 430px;
    border-radius: 16px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.18);
    animation: fadeSlide .6s ease-in-out;
}

@keyframes fadeSlide {
    from { opacity: 0; transform: translateY(40px); }
    to { opacity: 1; transform: translateY(0); }
}

h2 {
    margin-top: 0;
    font-weight: 600;
    text-align: center;
    color: #222;
}

.subtitle {
    margin: -10px 0 20px;
    font-size: 14px;
    color: #777;
    text-align: center;
}

input, select {
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    font-size: 14px;
    border-radius: 8px;
    border: 1px solid #ddd;
    outline: none;
    transition: .25s;
}

input:focus, select:focus {
    border-color: #ff914d;
    box-shadow: 0 0 0 2px rgba(255,145,77,0.2);
}

label {
    font-size: 14px;
    margin-top: 12px;
    display: block;
    font-weight: 500;
}

button {
    width: 100%;
    background: #ff6b29;
    border: none;
    padding: 14px;
    margin-top: 18px;
    font-size: 16px;
    font-weight: 600;
    color: white;
    border-radius: 10px;
    cursor: pointer;
    transition: .3s;
}

button:hover {
    background: #ff4e00;
    transform: translateY(-1px);
}

.login-link {
    text-align: center;
    margin-top: 16px;
    font-size: 14px;
    color: #555;
}

.login-link a {
    color: #ff6b29;
    font-weight: 600;
    text-decoration: none;
}

.two-col {
    display: flex;
    gap: 12px;
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
    <div class="background"></div>

    <div class="form-wrapper">
        <div class="form-card">
            <h2>Create Your Profile 👤</h2>
            <p class="subtitle">Let's personalize your food experience</p>

            <form action="registration.php" method="POST">
                <div class="two-col">
                    <input type="text" name="username" placeholder="Full Name" required>
                    <input type="email" name="email" placeholder="Email Address" required>
                </div>

                <div class="two-col">
                    <input type="password" name="password" placeholder="Password" required>
                    <input type="text" name="phone_number" placeholder="Phone Number" required>
                </div>

                <div class="two-col">
                    <input type="number" name="age" placeholder="Age" required>
                    <input type="number" name="weight" placeholder="Weight (kg)" required>
                </div>

                <input type="text" name="address" placeholder="Address" required>
                <input type="text" name="bank_account" placeholder="Bank Account Number (optional)">

                <label>Allergies</label>
                <select name="allergies" onchange="toggleTextbox(this, 'custom_allergy')">
                    <option value="None">None</option>
                    <option value="Peanuts">Peanuts</option>
                    <option value="Dairy">Dairy</option>
                    <option value="Gluten">Gluten</option>
                    <option value="Shellfish">Shellfish</option>
                    <option value="Other">Other</option>
                </select>
                <input type="text" id="custom_allergy" name="custom_allergy" placeholder="Specify Allergy" style="display:none">

                <label>Fitness Goal</label>
                <select name="fitness_goals" onchange="toggleTextbox(this, 'custom_fitness_goal')">
                    <option value="Weight Loss">Weight Loss</option>
                    <option value="Muscle Gain">Muscle Gain</option>
                    <option value="Endurance">Endurance</option>
                    <option value="General Fitness">General Fitness</option>
                    <option value="Other">Other</option>
                </select>
                <input type="text" id="custom_fitness_goal" name="custom_fitness_goal" placeholder="Specify Goal" style="display:none">

                <label>Health Conditions</label>
                <select name="health_conditions" onchange="toggleTextbox(this, 'custom_health_condition')">
                    <option value="None">None</option>
                    <option value="Diabetes">Diabetes</option>
                    <option value="High Blood Pressure">High Blood Pressure</option>
                    <option value="Heart Disease">Heart Disease</option>
                    <option value="Other">Other</option>
                </select>
                <input type="text" id="custom_health_condition" name="custom_health_condition" placeholder="Specify Condition" style="display:none">

                <button type="submit">Register & Continue →</button>
                <p class="login-link">Already have an account? <a href="login.php">Login</a></p>
            </form>
        </div>
    </div>

</body>
</html>
