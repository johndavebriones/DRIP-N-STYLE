document.addEventListener('DOMContentLoaded', () => {
  const productCards = document.querySelectorAll('.product-clickable');
  const modalEl = document.getElementById('productDetailModal');
  const modal = new bootstrap.Modal(modalEl);
  const actionBtn = document.getElementById('modalActionBtn');
  const qtyInput = document.getElementById('detailQty');
  const isLoggedIn = modalEl.dataset.loggedin === "1";

  let currentProduct = null;
  let selectedSize = null;
  let selectedColor = null;
  let selectedVariation = null;

  // ---- OPEN MODAL WITH SIZE AND COLOR VARIATIONS ----
  productCards.forEach(card => {
    card.addEventListener('click', () => {
      const productData = {
        name: card.dataset.name,
        category: card.dataset.category,
        image: card.dataset.image,
        variations: JSON.parse(card.dataset.variations || '[]')
      };

      currentProduct = productData;
      selectedSize = null;
      selectedColor = null;
      selectedVariation = null;
      openProductModal(productData);
    });
  });

  function openProductModal(product) {
    // Set basic product info
    document.getElementById('detailImage').src = product.image;
    document.getElementById('detailImage').alt = product.name;
    document.getElementById('detailName').textContent = product.name;
    document.getElementById('detailCategory').textContent = product.category;

    // Get unique sizes and colors
    const uniqueSizes = [...new Set(product.variations.map(v => v.size))];
    const uniqueColors = [...new Set(product.variations.map(v => v.color))];

    // Create size selector
    const sizeSelector = document.getElementById('sizeSelector');
    sizeSelector.innerHTML = '';
    
    uniqueSizes.forEach(size => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'size-color-btn rounded';
      btn.textContent = size;
      btn.dataset.size = size;
      
      btn.addEventListener('click', () => selectSize(size));
      sizeSelector.appendChild(btn);
    });

    // Create color selector
    const colorSelector = document.getElementById('colorSelector');
    colorSelector.innerHTML = '';
    
    uniqueColors.forEach(color => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'size-color-btn rounded';
      btn.textContent = color;
      btn.dataset.color = color;
      
      btn.addEventListener('click', () => selectColor(color));
      colorSelector.appendChild(btn);
    });

    // Reset modal state
    document.getElementById('detailPrice').textContent = '';
    document.getElementById('detailDescription').textContent = 'Please select size and color';
    document.getElementById('detailStock').textContent = '';
    actionBtn.disabled = true;

    // Setup modal controls
    setupModalControls();
    modal.show();
  }

  function selectSize(size) {
    // Toggle selection if clicking the same size
    if (selectedSize === size) {
      selectedSize = null;
      document.querySelectorAll('.size-color-btn[data-size]').forEach(btn => {
        btn.classList.remove('active');
      });
      
      // Reset color availability
      document.querySelectorAll('.size-color-btn[data-color]').forEach(btn => {
        btn.disabled = false;
      });
      
      // Clear variation
      selectedVariation = null;
      resetModalDisplay();
      return;
    }
    
    selectedSize = size;
    
    // Update size button states
    document.querySelectorAll('.size-color-btn[data-size]').forEach(btn => {
      btn.classList.remove('active');
      if (btn.dataset.size === size) {
        btn.classList.add('active');
      }
    });

    // Update available colors based on selected size
    updateColorAvailability();
    
    // If color was already selected, update variation
    if (selectedColor) {
      updateSelectedVariation();
    } else {
      resetModalDisplay();
    }
  }

  function selectColor(color) {
    // Toggle selection if clicking the same color
    if (selectedColor === color) {
      selectedColor = null;
      document.querySelectorAll('.size-color-btn[data-color]').forEach(btn => {
        btn.classList.remove('active');
      });
      
      // Reset size availability
      document.querySelectorAll('.size-color-btn[data-size]').forEach(btn => {
        btn.disabled = false;
      });
      
      // Clear variation
      selectedVariation = null;
      resetModalDisplay();
      return;
    }
    
    selectedColor = color;
    
    // Update color button states
    document.querySelectorAll('.size-color-btn[data-color]').forEach(btn => {
      btn.classList.remove('active');
      if (btn.dataset.color === color) {
        btn.classList.add('active');
      }
    });

    // Update available sizes based on selected color
    updateSizeAvailability();
    
    // If size was already selected, update variation
    if (selectedSize) {
      updateSelectedVariation();
    } else {
      resetModalDisplay();
    }
  }

  function updateColorAvailability() {
    if (!selectedSize) return;

    // Get colors available for the selected size
    const availableColors = currentProduct.variations
      .filter(v => v.size === selectedSize && v.stock > 0)
      .map(v => v.color);

    // Update color buttons
    document.querySelectorAll('.size-color-btn[data-color]').forEach(btn => {
      const color = btn.dataset.color;
      const isAvailable = availableColors.includes(color);
      
      if (isAvailable) {
        btn.disabled = false;
      } else {
        btn.disabled = true;
        btn.classList.remove('active');
        
        // If this was the selected color, deselect it
        if (color === selectedColor) {
          selectedColor = null;
        }
      }
    });
  }

  function updateSizeAvailability() {
    if (!selectedColor) return;

    // Get sizes available for the selected color
    const availableSizes = currentProduct.variations
      .filter(v => v.color === selectedColor && v.stock > 0)
      .map(v => v.size);

    // Update size buttons
    document.querySelectorAll('.size-color-btn[data-size]').forEach(btn => {
      const size = btn.dataset.size;
      const isAvailable = availableSizes.includes(size);
      
      if (isAvailable) {
        btn.disabled = false;
      } else {
        btn.disabled = true;
        btn.classList.remove('active');
        
        // If this was the selected size, deselect it
        if (size === selectedSize) {
          selectedSize = null;
        }
      }
    });
  }

  function resetModalDisplay() {
    document.getElementById('detailPrice').textContent = '';
    document.getElementById('detailDescription').textContent = 'Please select size and color';
    document.getElementById('detailStock').textContent = '';
    actionBtn.disabled = true;
    qtyInput.style.display = 'none';
  }

  function updateSelectedVariation() {
    if (!selectedSize || !selectedColor) {
      actionBtn.disabled = true;
      return;
    }

    // Find the matching variation
    selectedVariation = currentProduct.variations.find(
      v => v.size === selectedSize && v.color === selectedColor
    );

    if (selectedVariation && selectedVariation.stock > 0) {
      // Update UI with variation details
      document.getElementById('detailPrice').textContent = `₱${parseFloat(selectedVariation.price).toFixed(2)}`;
      document.getElementById('detailDescription').textContent = selectedVariation.description || 'No description available.';
      document.getElementById('detailStock').textContent = `Stock: ${selectedVariation.stock}`;

      // Update quantity input
      qtyInput.max = selectedVariation.stock;
      qtyInput.value = 1;
      qtyInput.min = 1;

      // Enable add to cart
      actionBtn.disabled = false;
      if (isLoggedIn) {
        qtyInput.style.display = 'block';
      }
    } else {
      document.getElementById('detailDescription').textContent = 'This combination is out of stock.';
      document.getElementById('detailStock').textContent = 'Stock: 0';
      actionBtn.disabled = true;
      qtyInput.style.display = 'none';
    }
  }

  function setupModalControls() {
    const modalControls = document.getElementById('modalControls');

    if (isLoggedIn) {
      modalControls.style.display = 'flex';
      qtyInput.style.display = 'none';
      actionBtn.textContent = 'Add to Cart';
      actionBtn.classList.replace('btn-dark', 'btn-warning');
      actionBtn.classList.remove('w-100', 'w-auto');
      actionBtn.classList.add('w-50');
      modalControls.classList.remove('justify-content-end');
    } else {
      qtyInput.style.display = 'none';
      actionBtn.textContent = 'Log in to Add to Cart';
      modalControls.classList.add('justify-content-end');
      actionBtn.classList.remove('w-50');
      actionBtn.classList.add('w-auto');
      actionBtn.classList.replace('btn-warning', 'btn-dark');
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

    // Check if size and color are selected
    if (!selectedSize || !selectedColor || !selectedVariation) {
      Swal.fire({
        icon: 'warning',
        title: 'Selection Required',
        text: 'Please select both size and color.',
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

    // Show loading state
    actionBtn.disabled = true;
    const originalText = actionBtn.textContent;
    actionBtn.textContent = 'Adding...';

    // Send request
    const result = await sendCartRequest('add', {
      product_id: selectedVariation.product_id,
      quantity: qty,
      price: selectedVariation.price
    });

    // Restore button state
    actionBtn.disabled = false;
    actionBtn.textContent = originalText;

    // Handle response
    if (result.success) {
      Swal.fire({
        icon: 'success',
        title: 'Added to Cart!',
        text: `${currentProduct.name} (${selectedSize}, ${selectedColor}) x${qty} added to your cart.`,
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