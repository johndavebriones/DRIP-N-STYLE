<!-- Shop Filter Dropdown -->
<div class="dropdown mb-3 text-end">
    <button class="btn btn-outline-warning text-dark dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-funnel"></i> Filter & Sort
    </button>
    <div class="dropdown-menu dropdown-menu-end p-3 shadow" style="width: 300px;">
        <form method="GET" action="shop.php">

            <!-- Search -->
            <div class="mb-3">
                <input type="text" name="search" class="form-control" placeholder="Search products..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            </div>

            <!-- Category -->
            <div class="mb-3">
                <label for="category" class="form-label fw-bold">Category</label>
                <select name="category" id="category" class="form-select">
                    <option value="">All</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['category_id'] ?>" <?= isset($_GET['category']) && $_GET['category'] == $cat['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['category_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Sort -->
            <div class="mb-3">
                <label for="sort" class="form-label fw-bold">Sort By</label>
                <select name="sort" id="sort" class="form-select">
                    <option value="newest" <?= ($_GET['sort'] ?? '') === 'newest' ? 'selected' : '' ?>>Newest</option>
                    <option value="price_asc" <?= ($_GET['sort'] ?? '') === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                    <option value="price_desc" <?= ($_GET['sort'] ?? '') === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                </select>
            </div>

            <!-- Apply Button -->
            <button type="submit" class="btn btn-warning w-100">Apply Filters</button>
        </form>
    </div>
</div>
