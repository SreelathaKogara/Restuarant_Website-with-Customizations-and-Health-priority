<?php
session_start();
include("includes/config.php");
include("includes/db.php");

// Assuming user_id is stored in session
$user_id = $_SESSION['user_id'] ?? 1; 

// 1. Fetch health profile
$profile_sql = "SELECT * FROM user_health_profile WHERE user_id = $user_id LIMIT 1";
$profile = $conn->query($profile_sql)->fetch_assoc();

// 2. Fetch today’s intake
$today = date("Y-m-d");
$order_sql = "
    SELECT SUM(calories) AS total_calories,
           SUM(protein) AS total_protein,
           SUM(sugar) AS total_sugar,
           SUM(sodium) AS total_sodium
    FROM orders o 
    JOIN order_items oi ON o.order_id = oi.order_id
    WHERE o.user_id = $user_id AND DATE(o.order_date) = '$today'";
$intake = $conn->query($order_sql)->fetch_assoc();

// 3. Fetch available dishes
$dishes_sql = "SELECT dish_name, description, calories, protein, sugar, sodium FROM dishes LIMIT 20";
$dishes_result = $conn->query($dishes_sql);
$dishes = [];
while($row = $dishes_result->fetch_assoc()){
    $dishes[] = $row;
}

// 4. Prepare AI prompt
$prompt = "User Health Profile: ".json_encode($profile)."\n".
          "Today's Intake: ".json_encode($intake)."\n".
          "Available Dishes: ".json_encode($dishes)."\n".
          "Suggest the healthiest 2-3 dishes for the user. Explain why.";

// 5. Call OpenAI API
$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer " . OPENAI_API_KEY
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    "model" => "gpt-4o-mini",
    "messages" => [
        ["role" => "system", "content" => "You are a nutrition assistant."],
        ["role" => "user", "content" => $prompt]
    ],
    "max_tokens" => 200
]));
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
$suggestion = $result['choices'][0]['message']['content'] ?? "No suggestion available.";

// 6. Show suggestion
echo "<h2>AI Meal Suggestions</h2>";
echo "<p>$suggestion</p>";
?>
