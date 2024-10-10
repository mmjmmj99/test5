<?php
// Database connection
$servername = "	sql306.infinityfree.com";
$username = "	if0_37298328";
$password = "czs3l52Q5l";
$dbname = "if0_37298328_control";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['id']) && isset($data['product_name']) && isset($data['price']) && isset($data['quantity'])) {
    $orderId = $data['id'];
    $productName = $data['product_name'];
    $price = $data['price'];
    $quantity = $data['quantity'];
    $totalPrice = $price * $quantity;

    $sql = "UPDATE order_items SET product_name = ?, price = ?, quantity = ?, total_price = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sddi", $productName, $price, $quantity, $totalPrice, $orderId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    $stmt->close();
}

$conn->close();
?>
