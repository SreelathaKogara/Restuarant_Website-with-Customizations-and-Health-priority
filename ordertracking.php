<?php
session_start();
include 'includes/db_connect.php';

// Get order details from URL (sent by ordernow.php)
$finalPrice = isset($_GET['price']) ? (float)$_GET['price'] : null;
$paymentMethod = $_GET['method'] ?? '';
$discount = isset($_GET['discount']) ? (float)$_GET['discount'] : 0;

if (!$finalPrice || !isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    die("<p style='text-align:center;font-size:18px;color:red;'>❌ Invalid or missing order.</p>");
}

$cart = $_SESSION['cart'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Tracking | FoodZone</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
body { background: #f9fafb; font-family: 'Segoe UI', sans-serif; padding: 20px; }
.container { max-width: 700px; margin:auto; background:#fff; border-radius:16px; padding:30px; box-shadow:0 4px 16px rgba(0,0,0,0.1);}
h2 { text-align:center; font-size:28px; margin-bottom:20px; color:#111; }
ul li { margin-bottom:5px; }
.steps { display:flex; justify-between; position:relative; margin-top:30px; padding:0 10px;}
.step { flex:1; text-align:center; position:relative; font-size:14px; color:#888;}
.step::after { content: ''; position:absolute; top:10px; right:0; width:100%; height:3px; background:#ddd; z-index:-1;}
.step:last-child::after { display:none; }
.step.active { color:#ff3300; font-weight:bold;}
.step.active::after { background:#ff3300; }
.step span { display:block; margin-top:6px; font-size:20px; }
.order-summary { margin-bottom:20px; padding:15px; background:#f0f8ff; border-radius:10px;}
.order-summary p { margin:5px 0; font-size:16px; }
</style>
</head>
<body>
<div class="container">
<h2>🛒 Track Your Order</h2>

<!-- Order summary -->
<div class="order-summary">
    <h3 class="font-semibold mb-2">📝 Your Order</h3>
    <ul class="list-disc list-inside text-gray-700">
        <?php foreach ($cart as $item): ?>
            <li><?= htmlspecialchars($item['name']) ?> — ₹<?= htmlspecialchars(number_format($item['price'],2)) ?></li>
        <?php endforeach; ?>
    </ul>
    <?php if($discount > 0): ?>
        <p class="text-blue-600">Coupon Applied: <strong>₹<?= number_format($discount,2) ?></strong> discount</p>
    <?php endif; ?>
    <p class="font-semibold text-green-600 text-lg">Total Paid: ₹<?= number_format($finalPrice,2) ?></p>
    <p>Payment Method: <strong><?= htmlspecialchars(strtoupper($paymentMethod)) ?></strong></p>
</div>

<!-- Order status steps -->
<div class="steps">
    <div class="step" id="step1">👨‍🍳<span>Order Received</span></div>
    <div class="step" id="step2">🍳<span>Preparing Your Food</span></div>
    <div class="step" id="step3">🚴<span>Out for Delivery</span></div>
    <div class="step" id="step4">📍<span>Almost There</span></div>
    <div class="step" id="step5">🎉<span>Delivered</span></div>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const steps = [
        document.getElementById('step1'),
        document.getElementById('step2'),
        document.getElementById('step3'),
        document.getElementById('step4'),
        document.getElementById('step5')
    ];
    let index = 0;

    function advanceStep() {
        if(index < steps.length) {
            steps[index].classList.add('active');
            index++;
            if(index < steps.length) setTimeout(advanceStep, 10000); // auto advance every 10s
        }
    }

    advanceStep();
});
</script>
</body>
</html>
