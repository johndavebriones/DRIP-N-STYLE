document.addEventListener('DOMContentLoaded', () => {

  const productCards = document.querySelectorAll('.product-clickable');
  const modalEl = document.getElementById('productDetailModal');
  const modal = new bootstrap.Modal(modalEl);

  const actionBtn = document.getElementById('modalActionBtn');
  const qtyInput = document.getElementById('detailQty');

  // Login status from modal data attribute
  const isLoggedIn = modalEl.dataset.loggedin === "1";

  productCards.forEach(card => {
    card.addEventListener('click', () => {

      // Populate modal fields from card data attributes
      document.getElementById('detailImage').src = card.dataset.image;
      document.getElementById('detailImage').alt = card.dataset.name;
      document.getElementById('detailName').textContent = card.dataset.name;
      document.getElementById('detailCategory').textContent = card.dataset.category;
      document.getElementById('detailPrice').textContent = `₱${parseFloat(card.dataset.price).toFixed(2)}`;
      document.getElementById('detailStock').textContent = `Stock: ${card.dataset.stock}`;
      document.getElementById('detailDescription').textContent = card.dataset.description || 'No description available.';

      if (isLoggedIn) {
        // Logged-in: show quantity input and Add to Cart button
        qtyInput.style.display = 'block';
        qtyInput.value = 1;
        qtyInput.max = card.dataset.stock;

        actionBtn.textContent = 'Add to Cart';
        actionBtn.classList.remove('btn-dark');
        actionBtn.classList.add('btn-warning');

      } else {
        // Guest: hide quantity input and show login button
        qtyInput.style.display = 'none';
        actionBtn.textContent = 'Log in to Add to Cart';
        actionBtn.classList.remove('btn-warning');
        actionBtn.classList.add('btn-dark');
      }

      modal.show();
    });
  });

  actionBtn.addEventListener('click', () => {
    if (isLoggedIn) {
      // Add to Cart logic
      const qty = parseInt(qtyInput.value, 10) || 1;
      const maxQty = parseInt(qtyInput.max, 10) || qty;

      if (qty < 1) {
        Swal.fire({ icon: 'warning', title: 'Invalid Quantity', text: 'Quantity must be at least 1.' });
        return;
      }

      if (qty > maxQty) {
        Swal.fire({ icon: 'warning', title: 'Stock Limit', text: `You can only order up to ${maxQty} items.` });
        return;
      }

      // TODO: Replace with your real add-to-cart logic
      Swal.fire({
        icon: 'success',
        title: 'Added!',
        text: `${qty} item(s) added to your cart.`,
        timer: 1500,
        showConfirmButton: false
      });

      modal.hide();

    } else {
      // Guest: prompt to log in
      Swal.fire({
        icon: 'info',
        title: 'Please log in',
        text: 'You need to log in to add items to your cart.',
        showCancelButton: true,
        confirmButtonText: 'Log In',
        cancelButtonText: 'Cancel'
      }).then(result => {
        if (result.isConfirmed) {
          window.location.href = '../LoginPage.php';
        }
      });
    }
  });

});
