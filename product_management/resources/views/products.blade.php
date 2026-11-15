<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Product Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-primary mb-4">
    <div class="container">
        <span class="navbar-brand"><i class="bi bi-box-seam"></i> Product Management</span>
    </div>
</nav>

<div class="container">
    <div id="alertBox"></div>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">Products <span class="badge rounded-pill text-bg-light border" id="productCount"></span></h5>
            <button class="btn btn-primary" onclick="openForm()">
                <i class="bi bi-plus-lg"></i> Add<span class="d-none d-sm-inline"> Product</span>
            </button>
        </div>

        <!-- Phones & tablets: one card per product -->
        <div class="d-lg-none p-2 p-sm-3">
            <div class="row g-2 g-sm-3" id="productCards">
                <div class="col-12 text-center py-4">Loading...</div>
            </div>
        </div>

        <!-- Large screens: table -->
        <div class="table-responsive d-none d-lg-block">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Barcode</th>
                    <th>Expire Date</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Price In</th>
                    <th class="text-end">Price Out</th>
                    <th class="text-center">Action</th>
                </tr>
                </thead>
                <tbody id="productRows">
                <tr><td colspan="10" class="text-center py-4">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Insert / Edit form -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-fullscreen-sm-down modal-dialog-scrollable">
        <form class="modal-content" id="productForm">
            <div class="modal-header">
                <h5 class="modal-title" id="formTitle">Add Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <input type="hidden" id="ProductID">
                <div class="col-md-6">
                    <label class="form-label">Product Name *</label>
                    <input class="form-control" id="ProductName" maxlength="100" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Category *</label>
                    <select class="form-select" id="CategoryID" required></select>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <input class="form-control" id="Description" maxlength="255">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Barcode</label>
                    <input class="form-control" id="Barcode" maxlength="255">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Expire Date</label>
                    <input type="date" class="form-control" id="Expiredate">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Quantity *</label>
                    <input type="number" class="form-control" id="Qty" min="0" value="0" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Unit Price In *</label>
                    <input type="number" class="form-control" id="UnitPriceIn" min="0" step="0.01" value="0.00" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Unit Price Out *</label>
                    <input type="number" class="form-control" id="UnitPriceOut" min="0" step="0.01" value="0.00" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Product Image</label>
                    <div class="d-flex align-items-center gap-3">
                        <img id="imagePreview" src="" alt="" class="rounded border d-none"
                             style="width:80px;height:80px;object-fit:cover">
                        <input type="file" class="form-control" id="ProductImage" accept="image/*">
                    </div>
                    <div class="form-text">Choose a picture from your computer (JPG, PNG..., max 2 MB).
                        When editing, leave empty to keep the current picture.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveButton">Save Product</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // This page uses the same REST API as the Flutter app.
    const API = '{{ url('/api') }}';
    const headers = { 'Accept': 'application/json' };
    const modal = new bootstrap.Modal(document.getElementById('productModal'));
    const fields = ['ProductName', 'Description', 'CategoryID', 'Barcode', 'Expiredate',
                    'Qty', 'UnitPriceIn', 'UnitPriceOut'];
    const imageInput = document.getElementById('ProductImage');
    const imagePreview = document.getElementById('imagePreview');
    let products = [];

    // Escape text before putting it in HTML
    function esc(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    function showAlert(message, type = 'success') {
        document.getElementById('alertBox').innerHTML =
            `<div class="alert alert-${type} alert-dismissible fade show">${esc(message)}
             <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
    }

    async function errorText(response) {
        const data = await response.json().catch(() => ({}));
        if (data.errors) return Object.values(data.errors).flat().join(' ');
        return data.message || 'Error ' + response.status;
    }

    // Product picture, or a grey box with an icon when there is none
    function imageBox(p, size) {
        const style = `width:${size}px;height:${size}px;flex-shrink:0`;
        return p.image_url
            ? `<img src="${esc(p.image_url)}" class="rounded border" style="${style};object-fit:cover">`
            : `<div class="rounded border bg-light text-muted d-flex align-items-center justify-content-center" style="${style}"><i class="bi bi-image"></i></div>`;
    }

    // Display Products
    async function loadProducts() {
        const response = await fetch(`${API}/products`, { headers });
        products = await response.json();
        const rows = document.getElementById('productRows');
        const cards = document.getElementById('productCards');
        document.getElementById('productCount').textContent = products.length;

        if (products.length === 0) {
            rows.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-muted">No products yet.</td></tr>';
            cards.innerHTML = '<div class="col-12 text-center py-4 text-muted">No products yet.</div>';
            return;
        }

        // Cards for phones / tablets
        cards.innerHTML = products.map(p => `
            <div class="col-12 col-md-6">
                <div class="border rounded-3 p-3 h-100 bg-white">
                    <div class="d-flex gap-3">
                        ${imageBox(p, 72)}
                        <div class="flex-grow-1" style="min-width:0">
                            <div class="fw-bold text-truncate">${esc(p.ProductName)}</div>
                            <div class="small text-muted text-truncate">${esc(p.Description)}</div>
                            <span class="badge text-bg-secondary mt-1">${esc(p.category ? p.category.category_name : p.CategoryID)}</span>
                        </div>
                        <div class="small text-muted">#${p.ProductID}</div>
                    </div>
                    <div class="row g-2 small mt-2 text-center">
                        <div class="col-4"><div class="bg-light rounded py-1"><div class="text-muted">Qty</div><b>${p.Qty}</b></div></div>
                        <div class="col-4"><div class="bg-light rounded py-1"><div class="text-muted">Price In</div><b>$${esc(p.UnitPriceIn)}</b></div></div>
                        <div class="col-4"><div class="bg-light rounded py-1"><div class="text-muted">Price Out</div><b>$${esc(p.UnitPriceOut)}</b></div></div>
                    </div>
                    <div class="small text-muted mt-2">
                        ${p.Barcode ? `<i class="bi bi-upc"></i> ${esc(p.Barcode)}` : ''}
                        ${p.Expiredate ? `<span class="ms-2"><i class="bi bi-calendar-event"></i> ${esc(p.Expiredate)}</span>` : ''}
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <button class="btn btn-sm btn-outline-primary flex-fill" onclick="openForm(${p.ProductID})"><i class="bi bi-pencil"></i> Edit</button>
                        <button class="btn btn-sm btn-outline-danger flex-fill" onclick="deleteProduct(${p.ProductID})"><i class="bi bi-trash"></i> Delete</button>
                    </div>
                </div>
            </div>`).join('');

        // Table for large screens
        rows.innerHTML = products.map(p => `
            <tr>
                <td>${p.ProductID}</td>
                <td>${imageBox(p, 48)}</td>
                <td><strong>${esc(p.ProductName)}</strong><br><small class="text-muted">${esc(p.Description)}</small></td>
                <td><span class="badge text-bg-secondary">${esc(p.category ? p.category.category_name : p.CategoryID)}</span></td>
                <td>${esc(p.Barcode)}</td>
                <td>${esc(p.Expiredate)}</td>
                <td class="text-end">${p.Qty}</td>
                <td class="text-end">$${esc(p.UnitPriceIn)}</td>
                <td class="text-end">$${esc(p.UnitPriceOut)}</td>
                <td class="text-center text-nowrap">
                    <button class="btn btn-sm btn-outline-primary" onclick="openForm(${p.ProductID})"><i class="bi bi-pencil"></i> Edit</button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteProduct(${p.ProductID})"><i class="bi bi-trash"></i> Delete</button>
                </td>
            </tr>`).join('');
    }

    async function loadCategories() {
        const response = await fetch(`${API}/categories`, { headers });
        const categories = await response.json();
        document.getElementById('CategoryID').innerHTML =
            '<option value="">-- Choose category --</option>' +
            categories.map(c => `<option value="${c.category_id}">${esc(c.category_name)}</option>`).join('');
    }

    // Open the form: no id = Insert, with id = Edit
    function openForm(id = null) {
        const form = document.getElementById('productForm');
        form.reset();
        document.getElementById('ProductID').value = id ?? '';
        document.getElementById('formTitle').textContent = id ? 'Edit Product' : 'Add Product';
        document.getElementById('saveButton').textContent = id ? 'Update Product' : 'Save Product';

        let currentImage = null;
        if (id) {
            const p = products.find(item => item.ProductID === id);
            fields.forEach(f => document.getElementById(f).value = p[f] ?? '');
            currentImage = p.image_url;
        }
        setPreview(currentImage);
        modal.show();
    }

    function setPreview(src) {
        imagePreview.src = src || '';
        imagePreview.classList.toggle('d-none', !src);
    }

    // Show the chosen picture before saving
    imageInput.addEventListener('change', () => {
        const file = imageInput.files[0];
        if (file) setPreview(URL.createObjectURL(file));
    });

    // Insert Product / Edit Product
    document.getElementById('productForm').addEventListener('submit', async (event) => {
        event.preventDefault();
        const id = document.getElementById('ProductID').value;
        // form-data so the image file can be uploaded
        const body = new FormData();
        fields.forEach(f => body.append(f, document.getElementById(f).value.trim()));
        if (imageInput.files[0]) body.append('ProductImage', imageInput.files[0]);
        if (id) body.append('_method', 'PUT'); // files must be sent with POST, Laravel treats it as PUT

        const response = await fetch(id ? `${API}/products/${id}` : `${API}/products`, {
            method: 'POST',
            headers,
            body,
        });

        if (response.ok) {
            modal.hide();
            showAlert(id ? 'Product updated successfully' : 'Product added successfully');
            loadProducts();
        } else {
            alert(await errorText(response)); // keep the form open so the user can fix it
        }
    });

    // Delete Product (the API sets isDeleted = 1)
    async function deleteProduct(id) {
        const p = products.find(item => item.ProductID === id);
        if (!confirm(`Are you sure you want to delete "${p.ProductName}"?`)) return;

        const response = await fetch(`${API}/products/${id}`, { method: 'DELETE', headers });
        if (response.ok) {
            showAlert('Product deleted successfully');
            loadProducts();
        } else {
            showAlert(await errorText(response), 'danger');
        }
    }

    loadCategories();
    loadProducts();
</script>
</body>
</html>
