<?php
require '../includes/db_connect.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';
$edit_product = null;

// Handle Add, Edit, Delete Actions
$action = $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add' || $action === 'edit') {
        $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
        $stock_quantity = filter_input(INPUT_POST, 'stock_quantity', FILTER_VALIDATE_INT);
        $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
        $image_url = $_POST['current_image_url'] ?? 'default_product_image.png'; // Default or current image

        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $target_dir = "../images/products/";
            $image_name = basename($_FILES['image']['name']);
            $target_file = $target_dir . $image_name;
            $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

            // Check if image file is a actual image or fake image
            $check = getimagesize($_FILES['image']['tmp_name']);
            if($check !== false) {
                // Allow certain file formats
                if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
                && $imageFileType != "gif" ) {
                    $message = "<div class='alert alert-danger'>Sorry, only JPG, JPEG, PNG & GIF files are allowed.</div>";
                } else {
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                        $image_url = $image_name;
                    } else {
                        $message = "<div class='alert alert-danger'>Sorry, there was an error uploading your file.</div>";
                    }
                }
            } else {
                $message = "<div class='alert alert-danger'>File is not an image.</div>";
            }
        }

        if (empty($message) && !empty($name) && !empty($description) && $price !== false && $stock_quantity !== false && $category_id !== false) {
            if ($action === 'add') {
                $sql = "INSERT INTO products (name, description, price, stock_quantity, category_id, image_url) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $description, $price, $stock_quantity, $category_id, $image_url]);
                $message = "<div class='alert alert-success'>Product added successfully!</div>";
            } elseif ($action === 'edit' && $product_id) {
                $sql = "UPDATE products SET name = ?, description = ?, price = ?, stock_quantity = ?, category_id = ?, image_url = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $description, $price, $stock_quantity, $category_id, $image_url, $product_id]);
                $message = "<div class='alert alert-success'>Product updated successfully!</div>";
            }
        } else if (empty($message)) {
            $message = "<div class='alert alert-danger'>Please fill all fields correctly.</div>";
        }
    } elseif ($action === 'delete') {
        $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        if ($product_id) {
            try {
                $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
                $stmt->execute([$product_id]);
                $message = "<div class='alert alert-success'>Product deleted successfully!</div>";
            } catch (PDOException $e) {
                if ($e->getCode() == '23000') { // Integrity constraint violation
                    $message = "<div class='alert alert-danger'>Cannot delete this product because it is part of one or more existing customer orders. Consider marking it as unavailable instead.</div>";
                } else {
                    $message = "<div class='alert alert-danger'>An unexpected database error occurred: " . $e->getMessage() . "</div>";
                }
            }
        } else {
            $message = "<div class='alert alert-danger'>Invalid product ID for deletion.</div>";
        }
    }
    // Redirect to prevent form resubmission on refresh
    header("Location: manage_products.php?message=" . urlencode($message));
    exit();
}

// Display messages from redirection
if (isset($_GET['message'])) {
    $message = urldecode($_GET['message']);
}

// Fetch product for editing if edit_id is present in GET request
if (isset($_GET['edit_id'])) {
    $edit_id = filter_input(INPUT_GET, 'edit_id', FILTER_VALIDATE_INT);
    if ($edit_id) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$edit_id]);
        $edit_product = $stmt->fetch();
        if (!$edit_product) {
            $message = "<div class='alert alert-danger'>Product not found for editing.</div>";
        }
    }
}

$products = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC")->fetchAll();
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1>Manage Products</h1>
    <a href="dashboard.php" class="btn btn-secondary mb-3">Back to Dashboard</a>
    
    <?php echo $message; ?>

    <!-- Add/Edit Product Form -->
    <div class="card mb-4">
        <div class="card-header"><?php echo $edit_product ? 'Edit Product' : 'Add New Product'; ?></div>
        <div class="card-body">
            <form action="manage_products.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $edit_product ? 'edit' : 'add'; ?>">
                <input type="hidden" name="product_id" value="<?php echo $edit_product ? htmlspecialchars($edit_product['id']) : ''; ?>">
                <input type="hidden" name="current_image_url" value="<?php echo $edit_product ? htmlspecialchars($edit_product['image_url']) : ''; ?>">
                <div class="mb-3">
                    <label for="name" class="form-label">Product Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo $edit_product ? htmlspecialchars($edit_product['name']) : ''; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-select" id="category" name="category_id" required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo ($edit_product && $category['id'] == $edit_product['category_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="price" class="form-label">Price</label>
                    <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?php echo $edit_product ? htmlspecialchars($edit_product['price']) : ''; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="stock" class="form-label">Stock Quantity</label>
                    <input type="number" class="form-control" id="stock" name="stock_quantity" value="<?php echo $edit_product ? htmlspecialchars($edit_product['stock_quantity']) : ''; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3" required><?php echo $edit_product ? htmlspecialchars($edit_product['description']) : ''; ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="image" class="form-label">Product Image</label>
                    <input type="file" class="form-control" id="image" name="image">
                    <?php if ($edit_product && $edit_product['image_url']): ?>
                        <small class="form-text text-muted">Current image: <img src="../images/products/<?php echo htmlspecialchars($edit_product['image_url']); ?>" alt="Current Product Image" style="width: 50px; height: auto;"></small>
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn btn-primary">Save Product</button>
            </form>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card">
        <div class="card-header">Existing Products</div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                        <td>$<?php echo htmlspecialchars($product['price']); ?></td>
                        <td><?php echo htmlspecialchars($product['stock_quantity']); ?></td>
                        <td><img src="../images/products/<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 50px; height: auto;"></td>
                        <td>
                            <a href="manage_products.php?edit_id=<?php echo $product['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                            <form action="manage_products.php" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product? This cannot be undone if the product is not part of any order.');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
