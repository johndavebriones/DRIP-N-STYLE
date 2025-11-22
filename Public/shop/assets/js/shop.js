document.addEventListener('DOMContentLoaded', () => {
  const productCards = document.querySelectorAll('.product-clickable');
  const modalEl = document.getElementById('productDetailModal');
  const modal = new bootstrap.Modal(modalEl);
  const actionBtn = document.getElementById('modalActionBtn');
  const qtyInput = document.getElementById('detailQty');
  const isLoggedIn = modalEl.dataset.loggedin === "1";

  // ---- OPEN MODAL ----
  productCards.forEach(card => {
    card.addEventListener('click', () => {
      const img = document.getElementById('detailImage');
      img.src = card.dataset.image;
      img.dataset.id = card.dataset.id;

      document.getElementById('detailName').textContent = card.dataset.name;
      document.getElementById('detailCategory').textContent = card.dataset.category;
      document.getElementById('detailSize').textContent = 'Size: ' + (card.dataset.size || 'N/A');
      document.getElementById('detailPrice').textContent = `₱${parseFloat(card.dataset.price).toFixed(2)}`;
      document.getElementById('detailStock').textContent = `Stock: ${card.dataset.stock}`;
      document.getElementById('detailDescription').textContent = card.dataset.description || 'No description available.';

      const stockEl = document.getElementById('detailStock');
      const modalControls = document.getElementById('modalControls');

      if (isLoggedIn) {
        modalControls.style.display = 'flex';
        qtyInput.style.display = 'block';
        qtyInput.value = 1;
        qtyInput.max = card.dataset.stock;
        actionBtn.textContent = 'Add to Cart';
        actionBtn.classList.replace('btn-dark', 'btn-warning');
        actionBtn.classList.remove('w-100');
        actionBtn.classList.add('w-50');
        modalControls.classList.remove('justify-content-end');
      } else {
        qtyInput.style.display = 'none';
        stockEl.style.display = 'none';
        actionBtn.textContent = 'Log in to Add to Cart';
        modalControls.classList.add('justify-content-end');
        actionBtn.classList.remove('w-50');
        actionBtn.classList.add('w-auto');
        actionBtn.classList.replace('btn-warning', 'btn-dark');
      }

      modal.show();
    });
  });

  // ---- Quantity input validation ----
  qtyInput.addEventListener('input', () => {
    const maxQty = parseInt(qtyInput.max, 10) || 1;
    let qty = parseInt(qtyInput.value, 10) || 1;

    if (qty > maxQty) {
      qtyInput.value = maxQty;
      alert(`You can only order up to ${maxQty} items.`);
    } else if (qty < 1) {
      qtyInput.value = 1;
      alert('Quantity must be at least 1.');
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
      const json = await res.json(); // parse JSON response

      alert(json.message); // default alert instead of SweetAlert

      return json.success;
    } catch (err) {
      console.error(err);
      alert('Something went wrong.');
      return false;
    }
  };

  // ---- ADD TO CART BUTTON ----
  actionBtn.addEventListener('click', async () => {
    if (!isLoggedIn) {
      if (confirm('You need to log in to add items to your cart. Do you want to log in now?')) {
        window.location.href = '../LoginPage.php';
      }
      return;
    }

    const qty = parseInt(qtyInput.value, 10) || 1;
    const maxQty = parseInt(qtyInput.max, 10) || qty;

    if (qty < 1 || qty > maxQty) {
      alert(qty < 1 ? 'Quantity must be at least 1.' : `You can only order up to ${maxQty} items.`);
      return;
    }

    const productId = document.getElementById('detailImage').dataset.id;
    const price = parseFloat(
      document.getElementById('detailPrice').textContent.replace(/[₱,]/g, '')
    );

    const success = await sendCartRequest('add', { 
      product_id: productId, 
      quantity: qty, 
      price 
    });

    if (success) {
      qtyInput.value = 1;
      setTimeout(() => modal.hide(), 60);
    }
  });
});
