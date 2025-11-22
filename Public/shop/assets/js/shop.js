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
        modalControls.style.display = 'flex';   // show quantity & button
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
      Swal.fire({
        toast: true,
        position: 'bottom-end',
        icon: 'warning',
        title: `You can only order up to ${maxQty} items.`,
        showConfirmButton: false,
        timer: 1500
      });
    } else if (qty < 1) {
      qtyInput.value = 1;
      Swal.fire({
        toast: true,
        position: 'bottom-end',
        icon: 'warning',
        title: 'Quantity must be at least 1.',
        showConfirmButton: false,
        timer: 1500
      });
    }
  });

  // ---- AJAX HELPERS ----
  const sendCartRequest = (action, data) => {
    const formData = new FormData();
    formData.append('action', action);
    for (const key in data) formData.append(key, data[key]);

    return fetch('/DRIP-N-STYLE/App/Controllers/CartController.php', { 
        method: 'POST', 
        body: formData 
      })
      .then(res => res.text())
      .then(text => {
        const [status, message] = text.split('|');

        Swal.fire({
          toast: true,
          position: 'bottom-end',
          icon: status === 'success' ? 'success' : 'error',
          title: message,
          showConfirmButton: false,
          timer: 1500
        });

        return status === 'success';
      })
      .catch(() => {
        Swal.fire({ icon: 'error', title: 'Something went wrong' });
        return false;
      });
  };

  // ---- ADD TO CART BUTTON ----
  actionBtn.addEventListener('click', async () => {
    if (!isLoggedIn) {
      Swal.fire({
        icon: 'info',
        title: 'Please log in',
        text: 'You need to log in to add items to your cart.',
        showCancelButton: true,
        confirmButtonText: 'Log In'
      }).then(result => {
        if (result.isConfirmed) window.location.href = '../LoginPage.php';
      });
      return;
    }

    const qty = parseInt(qtyInput.value, 10) || 1;
    const maxQty = parseInt(qtyInput.max, 10) || qty;

    if (qty < 1 || qty > maxQty) {
      Swal.fire({
        icon: 'warning',
        title: qty < 1 ? 'Invalid Quantity' : 'Stock Limit',
        text: qty < 1 ? 'Quantity must be at least 1.' : `You can only order up to ${maxQty} items.`
      });
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
      setTimeout(() => document.activeElement.blur(), 10);
      setTimeout(() => modal.hide(), 60);
    }
  });
});
