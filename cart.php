<?php
session_start();
require_once __DIR__ . '/includes/db_connect.php'; // $conn = mysqli

// ✅ Init cart
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// Add item
if (isset($_GET['dish']) && isset($_GET['price'])) {
    $_SESSION['cart'][] = [
        'name' => $_GET['dish'],
        'price' => $_GET['price'],
        'customizations' => '',
        'note' => ''
    ];
}

// Remove item
if (isset($_GET['remove'])) {
    $idx = (int)$_GET['remove'];
    if (isset($_SESSION['cart'][$idx])) array_splice($_SESSION['cart'], $idx, 1);
}

// When submitting customizations
if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (!empty($_POST['addon'])) {
        foreach ($_POST['addon'] as $index=>$addon) {
            $_SESSION['cart'][$index]['customizations'] = $addon;
        }
    }
    if (!empty($_POST['custom_note'])) {
        foreach ($_POST['custom_note'] as $index=>$note) {
            $_SESSION['cart'][$index]['note'] = $note;
        }
    }
}

// ✅ Get dish details
function getDishDetails(mysqli $conn, $dishName) {
    $stmt = $conn->prepare("SELECT * FROM menu WHERE name = ?");
    $stmt->bind_param("s", $dishName);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// ✅ Fetch user profile from DB
$userId = $_SESSION['user_id'] ?? 1;
$stmt = $conn->prepare("SELECT * FROM user_health_profile WHERE user_id=?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

// Default if no profile
if (!$profile) {
    $profile = [
        "goal" => "General Fitness",
        "daily_calorie_target" => 2000,
        "protein_target" => 60,
        "sugar_limit" => 30,
        "sodium_limit" => 1500,
        "diabetes" => 0,
        "bp" => 0,
        "heart" => 0,
        "allergy_peanut" => 0,
        "allergy_dairy" => 0,
        "allergy_gluten" => 0,
        "allergy_shellfish" => 0
    ];
}

// ✅ Fetch today’s nutrition totals
$today_orders = ["cal"=>0,"sugar"=>0,"protein"=>0];
$stmt = $conn->prepare("
    SELECT SUM(m.calories) cal, SUM(m.sugar) sugar, SUM(m.protein) protein
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    JOIN menu m ON oi.dish_id = m.id
    WHERE o.user_id = ? AND DATE(o.order_date) = CURDATE()
");
$stmt->bind_param("i",$userId);
$stmt->execute();
if ($row = $stmt->get_result()->fetch_assoc()) {
    $today_orders = [
        'cal'     => (float)($row['cal'] ?? 0),
        'sugar'   => (float)($row['sugar'] ?? 0),
        'protein' => (float)($row['protein'] ?? 0),
    ];
}

// ✅ Rule-based AI function
function generateAISuggestion($dish, $profile, $customizations) {
    $suggestions = [];

    // Calories check
    if ($dish['calories'] > 600) {
        $suggestions[] = "This dish is quite high in calories, balance it with lighter meals.";
    } else {
        $suggestions[] = "This dish is moderate in calories and fits well into most diets.";
    }

    // Health benefits
    if (!empty($dish['health_benefits']) && stripos($dish['health_benefits'], "fiber") !== false) {
        $suggestions[] = "Good source of fiber, which supports digestion.";
    }

    // Conditions check
    if (!empty($profile['diabetes']) && stripos($customizations, "sugar") === false) {
        $suggestions[] = "Avoiding sugar is good for diabetes management.";
    }
    if (!empty($profile['bp']) && stripos($customizations, "No Salt") === false) {
        $suggestions[] = "Consider reducing salt to help manage blood pressure.";
    }
    if (!empty($profile['heart']) && stripos($customizations, "oil") === false) {
        $suggestions[] = "Try avoiding excess oil for better heart health.";
    }

    // Fitness goals
    if (strcasecmp($profile['goal'], "weight_loss") == 0) {
        $suggestions[] = "For weight loss, limit carbs in this dish.";
    } elseif (strcasecmp($profile['goal'], "muscle_gain") == 0) {
        $suggestions[] = "High protein options will support muscle gain.";
    }

    // Final fallback
    if (empty($suggestions)) {
        $suggestions[] = "This dish looks fine for your diet plan!";
    }

    return implode(" ", $suggestions);
}

// ✅ Total if the user places the current cart
$cart_calories = 0;
foreach ($_SESSION['cart'] as $item) {
    $d = getDishDetails($conn, $item['name']);
    if ($d && isset($d['calories'])) {
        $cart_calories += (float)$d['calories'];
    }
}
$total_if_ordered = $today_orders['cal'] + $cart_calories;
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Your Cart</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 p-6">

<h2 class="text-2xl font-bold mb-4">🛒 Your Cart</h2>

<!-- SINGLE form that wraps both columns -->
<!-- SINGLE form that wraps both columns -->
<form id="cartForm" method="POST" action="ordernow.php">
  <div class="flex items-start gap-6">
    <!-- Left side: Cart items -->
    <div class="flex-1">
      <?php
    $index = 0;
    $user_conditions = ["dairy_allergy", "diabetes"]; // example, fetch real from DB/session

if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $details = getDishDetails($conn, $item['name']);

        echo "<div class='cart-item bg-white p-4 mb-4 rounded-2xl shadow flex gap-4'>";
        echo "<div class='flex-1'>";
        echo "<h3 class='text-lg font-semibold'>" . htmlspecialchars($details['name']) . " - ₹" . htmlspecialchars($details['price']) . "</h3>";
        echo "<p class='text-sm text-gray-600'><strong>Description:</strong> " . htmlspecialchars($details['description']) . "</p>";
        echo "<p class='text-sm text-gray-600'><strong>Health Benefits:</strong> " . htmlspecialchars($details['health_benefits']) . "</p>";
        echo "<p class='text-sm text-gray-600'><strong>Calories:</strong> " . htmlspecialchars($details['calories']) . " kcal</p>";
        echo "<p class='text-sm text-gray-600'><strong>Available at:</strong> " . htmlspecialchars($details['restaurants']) . "</p>";
        echo "<a href='cart.php?remove=$index' class='inline-block mt-2 px-3 py-1 bg-red-500 text-white rounded-md'>Remove</a>";

        // ✅ Customization (now outside echo to allow HTML + JS)
        ?>

<!-- ✅ Customizations below Remove -->
<div class="mt-3">
  <label class="block text-sm font-medium mb-2">Choose customizations:</label>
  <div id="customization_<?php echo $index; ?>" class="customization flex flex-wrap gap-2">
    <button type="button" data-value="Cheese" class="chip px-3 py-1 rounded-full bg-gray-200 hover:bg-green-300">🧀 Cheese</button>
    <button type="button" data-value="Butter" class="chip px-3 py-1 rounded-full bg-gray-200 hover:bg-green-300">🧈 Butter</button>
    <button type="button" data-value="Milk" class="chip px-3 py-1 rounded-full bg-gray-200 hover:bg-green-300">🥛 Milk</button>
    <button type="button" data-value="Sugar" class="chip px-3 py-1 rounded-full bg-gray-200 hover:bg-green-300">🍬 Sugar</button>
    <button type="button" data-value="Salt" class="chip px-3 py-1 rounded-full bg-gray-200 hover:bg-green-300">🧂 Salt</button>
  </div>
  <!-- Hidden input to store selected values -->
  <input type="hidden" name="addon[<?php echo $index; ?>]" id="addon_input_<?php echo $index; ?>">

  <!-- ✅ Custom Request with Border -->
  <label class="block text-sm font-medium mt-3">Custom request:</label>
  <textarea 
    id="custom_text_<?php echo $index; ?>" 
    name="custom_note[<?php echo $index; ?>]" 
    placeholder="Eg: Avoid oil, add turmeric..." 
    class="w-full border border-gray-300 rounded-lg p-2 mt-1 focus:ring-2 focus:ring-green-400 focus:border-green-400"><?php echo htmlspecialchars($item['note']); ?></textarea>
  <div id="warning_<?php echo $index; ?>" class="warning-box text-sm text-red-600 mt-2" style="display:none"></div>

</div>

<style>
  .chip.active {
    background-color: #4ade80; /* Tailwind green-400 */
    color: white;
  }
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const customization = document.getElementById("customization_<?php echo $index; ?>");
    const customText = document.getElementById("custom_text_<?php echo $index; ?>");
    const hiddenInput = document.getElementById("addon_input_<?php echo $index; ?>");
    const warningBox = document.getElementById("warning_<?php echo $index; ?>");

    const userConditions = <?php echo json_encode($user_conditions); ?>;

    const riskyItems = {
        dairy_allergy: ["cheese", "milk", "butter", "cream"],
        diabetes: ["sugar", "chocolate", "sweet"],
        bp: ["salt", "pickle","spice"],
        heart: ["fried", "butter", "cream", "oil"]
    };

    // ✅ Chip click handling
    if (customization) {
        customization.querySelectorAll(".chip").forEach(chip => {
            chip.addEventListener("click", function() {
                this.classList.toggle("active");

                // collect all active chip values for hidden input
                let selected = [];
                customization.querySelectorAll(".chip.active").forEach(c => {
                    selected.push(c.dataset.value);
                });
                hiddenInput.value = selected.join(", ");

                checkForAllergens(); // re-run allergen check on click
            });
        });
    }

    function checkForAllergens() {
        let foundMessages = [];

        // ✅ Check active chips
        customization.querySelectorAll(".chip.active").forEach(chip => {
            for (let cond of userConditions) {
                if (riskyItems[cond] && riskyItems[cond].includes(chip.dataset.value.toLowerCase())) {
                    foundMessages.push(`⚠️ ${chip.dataset.value} is not recommended for ${cond.replace("_"," ")}.`);
                }
            }
        });

        // ✅ Check custom text area
        const text = customText.value.toLowerCase();
        for (let cond of userConditions) {
            if (!riskyItems[cond]) continue;
            for (let risky of riskyItems[cond]) {
                if (text.includes(risky)) {
                    foundMessages.push(`⚠️ "${risky}" in custom request may be harmful for ${cond.replace("_"," ")}.`);
                }
            }
        }

        // Show/hide warning
        if (foundMessages.length > 0) {
            warningBox.style.display = "block";
            warningBox.innerHTML = foundMessages.join("<br>");
        } else {
            warningBox.style.display = "none";
            warningBox.innerHTML = "";
        }
    }

    // Also run on typing in textarea
    customText.addEventListener("input", checkForAllergens);
});
</script>

             <?php
        // ✅ AI Suggestion goes here, still inside the loop
        $ai_suggestion = generateAISuggestion($details, $profile, $item['customizations'] ?? '');
        echo "<div class='mt-3 p-2 bg-yellow-50 rounded text-sm text-gray-700'>
                🤖 AI Suggestion: " . htmlspecialchars($ai_suggestion) . "
              </div>";

        echo "</div></div>"; // close flex-1 + cart-item
        $index++;
    }
}else {
          echo "<p>Your cart is empty.</p>";
      }
      ?>

      <?php if(!empty($_SESSION['cart'])): ?>
          <button type="submit" class="order-btn mt-4 px-6 py-2 bg-green-600 text-white rounded-lg">Proceed to Order</button>
      <?php endif; ?>
    </div> <!-- /.flex-1 -->

    <!-- RIGHT: Nutrition card -->
    <aside class="w-80 self-start sticky top-6">
      <div class="bg-white shadow-lg rounded-2xl p-6">
        <h2 class="text-lg font-bold mb-4">AI Nutrition Assistant</h2>
        <div class="flex justify-center mb-4" style="height:200px;">
          <canvas id="caloriesChart" class="w-full h-full"></canvas>
        </div>
        <p class="text-sm text-gray-600">
          Remaining Calories:
          <span class="font-bold text-green-600">
            <?= max(0, $profile['daily_calorie_target'] - $total_if_ordered) ?> kcal
          </span>
        </p>
        <p class="text-sm text-gray-600 mb-3">
          If you order this cart:
          <span class="font-bold text-blue-600"><?= $total_if_ordered ?> kcal</span> /
          Goal: <span class="font-bold"><?= $profile['daily_calorie_target'] ?> kcal</span>
        </p>
      </div>
    </aside>
  </div> <!-- /.flex -->
</form>

<style>
.warning-box {
  font-size: 14px;
}
</style>


<script>
/* -------------------------
   Health check and submit
   ------------------------- */
function checkHealthRisks() {
  const profile = <?= json_encode($profile) ?>;
  let issues = [];

  document.querySelectorAll(".cart-item").forEach((item, i) => {
    let txt = "";

    const note = item.querySelector("textarea[name^='custom_note']");
    if (note && note.value) txt += " " + note.value.toLowerCase();

    const select = item.querySelector("select[name^='addon']");
    if (select && select.value) txt += " " + select.value.toLowerCase();

    if (profile.diabetes == 1 && txt.includes("sugar"))
      issues.push("Item " + (i+1) + ": ❌ Sugar is risky for Diabetes.");
    if (profile.bp == 1 && (txt.includes("salt") || txt.includes("spice")))
      issues.push("Item " + (i+1) + ": ❌ Avoid salty/spicy food (BP).");
    if (profile.heart == 1 && (txt.includes("oil") || txt.includes("fried")))
      issues.push("Item " + (i+1) + ": ❌ Fried/oily food may affect heart.");
    if (profile.allergy_dairy == 1 && (txt.includes("milk") || txt.includes("cheese") || txt.includes("butter") || txt.includes("cream")))
      issues.push("Item " + (i+1) + ": ⚠️ Dairy allergy risk.");
    if (profile.allergy_peanut == 1 && (txt.includes("peanut") || txt.includes("nut")))
      issues.push("Item " + (i+1) + ": ⚠️ Peanut/nut allergy risk.");

    if (profile.goal && profile.goal.toLowerCase() === "weight loss" &&
        (txt.includes("fried") || txt.includes("sugar") || txt.includes("cream") ||
         txt.includes("butter") || txt.includes("cheese") || txt.includes("oil") ||
         txt.includes("sweet") || txt.includes("dessert"))) {
      issues.push("Item " + (i+1) + ": ⚠️ Not ideal for Weight Loss.");
    }
  });

  if (issues.length > 0) {
    alert("⚠️ Health Warnings:\n\n" + issues.join("\n"));
    return false;
  }
  return true;
}

document.addEventListener('DOMContentLoaded', function () {
  // attach submit handler
  const cartForm = document.getElementById('cartForm');
  if (cartForm) {
    cartForm.addEventListener("submit", function (e) {
      if (!checkHealthRisks()) e.preventDefault();
    });
  }

  // --------------------------
  // Chart (calories)
  // --------------------------
  const ctx = document.getElementById("caloriesChart").getContext("2d");
  new Chart(ctx, {
    type: "doughnut",
    data: {
      labels: ["Consumed (after cart)", "Remaining"],
      datasets: [{
        data: [
          <?= (float)$total_if_ordered ?>,
          <?= (float)max(0, $profile['daily_calorie_target'] - $total_if_ordered) ?>
        ],
        // Chart.js will choose default colors if you don't specify any
      }]
    },
    options: {
      maintainAspectRatio: false, // lets canvas fill the container height
      cutout: "70%",
      plugins: {
        legend: { position: 'top' }
      }
    }
  });
});
</script>
</body>
</html>
