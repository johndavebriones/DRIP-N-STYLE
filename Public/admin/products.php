<?php
require_once __DIR__ . '/../../App/Helpers/SessionHelper.php';
SessionHelper::requireAdminLogin();
SessionHelper::preventCache();

require_once __DIR__ . '/../../App/Controllers/ProductController.php';
$productController = new ProductController();

// Capture filters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$status = $_GET['status'] ?? '';

// Fetch filtered data
$products = $productController->getFilteredProducts($search, $category, $status);
$categories = $productController->getCategories();
$statuses = $productController->getStatuses();

// Group products by name, category_id, and price
$groupedProducts = [];
foreach ($products as $product) {
    $groupKey = $product['name'] . '|' . $product['category_id'] . '|' . $product['price'];
    
    if (!isset($groupedProducts[$groupKey])) {
        $groupedProducts[$groupKey] = [
            'name' => $product['name'],
            'category_name' => $product['category_name'],
            'category_id' => $product['category_id'],
            'price' => $product['price'],
            'image' => $product['image'],
            'description' => $product['description'] ?? '',
            'variants' => []
        ];
    }
    
    $groupedProducts[$groupKey]['variants'][] = [
        'product_id' => $product['product_id'],
        'size' => $product['size'],
        'color' => $product['color'],
        'stock' => $product['stock'],
        'status' => $product['status'],
        'image' => $product['image'],
        'description' => $product['description'] ?? ''
    ];
}

$title = "Products";
ob_start();
?>
<link rel="stylesheet" href="assets/css/products.css">
<!-- Header Bar -->
<div class="page-fade">
    <div class="page-header d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="fw-bold text-light mb-0">🛍️ Product Management</h2>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-success fw-semibold" id="history_modal_btn">
                <i class="bi bi-clock-history me-1"></i> History
            </button>
            <button class="btn btn-warning fw-semibold shadow-sm" id="product_modal_btn">
                <i class="bi bi-plus-lg me-1"></i> Add Product
            </button>
        </div>
    </div>

    <!-- Filters -->
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

    <!-- Product Cards (Grouped) -->
    <div class="row g-4">
        <?php if (!empty($groupedProducts)): ?>
            <?php foreach ($groupedProducts as $group): ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                    <div class="card product-card border-0 shadow-sm h-100">
                        
                        <!-- Product Image -->
                        <div class="position-relative">
                            <img src="../../Public/<?= htmlspecialchars($group['image'] ?: 'uploads/no-image.png') ?>"
                                class="card-img-top"
                                alt="<?= htmlspecialchars($group['name']) ?>"
                                style="height: 200px; object-fit: cover;">
                            <span class="badge bg-dark position-absolute top-0 end-0 m-2">
                                <?= count($group['variants']) ?> variant(s)
                            </span>
                        </div>

                        <!-- Product Info -->
                        <div class="card-body d-flex flex-column">
                            <div class="text-center mb-3">
                                <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($group['name']) ?></h6>
                                <p class="text-muted small mb-1"><?= htmlspecialchars($group['category_name'] ?? 'Uncategorized') ?></p>
                                <p class="text-warning fw-bold mb-0">₱<?= number_format($group['price'], 2) ?></p>
                            </div>

                            <!-- Variants List -->
                            <div class="flex-grow-1" style="max-height: 300px; overflow-y: auto;">
                                <ul class="list-unstyled">
                                    <?php foreach ($group['variants'] as $variant): ?>
                                        <li class="variant-item">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div class="flex-grow-1">
                                                    <span class="badge bg-<?= $variant['status'] === 'Available' ? 'success' : 'danger' ?> mb-1">
                                                        <?= htmlspecialchars($variant['status']) ?>
                                                    </span>
                                                    <div class="small mb-1">
                                                        <strong>Size:</strong> <?= htmlspecialchars($variant['size'] ?: 'N/A') ?>
                                                    </div>
                                                    <div class="small mb-1">
                                                        <strong>Color:</strong> 
                                                        <span class="color-text"><?= htmlspecialchars($variant['color'] ?: 'N/A') ?></span>
                                                    </div>
                                                    <div class="small">
                                                        <strong>Stock:</strong> <?= (int)$variant['stock'] ?>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Action Buttons -->
                                            <div class="d-flex gap-2 mt-2">
                                                <button class="btn btn-dark btn-sm flex-fill edit-product-btn"
                                                        data-product-id="<?= $variant['product_id'] ?>">
                                                    <i class="bi bi-pencil-square me-1"></i> Edit
                                                </button>
                                                <button class="btn btn-danger btn-sm flex-fill delete-product-btn"
                                                        data-product-id="<?= $variant['product_id'] ?>">
                                                    <i class="bi bi-trash3 me-1"></i> Delete
                                                </button>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            
                            <!-- Add Variant Button -->
                            <div class="mt-3">
                                <button class="btn btn-success btn-sm w-100 add-variant-btn" 
                                        data-product-name="<?= htmlspecialchars($group['name']) ?>"
                                        data-product-price="<?= htmlspecialchars($group['price']) ?>"
                                        data-category-id="<?= htmlspecialchars($group['category_id']) ?>"
                                        data-product-image="<?= htmlspecialchars($group['image']) ?>">
                                    <i class="bi bi-plus-lg me-1"></i> Add Variant
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center text-muted py-5">No products found.</div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="product_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg rounded-4 border-0">
            <div class="modal-header bg bg-warning text-dark rounded-top-4">
                <h5 class="modal-title fw-bold" id="productModalLabel">Add Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="productForm" enctype="multipart/form-data">
                <div class="modal-body py-4 px-5">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" id="product_id">
                    <!-- SINGLE hidden field for image path (used in EDIT mode) -->
                    <input type="hidden" id="product_image_path" value="">

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="product_name" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
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
                            <label class="form-label fw-semibold">Price <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="price" id="product_price" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Size <span class="text-danger">*</span></label>
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
                            <label class="form-label fw-semibold">Color <span class="text-danger">*</span></label>
                            <input type="text" name="color" id="product_color" class="form-control" placeholder="e.g. Red, Blue, Black" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Stock <span class="text-danger">*</span></label>
                            <input type="number" name="stock" id="product_stock" class="form-control" min="0" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" id="product_description" rows="3" class="form-control" 
                                placeholder="Enter product details..."></textarea>
                        </div>

                        <!-- Section 1: For ADD mode (file upload) -->
                        <div class="col-12" id="add_image_section">
                            <label class="form-label fw-semibold">Upload Image <span class="text-danger">*</span></label>
                            <input type="file" name="image" id="product_image_file" class="form-control" accept="image/*" required>
                            <div class="mt-3 text-center">
                                <img id="add_image_preview" src="" alt="Image Preview"
                                    class="img-fluid rounded-3 shadow-sm" style="max-height: 200px; display: none;">
                            </div>
                        </div>

                        <!-- Section 2: For EDIT mode (just show image) -->
                        <div class="col-12" id="edit_image_section" style="display: none;">
                            <label class="form-label fw-semibold">Product Image</label>
                            <div class="mt-2 text-center">
                                <img id="edit_image_preview" src="" alt="Current Image"
                                    class="img-fluid rounded-3 shadow-sm" style="max-height: 200px;">
                            </div>
                            <div class="alert alert-info small mt-3 mb-0">
                                <i class="bi bi-info-circle me-1"></i> Image will remain the same from the product group
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

<!-- Add Variant Modal -->
<div class="modal fade" id="variant_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg rounded-4 border-0">
            <div class="modal-header bg-success text-white rounded-top-4">
                <h5 class="modal-title fw-bold">Add Variant</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="variantForm" enctype="multipart/form-data">
                <div class="modal-body py-4 px-4">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="name" id="variant_name">
                    <input type="hidden" name="price" id="variant_price">
                    <input type="hidden" name="category_id" id="variant_category">
                    <!-- SINGLE hidden field for image path -->
                    <input type="hidden" id="variant_image_path" value="">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Size <span class="text-danger">*</span></label>
                        <select name="size" id="variant_size" class="form-select" required>
                            <option value="">Select Size</option>
                            <option value="Small">Small</option>
                            <option value="Medium">Medium</option>
                            <option value="Large">Large</option>
                            <option value="XL">XL</option>
                            <option value="Free Size">Free Size</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Color <span class="text-danger">*</span></label>
                        <input type="text" name="color" id="variant_color" class="form-control" placeholder="e.g. Red, Blue, Black" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Stock <span class="text-danger">*</span></label>
                        <input type="number" name="stock" id="variant_stock" class="form-control" min="0" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" id="variant_description" rows="3" class="form-control" 
                            placeholder="Enter variant details..."></textarea>
                    </div>

                    <div class="alert alert-info small mb-0">
                        <i class="bi bi-info-circle me-1"></i> Image will be copied from the main product
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary fw-semibold rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-bold rounded-pill px-4">Add Variant</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- History Modal -->
<div class="modal fade" id="history_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg rounded-4 border-0">
            <div class="modal-header bg-warning text-dark rounded-top-4">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-archive me-2"></i>Archived Products History
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase small sticky-top">
                            <tr>
                                <th class="ps-4">Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Size</th>
                                <th>Color</th>
                                <th>Stock</th>
                                <th>Deleted At</th>
                                <th class="text-center pe-4" style="width: 120px;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="history_tbody">
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <div class="spinner-border spinner-border-sm me-2" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    Loading...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light rounded-bottom-4">
                <button class="btn btn-secondary fw-semibold rounded-pill px-4" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="assets/js/products.js?v=<?= time() ?>"></script>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout/main.php';
?>