<?php
require '../includes/db_connect.php';
session_start();

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
    $user_id = $_SESSION['user_id'];

    // Validate inputs
    if ($product_id === false || $quantity === false || $quantity < 1) {
        die("Invalid input.");
    }

    try {
        // Begin a transaction
        $pdo->beginTransaction();

        // 1. Get product price and check stock
        $stmt = $pdo->prepare("SELECT price, stock_quantity FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if (!$product || $quantity > $product['stock_quantity']) {
            // If product doesn't exist or not enough stock, roll back and die
            $pdo->rollBack();
            die("Product not found or not enough stock.");
        }

        $price_per_unit = $product['price'];
        $total_amount = $price_per_unit * $quantity;

        // 2. Insert into orders table
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount) VALUES (?, ?)");
        $stmt->execute([$user_id, $total_amount]);
        $order_id = $pdo->lastInsertId();

        // 3. Insert into order_items table
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_per_unit) VALUES (?, ?, ?, ?)");
        $stmt->execute([$order_id, $product_id, $quantity, $price_per_unit]);

        // 4. Update product stock
        $new_stock = $product['stock_quantity'] - $quantity;
        $stmt = $pdo->prepare("UPDATE products SET stock_quantity = ? WHERE id = ?");
        $stmt->execute([$new_stock, $product_id]);

        // If all queries were successful, commit the transaction
        $pdo->commit();

        // Redirect to my orders page
        header("Location: ../index.php");
        exit();

    } catch (PDOException $e) {
        // If any query fails, roll back the transaction and show an error
        $pdo->rollBack();
        die("Order placement failed: " . $e->getMessage());
    }
}
?>