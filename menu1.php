<?php
include 'includes/db_connect.php'; // Ensure this connects to your database

$query = "SELECT * FROM menu ORDER BY category";
$result = mysqli_query($conn, $query);

$menu = [];
while ($row = mysqli_fetch_assoc($result)) {
    $menu[$row['category']][] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Customize Your Healthy Food</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .hero {
            text-align: center;
            padding: 80px 20px;
            background: linear-gradient(to right, #28a745, #218838);
            color: white;
        }
        .btn-custom {
            background-color: #ffc107;
            color: black;
            padding: 12px 25px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
        }
        .btn-custom:hover {
            background-color: #e0a800;
        }
        .navbar {
            background: white;
            box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            font-weight: bold;
            color: #28a745 !important;
        }
        .nav-link {
            font-weight: 500;
        }
        .footer {
            background-color: white;
            padding: 15px 0;
            box-shadow: 0px -2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container">
                <a class="navbar-brand" href="index.php">Healthy Food</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="menu.php">Menu</a></li>
                        <li class="nav-item"><a class="nav-link" href="pages/profile.php" id="profileLink" style="display:none;">My Profile</a></li>
                        <li class="nav-item"><a class="nav-link" href="about.php">About Us</a></li>
                        <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                        <li class="nav-item">
                            <a class="nav-link" href="cart.php">
                                <img src="images/cart.jpg" alt="Cart" style="width: 25px; height: 25px;">
                            </a>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="pages/login.php" id="loginLink">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="pages/registration.php" id="registerLink">Register</a></li>
                        <li class="nav-item"><a class="nav-link" href="pages/logout.php" id="logoutLink" style="display:none;">Logout</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <section class="hero">
            <h1>Welcome to Customize Your Healthy Food!</h1>
            <p>Your health, your choice, your way! Explore our menu and start your journey to a healthier lifestyle.</p>
            <a href="menu.php" class="btn btn-custom">Explore Menu</a>
        </section>
    </main>

    <footer class="footer text-center">
        <p>&copy; 2025 Customize Your Healthy Food. All rights reserved.</p>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var loggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
            
            var loginLink = document.getElementById("loginLink");
            var registerLink = document.getElementById("registerLink");
            var profileLink = document.getElementById("profileLink");
            var logoutLink = document.getElementById("logoutLink");

            if (loggedIn) {
                loginLink.style.display = "none";
                registerLink.style.display = "none";
                profileLink.style.display = "block";
                logoutLink.style.display = "block";
            } else {
                loginLink.style.display = "block";
                registerLink.style.display = "block";
                profileLink.style.display = "none";
                logoutLink.style.display = "none";
            }
        });
    </script>

</body>
</html>