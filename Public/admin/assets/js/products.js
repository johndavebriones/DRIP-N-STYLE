// File: Public/admin/assets/js/products.js
document.addEventListener("DOMContentLoaded", () => {
    const productModalEl = document.getElementById('product_modal');
    const productModal = new bootstrap.Modal(productModalEl);
    const historyModalEl = document.getElementById('history_modal');
    const historyModal = new bootstrap.Modal(historyModalEl);

    const addBtn = document.getElementById('product_modal_btn');
    const historyBtn = document.getElementById('history_modal_btn');
    const productForm = document.getElementById('productForm');

    const imagePreview = document.getElementById('image_preview');
    const fileInput = document.getElementById('product_image');
    const stockInput = document.getElementById('product_stock');
    const statusSelect = document.getElementById('product_status');

    // ============================================================
    // 🟢 AUTO-REFRESH FILTERS
    // ============================================================
    const filterForm = document.getElementById('filterForm');
    [document.getElementById("searchBar"), document.getElementById("filterCategory"), document.getElementById("filterStatus")]
        .forEach(el => {
            if (!el) return;
            el.addEventListener('input', () => {
                clearTimeout(el._timeout);
                el._timeout = setTimeout(() => filterForm.submit(), 400);
            });
            el.addEventListener('change', () => filterForm.submit());
        });

    // ============================================================
    // 🟡 ADD PRODUCT
    // ============================================================
    addBtn?.addEventListener('click', () => {
        productForm.reset();
        imagePreview.style.display = "none";
        imagePreview.src = "";
        productForm.querySelector('[name="action"]').value = 'add';
        document.getElementById('productModalLabel').textContent = 'Add Product';
        productModal.show();
    });

    // ============================================================
    // 🟣 IMAGE PREVIEW (Add + Edit)
    // ============================================================
    if (fileInput && imagePreview) {
        fileInput.addEventListener('change', e => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = () => {
                    imagePreview.src = reader.result;
                    imagePreview.style.display = "block";
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // ============================================================
    // 🟠 EDIT PRODUCT
    // ============================================================
    document.querySelectorAll('.edit-product-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const productId = btn.dataset.productId;
            if (!productId) return;

            try {
                const formData = new FormData();
                formData.append('action', 'getProductById');
                formData.append('product_id', productId);

                const res = await fetch('../../App/Helpers/productHelper.php', { method: 'POST', body: formData });
                const data = await res.json();

                if (!data.success || !data.product) {
                    Swal.fire('Error!', 'Product not found', 'error');
                    return;
                }

                const p = data.product;
                productForm.reset();

                productForm.querySelector('[name="action"]').value = 'edit';
                document.getElementById('product_id').value = p.product_id;
                document.getElementById('product_name').value = p.name;
                document.getElementById('product_price').value = p.price;
                document.getElementById('product_size').value = p.size || '';
                document.getElementById('product_stock').value = p.stock;
                document.getElementById('product_status').value = p.status;
                document.getElementById('product_category').value = p.category_id;
                document.getElementById('existing_image').value = p.image || '';

                // 🖼️ Show existing image
                if (p.image) {
                    imagePreview.src = `../../Public/${p.image}`;
                    imagePreview.style.display = "block";
                } else {
                    imagePreview.style.display = "none";
                }

                document.getElementById('productModalLabel').textContent = 'Edit Product';
                productModal.show();

            } catch (err) {
                console.error(err);
                Swal.fire('Error!', 'Failed to fetch product data', 'error');
            }
        });
    });

    // ============================================================
    // 🔵 AUTO UPDATE STATUS BASED ON STOCK
    // ============================================================
    if (stockInput && statusSelect) {
        stockInput.addEventListener('input', () => {
            const stock = parseInt(stockInput.value, 10);
            statusSelect.value = (!isNaN(stock) && stock > 0) ? 'Available' : 'Out of Stock';
        });
    }

    // ============================================================
    // 🟢 SAVE PRODUCT (ADD + EDIT)
    // ============================================================
    productForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(productForm);

        const stock = parseInt(formData.get('stock') || 0, 10);
        formData.set('status', stock > 0 ? 'Available' : 'Out of Stock');

        Swal.fire({
            title: 'Saving...',
            text: 'Please wait',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            const res = await fetch('../../App/Helpers/productHelper.php', {
                method: 'POST',
                body: formData
            });
            const text = await res.text();
            let data;
            try { data = JSON.parse(text); }
            catch { throw new Error("Invalid JSON response: " + text); }

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: data.message || 'Success!',
                    timer: 1500,
                    showConfirmButton: false
                });
                productModal.hide();
                setTimeout(() => location.reload(), 800);
            } else {
                Swal.fire('Error!', data.message || 'Something went wrong', 'error');
            }
        } catch (err) {
            console.error(err);
            Swal.fire('Server Error', err.message, 'error');
        }
    });

    // ============================================================
    // 🔴 SOFT DELETE PRODUCT
    // ============================================================
    document.querySelectorAll('.delete-product-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const productId = btn.dataset.productId;
            if (!productId) return;

            Swal.fire({
                title: 'Are you sure?',
                text: "This product will be marked as deleted.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete!'
            }).then(result => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('product_id', productId);

                    fetch('../../App/Helpers/productHelper.php', { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('Deleted!', data.message, 'success');
                                setTimeout(() => location.reload(), 800);
                            } else {
                                Swal.fire('Error!', data.message, 'error');
                            }
                        })
                        .catch(() => Swal.fire('Error!', 'Server error', 'error'));
                }
            });
        });
    });

    // ============================================================
    // 🕘 HISTORY MODAL
    // ============================================================
    historyBtn?.addEventListener('click', fetchDeletedProducts);

    async function fetchDeletedProducts() {
        const formData = new FormData();
        formData.append('action', 'getDeletedProducts');
        try {
            const res = await fetch('../../App/Helpers/productHelper.php', { method: 'POST', body: formData });
            const data = await res.json();

            if (data.success) {
                renderHistoryTable(data.deletedProducts);
                historyModal.show();
            } else {
                Swal.fire('Error!', 'Could not fetch deleted products', 'error');
            }
        } catch (err) {
            console.error(err);
            Swal.fire('Error!', 'Server error', 'error');
        }
    }

    function renderHistoryTable(products) {
        const tbody = document.getElementById('history_tbody');
        tbody.innerHTML = '';

        if (!products.length) {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center">No deleted products</td></tr>`;
            return;
        }

        products.forEach(p => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${p.name}</td>
                <td>${p.category_name || 'Uncategorized'}</td>
                <td>₱${parseFloat(p.price).toFixed(2)}</td>
                <td>${p.size}</td>
                <td>${p.stock}</td>
                <td>${p.deleted_at}</td>
                <td>
                    <button class="btn btn-sm btn-danger permanent-delete-btn" data-product-id="${p.product_id}">
                        Permanent Delete
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        tbody.querySelectorAll('.permanent-delete-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const productId = btn.dataset.productId;
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This product will be permanently deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete permanently!'
                }).then(result => {
                    if (result.isConfirmed) {
                        const formData = new FormData();
                        formData.append('action', 'permanentDelete');
                        formData.append('product_id', productId);

                        fetch('../../App/Helpers/productHelper.php', { method: 'POST', body: formData })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire('Deleted!', data.message, 'success');
                                    setTimeout(() => fetchDeletedProducts(), 800);
                                } else {
                                    Swal.fire('Error!', data.message, 'error');
                                }
                            })
                            .catch(() => Swal.fire('Error!', 'Server error', 'error'));
                    }
                });
            });
        });
    }
});
