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

<!-- 🔶 Header Bar -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <h2 class="fw-bold text-dark mb-0">🛍️ Product Management</h2>
  </div>

  <div class="d-flex gap-2">
    <button class="btn btn-outline-secondary fw-semibold" id="history_modal_btn">
      <i class="bi bi-clock-history me-1"></i> History
    </button>
    <button class="btn btn-warning fw-semibold shadow-sm" id="product_modal_btn">
      <i class="bi bi-plus-lg me-1"></i> Add Product
    </button>
  </div>
</div>

<!-- 🔹 Filters -->
<form method="GET" id="filterForm" class="filter-card p-3 rounded-4 shadow-sm mb-4">
  <div class="row g-3 align-items-center">
    <div class="col-md-5">
      <div class="input-group">
        <span class="input-group-text bg-white border-end-0">
          <i class="bi bi-search text-muted"></i>
        </span>
        <input type="text" name="search" id="searchBar" class="form-control border-start-0"
               placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
      </div>
    </div>
    <div class="col-md-3">
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
    <div class="col-md-4">
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
  </div>
</form>

<!-- 🔹 Modern Product Cards -->
<div class="row g-4">
    <?php if (!empty($products)): ?>
        <?php foreach ($products as $product): ?>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card product-card border-0 shadow-sm h-100 position-relative overflow-hidden">
                    
                    <!-- Product Image -->
                    <div class="product-image-container position-relative">
                        <img src="../../Public/<?= htmlspecialchars($product['image'] ?: 'uploads/no-image.png') ?>"
                             class="card-img-top product-image"
                             alt="<?= htmlspecialchars($product['name']) ?>">
                        <span class="badge bg-<?= $product['status'] === 'Available' ? 'success' : 'secondary' ?> position-absolute top-0 end-0 m-2">
                            <?= htmlspecialchars($product['status']) ?>
                        </span>
                    </div>

                    <!-- Product Info -->
                    <div class="card-body text-center d-flex flex-column justify-content-between">
                        <div>
                            <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($product['name']) ?></h6>
                            <p class="text-muted small mb-1"><?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?></p>
                            <p class="fw-bold text-warning mb-2">₱<?= number_format($product['price'], 2) ?></p>
                            <p class="small text-secondary mb-1">Size: <span class="fw-semibold"><?= htmlspecialchars($product['size'] ?: 'N/A') ?></span></p>
                            <p class="small text-secondary mb-1">Stock: <span class="fw-semibold"><?= (int)$product['stock'] ?></span></p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-3 d-flex justify-content-center gap-2">
                            <button class="btn btn-outline-dark btn-sm edit-product-btn px-3"
                                    data-product-id="<?= $product['product_id'] ?>">
                                <i class="bi bi-pencil-square me-1"></i> Edit
                            </button>
                            <button class="btn btn-outline-danger btn-sm delete-product-btn px-3"
                                    data-product-id="<?= $product['product_id'] ?>">
                                <i class="bi bi-trash3 me-1"></i> Delete
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

<!-- 🟡 Modern Add/Edit Product Modal -->
<div class="modal fade" id="product_modal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg rounded-4 border-0">
            <div class="modal-header bg-gradient-warning text-dark rounded-top-4">
                <h5 class="modal-title fw-bold" id="productModalLabel">Add Product</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="productForm" enctype="multipart/form-data">
                <div class="modal-body py-4 px-5">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" id="product_id">
                    <input type="hidden" name="existing_image" id="existing_image">

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Product Name</label>
                            <input type="text" name="name" id="product_name" class="form-control form-control-lg shadow-sm" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Category</label>
                            <select name="category_id" id="product_category" class="form-select form-select-lg shadow-sm" required>
                                <option value="">Select category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['category_id'] ?>">
                                        <?= htmlspecialchars($category['category_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Price</label>
                            <input type="number" step="0.01" name="price" id="product_price" class="form-control form-control-lg shadow-sm" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Size</label>
                            <select name="size" id="product_size" class="form-select form-select-lg shadow-sm" required>
                                <option value="">Select Size</option>
                                <option value="Small">Small</option>
                                <option value="Medium">Medium</option>
                                <option value="Large">Large</option>
                                <option value="XL">XL</option>
                                <option value="Free Size">Free Size</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Stock</label>
                            <input type="number" name="stock" id="product_stock" class="form-control form-control-lg shadow-sm" min="0" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" id="product_status" class="form-select form-select-lg shadow-sm" required>
                                <option value="Available">Available</option>
                                <option value="Out of Stock">Out of Stock</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" id="product_description" rows="4" class="form-control shadow-sm" placeholder="Enter product details (e.g. material, fit, notes, etc.)"></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Upload Image</label>
                            <input type="file" name="image" id="product_image" class="form-control shadow-sm" accept="image/*">
                            <div class="mt-3 text-center">
                                <img id="image_preview" src="" alt="Image Preview"
                                     class="img-fluid rounded-3 shadow-sm" style="max-height: 200px; display: none;">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary fw-semibold rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning fw-bold rounded-pill px-4">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 🟡 Modern History Modal -->
<div class="modal fade" id="history_modal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content shadow-lg rounded-4 border-0">
            <div class="modal-header bg-gradient-secondary text-white rounded-top-4">
                <h5 class="modal-title fw-bold text-black" id="historyModalLabel">Deleted Products History</h5>
                <button type="button" class="btn-close btn-close-black" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-4 px-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase small">
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
                                <td colspan="7" class="text-center py-4 text-muted">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-outline-secondary fw-semibold rounded-pill px-4" data-bs-dismiss="modal">Close</button>
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
