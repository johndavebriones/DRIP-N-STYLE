// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Products JS initialized');

    // Auto-submit filter form on change
    const searchBar = document.getElementById('searchBar');
    const filterCategory = document.getElementById('filterCategory');
    const filterStatus = document.getElementById('filterStatus');
    const filterForm = document.getElementById('filterForm');

    if (searchBar) {
        searchBar.addEventListener('input', function() {
            filterForm.submit();
        });
    }

    if (filterCategory) {
        filterCategory.addEventListener('change', function() {
            filterForm.submit();
        });
    }

    if (filterStatus) {
        filterStatus.addEventListener('change', function() {
            filterForm.submit();
        });
    }

    // Open Add Product Modal
    const productModalBtn = document.getElementById('product_modal_btn');
    if (productModalBtn) {
        productModalBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Add Product button clicked');
            resetProductForm();
            document.getElementById('productModalLabel').textContent = 'Add Product';
            document.querySelector('input[name="action"]').value = 'add';
            
            // Show add image section, hide edit image section
            document.getElementById('add_image_section').style.display = 'block';
            document.getElementById('edit_image_section').style.display = 'none';
            
            // Make image required for add mode
            const imageFileInput = document.getElementById('product_image_file');
            if (imageFileInput) {
                imageFileInput.required = true;
            }
            
            const productModalEl = document.getElementById('product_modal');
            
            try {
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    let modal = bootstrap.Modal.getInstance(productModalEl);
                    if (!modal) {
                        modal = new bootstrap.Modal(productModalEl);
                    }
                    modal.show();
                    console.log('Add Product modal opened');
                } else {
                    throw new Error('Bootstrap not available');
                }
            } catch (error) {
                console.error('Bootstrap modal error:', error);
                productModalEl.classList.add('show');
                productModalEl.style.display = 'block';
                productModalEl.setAttribute('aria-modal', 'true');
                productModalEl.setAttribute('role', 'dialog');
                productModalEl.removeAttribute('aria-hidden');
                
                let backdrop = document.querySelector('.modal-backdrop');
                if (!backdrop) {
                    backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop fade show';
                    document.body.appendChild(backdrop);
                }
                document.body.classList.add('modal-open');
                console.log('Modal opened manually');
            }
        });
    }

    // Open Add Variant Modal - Using event delegation
    document.addEventListener('click', function(e) {
        const variantBtn = e.target.closest('.add-variant-btn');
        if (variantBtn) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Add Variant button clicked!');
            
            const productName = variantBtn.getAttribute('data-product-name');
            const productPrice = variantBtn.getAttribute('data-product-price');
            const categoryId = variantBtn.getAttribute('data-category-id');
            const productImage = variantBtn.getAttribute('data-product-image');

            console.log('Variant data:', {
                name: productName, 
                price: productPrice, 
                category: categoryId,
                image: productImage
            });

            // Reset the form first
            resetVariantForm();

            // Set form values
            const variantNameEl = document.getElementById('variant_name');
            const variantPriceEl = document.getElementById('variant_price');
            const variantCategoryEl = document.getElementById('variant_category');
            const variantImagePathEl = document.getElementById('variant_image_path');
            
            if (variantNameEl) variantNameEl.value = productName;
            if (variantPriceEl) variantPriceEl.value = productPrice;
            if (variantCategoryEl) variantCategoryEl.value = categoryId;
            if (variantImagePathEl) variantImagePathEl.value = productImage;
            
            console.log('✓ Variant form populated with parent product data');
            
            const variantModalEl = document.getElementById('variant_modal');
            
            if (!variantModalEl) {
                console.error('Variant modal not found in DOM!');
                return;
            }
            
            try {
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    let modal = bootstrap.Modal.getInstance(variantModalEl);
                    if (!modal) {
                        modal = new bootstrap.Modal(variantModalEl);
                    }
                    modal.show();
                    console.log('Variant modal opened with Bootstrap 5');
                } else {
                    throw new Error('Bootstrap not available');
                }
            } catch (error) {
                console.error('Bootstrap modal error:', error);
                variantModalEl.classList.add('show');
                variantModalEl.style.display = 'block';
                variantModalEl.setAttribute('aria-modal', 'true');
                variantModalEl.setAttribute('role', 'dialog');
                variantModalEl.removeAttribute('aria-hidden');
                
                let backdrop = document.querySelector('.modal-backdrop');
                if (!backdrop) {
                    backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop fade show';
                    document.body.appendChild(backdrop);
                }
                document.body.classList.add('modal-open');
                console.log('Variant modal opened manually');
            }
        }
    });

    // Edit Product Button - Using event delegation
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-product-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.edit-product-btn');
            console.log('Edit button clicked');
            
            const productId = btn.getAttribute('data-product-id');
            console.log('Product ID:', productId);

            fetch('../../App/Helpers/productHelper.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=getProductById&product_id=' + productId
            })
            .then(response => response.json())
            .then(data => {
                console.log('Edit response:', data);
                if (data.success) {
                    const product = data.product;
                    
                    document.getElementById('productModalLabel').textContent = 'Edit Product';
                    document.querySelector('input[name="action"]').value = 'edit';
                    document.getElementById('product_id').value = product.product_id;
                    document.getElementById('product_name').value = product.name;
                    document.getElementById('product_category').value = product.category_id;
                    document.getElementById('product_price').value = product.price;
                    document.getElementById('product_size').value = product.size;
                    document.getElementById('product_color').value = product.color || '';
                    document.getElementById('product_stock').value = product.stock;
                    document.getElementById('product_description').value = product.description || '';

                    // Show edit image section, hide add image section
                    document.getElementById('add_image_section').style.display = 'none';
                    document.getElementById('edit_image_section').style.display = 'block';
                    
                    // Remove required attribute from file input in edit mode
                    const imageFileInput = document.getElementById('product_image_file');
                    if (imageFileInput) {
                        imageFileInput.required = false;
                    }

                    // Display current image in edit preview
                    const editImagePreview = document.getElementById('edit_image_preview');
                    if (product.image) {
                        editImagePreview.src = '../../Public/' + product.image;
                        editImagePreview.style.display = 'block';
                        console.log('✓ Image preview loaded');
                    }

                    const productModalEl = document.getElementById('product_modal');
                    
                    try {
                        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                            let modal = bootstrap.Modal.getInstance(productModalEl);
                            if (!modal) {
                                modal = new bootstrap.Modal(productModalEl);
                            }
                            modal.show();
                            console.log('Edit modal opened');
                        } else {
                            throw new Error('Bootstrap not available');
                        }
                    } catch (error) {
                        console.error('Bootstrap modal error:', error);
                        productModalEl.classList.add('show');
                        productModalEl.style.display = 'block';
                        productModalEl.setAttribute('aria-modal', 'true');
                        productModalEl.setAttribute('role', 'dialog');
                        productModalEl.removeAttribute('aria-hidden');
                        
                        let backdrop = document.querySelector('.modal-backdrop');
                        if (!backdrop) {
                            backdrop = document.createElement('div');
                            backdrop.className = 'modal-backdrop fade show';
                            document.body.appendChild(backdrop);
                        }
                        document.body.classList.add('modal-open');
                        console.log('Modal opened manually');
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                    });
                }
            })
            .catch(error => {
                console.error('Edit AJAX Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to fetch product details'
                });
            });
        }
    });

    // Delete Product Button - Using event delegation
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-product-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.delete-product-btn');
            const productId = btn.getAttribute('data-product-id');

            Swal.fire({
                title: 'Are you sure?',
                text: 'This product will be archived.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, archive it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('../../App/Helpers/productHelper.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=delete&product_id=' + productId
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Archived!',
                                text: data.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => location.reload());
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to archive product'
                        });
                    });
                }
            });
        }
    });

    // Image preview for Product (Add mode only)
    const productImageFile = document.getElementById('product_image_file');
    if (productImageFile) {
        productImageFile.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const preview = document.getElementById('add_image_preview');
                    preview.src = event.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Submit Product Form (Add/Edit)
    const productForm = document.getElementById('productForm');
    if (productForm) {
        productForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Product form submitted');
            
            const formData = new FormData(this);
            const action = formData.get('action');
            
            // SIMPLE FIX: For edit mode, just remove the image field completely
            // The backend won't update the image if it's not sent
            if (action === 'edit') {
                formData.delete('image');
                console.log('✓ Edit mode - Image field removed (will not update image)');
            }

            fetch('../../App/Helpers/productHelper.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Response:', data);
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                    });
                }
            })
            .catch(error => {
                console.error('AJAX Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while processing your request'
                });
            });
        });
    }

    // Submit Variant Form
    const variantForm = document.getElementById('variantForm');
    if (variantForm) {
        variantForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Variant form submitted');
            
            const formData = new FormData(this);
            
            // Get the image path from the hidden field
            const imagePathValue = document.getElementById('variant_image_path').value;
            
            if (imagePathValue) {
                // Add the parent product's image path
                formData.append('image', imagePathValue);
                console.log('✓ Variant will use parent image:', imagePathValue);
            } else {
                console.warn('⚠ No image path found, variant will be created without image');
            }

            fetch('../../App/Helpers/productHelper.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Variant Response:', data);
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                    });
                }
            })
            .catch(error => {
                console.error('Variant AJAX Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while adding variant'
                });
            });
        });
    }

    // Open History Modal
    const historyModalBtn = document.getElementById('history_modal_btn');
    if (historyModalBtn) {
        historyModalBtn.addEventListener('click', function(e) {
            e.preventDefault();
            loadDeletedProducts();
            const modal = new bootstrap.Modal(document.getElementById('history_modal'));
            modal.show();
        });
    }

    // Load Deleted Products
    function loadDeletedProducts() {
        fetch('../../App/Helpers/productHelper.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=getDeletedProducts'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let html = '';
                const products = data.deletedProducts;

                if (products.length === 0) {
                    html = `
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                No archived products
                            </td>
                        </tr>
                    `;
                } else {
                    products.forEach(product => {
                        const deletedDate = new Date(product.deleted_at).toLocaleString('en-US', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });

                        html += `
                            <tr>
                                <td class="ps-4">
                                    <strong>${product.name}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">${product.category_name || 'N/A'}</span>
                                </td>
                                <td class="text-success fw-bold">₱${parseFloat(product.price).toFixed(2)}</td>
                                <td>${product.size || 'N/A'}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">${product.color || 'N/A'}</span>
                                </td>
                                <td>
                                    <span class="badge ${product.stock > 0 ? 'bg-info' : 'bg-warning'} text-dark">
                                        ${product.stock}
                                    </span>
                                </td>
                                <td class="small text-muted">${deletedDate}</td>
                                <td class="text-center pe-4">
                                    <button class="btn btn-sm btn-success restore-product-btn" 
                                            data-product-id="${product.product_id}"
                                            title="Restore this product">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i>Restore
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                }

                document.getElementById('history_tbody').innerHTML = html;
            } else {
                document.getElementById('history_tbody').innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center py-4 text-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Failed to load history
                        </td>
                    </tr>
                `;
            }
        })
        .catch(error => {
            console.error('History load error:', error);
            document.getElementById('history_tbody').innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-4 text-danger">
                        <i class="bi bi-wifi-off me-2"></i>
                        Error loading history
                    </td>
                </tr>
            `;
        });
    }

    // Restore Product - Using event delegation
    document.addEventListener('click', function(e) {
        if (e.target.closest('.restore-product-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.restore-product-btn');
            const productId = btn.getAttribute('data-product-id');

            Swal.fire({
                title: 'Restore Product?',
                text: 'This product will be restored to active products.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, restore it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('../../App/Helpers/productHelper.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=restoreProduct&product_id=' + productId
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Restored!',
                                text: data.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                loadDeletedProducts();
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to restore product'
                        });
                    });
                }
            });
        }
    });

    // Reset Product Form
    function resetProductForm() {
        document.getElementById('productForm').reset();
        document.getElementById('product_id').value = '';
        document.getElementById('add_image_preview').style.display = 'none';
        document.getElementById('edit_image_preview').style.display = 'none';
        document.getElementById('product_color').value = '';
        
        // Reset file input
        const imageFileInput = document.getElementById('product_image_file');
        if (imageFileInput) {
            imageFileInput.value = '';
        }
    }

    // Reset Variant Form
    function resetVariantForm() {
        const variantSizeEl = document.getElementById('variant_size');
        const variantColorEl = document.getElementById('variant_color');
        const variantStockEl = document.getElementById('variant_stock');
        const variantDescEl = document.getElementById('variant_description');
        
        if (variantSizeEl) variantSizeEl.value = '';
        if (variantColorEl) variantColorEl.value = '';
        if (variantStockEl) variantStockEl.value = '';
        if (variantDescEl) variantDescEl.value = '';
    }
});