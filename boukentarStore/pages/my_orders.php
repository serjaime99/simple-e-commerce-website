<?php
$hide_categories_filter = true; // Set flag to hide categories in header
require '../includes/db_connect.php';
require '../includes/header.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch orders for the logged-in user, including product names
$sql = "SELECT 
            o.id, 
            o.order_date, 
            o.status, 
            o.total_amount,
            GROUP_CONCAT(p.name SEPARATOR ', ') as product_names
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        WHERE o.user_id = ?
        GROUP BY o.id, o.order_date, o.status, o.total_amount
        ORDER BY o.order_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

// Function to get a styled badge for the status
function getStatusBadge($status) {
    $badgeClass = 'bg-secondary'; // Default
    if ($status === 'pending') $badgeClass = 'bg-warning text-dark';
    if ($status === 'confirmed') $badgeClass = 'bg-info text-dark';
    if ($status === 'shipped') $badgeClass = 'bg-primary';
    if ($status === 'delivered') $badgeClass = 'bg-success';
    if ($status === 'cancelled') $badgeClass = 'bg-danger';
    return "<span class='badge " . $badgeClass . "'>" . htmlspecialchars(ucfirst($status)) . "</span>";
}
?>

<main class="container my-5">
    <h2 class="text-center mb-4">My Orders</h2>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success_message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['error_message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle orders-table">
                    <thead class="table-light">
                        <tr>
                            <th>Products</th>
                            <th>Total Amount</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($orders): ?>
                            <?php foreach($orders as $order): ?>
                                <tr>
                                    <td data-label="Products"><?php echo htmlspecialchars($order['product_names']); ?></td>
                                    <td data-label="Total Amount">$<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></td>
                                    <td data-label="Status" class="text-center"><?php echo getStatusBadge($order['status']); ?></td>
                                    <td data-label="Actions" class="text-center">
                                        <?php if ($order['status'] === 'pending'): ?>
                                            <a href="edit_order.php?order_id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary me-1">Edit</a>
                                            <form action="cancel_order.php" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Cancel</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center p-4">You have no orders yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

