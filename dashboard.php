<?php
// Database connection
$servername = "	sql306.infinityfree.com";
$username = "	if0_37298328";
$password = "czs3l52Q5l";
$dbname = "if0_37298328_control";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user and order data
$sql = "
    SELECT users.id as user_id, users.first_name, users.last_name, users.phone_number, users.governorate, 
           order_items.id as order_id, order_items.product_name, order_items.price, order_items.quantity, order_items.total_price
    FROM users
    JOIN order_items ON users.id = order_items.user_id
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Dashboard</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 90%;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .user-section {
            background-color: #e3f2fd;
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 10px;
        }
        .user-header {
            background-color: #2196F3;
            color: white;
            padding: 10px;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .btn {
            padding: 8px 16px;
            margin: 5px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
        }
        .btn-edit {
            background-color: #f39c12;
        }
        .btn-delete {
            background-color: #e74c3c;
        }
        .btn:hover {
            opacity: 0.9;
        }
        .form-container {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            border: 1px solid #ddd;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            z-index: 1000;
        }
        .form-container h2 {
            margin-top: 0;
        }
        .form-container input, .form-container select, .form-container button {
            display: block;
            margin: 10px 0;
            width: 100%;
            padding: 8px;
        }
        .form-container button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        .form-container button.cancel {
            background-color: #e74c3c;
        }
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Orders Dashboard</h1>

    <?php
    if ($result->num_rows > 0) {
        $currentUserId = null;
        $bgColor = ['#ffebee', '#e8f5e9', '#e3f2fd']; // Color options for different users

        while ($row = $result->fetch_assoc()) {
            // Change user section background color
            if ($currentUserId !== $row['user_id']) {
                if ($currentUserId !== null) {
                    echo '</table>'; // Close previous user's table
                    echo '</div>'; // Close previous user's section
                }
                $currentUserId = $row['user_id'];
                $colorIndex = $currentUserId % count($bgColor); // Rotate background colors

                echo "<div class='user-section' style='background-color: {$bgColor[$colorIndex]}'>";
                echo "<div class='user-header'>
                        <strong>{$row['first_name']} {$row['last_name']}</strong> 
                        <span style='float:right;'>Phone: {$row['phone_number']}, Governorate: {$row['governorate']}</span>
                      </div>";
                echo "<table>
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>";
            }

            // Display order items for each user
            echo "<tr>
                    <td>{$row['product_name']}</td>
                    <td>{$row['price']}</td>
                    <td>{$row['quantity']}</td>
                    <td>{$row['total_price']}</td>
                    <td>
                        <button class='btn btn-edit' onclick='editProduct({$row['order_id']}, \"{$row['product_name']}\", {$row['price']}, {$row['quantity']})'>Edit</button>
                        <button class='btn btn-delete' onclick='deleteProduct({$row['order_id']})'>Delete</button>
                    </td>
                  </tr>";
        }
        echo '</table>'; // Close last user's table
        echo '</div>'; // Close last user's section
    } else {
        echo "<p>No orders found</p>";
    }
    $conn->close();
    ?>

</div>

<div class="overlay" id="overlay"></div>

<div class="form-container" id="form-container">
    <h2 id="form-title">Edit Product</h2>
    <form id="product-form">
        <input type="hidden" id="order-id">
        <label for="product-name">Product Name:</label>
        <input type="text" id="product-name" required>
        <label for="price">Price:</label>
        <input type="number" id="price" step="0.01" required>
        <label for="quantity">Quantity:</label>
        <input type="number" id="quantity" required>
        <button type="submit">Save Changes</button>
        <button type="button" class="cancel" onclick="closeForm()">Cancel</button>
    </form>
</div>

<script>
    function editProduct(orderId, productName, price, quantity) {
        document.getElementById('form-title').innerText = 'Edit Product';
        document.getElementById('order-id').value = orderId;
        document.getElementById('product-name').value = productName;
        document.getElementById('price').value = price;
        document.getElementById('quantity').value = quantity;
        document.getElementById('form-container').style.display = 'block';
        document.getElementById('overlay').style.display = 'block';
    }

    function deleteProduct(orderId) {
        if (confirm("Are you sure you want to delete this product?")) {
            // Send a request to delete the product
            fetch('delete_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: orderId }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Product deleted successfully');
                    location.reload();
                } else {
                    alert('Error deleting product');
                }
            });
        }
    }

    document.getElementById('product-form').addEventListener('submit', function(event) {
        event.preventDefault();

        const orderId = document.getElementById('order-id').value;
        const productName = document.getElementById('product-name').value;
        const price = document.getElementById('price').value;
        const quantity = document.getElementById('quantity').value;

        fetch('edit_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: orderId,
                product_name: productName,
                price: price,
                quantity: quantity
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Product updated successfully');
                location.reload();
            } else {
                alert('Error updating product');
            }
        });
    });

    function closeForm() {
        document.getElementById('form-container').style.display = 'none';
        document.getElementById('overlay').style.display = 'none';
    }
</script>

</body>
</html>
