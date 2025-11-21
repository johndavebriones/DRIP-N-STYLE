document.addEventListener('DOMContentLoaded', () => {
  const productCards = document.querySelectorAll('.product-clickable');
  const modalEl = document.getElementById('productDetailModal');
  const modal = new bootstrap.Modal(modalEl);
  const actionBtn = document.getElementById('modalActionBtn');
  const qtyInput = document.getElementById('detailQty');
  const isLoggedIn = modalEl.dataset.loggedin === "1";

  // Open modal
  productCards.forEach(card => {
    card.addEventListener('click', () => {
      document.getElementById('detailImage').src = card.dataset.image;
      document.getElementById('detailImage').dataset.id = card.dataset.id;
      document.getElementById('detailName').textContent = card.dataset.name;
      document.getElementById('detailCategory').textContent = card.dataset.category;
      document.getElementById('detailPrice').textContent = `₱${parseFloat(card.dataset.price).toFixed(2)}`;
      document.getElementById('detailStock').textContent = `Stock: ${card.dataset.stock}`;
      document.getElementById('detailDescription').textContent = card.dataset.description || 'No description available.';

      if (isLoggedIn) {
        qtyInput.style.display = 'block';
        qtyInput.value = 1;
        qtyInput.max = card.dataset.stock;
        actionBtn.textContent = 'Add to Cart';
        actionBtn.classList.replace('btn-dark', 'btn-warning');
      } else {
        qtyInput.style.display = 'none';
        actionBtn.textContent = 'Log in to Add to Cart';
        actionBtn.classList.replace('btn-warning', 'btn-dark');
      }

      modal.show();
    });
  });

  // Add to cart
  actionBtn.addEventListener('click', () => {
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

    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('product_id', document.getElementById('detailImage').dataset.id);
    formData.append('quantity', qty);
    formData.append('price', parseFloat(document.getElementById('detailPrice').textContent.replace(/[₱,]/g, '')));

    fetch('/DRIP-N-STYLE/App/Controllers/CartController.php', { method: 'POST', body: formData })
      .then(res => res.json())
      .then(data => {
        Swal.fire({
          toast: true,
          position: 'bottom-end',
          icon: data.success ? 'success' : 'error',
          title: data.message,
          showConfirmButton: false,
          timer: 1500
        });
        if (data.success) modal.hide();
      })
      .catch(() => {
        Swal.fire({ icon: 'error', title: 'Something went wrong' });
      });
  });
});
