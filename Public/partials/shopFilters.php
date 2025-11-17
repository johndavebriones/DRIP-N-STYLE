<!-- Shop Filter Bar -->
<div class="shop-filters-container mb-4">
    <form method="GET" action="shop.php" class="row g-2 align-items-center">

        <!-- Search -->
        <div class="col-12 col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search products..."
                   value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </div>

        <!-- Category -->
        <div class="col-12 col-md-3">
            <select name="category" id="category" class="form-select">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['category_id'] ?>" <?= isset($_GET['category']) && $_GET['category'] == $cat['category_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['category_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Sort -->
        <div class="col-12 col-md-3">
            <select name="sort" id="sort" class="form-select">
                <option value="newest" <?= ($_GET['sort'] ?? '') === 'newest' ? 'selected' : '' ?>>Newest</option>
                <option value="price_asc" <?= ($_GET['sort'] ?? '') === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                <option value="price_desc" <?= ($_GET['sort'] ?? '') === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
            </select>
        </div>

        <!-- Apply Button -->
        <div class="col-12 col-md-2">
            <button type="submit" class="btn btn-warning w-100">Apply</button>
        </div>

    </form>
</div>

<style>
.shop-filters-container {
    width: 100%;
}
</style>
