<?php
require '../includes/db_connect.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';
$edit_category = null;

// Handle Add, Edit, Delete Actions
$action = $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add' || $action === 'edit') {
        $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);

        if (!empty($name)) {
            if ($action === 'add') {
                $sql = "INSERT INTO categories (name, description) VALUES (?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $description]);
                $message = "<div class='alert alert-success'>Category added successfully!</div>";
            } elseif ($action === 'edit' && $category_id) {
                $sql = "UPDATE categories SET name = ?, description = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $description, $category_id]);
                $message = "<div class='alert alert-success'>Category updated successfully!</div>";
            }
        } else {
            $message = "<div class='alert alert-danger'>Category name cannot be empty.</div>";
        }
    } elseif ($action === 'delete') {
        $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
        if ($category_id) {
            // Check if category has products
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
            $stmt->execute([$category_id]);
            if ($stmt->fetchColumn() > 0) {
                $message = "<div class='alert alert-danger'>Cannot delete category: It still contains products.</div>";
            } else {
                $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
                $stmt->execute([$category_id]);
                $message = "<div class='alert alert-success'>Category deleted successfully!</div>";
            }
        } else {
            $message = "<div class='alert alert-danger'>Invalid category ID for deletion.</div>";
        }
    }
    // Redirect to prevent form resubmission on refresh
    header("Location: manage_categories.php?message=" . urlencode($message));
    exit();
}

// Display messages from redirection
if (isset($_GET['message'])) {
    $message = urldecode($_GET['message']);
}

// Fetch category for editing if edit_id is present in GET request
if (isset($_GET['edit_id'])) {
    $edit_id = filter_input(INPUT_GET, 'edit_id', FILTER_VALIDATE_INT);
    if ($edit_id) {
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$edit_id]);
        $edit_category = $stmt->fetch();
        if (!$edit_category) {
            $message = "<div class='alert alert-danger'>Category not found for editing.</div>";
        }
    }
}

$categories = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) as product_count FROM categories c ORDER BY c.name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Categories</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1>Manage Categories</h1>
    <a href="dashboard.php" class="btn btn-secondary mb-3">Back to Dashboard</a>

    <?php echo $message; ?>

    <!-- Add/Edit Category Form -->
    <div class="card mb-4">
        <div class="card-header"><?php echo $edit_category ? 'Edit Category' : 'Add New Category'; ?></div>
        <div class="card-body">
            <form action="manage_categories.php" method="post">
                <input type="hidden" name="action" value="<?php echo $edit_category ? 'edit' : 'add'; ?>">
                <input type="hidden" name="category_id" value="<?php echo $edit_category ? htmlspecialchars($edit_category['id']) : ''; ?>">
                <div class="mb-3">
                    <label for="name" class="form-label">Category Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo $edit_category ? htmlspecialchars($edit_category['name']) : ''; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?php echo $edit_category ? htmlspecialchars($edit_category['description']) : ''; ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Save Category</button>
            </form>
        </div>
    </div>

    <!-- Categories Table -->
    <div class="card">
        <div class="card-header">Existing Categories</div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Products</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($category['name']); ?></td>
                        <td><?php echo htmlspecialchars($category['description']); ?></td>
                        <td><?php echo $category['product_count']; ?></td>
                        <td>
                            <a href="manage_categories.php?edit_id=<?php echo $category['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                            <form action="manage_categories.php" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this category? This cannot be undone if there are no products associated.');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger" <?php if ($category['product_count'] > 0) echo 'disabled'; ?>>
                                    Delete
                                </button>
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
