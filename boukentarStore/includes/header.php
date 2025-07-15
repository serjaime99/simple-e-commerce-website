<?php
// Start the session only if one isn't already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db_connect.php'; // Include db_connect.php for categories

// Fetch categories for the dropdown
$categories = [];
try {
    $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Log error or handle it gracefully, e.g., display a message
    error_log("Error fetching categories: " . $e->getMessage());
}

// Determine the active category for styling the buttons
$active_category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>boukentarStore</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="/boukentarStore/css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="/boukentarStore/index.php">
            <img src="/boukentarStore/images/boukentarStore.png" alt="boukentarStore Logo" style="height: 40px;">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0"> <!-- Left-aligned items -->
                <li class="nav-item">
                    <a class="nav-link" href="/boukentarStore/index.php">Home</a>
                </li>
                
            </ul>
            <form class="search-container d-flex me-2" action="/boukentarStore/index.php" method="GET">
                <i class="fas fa-search search-icon"></i>
                <input class="form-control" type="search" placeholder="Search products..." aria-label="Search" name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            </form>
            <ul class="navbar-nav"> <!-- Right-aligned items -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/boukentarStore/pages/my_orders.php">My Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/boukentarStore/pages/logout.php">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/boukentarStore/pages/login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/boukentarStore/pages/register.php">Register</a>
                    </li>
                <?php endif; ?>
                
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
</div>

<?php if (!isset($hide_categories_filter) || !$hide_categories_filter): ?>
<div class="category-container">
    <div class="category-scroll">
        <a class="category-item all-products-item <?php echo !$active_category_id ? 'active' : ''; ?>" href="/boukentarStore/index.php">All Products</a>
        <?php foreach ($categories as $category): ?>
            <a class="category-item <?php echo ($active_category_id === (int)$category['id']) ? 'active' : ''; ?>" href="/boukentarStore/index.php?category_id=<?php echo htmlspecialchars($category['id']); ?>"><?php echo htmlspecialchars($category['name']); ?></a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div class="container mt-4">
