<?php
session_start();
require '../includes/db_connect.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['order_id'])) {
    $_SESSION['error_message'] = "Invalid request.";
    header('Location: my_orders.php');
    exit;
}

$order_id = $_POST['order_id'];
$user_id = $_SESSION['user_id'];
$quantities = $_POST['quantities'] ?? [];
$remove_items = $_POST['remove_items'] ?? [];

try {
    $pdo->beginTransaction();

    // Verify the order belongs to the user and is pending
    $sql = "SELECT * FROM orders WHERE id = ? AND user_id = ? AND status = 'pending'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch();

    if (!$order) {
        throw new Exception("Order not found or cannot be edited.");
    }

    // Get current order items
    $sql_items = "SELECT product_id, quantity FROM order_items WHERE order_id = ?";
    $stmt_items = $pdo->prepare($sql_items);
    $stmt_items->execute([$order_id]);
    $current_items = $stmt_items->fetchAll(PDO::FETCH_KEY_PAIR);

    $new_total_amount = 0;

    // Process items to be removed
    foreach ($remove_items as $product_id) {
        if (isset($current_items[$product_id])) {
            // Restock the removed item
            $update_stock_sql = "UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?";
            $pdo->prepare($update_stock_sql)->execute([$current_items[$product_id], $product_id]);
            
            // Delete from order_items
            $delete_sql = "DELETE FROM order_items WHERE order_id = ? AND product_id = ?";
            $pdo->prepare($delete_sql)->execute([$order_id, $product_id]);
            
            unset($quantities[$product_id]); // Don't process it further
        }
    }

    // Process updated quantities
    foreach ($quantities as $product_id => $new_quantity) {
        $new_quantity = (int)$new_quantity;
        if ($new_quantity <= 0) {
            throw new Exception("Invalid quantity for a product.");
        }

        $old_quantity = $current_items[$product_id] ?? 0;
        $quantity_diff = $new_quantity - $old_quantity;

        // Fetch product details (price and stock)
        $product_sql = "SELECT price, stock_quantity FROM products WHERE id = ?";
        $product_stmt = $pdo->prepare($product_sql);
        $product_stmt->execute([$product_id]);
        $product = $product_stmt->fetch();

        if (!$product) {
            throw new Exception("Product not found.");
        }

        // Check if there is enough stock
        if ($quantity_diff > $product['stock_quantity']) {
            throw new Exception("Not enough stock for " . htmlspecialchars($product['name']));
        }

        // Update stock
        $update_stock_sql = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?";
        $pdo->prepare($update_stock_sql)->execute([$quantity_diff, $product_id]);

        // Update order item
        $update_item_sql = "UPDATE order_items SET quantity = ? WHERE order_id = ? AND product_id = ?";
        $pdo->prepare($update_item_sql)->execute([$new_quantity, $order_id, $product_id]);

        // Add to new total amount
        $new_total_amount += $product['price'] * $new_quantity;
    }

    // Update the order's total amount
    $update_order_sql = "UPDATE orders SET total_amount = ? WHERE id = ?";
    $pdo->prepare($update_order_sql)->execute([$new_total_amount, $order_id]);

    $pdo->commit();
    $_SESSION['success_message'] = "Order #{$order_id} has been successfully updated.";

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error_message'] = "Error updating order: " . $e->getMessage();
}

header('Location: my_orders.php');
exit;
?>
