// File: Public/admin/assets/js/products.js
document.addEventListener("DOMContentLoaded", () => {
    // Modal Elements
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
    const descInput = document.getElementById('product_description');

    // Helper: POST Request
    const postData = async (url, formData) => {
        try {
            const res = await fetch(url, { method: 'POST', body: formData });
            const text = await res.text();
            try { return JSON.parse(text); }
            catch { throw new Error("Invalid JSON: " + text); }
        } catch (err) {
            console.error(err);
            Swal.fire('Error!', err.message, 'error');
            return null;
        }
    };

    // Auto-Refresh Filters
    const filterForm = document.getElementById('filterForm');
    ['searchBar', 'filterCategory', 'filterStatus'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('input', () => {
            clearTimeout(el._timeout);
            el._timeout = setTimeout(() => filterForm.submit(), 400);
        });
    });

    // Add Product
    addBtn?.addEventListener('click', () => {
        productForm.reset();
        imagePreview.style.display = "none";
        imagePreview.src = "";
        descInput.value = "";
        productForm.querySelector('[name="action"]').value = 'add';
        document.getElementById('productModalLabel').textContent = 'Add Product';
        productModal.show();
    });

    // Image Preview
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

    // Edit Product
    document.querySelectorAll('.edit-product-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const productId = btn.dataset.productId;
            if (!productId) return;

            const formData = new FormData();
            formData.append('action', 'getProductById');
            formData.append('product_id', productId);

            const result = await postData('../../App/Helpers/productHelper.php', formData);

            if (!result?.success || !result.product) {
                Swal.fire('Error!', 'Product not found', 'error');
                return;
            }

            const p = result.product;

            productForm.reset();
            productForm.querySelector('[name="action"]').value = 'edit';

            document.getElementById('product_id').value = p.product_id;
            document.getElementById('product_name').value = p.name;
            document.getElementById('product_price').value = p.price;
            document.getElementById('product_size').value = p.size;
            document.getElementById('product_stock').value = p.stock;
            document.getElementById('product_status').value = p.status;
            document.getElementById('product_category').value = p.category_id;

            // Description
            document.getElementById('product_description').value = p.description ?? '';

            // Existing image
            document.getElementById('existing_image').value = p.image || '';
            if (p.image) {
                imagePreview.src = `../../Public/${p.image}`;
                imagePreview.style.display = "block";
            } else {
                imagePreview.style.display = "none";
            }

            document.getElementById('productModalLabel').textContent = 'Edit Product';
            productModal.show();
        });
    });

    // Auto Update Status Based on Stock
    if (stockInput && statusSelect) {
        stockInput.addEventListener('input', () => {
            const stock = parseInt(stockInput.value, 10);
            statusSelect.value = (!isNaN(stock) && stock > 0) ? 'Available' : 'Out of Stock';
        });
    }

    // Save Product (Add + Edit)
    productForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(productForm);

        const stock = parseInt(formData.get('stock') || 0, 10);
        formData.set('status', stock > 0 ? 'Available' : 'Out of Stock');
        formData.set('description', descInput.value.trim());

        Swal.fire({ title: 'Saving...', text: 'Please wait', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        const data = await postData('../../App/Helpers/productHelper.php', formData);
        if (!data) return;

        Swal.close();
        if (data.success) {
            Swal.fire({ icon: 'success', title: data.message, timer: 1500, showConfirmButton: false });
            productModal.hide();
            setTimeout(() => location.reload(), 800);
        } else {
            if (data.message?.includes('duplicate')) {
                Swal.fire('Warning!', data.message, 'warning');
            } else {
                Swal.fire('Error!', data.message || 'Something went wrong', 'error');
            }
        }
    });

    // Soft Delete Product
    document.querySelectorAll('.delete-product-btn').forEach(btn => {
        btn.addEventListener('click', () => handleSoftDelete(btn.dataset.productId));
    });

    async function handleSoftDelete(productId) {
        if (!productId) return;

        const result = await Swal.fire({
            title: 'Delete Product?',
            text: 'This product will be archived and moved to history.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete'
        });

        if (!result.isConfirmed) return;

        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('product_id', productId);

        const data = await postData('../../App/Helpers/productHelper.php', formData);
        if (!data) return;

        if (data.success) {
            Swal.fire('Deleted!', data.message, 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            Swal.fire('Error!', data.message, 'error');
        }
    }

    // History Modal (Deleted Products)
    historyBtn?.addEventListener('click', fetchDeletedProducts);

    async function fetchDeletedProducts() {
        const formData = new FormData();
        formData.append('action', 'getDeletedProducts');
        const data = await postData('../../App/Helpers/productHelper.php', formData);
        if (!data) return;

        if (data.success) {
            renderHistoryTable(data.deletedProducts);
            historyModal.show();
        } else {
            Swal.fire('Error!', 'Could not fetch deleted products', 'error');
        }
    }

    // Render Deleted Products + Restore Button
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
                    <button class="btn btn-sm btn-success restore-product-btn" data-product-id="${p.product_id}">
                        Restore
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        tbody.querySelectorAll('.restore-product-btn').forEach(btn => {
            btn.addEventListener('click', () => handleRestore(btn.dataset.productId));
        });
    }

    // Restored Product
    async function handleRestore(productId) {
        if (!productId) return;

        const result = await Swal.fire({
            title: 'Restore Product?',
            text: 'This product will be restored and visible to customers again.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, restore'
        });

        if (!result.isConfirmed) return;

        const formData = new FormData();
        formData.append('action', 'restoreProduct');
        formData.append('product_id', productId);

        const data = await postData('../../App/Helpers/productHelper.php', formData);
        if (!data) return;

        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Restored!',
                text: data.message,
                timer: 1200,
                showConfirmButton: false
            });

            setTimeout(() => {
                location.reload();
            }, 800);
        }
        else {
            Swal.fire('Error!', data.message, 'error');
        }
    }
});
