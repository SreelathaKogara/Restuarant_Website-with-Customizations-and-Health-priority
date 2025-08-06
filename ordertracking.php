<?php
$dish = $_GET['dish'] ?? '';
$price = $_GET['price'] ?? '';
$restaurant = $_GET['restaurant'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Your Order | FoodZone</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #fffaf5;
            color: #333;
            padding: 20px;
        }
        .container {
            max-width: 700px;
            margin: auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            padding: 30px;
            text-align: center;
        }
        h2 {
            font-size: 28px;
            margin-bottom: 15px;
            color: #111;
        }
        p {
            font-size: 16px;
            color: #555;
        }
        .order-info {
            font-size: 18px;
            margin: 15px 0 30px;
        }
        .progress-container {
            margin: 30px 0;
        }
        .progress-bar {
            background: #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
            height: 10px;
        }
        .progress {
            width: 0%;
            height: 100%;
            background: linear-gradient(to right, #ff6600, #ff3300);
            transition: width 1s ease-in-out;
        }
        .status {
            margin-top: 15px;
            font-weight: bold;
            color: #ff3300;
            font-size: 18px;
        }
        .steps {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-top: 20px;
        }
        .steps::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            width: 100%;
            height: 2px;
            background: #ddd;
            z-index: 0;
        }
        .step {
            position: relative;
            background: #ccc;
            color: #fff;
            height: 40px;
            width: 40px;
            line-height: 40px;
            border-radius: 50%;
            font-weight: bold;
            z-index: 1;
        }
        .step.active {
            background: #ff3300;
        }
        .step-labels {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            font-size: 12px;
            color: #666;
        }
        @media (max-width: 500px) {
            h2 { font-size: 22px; }
            .order-info { font-size: 16px; }
            .status { font-size: 16px; }
            .step-labels { font-size: 10px; }
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Order Tracking</h2>
    <p class="order-info">Order for <strong><?= htmlspecialchars($dish) ?></strong> (₹<?= htmlspecialchars($price) ?>) from <strong><?= htmlspecialchars($restaurant) ?></strong></p>

    <div class="progress-container">
        <div class="steps">
            <div class="step" id="step-1">1</div>
            <div class="step" id="step-2">2</div>
            <div class="step" id="step-3">3</div>
            <div class="step" id="step-4">4</div>
            <div class="step" id="step-5">5</div>
        </div>
        <div class="step-labels">
            <span>Order Received</span>
            <span>Preparing</span>
            <span>Out for Delivery</span>
            <span>Almost There</span>
            <span>Delivered</span>
        </div>
        <div class="progress-bar">
            <div class="progress" id="progress-bar"></div>
        </div>
        <div class="status" id="status-text">Order Received</div>
    </div>
</div>

<script>
    function startTracking() {
        const steps = [
            "Order Received",
            "Preparing Your Food",
            "Out for Delivery",
            "Almost There",
            "Delivered"
        ];
        let index = 0;
        const bar = document.getElementById("progress-bar");
        const status = document.getElementById("status-text");
        const stepDots = document.querySelectorAll(".step");

        const interval = setInterval(() => {
            if (index < steps.length) {
                let progress = (index + 1) * 25;
                bar.style.width = progress + "%";
                status.innerText = steps[index];
                stepDots[index].classList.add("active");
                index++;
            } else {
                clearInterval(interval);
            }
        }, 2500);
    }

    window.onload = startTracking;
</script>
</body>
</html>
