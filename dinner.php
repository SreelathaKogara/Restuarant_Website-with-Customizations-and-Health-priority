<?php
// Include database connection
include 'includes/db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dinner Menu</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f5f5f5;
    }
     .header {
      background-color: #28a745;
      padding: 20px;
      color: white;
      font-size: 24px;
      text-align: center;
      position: sticky;
      top: 0;
      z-index: 1000;
    }
    .header a {
      color: white;
      text-decoration: none;
      font-size: 18px;
    }
    .header-links {
      display: flex;
      gap: 20px;
    }
    .header a {
      color: white;
      text-decoration: none;
      font-size: 16px;
    }
    .card-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      padding: 20px;
    }
    .card {
      width: 260px;
      background: white;
      border-radius: 12px;
      margin: 15px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      position: relative;
    }
    .card img {
      width: 100%;
      height: 160px;
      object-fit: cover;
    }
    .card-content {
      padding: 15px;
    }
    .card-content h4 {
      margin: 0 0 5px 0;
      font-size: 18px;
    }
    .rating {
      color: green;
      font-size: 14px;
    }
    .delivery-time {
      font-size: 14px;
      color: #777;
      margin-top: 5px;
    }
    .price {
      margin-top: 10px;
      font-weight: bold;
    }
    .offer-badge {
      position: absolute;
      top: 10px;
      left: 10px;
      background-color: #ff4d4f;
      color: white;
      padding: 5px 10px;
      border-radius: 20px;
      font-size: 12px;
    }
    .heart-icon {
      position: absolute;
      top: 10px;
      right: 10px;
      color: white;
      background: rgba(0,0,0,0.4);
      border-radius: 50%;
      padding: 8px;
      font-size: 14px;
      cursor: pointer;
    }
    .add-to-cart {
      background-color: #ff5722;
      color: white;
      text-align: center;
      padding: 10px;
      font-weight: bold;
      cursor: pointer;
    }
  </style>
</head>
<body>

<div class="header">
  Dinner Menu
  <a href="cart.php" title="View Cart" style="color: white; float: right; margin-right: 20px;">
    <i class="fas fa-shopping-cart"></i>
  </a>
  <a href="menu.php" style="color: white; float: right; margin-right: 20px;">Menu</a>
  <a href="index.php" style="color: white; float: right; margin-right: 20px;">Home</a>
</div>

<div class="card-container">
<?php
$query = "SELECT name, dish_image, restaurant_image, price, delivery_time, rating FROM menu WHERE category='Dinner'";
$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    $name = $row['name'];
    $image = $row['dish_image'];
    $restaurant = $row['restaurant_image'];
    $price = $row['price'];
    $delivery = $row['delivery_time'];
    $rating = $row['rating'];

    $offerText = '';
    if ($price >= 500) {
        $offerText = "FLAT ₹100 OFF";
    } elseif ($price >= 250) {
        $offerText = "FLAT ₹66 OFF";
    } elseif ($price >= 150) {
        $offerText = "FLAT ₹30 OFF";
    }

    echo "<div class='card'>";
    echo "<img src='{$image}' alt='{$name}'>";
    if ($offerText !== '') {
        echo "<div class='offer-badge'>{$offerText}</div>";
    }
    echo "<div class='heart-icon'><i class='fas fa-heart'></i></div>";
    echo "<div class='card-content'>";
    echo "<h4>{$name}</h4>";
    echo "<div class='rating'>⭐ {$rating}</div>";
    echo "<div class='delivery-time'>⏱️ {$delivery} mins</div>";
    echo "<div class='price'>₹{$price}</div>";
    echo "</div>";
    echo "<div class='add-to-cart' onclick=\"addToCart('{$name}', '{$price}')\">Add to Cart</div>";
    echo "</div>";
}
?>
</div>

<script>
  function addToCart(name, price) {
    window.location.href = `cart.php?dish=${encodeURIComponent(name)}&price=${encodeURIComponent(price)}`;
  }
</script>
</body>
</html>
