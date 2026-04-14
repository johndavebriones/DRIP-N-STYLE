document.addEventListener('DOMContentLoaded', () => {
  const productCards    = document.querySelectorAll('.product-clickable');
  const overlayEl       = document.getElementById('productModalOverlay');
  const actionBtn       = document.getElementById('modalActionBtn');
  const qtyInput        = document.getElementById('detailQty');

  // Read login state from the overlay element (matches PHP data-loggedin attr)
  const isLoggedIn = overlayEl?.dataset.loggedin === "1";

  let currentProduct    = null;
  let selectedSize      = null;
  let selectedColor     = null;
  let selectedVariation = null;

  // ── Custom modal open / close (replaces bootstrap.Modal) ──────────────────
  function openModal()  { overlayEl.classList.add('open');    }
  function closeModal() { overlayEl.classList.remove('open'); }

  // Close on backdrop click
  overlayEl.addEventListener('click', e => {
    if (e.target === overlayEl) closeModal();
  });
  // Close button inside modal
  const closeBtn = overlayEl.querySelector('.modal-close');
  if (closeBtn) closeBtn.addEventListener('click', closeModal);

  // ── Wire every product card ───────────────────────────────────────────────
  productCards.forEach(card => {
    card.addEventListener('click', () => {
      const productData = {
        name      : card.dataset.name,
        category  : card.dataset.category,
        image     : card.dataset.image,
        variations: JSON.parse(card.dataset.variations || '[]')
      };

      currentProduct    = productData;
      selectedSize      = null;
      selectedColor     = null;
      selectedVariation = null;
      openProductModal(productData);
    });
  });

  // ── Populate and open modal ───────────────────────────────────────────────
  function openProductModal(product) {
    document.getElementById('detailImage').src                = product.image;
    document.getElementById('detailImage').alt                = product.name;
    document.getElementById('detailName').textContent         = product.name;
    document.getElementById('detailCategory').textContent     = product.category;
    document.getElementById('productDetailLabel').textContent = product.name;

    const uniqueSizes  = [...new Set(product.variations.map(v => v.size))];
    const uniqueColors = [...new Set(product.variations.map(v => v.color))];

    // Build size buttons
    const sizeSelector = document.getElementById('sizeSelector');
    sizeSelector.innerHTML = '';
    uniqueSizes.forEach(size => {
      const btn = document.createElement('button');
      btn.type      = 'button';
      btn.className = 'selector-btn';
      btn.textContent   = size;
      btn.dataset.size  = size;
      btn.addEventListener('click', () => selectSize(size));
      sizeSelector.appendChild(btn);
    });

    // Build color buttons
    const colorSelector = document.getElementById('colorSelector');
    colorSelector.innerHTML = '';
    uniqueColors.forEach(color => {
      const btn = document.createElement('button');
      btn.type       = 'button';
      btn.className  = 'selector-btn';
      btn.textContent    = color;
      btn.dataset.color  = color;
      btn.addEventListener('click', () => selectColor(color));
      colorSelector.appendChild(btn);
    });

    resetModalDisplay();
    setupModalControls();
    openModal();
  }

  // ── Size selection ────────────────────────────────────────────────────────
  function selectSize(size) {
    if (selectedSize === size) {
      selectedSize = null;
      document.querySelectorAll('.selector-btn[data-size]').forEach(b  => b.classList.remove('active'));
      document.querySelectorAll('.selector-btn[data-color]').forEach(b => { b.disabled = false; });
      selectedVariation = null;
      resetModalDisplay();
      return;
    }
    selectedSize = size;
    document.querySelectorAll('.selector-btn[data-size]').forEach(b => {
      b.classList.toggle('active', b.dataset.size === size);
    });
    updateColorAvailability();
    if (selectedColor) updateSelectedVariation();
    else resetModalDisplay();
  }

  // ── Color selection ───────────────────────────────────────────────────────
  function selectColor(color) {
    if (selectedColor === color) {
      selectedColor = null;
      document.querySelectorAll('.selector-btn[data-color]').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.selector-btn[data-size]').forEach(b  => { b.disabled = false; });
      selectedVariation = null;
      resetModalDisplay();
      return;
    }
    selectedColor = color;
    document.querySelectorAll('.selector-btn[data-color]').forEach(b => {
      b.classList.toggle('active', b.dataset.color === color);
    });
    updateSizeAvailability();
    if (selectedSize) updateSelectedVariation();
    else resetModalDisplay();
  }

  // ── Disable colors with no stock for the chosen size ─────────────────────
  function updateColorAvailability() {
    if (!selectedSize) return;
    const available = currentProduct.variations
      .filter(v => v.size === selectedSize && v.stock > 0)
      .map(v => v.color);

    document.querySelectorAll('.selector-btn[data-color]').forEach(btn => {
      const ok = available.includes(btn.dataset.color);
      btn.disabled = !ok;
      if (!ok) {
        btn.classList.remove('active');
        if (btn.dataset.color === selectedColor) selectedColor = null;
      }
    });
  }

  // ── Disable sizes with no stock for the chosen color ─────────────────────
  function updateSizeAvailability() {
    if (!selectedColor) return;
    const available = currentProduct.variations
      .filter(v => v.color === selectedColor && v.stock > 0)
      .map(v => v.size);

    document.querySelectorAll('.selector-btn[data-size]').forEach(btn => {
      const ok = available.includes(btn.dataset.size);
      btn.disabled = !ok;
      if (!ok) {
        btn.classList.remove('active');
        if (btn.dataset.size === selectedSize) selectedSize = null;
      }
    });
  }

  // ── Reset info before a valid combo is selected ───────────────────────────
  function resetModalDisplay() {
    document.getElementById('detailPrice').textContent       = '';
    document.getElementById('detailDescription').textContent = 'Please select a size and color.';
    document.getElementById('detailStock').textContent       = '';
    qtyInput.style.display = 'none';

    if (isLoggedIn) {
      actionBtn.disabled = true; // enabled only after a valid combo
    } else {
      actionBtn.disabled = false; // "Log In to Check Out" is always clickable
    }
  }

  // ── Update price / stock once a valid combo is chosen ────────────────────
  function updateSelectedVariation() {
    if (!selectedSize || !selectedColor) { actionBtn.disabled = true; return; }

    selectedVariation = currentProduct.variations.find(
      v => v.size === selectedSize && v.color === selectedColor
    );

    if (selectedVariation && selectedVariation.stock > 0) {
      document.getElementById('detailPrice').textContent =
        `₱${parseFloat(selectedVariation.price).toFixed(2)}`;
      document.getElementById('detailDescription').textContent =
        selectedVariation.description || 'No description available.';
      document.getElementById('detailStock').textContent =
        `Stock: ${selectedVariation.stock}`;

      qtyInput.max   = selectedVariation.stock;
      qtyInput.value = 1;
      qtyInput.min   = 1;

      if (isLoggedIn) {
        qtyInput.style.display = 'inline-block';
      }
      actionBtn.disabled = false;

    } else {
      document.getElementById('detailDescription').textContent = 'This combination is out of stock.';
      document.getElementById('detailStock').textContent       = 'Out of Stock';
      qtyInput.style.display = 'none';
      // Keep "Log In" button active; disable Add-to-Cart when out of stock
      actionBtn.disabled = isLoggedIn;
    }
  }

  // ── Button label / style based on login state ─────────────────────────────
  function setupModalControls() {
    const modalControls = document.getElementById('modalControls');
    modalControls.style.display = 'flex';

    if (isLoggedIn) {
      qtyInput.style.display = 'none';       // shown only after a valid combo
      actionBtn.textContent  = 'Add to Cart';
      actionBtn.disabled     = true;
      actionBtn.className    = 'add-cart-btn';
    } else {
      qtyInput.style.display = 'none';
      actionBtn.textContent  = 'Log In to Check Out';
      actionBtn.disabled     = false;
      actionBtn.className    = 'add-cart-btn login-required-btn';
    }
  }

  // ── Quantity input guard ──────────────────────────────────────────────────
  qtyInput.addEventListener('input', () => {
    const max = parseInt(qtyInput.max, 10) || 1;
    let qty   = parseInt(qtyInput.value, 10) || 1;
    if (qty > max)  { qtyInput.value = max; qtyAlert(`Max ${max} items allowed.`); }
    else if (qty < 1) { qtyInput.value = 1;  qtyAlert('Quantity must be at least 1.'); }
  });

  function qtyAlert(text) {
    Swal.fire({ icon:'warning', title:'Quantity', text,
      confirmButtonColor:'#c9a84c', timer:2000, showConfirmButton:false });
  }

  // ── AJAX helper ───────────────────────────────────────────────────────────
  async function sendCartRequest(action, data) {
    const formData = new FormData();
    formData.append('action', action);
    for (const key in data) formData.append(key, data[key]);
    try {
      const res = await fetch('/DRIP-N-STYLE/App/Controllers/CartController.php',
        { method:'POST', body: formData });
      return await res.json();
    } catch (err) {
      console.error(err);
      return { success:false, message:'Something went wrong.' };
    }
  }

  // ── Add to Cart / Log In button click ────────────────────────────────────
  actionBtn.addEventListener('click', async () => {

    // Not logged in → prompt login
    if (!isLoggedIn) {
      const result = await Swal.fire({
        icon: 'info',
        title: 'Login Required',
        text: 'You need to log in before you can check out.',
        showCancelButton  : true,
        confirmButtonText : 'Log In',
        cancelButtonText  : 'Cancel',
        confirmButtonColor: '#c9a84c',
        cancelButtonColor : '#6c757d'
      });
      if (result.isConfirmed) window.location.href = '../LoginPage.php';
      return;
    }

    // Logged in → validate combo
    if (!selectedSize || !selectedColor || !selectedVariation) {
      Swal.fire({ icon:'warning', title:'Select Options',
        text:'Please choose both a size and a color.', confirmButtonColor:'#c9a84c' });
      return;
    }

    const qty    = parseInt(qtyInput.value, 10) || 1;
    const maxQty = parseInt(qtyInput.max,   10) || qty;
    if (qty < 1 || qty > maxQty) {
      Swal.fire({ icon:'error', title:'Invalid Quantity',
        text: qty < 1 ? 'Quantity must be at least 1.' : `Max ${maxQty} items.`,
        confirmButtonColor:'#c9a84c' });
      return;
    }

    actionBtn.disabled    = true;
    const original        = actionBtn.textContent;
    actionBtn.textContent = 'Adding…';

    const res = await sendCartRequest('add', {
      product_id: selectedVariation.product_id,
      quantity  : qty,
      price     : selectedVariation.price
    });

    actionBtn.disabled    = false;
    actionBtn.textContent = original;

    if (res.success) {
      Swal.fire({
        icon: 'success', title: 'Added to Cart!',
        text: `${currentProduct.name} (${selectedSize}, ${selectedColor}) ×${qty} added.`,
        confirmButtonColor:'#c9a84c', timer:2000, showConfirmButton:false
      });
      qtyInput.value = 1;
      setTimeout(closeModal, 1600);

    } else if (res.need_login) {
      Swal.fire({ icon:'warning', title:'Login Required',
        text:'Please log in to continue.',
        confirmButtonText:'Go to Login', confirmButtonColor:'#c9a84c'
      }).then(r => { if (r.isConfirmed) window.location.href = '../LoginPage.php'; });

    } else {
      Swal.fire({ icon:'error', title:'Error',
        text: res.message || 'Failed to add to cart.', confirmButtonColor:'#c9a84c' });
    }
  });
});