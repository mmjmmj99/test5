<?php
header('Content-Type: application/json');

// Get the input data from the request
$data = json_decode(file_get_contents('php://input'), true);

// Check if data is valid
if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// Database connection details
$servername = "sql101.infinityfree.com";
$username = "if0_37306461";
$password = "E2FDuN9utI";
$dbname = "if0_37306461_end";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]);
    exit;
}

// Insert user information into `users` table
$stmt = $conn->prepare("INSERT INTO users (first_name, last_name, phone_number, governorate) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $data['firstName'], $data['lastName'], $data['phoneNumber'], $data['governorate']);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'User insert failed: ' . $stmt->error]);
    $stmt->close();
    $conn->close();
    exit;
}

$userId = $stmt->insert_id; // Get the ID of the inserted user
$stmt->close();

// Insert order items into `order_items` table
$stmt = $conn->prepare("INSERT INTO order_items (user_id, product_name, price, quantity, total_price) VALUES (?, ?, ?, ?, ?)");

foreach ($data['items'] as $item) {
    $total_price = $item['price'] * $item['quantity'];
    $stmt->bind_param("isdds", $userId, $item['name'], $item['price'], $item['quantity'], $total_price);
    
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Order items insert failed: ' . $stmt->error]);
        $stmt->close();
        $conn->close();
        exit;
    }
}

$stmt->close();
$conn->close();

echo json_encode(['success' => true, 'message' => 'Order placed successfully!']);
?>
