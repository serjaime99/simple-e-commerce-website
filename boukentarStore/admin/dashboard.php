<?php
require '../includes/db_connect.php';
require 'includes/header.php';

// Handle order status update
if (isset($_GET['action']) && isset($_GET['order_id'])) {
    $action = $_GET['action'];
    $order_id = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);
    $allowed_actions = ['confirm' => 'confirmed', 'cancel' => 'cancelled', 'ship' => 'shipped', 'deliver' => 'delivered'];

    if ($order_id && array_key_exists($action, $allowed_actions)) {
        $status = $allowed_actions[$action];
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $order_id]);
        header("Location: dashboard.php");
        exit();
    }
}

// Fetch all orders for display
$sql = "SELECT o.id, o.order_date, o.status, o.total_amount, u.full_name, 
               GROUP_CONCAT(p.name, ' (x', oi.quantity, ')' SEPARATOR '<br>') as products
        FROM orders o
        JOIN users u ON o.user_id = u.id
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        GROUP BY o.id
        ORDER BY FIELD(o.status, 'pending', 'confirmed', 'shipped', 'delivered', 'cancelled'), o.order_date DESC";
$stmt = $pdo->query($sql);
$orders = $stmt->fetchAll();

// Fetch stats for summary cards
$total_orders = count($orders);
$pending_orders = count(array_filter($orders, fn($o) => $o['status'] === 'pending'));
$total_revenue = array_sum(array_column($orders, 'total_amount'));

function getStatusClass($status) {
    switch ($status) {
        case 'pending': return 'warning';
        case 'confirmed': return 'primary';
        case 'shipped': return 'info';
        case 'delivered': return 'success';
        case 'cancelled': return 'danger';
        default: return 'secondary';
    }
}
?>

<h1 class="mb-4">Dashboard</h1>

<!-- Summary Cards -->
<div class="row">
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title">Total Orders</h5>
                <p class="card-text fs-4"><?php echo $total_orders; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5 class="card-title">Pending Orders</h5>
                <p class="card-text fs-4"><?php echo $pending_orders; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Total Revenue</h5>
                <p class="card-text fs-4">$<?php echo number_format($total_revenue, 2); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Orders Table -->
<div class="card mt-4">
    <div class="card-header">
        <h2>All Orders</h2>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Products</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($orders): ?>
                        <?php foreach($orders as $order): ?>
                            <tr>
                                <td data-label="Order ID">#<?php echo htmlspecialchars($order['id']); ?></td>
                                <td data-label="Customer"><?php echo htmlspecialchars($order['full_name']); ?></td>
                                <td data-label="Products"><?php echo $order['products']; ?></td>
                                <td data-label="Total">$<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></td>
                                <td data-label="Status"><span class="badge bg-<?php echo getStatusClass($order['status']); ?>"><?php echo htmlspecialchars(ucfirst($order['status'])); ?></span></td>
                                <td data-label="Actions">
                                    <?php if ($order['status'] == 'pending'): ?>
                                        <a href="dashboard.php?action=confirm&order_id=<?php echo $order['id']; ?>" class="btn btn-sm btn-success">Confirm</a>
                                        <a href="dashboard.php?action=cancel&order_id=<?php echo $order['id']; ?>" class="btn btn-sm btn-danger">Cancel</a>
                                    <?php elseif ($order['status'] == 'confirmed'): ?>
                                        <a href="dashboard.php?action=ship&order_id=<?php echo $order['id']; ?>" class="btn btn-sm btn-warning">Ship</a>
                                    <?php elseif ($order['status'] == 'shipped'): ?>
                                        <a href="dashboard.php?action=deliver&order_id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info">Deliver</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">No orders found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
