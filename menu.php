<?php
include 'includes/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Explore Menu</title>
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <!-- Fonts & Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest"></script>

  <style>
    :root {
      --bg: #f9f9f9;
      --card-bg: #ffffff;
      --text: #333;
      --text-light: #555;
      --shadow: rgba(0, 0, 0, 0.05);
    }
    .dark-mode {
      --bg: #1e1e1e;
      --card-bg: #2a2a2a;
      --text: #f1f1f1;
      --text-light: #d0d0d0;
      --shadow: rgba(0, 0, 0, 0.3);
    }

    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: 'Inter', sans-serif;
      background: var(--bg);
      color: var(--text);
      transition: background 0.3s, color 0.3s;
    }

    .top-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 16px 20px;
      background: var(--card-bg);
      box-shadow: 0 2px 5px var(--shadow);
      position: sticky;
      top: 0;
      z-index: 10;
    }
    .top-bar h2 {
      margin: 0;
      font-size: 22px;
    }

    .icons {
      display: flex;
      gap: 18px;
    }
    .icons .icon {
      width: 24px;
      height: 24px;
      cursor: pointer;
      color: var(--text);
      transition: transform 0.25s, color 0.25s;
      position: relative;
    }
    .icons .icon:hover {
      transform: scale(1.25) rotate(5deg);
      color: var(--text-light);
    }
    .icon[data-tooltip]:hover::after {
      content: attr(data-tooltip);
      position: absolute;
      bottom: -35px;
      left: 50%;
      transform: translateX(-50%);
      background: var(--card-bg);
      color: var(--text);
      padding: 4px 8px;
      font-size: 12px;
      border-radius: 6px;
      box-shadow: 0 2px 6px var(--shadow);
      white-space: nowrap;
    }

    .container {
      padding: 20px;
    }

    .quote {
      font-size: 18px;
      text-align: center;
      color: var(--text-light);
      margin: 15px 20px;
      font-style: italic;
    }

    .section-title {
      font-size: 20px;
      margin: 20px 0 12px;
      color: var(--text-light);
    }

    /* Horizontal category cards */
    .category-scroll {
      display: flex;
      justify-content: center;
      overflow-x: auto;
      gap: 20px;
      padding-bottom: 10px;
      scroll-behavior: smooth;
    }
    .category-card {
      flex-shrink: 0;
      width: 160px;
      background: var(--card-bg);
      border-radius: 14px;
      box-shadow: 0 2px 12px var(--shadow);
      cursor: pointer;
      transition: transform 0.25s;
      text-align: center;
      padding: 12px;
    }
    .category-card:hover {
      transform: scale(1.07);
    }
    .category-card img {
      width: 100%;
      height: 110px;
      object-fit: cover;
      border-radius: 10px;
    }
    .category-card p {
      margin: 8px 0 0;
      font-size: 17px;
      font-weight: 600;
    }
    .category-scroll::-webkit-scrollbar {
      display: none;
    }

    .suggestions {
      display: flex;
      overflow-x: auto;
      gap: 18px;
      padding-bottom: 10px;
      scroll-behavior: smooth;
    }
    .suggestion-card {
      flex-shrink: 0;
      min-width: 140px;
      background: var(--card-bg);
      border-radius: 12px;
      padding: 10px;
      box-shadow: 0 2px 8px var(--shadow);
      text-align: center;
      transition: transform 0.25s;
    }
    .suggestion-card:hover {
      transform: scale(1.05);
    }
    .suggestion-card img {
      width: 100%;
      height: 100px;
      object-fit: cover;
      border-radius: 10px;
    }
    .suggestion-card p {
      margin: 8px 0 0;
      font-size: 14px;
    }
    .offer-badge {
  position: absolute;
  top: 10px;
  left: 10px;
  background: #ff3e3e;
  color: white;
  font-size: 12px;
  padding: 4px 8px;
  border-radius: 6px;
  font-weight: bold;
}
.suggestion-card, .category-card {
  position: relative;
}
@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}
.suggestion-card, .category-card {
  animation: fadeInUp 0.5s ease forwards;
}
#theme-toggle:hover {
  box-shadow: 0 0 8px rgba(255, 220, 100, 0.7);
  border-radius: 50%;
}
.breakfast-bg { background: linear-gradient(135deg, #fbc2eb 0%, #a6c1ee 100%); }
.lunch-bg     { background: linear-gradient(135deg, #f6d365 0%, #fda085 100%); }
.snacks-bg    { background: linear-gradient(135deg, #e0c3fc 0%, #8ec5fc 100%); }
.dinner-bg {background: linear-gradient(135deg, #fbc687 0%, #f7797d 100%);}
.drinks-bg {background: linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%);}
.sticky-offer {
  position: fixed;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  background: #ff6f61;
  color: white;
  padding: 10px 20px;
  font-size: 14px;
  border-radius: 8px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.2);
  z-index: 99;
  animation: fadeInUp 0.6s ease-in-out;
}
@keyframes smoothFadeSlide {
    from {
        opacity: 0;
        transform: translateX(30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}
.fade-line {
    opacity: 0;
    animation: smoothFadeSlide 0.8s ease forwards;
    animation-fill-mode: forwards;
}
</style>
</head>
<body>

<!-- Top Bar -->
<div class="top-bar">
  <h2>Explore Menu</h2>
  <div class="icons">
    <i data-lucide="shopping-cart" class="icon" data-tooltip="Cart" onclick="window.location.href='cart.php'"></i>
    <i data-lucide="user-circle" class="icon" data-tooltip="Profile" onclick="window.location.href='pages/profile.php'"></i>
    <i data-lucide="sun" class="icon" id="theme-toggle" data-tooltip="Toggle Theme" onclick="toggleTheme()"></i>
  </div>
</div>
<!-- Quote
<div class="quote">
  "We promote healthy habits and delicious food choices that nourish your body and soul."
</div>-->
<!-- Content -->
<div class="container">

  <!-- Categories -->
  <div class="section-title">Categories🍴</div>
  <div class="category-scroll">
    <div class="category-card breakfast-bg" onclick="navigateTo('breakfast.php')">
      <img src="images/breakfast.jpg" alt="Breakfast">
      <p>Breakfast</p>
    </div>
    <div class="category-card lunch-bg" onclick="navigateTo('lunch.php')">
      <img src="images/lunch.jpg" alt="Lunch">
      <p>Lunch</p>
    </div>
    <div class="category-card snacks-bg" onclick="navigateTo('snacks.php')">
      <img src="images/snacks.jpg" alt="Snacks">
      <p>Snacks</p>
    </div>
    <div class="category-card dinner-bg" onclick="navigateTo('dinner.php')">
      <img src="images/dinner.jpg" alt="Dinner">
      <p>Dinner</p>
    </div>
    <div class="category-card drinks-bg" onclick="navigateTo('drinks.php')">
      <img src="images/drinks.jpg" alt="Drinks">
      <p>Drinks</p>
    </div>
  </div>
<div style="position: relative; height: 330px; margin-bottom: 50px; overflow: hidden;">
    <!-- Staggered text lines -->
    <div style="
        position: absolute;
        top: 50%;
        right: 300px;
        transform: translateY(-50%);
        font-family: 'Poppins', sans-serif;
        font-size: 22px;
        font-weight: 500;
        color: #2d3142;
        max-width: 500px;
        line-height: 1.8;
    ">
        <div class="fade-line" style="animation-delay: 0.2s; font-size: 30px; font-family: 'Pacifico', cursive; color: #007f5f;">
            Eat the food that feeds your soul 🍽️
        </div>
        <div class="fade-line" style="animation-delay: 1s; color: #0081a7;">❤️ Your health is our priority</div>
        <div class="fade-line" style="animation-delay: 1.8s; color: #00afb9;">😋 Delicious dishes made with love</div>
        <div class="fade-line" style="animation-delay: 2.6s; color: #4cc9f0;">💰 Pocket-friendly prices every day</div>
        <div class="fade-line" style="animation-delay: 3.4s; color: #7209b7;">🎁 Exciting offers just for you!</div>
    </div>

    <!-- Right-side Image -->
    <img src="images/chef.jpg" alt="Chef Banner" style="
        position: absolute;
        top: 50%;
        right: 5%;
        transform: translateY(-50%);
        height: 250px;
        border-radius: 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    ">
</div>
  <!-- Suggestions -->
  <div class="section-title">Items You Might Like😍😋</div>
  <div class="suggestions">
    <div class="suggestion-card">
      <img src="images/dry_fruit_lassi.jpg" alt="Dry Fruit Lassi">
      <p>Dry Fruit Lassi</p>
    </div>
    <div class="suggestion-card">
      <img src="images/pongal.jpg" alt="Pongal">
      <p>Pongal</p>
    </div>
    <div class="suggestion-card">
      <img src="images/millet_kichidi.jpg" alt="Millet Khichdi">
      <p>Millet Khichdi</p>
    </div>
    <div class="suggestion-card">
      <img src="images/millet_sandwich.jpg" alt="Millet Sandwich">
      <p>Millet Sandwich</p>
    </div>
  </div>
  <div class="offer-badge">₹50 OFF</div>
</div>
<div class="section-title">🔥 Hot Deals Today</div>
<div class="suggestions">
  <div class="suggestion-card">
    <img src="images/millet_cutlets.jpg" alt="Millet Cutlets">
    <div class="offer-badge">20% OFF</div>
    <p>Millet Cutlets</p>
  </div>
  <div class="suggestion-card">
    <img src="images/millet_meals.jpg" alt="Smoothie">
    <div class="offer-badge">₹30 OFF</div>
    <p>Millet Meals</p>
  </div>
</div>
<i data-lucide="heart" class="icon" data-tooltip="Favorites ❤️"></i>
<i data-lucide="gift" class="icon" data-tooltip="Offers 🎁"></i>
<div class="sticky-offer">
  🎁 Use code <b>HEALTH20</b> & get 20% OFF on orders above ₹199!
</div>


<!-- Scripts -->
<script>
  lucide.createIcons();

  function navigateTo(page) {
    window.location.href = page;
  }

  const body = document.body;
  const themeIcon = document.getElementById('theme-toggle');
  const THEME_KEY = 'preferredTheme';

  (function () {
    const saved = localStorage.getItem(THEME_KEY);
    if (saved === 'dark') {
      body.classList.add('dark-mode');
      switchToMoon();
    }
  })();

  function toggleTheme() {
    body.classList.toggle('dark-mode');
    const isDark = body.classList.contains('dark-mode');
    if (isDark) {
      switchToMoon();
      localStorage.setItem(THEME_KEY, 'dark');
    } else {
      switchToSun();
      localStorage.setItem(THEME_KEY, 'light');
    }
  }

  function switchToMoon() {
    themeIcon.setAttribute('data-lucide', 'moon');
    lucide.createIcons();
  }

  function switchToSun() {
    themeIcon.setAttribute('data-lucide', 'sun');
    lucide.createIcons();
  }
</script>
</body>
</html>

