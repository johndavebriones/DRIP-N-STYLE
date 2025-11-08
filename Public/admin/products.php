<?php
require_once __DIR__ . '/../../App/Helpers/SessionHelper.php';
SessionHelper::requireAdminLogin();
SessionHelper::preventCache();

require_once __DIR__ . '/../../App/Controllers/ProductController.php';
$productController = new ProductController();

// 🔹 Capture filters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$status = $_GET['status'] ?? '';

// 🔹 Fetch filtered data
$products = $productController->getFilteredProducts($search, $category, $status);
$categories = $productController->getCategories();
$statuses = $productController->getStatuses();

$title = "Products";
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">🛍️ Product List</h2>
    <div>
        <button class="btn btn-secondary fw-bold" id="history_modal_btn">🕘 History</button>
        <button class="btn btn-warning fw-bold" id="product_modal_btn">➕ Add Product</button>
    </div>
</div>

<!-- 🔹 Filters -->
<form method="GET" id="filterForm" class="sticky-filters bg-white p-3 mb-3 shadow-sm rounded-3">
    <div class="row g-2 align-items-center">
        <div class="col-md-4">
            <input type="text" name="search" id="searchBar" class="form-control"
                placeholder="Search by name..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-4">
            <select name="category" id="filterCategory" class="form-select">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= strtolower($cat['category_name']) ?>"
                        <?= strtolower($cat['category_name']) === strtolower($category) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['category_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <select name="status" id="filterStatus" class="form-select">
                <option value="">All Status</option>
                <?php foreach ($statuses as $s): ?>
                    <option value="<?= strtolower($s) ?>"
                        <?= strtolower($s) === strtolower($status) ? 'selected' : '' ?>>
                        <?= ucfirst($s) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-1">
            <button type="submit" class="btn btn-warning w-100 fw-bold">Go</button>
        </div>
    </div>
</form>

<!-- 🔹 Product Cards -->
<div class="row g-4">
    <?php if (!empty($products)): ?>
        <?php foreach ($products as $product): ?>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100">
                    <img src="../../Public/<?= htmlspecialchars($product['image'] ?: 'uploads/no-image.png') ?>"
                        class="card-img-top"
                        alt="<?= htmlspecialchars($product['name']) ?>"
                        style="height: 200px; object-fit: cover;">
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold mb-1"><?= htmlspecialchars($product['name']) ?></h5>
                        <p class="text-muted mb-1"><?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?></p>
                        <p class="text-muted mb-1">₱<?= number_format($product['price'], 2) ?></p>
                        <p class="small text-muted mb-1">Size: <?= htmlspecialchars($product['size'] ?: 'N/A') ?></p>
                        <p class="small text-muted mb-1">Stock: <?= (int)$product['stock'] ?></p>
                        <p class="small text-muted">Status: <?= htmlspecialchars($product['status']) ?></p>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-center gap-2 mt-2">
                            <button class="btn btn-sm btn-primary edit-product-btn"
                                    data-product-id="<?= $product['product_id'] ?>">
                                ✏️ Edit
                            </button>
                            <button class="btn btn-sm btn-danger delete-product-btn"
                                    data-product-id="<?= $product['product_id'] ?>">
                                🗑️ Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="text-center text-muted py-5">No products found.</div>
    <?php endif; ?>
</div>

<!-- 🟡 Add/Edit Product Modal -->
<div class="modal fade" id="product_modal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold" id="productModalLabel">Add Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="productForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" id="product_id">
                    <input type="hidden" name="existing_image" id="existing_image">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Product Name</label>
                            <input type="text" name="name" id="product_name" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Category</label>
                            <select name="category_id" id="product_category" class="form-select" required>
                                <option value="">Select category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['category_id'] ?>">
                                        <?= htmlspecialchars($category['category_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Price</label>
                            <input type="number" step="0.01" name="price" id="product_price" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Size</label>
                            <select name="size" id="product_size" class="form-select" required>
                                <option value="">Select Size</option>
                                <option value="Small">Small</option>
                                <option value="Medium">Medium</option>
                                <option value="Large">Large</option>
                                <option value="XL">XL</option>
                                <option value="Free Size">Free Size</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Stock</label>
                            <input type="number" name="stock" id="product_stock" class="form-control" min="0" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status" id="product_status" class="form-select" required>
                                <option value="Available">Available</option>
                                <option value="Out of Stock">Out of Stock</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold">Upload Image</label>
                            <input type="file" name="image" id="product_image" class="form-control" accept="image/*">
                            <div class="mt-3 text-center">
                                <img id="image_preview" src="" alt="Image Preview"
                                     style="max-height: 200px; object-fit: cover; border-radius: 8px; display: none;">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning fw-bold">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 🟡 History Modal -->
<div class="modal fade" id="history_modal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title fw-bold" id="historyModalLabel">Deleted Products History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Size</th>
                            <th>Stock</th>
                            <th>Deleted At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="history_tbody">
                        <tr>
                            <td colspan="8" class="text-center">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Styles -->
<style>
.page-fade {
  opacity: 0;
  animation: fadeIn 0.6s ease-in-out forwards;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/js/products.js"></script>

<?php
$content = ob_get_clean();
include __DIR__ . '/assets/layout/main.php';
?>
