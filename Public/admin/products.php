<?php
require_once __DIR__ . '/../../App/Controllers/ProductController.php';
$productController = new ProductController();

$title = "Products";
$products = $productController->getAllProducts();

ob_start();
?>

<div class="page-fade">
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
            <img src="<?= htmlspecialchars($product['image_path'] ?? 'assets/img/no-image.png') ?>" 
                 class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>" style="height: 200px; object-fit: cover;">
            <div class="card-body text-center">
              <h5 class="card-title fw-bold mb-1"><?= htmlspecialchars($product['name']) ?></h5>
              <p class="text-muted mb-2">Size: <?= htmlspecialchars($product['size'] ?? 'N/A') ?></p>
              <button class="btn btn-sm btn-outline-warning" 
                      onclick="openEditModal(<?= htmlspecialchars(json_encode($product)) ?>)">
                ✏️ Edit
              </button>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="text-center text-muted py-5">No products available.</div>
    <?php endif; ?>
  </div>
</div>

<!-- Reusable Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="productModalLabel">Add / Edit Product</h5>
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
            <label class="form-label">Size</label>
            <select class="form-select" name="size" id="product_size" required>
              <option value="">Select Size</option>
              <option value="S">Small</option>
              <option value="M">Medium</option>
              <option value="L">Large</option>
              <option value="XL">Extra Large</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Price (₱)</label>
            <input type="number" step="0.01" class="form-control" name="price" id="product_price" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Product Image</label>
            <input type="file" class="form-control" name="image">
            <img id="previewImage" class="mt-3 rounded" style="max-height: 150px; display:none;" alt="Preview">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning fw-bold">Save</button>
          <button type="button" id="deleteBtn" class="btn btn-danger d-none" onclick="deleteProduct()">Delete</button>
        </div>
      </form>
    </div>
  </div>
</div>
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
<script>
function openAddModal() {
  document.getElementById('productModalLabel').textContent = 'Add Product';
  document.getElementById('modalAction').value = 'add';
  document.getElementById('deleteBtn').classList.add('d-none');
  document.getElementById('product_id').value = '';
  document.getElementById('product_name').value = '';
  document.getElementById('product_size').value = '';
  document.getElementById('product_price').value = '';
  document.getElementById('previewImage').style.display = 'none';
}

function openEditModal(product) {
  const modal = new bootstrap.Modal(document.getElementById('productModal'));
  modal.show();

  document.getElementById('productModalLabel').textContent = 'Edit Product';
  document.getElementById('modalAction').value = 'edit';
  document.getElementById('deleteBtn').classList.remove('d-none');

  document.getElementById('product_id').value = product.product_id;
  document.getElementById('product_name').value = product.name;
  document.getElementById('product_size').value = product.size;
  document.getElementById('product_price').value = product.price;

  if (product.image_path) {
    const preview = document.getElementById('previewImage');
    preview.src = product.image_path;
    preview.style.display = 'block';
  }
}

function deleteProduct() {
  if (confirm('Are you sure you want to delete this product?')) {
    const id = document.getElementById('product_id').value;
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
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/assets/layout/main.php';
?>
