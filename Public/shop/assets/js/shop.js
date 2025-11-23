document.addEventListener('DOMContentLoaded', () => {
  const productCards = document.querySelectorAll('.product-clickable');
  const modalEl = document.getElementById('productDetailModal');
  const modal = new bootstrap.Modal(modalEl);
  const actionBtn = document.getElementById('modalActionBtn');
  const qtyInput = document.getElementById('detailQty');
  const isLoggedIn = modalEl.dataset.loggedin === "1";

  let currentProduct = null;
  let selectedSize = null;

  // ---- OPEN MODAL WITH SIZE VARIATIONS ----
  productCards.forEach(card => {
    card.addEventListener('click', () => {
      // Parse product data including sizes
      const productData = {
        id: card.dataset.id,
        name: card.dataset.name,
        category: card.dataset.category,
        price: card.dataset.price,
        stock: card.dataset.stock,
        description: card.dataset.description,
        image: card.dataset.image,
        sizes: JSON.parse(card.dataset.sizes || '[]')
      };

      currentProduct = productData;
      openProductModal(productData);
    });
  });

  function openProductModal(product) {
    // Set basic product info
    const img = document.getElementById('detailImage');
    img.src = product.image;
    img.alt = product.name;
    img.dataset.id = product.id;

    document.getElementById('detailName').textContent = product.name;
    document.getElementById('detailCategory').textContent = product.category;
    document.getElementById('detailPrice').textContent = `₱${parseFloat(product.price).toFixed(2)}`;

    // Create size selector buttons
    const sizeSelector = document.getElementById('sizeSelector');
    sizeSelector.innerHTML = '';

    product.sizes.forEach((sizeData, index) => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'btn btn-outline-warning btn-sm size-btn';
      btn.textContent = sizeData.size;
      btn.dataset.sizeIndex = index;

      // Disable if out of stock
      if (sizeData.stock === 0) {
        btn.disabled = true;
        btn.classList.add('opacity-50');
        btn.textContent += ' (Out of Stock)';
      }

      // Handle size selection
      btn.addEventListener('click', function() {
        document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        selectSize(index);
      });

      sizeSelector.appendChild(btn);
    });

    // Auto-select first available size
    const firstAvailable = product.sizes.findIndex(s => s.stock > 0);
    if (firstAvailable !== -1) {
      sizeSelector.children[firstAvailable].classList.add('active');
      selectSize(firstAvailable);
    } else {
      // No sizes available
      document.getElementById('detailDescription').textContent = 'This product is currently out of stock.';
      document.getElementById('detailStock').textContent = 'Stock: 0';
      actionBtn.disabled = true;
    }

    // Setup modal controls based on login status
    const stockEl = document.getElementById('detailStock');
    const modalControls = document.getElementById('modalControls');

    if (isLoggedIn) {
      modalControls.style.display = 'flex';
      qtyInput.style.display = 'block';
      actionBtn.textContent = 'Add to Cart';
      actionBtn.classList.replace('btn-dark', 'btn-warning');
      actionBtn.classList.remove('w-100', 'w-auto');
      actionBtn.classList.add('w-50');
      modalControls.classList.remove('justify-content-end');
    } else {
      qtyInput.style.display = 'none';
      stockEl.style.display = 'block';
      actionBtn.textContent = 'Log in to Add to Cart';
      modalControls.classList.add('justify-content-end');
      actionBtn.classList.remove('w-50');
      actionBtn.classList.add('w-auto');
      actionBtn.classList.replace('btn-warning', 'btn-dark');
    }

    modal.show();
  }

  function selectSize(sizeIndex) {
    selectedSize = currentProduct.sizes[sizeIndex];

    // Update description and stock based on selected size
    document.getElementById('detailDescription').textContent = selectedSize.description || 'No description available.';
    document.getElementById('detailStock').textContent = `Stock: ${selectedSize.stock}`;

    // Update quantity input constraints
    qtyInput.max = selectedSize.stock;
    qtyInput.value = 1;
    qtyInput.min = 1;

    // Enable/disable add to cart button
    if (selectedSize.stock > 0) {
      actionBtn.disabled = false;
      if (isLoggedIn) {
        qtyInput.style.display = 'block';
      }
    } else {
      actionBtn.disabled = true;
      qtyInput.style.display = 'none';
    }
  }

  // ---- Quantity input validation ----
  qtyInput.addEventListener('input', () => {
    const maxQty = parseInt(qtyInput.max, 10) || 1;
    let qty = parseInt(qtyInput.value, 10) || 1;

    if (qty > maxQty) {
      qtyInput.value = maxQty;
      Swal.fire({
        icon: 'warning',
        title: 'Stock Limit',
        text: `You can only order up to ${maxQty} items.`,
        confirmButtonColor: '#ffc107',
        timer: 2000
      });
    } else if (qty < 1) {
      qtyInput.value = 1;
      Swal.fire({
        icon: 'warning',
        title: 'Invalid Quantity',
        text: 'Quantity must be at least 1.',
        confirmButtonColor: '#ffc107',
        timer: 2000
      });
    }
  });

  // ---- AJAX Helpers ----
  const sendCartRequest = async (action, data) => {
    const formData = new FormData();
    formData.append('action', action);
    for (const key in data) formData.append(key, data[key]);

    try {
      const res = await fetch('/DRIP-N-STYLE/App/Controllers/CartController.php', {
        method: 'POST',
        body: formData
      });
      const json = await res.json();

      return json;
    } catch (err) {
      console.error(err);
      return { success: false, message: 'Something went wrong.' };
    }
  };

  // ---- ADD TO CART BUTTON ----
  actionBtn.addEventListener('click', async () => {
    // Check if logged in
    if (!isLoggedIn) {
      const result = await Swal.fire({
        icon: 'warning',
        title: 'Login Required',
        text: 'You need to log in to add items to your cart. Do you want to log in now?',
        showCancelButton: true,
        confirmButtonText: 'Log In',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d'
      });

      if (result.isConfirmed) {
        window.location.href = '../LoginPage.php';
      }
      return;
    }

    // Check if size is selected
    if (!selectedSize) {
      Swal.fire({
        icon: 'warning',
        title: 'Select Size',
        text: 'Please select a size first.',
        confirmButtonColor: '#ffc107'
      });
      return;
    }

    // Validate quantity
    const qty = parseInt(qtyInput.value, 10) || 1;
    const maxQty = parseInt(qtyInput.max, 10) || qty;

    if (qty < 1 || qty > maxQty) {
      Swal.fire({
        icon: 'error',
        title: 'Invalid Quantity',
        text: qty < 1 
          ? 'Quantity must be at least 1.' 
          : `You can only order up to ${maxQty} items.`,
        confirmButtonColor: '#ffc107'
      });
      return;
    }

    // Prepare cart data
    const productId = selectedSize.product_id;
    const price = parseFloat(
      document.getElementById('detailPrice').textContent.replace(/[₱,]/g, '')
    );

    // Show loading state
    actionBtn.disabled = true;
    const originalText = actionBtn.textContent;
    actionBtn.textContent = 'Adding...';

    // Send request
    const result = await sendCartRequest('add', {
      product_id: productId,
      quantity: qty,
      price
    });

    // Restore button state
    actionBtn.disabled = false;
    actionBtn.textContent = originalText;

    // Handle response
    if (result.success) {
      Swal.fire({
        icon: 'success',
        title: 'Added to Cart!',
        text: `${currentProduct.name} (${selectedSize.size}) x${qty} added to your cart.`,
        confirmButtonColor: '#ffc107',
        timer: 2000,
        showConfirmButton: false
      });

      qtyInput.value = 1;
      setTimeout(() => modal.hide(), 1500);
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: result.message || 'Failed to add to cart.',
        confirmButtonColor: '#ffc107'
      });
    }
  });
});