<?php
include 'includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Error: User not logged in.");
}

$user_id = $_SESSION['user_id'];
$item_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validate item_id
if ($item_id <= 0) {
    die("Error: Invalid menu item.");
}

// Fetch user health data
$user_query = "SELECT health_conditions FROM health_profiles WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$user_result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($user_result);

if (!$user) {
    die("Error: User health data not found.");
}

$health_issues = explode(',', strtolower($user['health_conditions'])); // Convert to array

// Fetch menu item details
$query = "SELECT * FROM menu WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $item_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$item = mysqli_fetch_assoc($result);

if (!$item) {
    die("Error: Menu item not found.");
}

// Get base ingredients
$base_ingredients = explode(',', strtolower($item['base_ingredients']));

// Define health warnings and alternatives
$warnings = [
    "sugar" => ["issue" => "diabetes", "warning" => "Avoid sugar due to diabetes!", "alternative" => "Stevia or Honey"],
    "salt" => ["issue" => "high blood pressure", "warning" => "Less salt is recommended for high blood pressure.", "alternative" => "Himalayan salt"],
    "peanuts" => ["issue" => "nut allergy", "warning" => "Peanuts can trigger severe allergies!", "alternative" => "Almonds or Sunflower seeds"],
    "milk" => ["issue" => "lactose intolerance", "warning" => "Avoid milk due to lactose intolerance!", "alternative" => "Almond or Soy Milk"],
    "wheat" => ["issue" => "gluten sensitivity", "warning" => "Gluten-free alternatives are better!", "alternative" => "Rice Flour or Quinoa"]
];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customize <?php echo htmlspecialchars($item['name']); ?></title>
    <style>
        .warning { color: red; font-size: 14px; display: none; }
        .alternative { color: green; font-size: 14px; display: none; }
    </style>
    <script>
        function checkHealthIssue(ingredient, healthIssues) {
            let warnings = <?php echo json_encode($warnings); ?>;
            
            let warningElement = document.getElementById(`warning-${ingredient}`);
            let alternativeElement = document.getElementById(`alternative-${ingredient}`);

            if (warnings[ingredient]) {
                let issue = warnings[ingredient].issue;
                if (healthIssues.includes(issue)) {
                    warningElement.innerText = warnings[ingredient].warning;
                    alternativeElement.innerText = "Alternative: " + warnings[ingredient].alternative;
                    warningElement.style.display = "block";
                    alternativeElement.style.display = "block";
                } else {
                    warningElement.style.display = "none";
                    alternativeElement.style.display = "none";
                }
            }
        }
    </script>
</head>
<body>
    <h1>Customize <?php echo htmlspecialchars($item['name']); ?></h1>
    <p><strong>Description:</strong> <?php echo htmlspecialchars($item['description']); ?></p>
    <p><strong>Base Ingredients:</strong> <?php echo htmlspecialchars($item['base_ingredients']); ?></p>

    <form method="POST" action="save_customizations.php">
        <h3>Select Customizations:</h3>

        <?php
        foreach ($base_ingredients as $ingredient) {
            echo "<label>
                    <input type='checkbox' name='ingredient[]' value='$ingredient' onchange=\"checkHealthIssue('$ingredient', '".implode(',', $health_issues)."')\"> No $ingredient
                  </label>
                  <p id='warning-$ingredient' class='warning'></p>
                  <p id='alternative-$ingredient' class='alternative'></p><br>";
        }
        ?>

        <h3>Additional Customization:</h3>
        <textarea name="custom_ingredients" placeholder="Add any specific customization request"></textarea>

        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
        <button type="submit">Save Customization</button>
    </form>
</body>
</html>





On Tue, 11 Mar 2025 at 14:50, Teja Sree <kltejasree123@gmail.com> wrote:
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