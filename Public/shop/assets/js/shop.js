document.addEventListener('DOMContentLoaded', () => {
    // Add to Cart from product cards
    document.querySelectorAll('.ajax-add-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const productId = btn.dataset.id;
            const price = btn.dataset.price;
            const qty = document.getElementById('ajaxQty' + productId).value || 1;

            fetch('../../App/Controllers/CartController.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ 
                    action: 'add', 
                    product_id: productId, 
                    quantity: qty, 
                    price: price 
                })
            })
            .then(res => res.text())
            .then(() => {
                Swal.fire({
                    toast: true,
                    position: 'bottom-end',
                    icon: 'success',
                    title: 'Added to cart!',
                    showConfirmButton: false,
                    timer: 1500
                });
            })
            .catch(err => console.error(err));
        });
    });

    // Product Detail Modal
    const modalEl = document.getElementById('productDetailModal');
    const productModal = new bootstrap.Modal(modalEl);
    const detailImage = document.getElementById('detailImage');
    const detailName = document.getElementById('detailName');
    const detailCategory = document.getElementById('detailCategory');
    const detailPrice = document.getElementById('detailPrice');
    const detailStock = document.getElementById('detailStock');
    const detailDescription = document.getElementById('detailDescription');
    const detailQty = document.getElementById('detailQty');
    const detailAddBtn = document.getElementById('detailAddBtn');

    document.querySelectorAll('.product-clickable').forEach(card => {
        card.addEventListener('click', async () => {
            const productId = card.dataset.id;
            const formData = new FormData();
            formData.append('action', 'getProductById');
            formData.append('product_id', productId);

            try {
                const res = await fetch('../../App/Helpers/productHelper.php', { method: 'POST', body: formData });
                const data = await res.json();

                if (!data.success || !data.product) throw new Error('Product not found');

                const p = data.product;
                detailImage.src = `../../Public/${p.image || 'uploads/no-image.png'}`;
                detailName.textContent = p.name;
                detailCategory.textContent = p.category_name || 'Uncategorized';
                detailPrice.textContent = `₱${parseFloat(p.price).toFixed(2)}`;
                detailStock.textContent = `Stock: ${p.stock}`;
                detailDescription.textContent = p.description || '';
                detailQty.value = 1;
                detailQty.max = p.stock;

                // Add to cart from modal
                detailAddBtn.onclick = () => {
                    const qtyVal = detailQty.value || 1;
                    fetch('../../App/Controllers/CartController.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ 
                            action: 'add', 
                            product_id: p.product_id, 
                            quantity: qtyVal, 
                            price: p.price 
                        })
                    })
                    .then(res => res.text())
                    .then(() => {
                        Swal.fire({
                            toast: true,
                            position: 'bottom-end',
                            icon: 'success',
                            title: 'Added to cart!',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    })
                    .catch(err => console.error(err));
                };

                productModal.show();
            } catch (err) {
                console.error(err);
                Swal.fire('Error', err.message, 'error');
            }
        });
    });
});
