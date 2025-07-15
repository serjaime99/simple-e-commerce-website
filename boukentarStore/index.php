<?php
require 'includes/db_connect.php';
require 'includes/header.php';

// Pagination settings
$products_per_page = 16; // Number of products to display per page
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $products_per_page;

$sql = "SELECT * FROM products WHERE stock_quantity > 0";
$params = [];

// Handle search query
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = '%' . $_GET['search'] . '%';
    $sql .= " AND (name LIKE ? OR description LIKE ?)";
    $params[] = $search;
    $params[] = $search;
}

// Handle category filter
if (isset($_GET['category_id']) && filter_var($_GET['category_id'], FILTER_VALIDATE_INT)) {
    $category_id = $_GET['category_id'];
    $sql .= " AND category_id = ?";
    $params[] = $category_id;
}

// Get total number of products for pagination
$count_sql = str_replace("SELECT *", "SELECT COUNT(*)", $sql);
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_products = $count_stmt->fetchColumn();
$total_pages = ceil($total_products / $products_per_page);

$sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $products_per_page;
$params[] = $offset;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
?>

<main class="container my-5">
    <!-- Hero Section -->
    <section class="hero-section text-center mb-5">
        <div class="hero-content">
            <h1 class="welcome-title">Welcome to boukentarStore</h1>
        </div>
    </section>
    

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        <?php if ($stmt->rowCount() > 0): ?>
            <?php while($product = $stmt->fetch()): ?>
                <div class="col">
                    <div class="card h-100 product-card">
                        <div class="product-image-container">
                            <img src="images/products/<?php echo htmlspecialchars($product['image_url']); ?>" class="card-img-top product-image" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <div class="product-price-tag">$<?php echo htmlspecialchars($product['price']); ?></div>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title product-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="card-text product-description flex-grow-1"><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...</p>
                            <div class="d-flex justify-content-end align-items-center mt-auto">
                                <form action="pages/place_order.php" method="post" class="d-flex align-items-center">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <input type="number" name="quantity" class="form-control form-control-sm me-2" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" style="width: 70px;" required>
                                    <button type="submit" class="btn btn-primary btn-sm">Order</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="col-12 text-center">No products found at this time.</p>
        <?php endif; ?>
    </div>

    <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $current_page - 1; ?><?php echo isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : ''; ?><?php echo isset($_GET['category_id']) ? '&category_id=' . htmlspecialchars($_GET['category_id']) : ''; ?>">Previous</a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo ($current_page == $i) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : ''; ?><?php echo isset($_GET['category_id']) ? '&category_id=' . htmlspecialchars($_GET['category_id']) : ''; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $current_page + 1; ?><?php echo isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : ''; ?><?php echo isset($_GET['category_id']) ? '&category_id=' . htmlspecialchars($_GET['category_id']) : ''; ?>">Next</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</main>

<?php
// We want to show the footer on the index page
$is_index_page = true;
require 'includes/footer.php';
?>