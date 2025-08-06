<?php
include 'mailhelper.php';
include 'db.php'; // your DB connection

$orderId = $_GET['order_id'];
$status = $_GET['status'];
$email = $_GET['email']; // ideally from DB

$update = "UPDATE orders SET order_status = '$status' WHERE id = $orderId";
mysqli_query($conn, $update);

switch ($status) {
    case 'Placed':
        $subject = "Your order has been placed!";
        $body = "Thank you for ordering! Your order #$orderId has been placed successfully.";
        break;
    case 'Picked':
        $subject = "Your order is being prepared!";
        $body = "Your order #$orderId has been picked up from the restaurant.";
        break;
    case 'Out for Delivery':
        $subject = "Order #$orderId is on its way!";
        $body = "Your food is out for delivery. Get ready to enjoy it soon!";
        break;
    case 'Delivered':
        $subject = "Order Delivered!";
        $body = "We hope you enjoyed your meal! Thank you for ordering from us.";
        break;
}

sendMail($email, $subject, $body);
echo "Email sent for status: $status";
?>
