<?php
session_start();
require '../includes/db_connect.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if order_id is provided via POST
if (!isset($_POST['order_id']) || !filter_var($_POST['order_id'], FILTER_VALIDATE_INT)) {
    $_SESSION['error_message'] = "Invalid request. No order specified.";
    header('Location: my_orders.php');
    exit;
}

$order_id = $_POST['order_id'];
$user_id = $_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    // Step 1: Verify the order belongs to the logged-in user and is in 'pending' status
    $sql = "SELECT * FROM orders WHERE id = ? AND user_id = ? AND status = 'pending'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch();

    // If no such order exists, redirect with an error
    if (!$order) {
        $_SESSION['error_message'] = "Order not found or it can no longer be cancelled.";
        header('Location: my_orders.php');
        $pdo->rollBack();
        exit;
    }

    // Step 2: Get all items from the order to restock them
    $sql_items = "SELECT product_id, quantity FROM order_items WHERE order_id = ?";
    $stmt_items = $pdo->prepare($sql_items);
    $stmt_items->execute([$order_id]);
    $order_items = $stmt_items->fetchAll();

    // Step 3: Update the stock quantity for each product in the order
    foreach ($order_items as $item) {
        $update_stock_sql = "UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?";
        $update_stock_stmt = $pdo->prepare($update_stock_sql);
        $update_stock_stmt->execute([$item['quantity'], $item['product_id']]);
    }

    // Step 4: Update the order's status to 'cancelled'
    $sql_cancel = "UPDATE orders SET status = 'cancelled' WHERE id = ?";
    $stmt_cancel = $pdo->prepare($sql_cancel);
    $stmt_cancel->execute([$order_id]);

    // If all operations are successful, commit the transaction
    $pdo->commit();

    $_SESSION['success_message'] = "Order #{$order_id} has been successfully cancelled.";

} catch (PDOException $e) {
    // If any error occurs, roll back the transaction to prevent partial data changes
    $pdo->rollBack();
    error_log("Order cancellation error: " . $e->getMessage());
    $_SESSION['error_message'] = "We encountered an error while cancelling your order. Please try again.";
}

// Redirect back to the orders page
header('Location: my_orders.php');
exit;
?>
