<?php
require_once __DIR__ . '/../../App/Controllers/ProductController.php';
$productController = new ProductController();
$products = $productController->getAllProducts();
$categories = $productController->getAllCategories();

ob_start();
?>

<div class="container py-4 page-fade">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">🛍️ Product List</h2>
    <button class="btn btn-warning fw-bold" data-bs-toggle="modal" data-bs-target="#productModal" onclick="openAddModal()">
      ➕ Add Product
    </button>
  </div>

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
              <p class="small text-muted"><?= htmlspecialchars($product['status']) ?></p>

              <div class="d-flex justify-content-center gap-2 mt-3">
                <button class="btn btn-sm btn-outline-warning" 
                        onclick='openEditModal(<?= json_encode($product, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                  ✏️ Edit
                </button>
                <button class="btn btn-sm btn-outline-danger" 
                        onclick="deleteProduct(<?= $product['product_id'] ?>)">
                  🗑️ Delete
                </button>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="text-center text-muted py-5">No products available.</div>
    <?php endif; ?>
  </div>
</div>

<!-- 🟡 Add/Edit Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="productModalLabel">Add Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="../../App/Controllers/ProductController.php" enctype="multipart/form-data">
        <div class="modal-body">
          <input type="hidden" name="action" id="modalAction" value="add">
          <input type="hidden" name="product_id" id="product_id">

          <div class="mb-3">
            <label class="form-label">Product Name</label>
            <input type="text" class="form-control" name="name" id="product_name" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" id="product_description"></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Category</label>
            <select class="form-select" name="category_id" id="product_category" required>
              <option value="">Select Category</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Price (₱)</label>
            <input type="number" step="0.01" class="form-control" name="price" id="product_price" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Stock</label>
            <input type="number" class="form-control" name="stock" id="product_stock" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Status</label>
            <select class="form-select" name="status" id="product_status">
              <option value="Available">Available</option>
              <option value="Out of Stock">Out of Stock</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Product Image</label>
            <input type="file" class="form-control" name="image" accept="image/*">
            <img id="previewImage" class="mt-3 rounded" style="max-height: 150px; display:none;" alt="Preview">
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

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function openAddModal() {
  document.getElementById('productModalLabel').textContent = 'Add Product';
  document.getElementById('modalAction').value = 'add';
  document.getElementById('product_id').value = '';
  document.getElementById('product_name').value = '';
  document.getElementById('product_description').value = '';
  document.getElementById('product_category').value = '';
  document.getElementById('product_price').value = '';
  document.getElementById('product_stock').value = '';
  document.getElementById('product_status').value = 'Available';
  document.getElementById('previewImage').style.display = 'none';
}

function openEditModal(product) {
  const modal = new bootstrap.Modal(document.getElementById('productModal'));
  modal.show();

  document.getElementById('productModalLabel').textContent = 'Edit Product';
  document.getElementById('modalAction').value = 'edit';

  document.getElementById('product_id').value = product.product_id;
  document.getElementById('product_name').value = product.name;
  document.getElementById('product_description').value = product.description || '';
  document.getElementById('product_category').value = product.category_id || '';
  document.getElementById('product_price').value = product.price;
  document.getElementById('product_stock').value = product.stock;
  document.getElementById('product_status').value = product.status;

  if (product.image) {
    const preview = document.getElementById('previewImage');
    preview.src = "../../Public/" + product.image;
    preview.style.display = 'block';
  }
}

function deleteProduct(id) {
  Swal.fire({
    title: "Are you sure?",
    text: "This will permanently delete the product.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#e74c3c",
    cancelButtonColor: "#6c757d",
    confirmButtonText: "Yes, delete it!"
  }).then((result) => {
    if (result.isConfirmed) {
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = '../../App/Controllers/ProductController.php';

      const idInput = document.createElement('input');
      idInput.type = 'hidden';
      idInput.name = 'product_id';
      idInput.value = id;

      const actionInput = document.createElement('input');
      actionInput.type = 'hidden';
      actionInput.name = 'action';
      actionInput.value = 'delete';

      form.appendChild(idInput);
      form.appendChild(actionInput);
      document.body.appendChild(form);
      form.submit();
    }
  });
}

document.addEventListener("DOMContentLoaded", function() {
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has('success')) {
    const action = urlParams.get('success');
    let message = '';
    if (action === 'add') message = 'Product added successfully!';
    else if (action === 'edit') message = 'Product updated successfully!';
    else if (action === 'delete') message = 'Product deleted successfully!';

    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: message,
      confirmButtonColor: '#f39c12'
    }).then(() => {
      window.history.replaceState({}, document.title, window.location.pathname);
    });
  }
});
</script>

<style>
.page-fade {
  opacity: 0;
  animation: fadeIn 0.6s ease-in-out forwards;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/assets/layout/main.php';
?>
