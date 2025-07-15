<?php
session_start();
require '../includes/db_connect.php';
require '../includes/header.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if order_id is provided and is valid
if (!isset($_GET['order_id']) || !filter_var($_GET['order_id'], FILTER_VALIDATE_INT)) {
    $_SESSION['error_message'] = "Invalid request. No order specified.";
    header('Location: my_orders.php');
    exit;
}

$order_id = $_GET['order_id'];
$user_id = $_SESSION['user_id'];

try {
    // Fetch the order to ensure it belongs to the user and is pending
    $sql = "SELECT * FROM orders WHERE id = ? AND user_id = ? AND status = 'pending'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch();

    if (!$order) {
        $_SESSION['error_message'] = "Order not found or cannot be edited.";
        header('Location: my_orders.php');
        exit;
    }

    // Fetch the items in the order
    $sql_items = "
        SELECT oi.product_id, oi.quantity, p.name, p.price, p.image_url, p.stock_quantity
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ";
    $stmt_items = $pdo->prepare($sql_items);
    $stmt_items->execute([$order_id]);
    $order_items = $stmt_items->fetchAll();

} catch (PDOException $e) {
    error_log("Edit order page error: " . $e->getMessage());
    $_SESSION['error_message'] = "Error fetching order details.";
    header('Location: my_orders.php');
    exit;
}
?>

<main class="container my-5">
    <div class="card shadow-lg p-4 border-0 rounded-3">
        <h2 class="text-center mb-4">Edit Order #<?php echo htmlspecialchars($order_id); ?></h2>
        
        <form action="update_order.php" method="post">
            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_id); ?>">
            
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Product</th>
                            <th scope="col">Price</th>
                            <th scope="col" class="text-center">Quantity</th>
                            <th scope="col" class="text-end">Total</th>
                            <th scope="col" class="text-center">Remove</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="../images/products/<?php echo htmlspecialchars($item['image_url']); ?>" class="img-fluid rounded me-3" style="width: 60px; height: 60px; object-fit: cover;" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                        <span><?php echo htmlspecialchars($item['name']); ?></span>
                                    </div>
                                </td>
                                <td>$<?php echo htmlspecialchars($item['price']); ?></td>
                                <td class="text-center">
                                    <input type="number" name="quantities[<?php echo $item['product_id']; ?>]" class="form-control form-control-sm mx-auto" value="<?php echo htmlspecialchars($item['quantity']); ?>" min="1" max="<?php echo $item['stock_quantity'] + $item['quantity']; ?>" style="width: 80px;" required>
                                </td>
                                <td class="text-end">$<?php echo htmlspecialchars(number_format($item['price'] * $item['quantity'], 2)); ?></td>
                                <td class="text-center">
                                    <input type="checkbox" name="remove_items[]" value="<?php echo $item['product_id']; ?>" class="form-check-input">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <a href="my_orders.php" class="btn btn-secondary me-2">Go Back</a>
                <button type="submit" class="btn btn-primary">Update Order</button>
            </div>
        </form>
    </div>
</main>

<?php require '../includes/footer.php'; ?>
