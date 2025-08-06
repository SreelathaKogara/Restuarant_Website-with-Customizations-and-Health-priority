<?php
session_start();
include 'includes/db_connect.php';

// Handle remove request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_index'])) {
    $removeIndex = (int)$_POST['remove_index'];
    if (isset($_SESSION['cart'][$removeIndex])) {
        unset($_SESSION['cart'][$removeIndex]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // reindex
    }
}

// Handle empty cart
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "<h2 style='text-align:center;'>🛒 Your cart is empty.</h2>";
    exit;
}

$total = 0;
$itemsWithPrice = [];

foreach ($_SESSION['cart'] as $index => $item) {
    $dishName = $item['name'];
    $stmt = $conn->prepare("SELECT price FROM menu WHERE name = ?");
    $stmt->bind_param("s", $dishName);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result) {
        $price = $result['price'];
        $total += $price;
        $itemsWithPrice[] = ['name' => $dishName, 'price' => $price, 'index' => $index];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Now</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f1f1f1;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 750px;
            margin: auto;
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        }
        h2, h3 {
            margin-bottom: 15px;
            color: #333;
        }
        .item-list {
            margin: 20px 0;
        }
        .item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px dashed #ccc;
            padding: 12px 0;
        }
        .item span {
            font-size: 16px;
        }
        .item .price {
            color: #ff5722;
            font-weight: bold;
        }
        .item .remove {
            color: #e53935;
            cursor: pointer;
            font-size: 14px;
            margin-left: 10px;
            border: none;
            background: none;
        }
        .total {
            font-weight: bold;
            font-size: 18px;
            text-align: right;
            margin-top: 10px;
        }
        button {
            background: #ff5722;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 20px;
        }
        .coupon-button {
            background-color: #007bff;
        }
        .coupon-section {
            display: none;
            background: #f9f9f9;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
        }
        .coupon-card {
            background: white;
            border-left: 5px solid #ff5722;
            margin: 10px 0;
            padding: 10px 15px;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .coupon-card h4 {
            margin: 0;
        }
        .coupon-card small {
            color: #555;
        }
        .coupon-card button {
            background-color: #28a745;
            font-size: 14px;
            padding: 6px 12px;
        }
        select, input {
            padding: 10px;
            width: 100%;
            margin-top: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        .upi-icons {
            display: none;
            justify-content: space-around;
            margin-top: 10px;
        }
        .upi-icons img {
            width: 60px;
            cursor: pointer;
            border: 2px solid transparent;
            border-radius: 8px;
            padding: 5px;
        }
        .upi-icons img.selected {
            border-color: #007bff;
            background: #e7f1ff;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>🧾 Order Summary</h2>
    <div class="item-list">
        <?php foreach ($itemsWithPrice as $item): ?>
            <div class="item">
                <span><?= htmlspecialchars($item['name']) ?></span>
                <span>
                    ₹<?= $item['price'] ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="remove_index" value="<?= $item['index'] ?>">
                        <button class="remove" title="Remove this item">🗑️</button>
                    </form>
                </span>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="total">Total: ₹<span id="total-price"><?= $total ?></span></div>

    <button type="button" class="coupon-button" onclick="toggleCouponSection()">Apply Coupon</button>

    <div class="coupon-section" id="coupon-section">
        <input type="text" id="coupon-code-input" placeholder="Enter Coupon Code">
        <button onclick="applyManualCoupon()">Apply</button>
        <div id="coupon-list">
            <div class="coupon-card">
                <div>
                    <h4>FOODIE50</h4>
                    <small>Get ₹50 off on orders above ₹200</small>
                </div>
                <button onclick="applyCoupon('FOODIE50', 50, 200)">APPLY</button>
            </div>
            <div class="coupon-card">
                <div>
                    <h4>NEWUSER100</h4>
                    <small>Flat ₹100 off on ₹300+</small>
                </div>
                <button onclick="applyCoupon('NEWUSER100', 100, 300)">APPLY</button>
            </div>
            <div class="coupon-card">
                <div>
                    <h4>FESTIVE20</h4>
                    <small>₹20 off above ₹100</small>
                </div>
                <button onclick="applyCoupon('FESTIVE20', 20, 100)">APPLY</button>
            </div>
            <div class="coupon-card">
                <div>
                    <h4>DINNER75</h4>
                    <small>Save ₹75 on ₹250+</small>
                </div>
                <button onclick="applyCoupon('DINNER75', 75, 250)">APPLY</button>
            </div>
            <div class="coupon-card">
                <div>
                    <h4>MEGA10</h4>
                    <small>Flat ₹10 off (no minimum)</small>
                </div>
                <button onclick="applyCoupon('MEGA10', 10, 0)">APPLY</button>
            </div>
            <div class="coupon-card">
                <div>
                    <h4>LUNCH60</h4>
                    <small>₹60 off on ₹299+</small>
                </div>
                <button onclick="applyCoupon('LUNCH60', 60, 299)">APPLY</button>
            </div>
        </div>
    </div>

    <h3>Select Payment Method</h3>
    <select id="payment-method" onchange="handlePaymentChange()">
        <option value="">-- Select Payment Method --</option>
        <option value="cod">Cash on Delivery</option>
        <option value="credit">Credit Card</option>
        <option value="upi">UPI</option>
    </select>

    <div id="upi-section">
        <div class="upi-icons" id="upi-icons">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/22/PhonePe_Logo.svg/512px-PhonePe_Logo.svg.png" onclick="selectUpi(this, 'phonepe')" title="PhonePe">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c7/Google_Pay_Logo.svg/512px-Google_Pay_Logo.svg.png" onclick="selectUpi(this, 'gpay')" title="GPay">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/55/Paytm_logo.png/512px-Paytm_logo.png" onclick="selectUpi(this, 'paytm')" title="Paytm">
        </div>
        <input type="text" id="upi-id" placeholder="Enter your UPI ID" style="margin-top:10px; display: none;">
    </div>

    <button onclick="proceedToTracking()">Proceed to Track Your Order</button>
</div>

<script>
    let couponVisible = false;
    let selectedDiscount = 0;
    let selectedUpi = '';

    function toggleCouponSection() {
        couponVisible = !couponVisible;
        document.getElementById('coupon-section').style.display = couponVisible ? 'block' : 'none';
    }

    function applyCoupon(code, discount, minAmount) {
        const original = parseFloat(<?= json_encode($total) ?>);
        if (original >= minAmount) {
            selectedDiscount = discount;
            document.getElementById('total-price').innerText = original - discount;
            alert(`Coupon "${code}" applied! You saved ₹${discount}`);
        } else {
            alert(`Coupon valid on orders above ₹${minAmount}`);
        }
    }

    function applyManualCoupon() {
        const code = document.getElementById('coupon-code-input').value.trim().toUpperCase();
        switch (code) {
            case 'FOODIE50': applyCoupon('FOODIE50', 50, 200); break;
            case 'NEWUSER100': applyCoupon('NEWUSER100', 100, 300); break;
            default: alert('Invalid coupon code');
        }
    }

    function handlePaymentChange() {
        const method = document.getElementById('payment-method').value;
        const upiIcons = document.getElementById('upi-icons');
        const upiId = document.getElementById('upi-id');
        if (method === 'upi') {
            upiIcons.style.display = 'flex';
            upiId.style.display = 'block';
        } else {
            upiIcons.style.display = 'none';
            upiId.style.display = 'none';
        }
    }

    function selectUpi(el, provider) {
        selectedUpi = provider;
        document.querySelectorAll('.upi-icons img').forEach(img => img.classList.remove('selected'));
        el.classList.add('selected');
    }

    function proceedToTracking() {
        const method = document.getElementById('payment-method').value;
        if (!method) return alert('Please select a payment method.');

        let url = `ordertracking.php?price=${parseFloat(<?= json_encode($total) ?>) - selectedDiscount}&method=${method}&discount=${selectedDiscount}`;

        if (method === 'upi') {
            const upiId = document.getElementById('upi-id').value.trim();
            if (!upiId || !selectedUpi) return alert('Please select UPI provider and enter UPI ID.');
            url += `&upiProvider=${selectedUpi}&upiId=${encodeURIComponent(upiId)}`;
        }

        window.location.href = url;
    }
</script>
</body>
</html>
