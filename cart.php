<?php
session_start();
include 'includes/db_connect.php';

// Sample user profile (you can replace this with dynamic login-based data)
$_SESSION['user_profile'] = [
    'allergies' => ['dairy', 'peanuts'],
    'health' => ['bp', 'heart'],
    'fitness_goals' => ['weight loss']
];

// Add item
if (isset($_GET['dish']) && isset($_GET['price'])) {
    $item = ['name' => $_GET['dish'], 'price' => $_GET['price']];
    $_SESSION['cart'][] = $item;
}

// Remove item
if (isset($_GET['remove'])) {
    $removeIndex = $_GET['remove'];
    if (isset($_SESSION['cart'][$removeIndex])) {
        array_splice($_SESSION['cart'], $removeIndex, 1);
    }
}

// Get dish details
function getDishDetails($conn, $dishName) {
    $stmt = $conn->prepare("SELECT * FROM menu WHERE name = ?");
    $stmt->bind_param("s", $dishName);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Your Cart</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f5f5f5;
      padding: 20px;
    }
    .cart-item {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      padding: 20px;
      margin-bottom: 20px;
      display: flex;
      gap: 20px;
    }
    .cart-item img {
      width: 120px;
      height: 120px;
      object-fit: cover;
      border-radius: 12px;
    }
    .cart-details {
      flex: 1;
    }
    .cart-details h3 {
      margin: 0 0 10px;
      color: #333;
    }
    .info-line {
      margin-bottom: 6px;
      font-size: 14px;
    }
    .info-line strong {
      color: #555;
    }
    .customize {
      margin-top: 10px;
    }
    .customize select, .customize textarea {
      width: 100%;
      margin-top: 8px;
      padding: 10px;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 14px;
    }
    .warning {
      background: #fff3f3;
      border: 1px solid red;
      padding: 10px;
      color: red;
      margin-top: 10px;
      border-radius: 6px;
    }
    .order-btn {
      padding: 12px 24px;
      background: #28a745;
      color: white;
      border: none;
      font-size: 16px;
      border-radius: 8px;
      cursor: pointer;
      margin-top: 20px;
    }
    .remove-link {
      color: red;
      font-size: 13px;
      margin-top: 8px;
      display: inline-block;
    }
    .remove-btn {
  display: inline-block;
  padding: 8px 14px;
  background-color: #ff4d4f;
  color: white;
  text-decoration: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  transition: background-color 0.2s ease;
  margin-top: 10px;
}
.remove-btn:hover {
  background-color: #d9363e;
}
  </style>
</head>
<body>

<h2>🛒 Your Cart</h2>

<form id="cartForm" method="POST" action="ordernow.php" enctype="multipart/form-data" onsubmit="return checkHealthRisks()">

<?php
$index = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $details = getDishDetails($conn, $item['name']);
        echo "<div class='cart-item'>";
        echo "<img src='{$details['restaurant_image']}' alt='Restaurant'>";
        echo "<div class='cart-details'>";
        echo "<h3>{$details['name']} - ₹{$details['price']}</h3>";
        echo "<div class='info-line'><strong>Description:</strong> {$details['description']}</div>";
        echo "<div class='info-line'><strong>Health Benefits:</strong> {$details['health_benefits']}</div>";
        echo "<div class='info-line'><strong>Calories:</strong> {$details['calories']} kcal</div>";
        echo "<div class='info-line'><strong>Available at:</strong> {$details['restaurants']}</div>";

        echo "<a class='remove-btn' href='cart.php?remove=$index'>Remove</a>";

        // Customization options
        echo "<div class='customize'>";
        echo "<label><strong>Choose Customizations:</strong></label>";
        echo "<select name='addon[$index][]' multiple>
                <option value='Extra Cheese'>Extra Cheese</option>
                <option value='Low Spice'>Low Spice</option>
                <option value='No Salt'>No Salt</option>
                <option value='Gluten-Free'>Gluten-Free</option>
                <option value='No Sugar'>No Sugar</option>
                <option value='High Protein'>High Protein</option>
              </select>";

        echo "<label><strong>Custom Note:</strong></label>";
        echo "<textarea name='custom_note[$index]' placeholder='Eg: Avoid oil, add turmeric...'></textarea>";
        echo "</div>";

        echo "</div></div>";
        $index++;
    }
} else {
    echo "<p>Your cart is empty.</p>";
}
?>

<?php if (!empty($_SESSION['cart'])): ?>
    <button type="submit" class="order-btn">Proceed to Order</button>
<?php endif; ?>

</form>

<script>
function checkHealthRisks() {
    const userProfile = <?php echo json_encode($_SESSION['user_profile']); ?>;
    const warnings = {
        diabetes: ["sugar", "sweet"],
        bp: ["salt", "spice", "spicy"],
        heart: ["oil", "fat", "fried"],
        dairy: ["milk", "cheese", "butter", "cream"],
        peanuts: ["peanut", "nuts"],
        gluten: ["wheat", "bread", "gluten"],
        shellfish: ["shrimp", "prawn", "crab"],
        weightloss: ["high calorie", "fried", "sugar"],
        musclegain: [],
        endurance: [],
        generalfitness: ["sugar", "fat"]
    };

    let index = 0;
    let foundIssues = [];

    document.querySelectorAll("textarea[name^='custom_note']").forEach(note => {
        const text = note.value.toLowerCase();
        let itemWarnings = [];

        if (userProfile.health.includes("diabetes")) {
            warnings.diabetes.forEach(w => { if (text.includes(w)) itemWarnings.push("❌ Sugar not recommended for Diabetes."); });
        }
        if (userProfile.health.includes("bp")) {
            warnings.bp.forEach(w => { if (text.includes(w)) itemWarnings.push("❌ Avoid Salt/Spicy food due to BP."); });
        }
        if (userProfile.health.includes("heart")) {
            warnings.heart.forEach(w => { if (text.includes(w)) itemWarnings.push("❌ Fried/Oily food may affect heart health."); });
        }

        if (userProfile.allergies.includes("dairy")) {
            warnings.dairy.forEach(w => { if (text.includes(w)) itemWarnings.push("⚠️ Contains dairy - allergy risk!"); });
        }
        if (userProfile.allergies.includes("peanuts")) {
            warnings.peanuts.forEach(w => { if (text.includes(w)) itemWarnings.push("⚠️ Peanut/Nut content - allergy!"); });
        }
        if (userProfile.allergies.includes("gluten")) {
            warnings.gluten.forEach(w => { if (text.includes(w)) itemWarnings.push("⚠️ Gluten alert - avoid if allergic."); });
        }
        if (userProfile.allergies.includes("shellfish")) {
            warnings.shellfish.forEach(w => { if (text.includes(w)) itemWarnings.push("⚠️ Shellfish ingredients - allergy warning."); });
        }

        if (userProfile.fitness_goals.includes("weight loss")) {
            warnings.weightloss.forEach(w => { if (text.includes(w)) itemWarnings.push("⚠️ Avoid high calorie food for weight loss."); });
        }

        if (itemWarnings.length > 0) {
            alert("Item " + (index+1) + " Warning:\n" + itemWarnings.join("\n"));
            foundIssues.push(true);
        }
        index++;
    });

    return foundIssues.length === 0;
}
</script>
</body>
</html>