<?php
require_once __DIR__ . '/../../App/Helpers/SessionHelper.php';

SessionHelper::requireAdminLogin();
SessionHelper::preventCache();

require_once __DIR__ . '/../../App/Config/database_connect.php';
require_once __DIR__ . '/../../App/DAO/adminDAO.php';

$title = "Dashboard";

// Initialize AdminDAO directly
$db = new Database();
$conn = $db->connect();
$adminDAO = new AdminDAO($conn);

// Get dashboard statistics
$totalProducts = $adminDAO->countProducts();
$totalOrders = $adminDAO->countOrders();
$totalRevenue = $adminDAO->sumRevenue();

// Get sales data
$dailySales = $adminDAO->getTotalSales('daily');
$weeklySales = $adminDAO->getTotalSales('weekly');
$monthlySales = $adminDAO->getTotalSales('monthly');
$yearlySales = $adminDAO->getTotalSales('yearly');

// Get order data
$dailyOrders = $adminDAO->getTotalOrders('daily');
$weeklyOrders = $adminDAO->getTotalOrders('weekly');
$monthlyOrders = $adminDAO->getTotalOrders('monthly');
$yearlyOrders = $adminDAO->getTotalOrders('yearly');

// Get analytics data
$revenueTrends = $adminDAO->getRevenueTrends(30);
$topProducts = $adminDAO->getTopSellingProducts(10);
$salesByCategory = $adminDAO->getSalesByCategory();
$orderTrends = $adminDAO->getOrderTrends(30);
$ordersByStatus = $adminDAO->getOrdersByStatus();
$ordersByCategory = $adminDAO->getOrdersByCategory();
$recentOrders = $adminDAO->getRecentOrders(5);

// Get inventory analytics data
$lowStockProducts = $adminDAO->getLowStockProducts(10, 10);
$outOfStockProducts = $adminDAO->getOutOfStockProducts(10);
$inventoryTurnover = $adminDAO->getInventoryTurnoverRate(30, 10);
$popularSizes = $adminDAO->getPopularSizes();
$popularColors = $adminDAO->getPopularColors(10);
$totalInventoryValue = $adminDAO->getTotalInventoryValue();
$stockLevelCounts = $adminDAO->getStockLevelCounts();

// Prepare data for charts
$chartData = [
    'trendDates' => array_column($revenueTrends, 'date'),
    'trendRevenues' => array_column($revenueTrends, 'revenue'),
    'productNames' => array_column($topProducts, 'name'),
    'productQuantities' => array_column($topProducts, 'total_quantity'),
    'categoryNames' => array_column($salesByCategory, 'category'),
    'categorySales' => array_column($salesByCategory, 'total_sales'),
    'orderTrendDates' => array_column($orderTrends, 'date'),
    'orderTrendCounts' => array_column($orderTrends, 'order_count'),
    'orderStatuses' => array_column($ordersByStatus, 'order_status'),
    'orderStatusCounts' => array_column($ordersByStatus, 'order_count'),
    'orderCategoryNames' => array_column($ordersByCategory, 'category_name'),
    'orderCategoryCounts' => array_column($ordersByCategory, 'order_count'),
    'turnoverProductNames' => array_column($inventoryTurnover, 'name'),
    'turnoverRates' => array_column($inventoryTurnover, 'turnover_rate'),
    'sizeNames' => array_column($popularSizes, 'size'),
    'sizeQuantities' => array_column($popularSizes, 'total_quantity_sold'),
    'colorNames' => array_column($popularColors, 'color'),
    'colorQuantities' => array_column($popularColors, 'total_quantity_sold')
];

ob_start();
?>

<link rel="stylesheet" href="assets/css/dashboard.css?v=2">

<div class="page-fade">
  <h2 class="mb-4">Welcome, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?> 👋</h2>

  <!-- Top Stats Row -->
  <div class="row g-4 mb-5">
    <div class="col-md-3">
      <div class="card text-center shadow-sm border-0 stat-card stat-card-yellow">
        <div class="card-body">
          <h5 class="card-title fw-bold">Total Products</h5>
          <h3 class="text-dark"><?= $totalProducts ?></h3>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card text-center shadow-sm border-0 stat-card stat-card-yellow">
        <div class="card-body">
          <h5 class="card-title fw-bold">Orders</h5>
          <h3 class="text-dark"><?= $totalOrders ?></h3>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card text-center shadow-sm border-0 stat-card stat-card-yellow">
        <div class="card-body">
          <h5 class="card-title fw-bold">Total Revenue</h5>
          <h3 class="text-dark">₱<?= number_format($totalRevenue, 2) ?></h3>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card text-center shadow-sm border-0 stat-card stat-card-yellow">
        <div class="card-body">
          <h5 class="card-title fw-bold">Inventory Value</h5>
          <h3 class="text-dark">₱<?= number_format($totalInventoryValue, 2) ?></h3>
        </div>
      </div>
    </div>
  </div>

  <!-- Total Sales Section -->
  <div class="row g-4 mb-5">
    <div class="col-12">
      <div class="card shadow-sm border-0">
        <div class="card-header header-yellow text-dark">
          <h5 class="mb-0 fw-bold">💰 Total Sales Overview</h5>
        </div>
        <div class="card-body">
          <div class="row text-center">
            <div class="col-md-3">
              <div class="sales-metric">
                <p class="sales-label">Daily</p>
                <h4 class="sales-value">₱<?= number_format($dailySales, 2) ?></h4>
              </div>
            </div>
            <div class="col-md-3">
              <div class="sales-metric">
                <p class="sales-label">Weekly</p>
                <h4 class="sales-value">₱<?= number_format($weeklySales, 2) ?></h4>
              </div>
            </div>
            <div class="col-md-3">
              <div class="sales-metric">
                <p class="sales-label">Monthly</p>
                <h4 class="sales-value">₱<?= number_format($monthlySales, 2) ?></h4>
              </div>
            </div>
            <div class="col-md-3">
              <div class="sales-metric">
                <p class="sales-label">Yearly</p>
                <h4 class="sales-value">₱<?= number_format($yearlySales, 2) ?></h4>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Revenue Trends Chart -->
  <div class="row g-4 mb-5">
    <div class="col-12">
      <div class="card shadow-sm border-0">
        <div class="card-header header-yellow text-dark">
          <h5 class="mb-0 fw-bold">📈 Revenue Trends (Last 30 Days)</h5>
        </div>
        <div class="card-body">
          <canvas id="revenueChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Charts Row -->
  <div class="row g-4 mb-5">
    <!-- Top Selling Products -->
    <div class="col-md-6">
      <div class="card shadow-sm border-0">
        <div class="card-header header-yellow text-dark">
          <h5 class="mb-0 fw-bold">🏆 Top Selling Products</h5>
        </div>
        <div class="card-body">
          <canvas id="topProductsChart"></canvas>
        </div>
      </div>
    </div>

    <!-- Sales by Category -->
    <div class="col-md-6">
      <div class="card shadow-sm border-0">
        <div class="card-header header-yellow text-dark">
          <h5 class="mb-0 fw-bold">📊 Sales by Category</h5>
        </div>
        <div class="card-body">
          <canvas id="categoryChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- ORDER ANALYTICS SECTION -->
  <h3 class="mb-4 mt-5 section-title">Order Analytics</h3>

  <!-- Total Orders Section -->
  <div class="row g-4 mb-5">
    <div class="col-12">
      <div class="card shadow-sm border-0">
        <div class="card-header header-yellow text-dark">
          <h5 class="mb-0 fw-bold">Total Orders Overview</h5>
        </div>
        <div class="card-body">
          <div class="row text-center">
            <div class="col-md-3">
              <div class="sales-metric">
                <p class="sales-label">Daily</p>
                <h4 class="sales-value text-primary"><?= $dailyOrders ?></h4>
              </div>
            </div>
            <div class="col-md-3">
              <div class="sales-metric">
                <p class="sales-label">Weekly</p>
                <h4 class="sales-value text-primary"><?= $weeklyOrders ?></h4>
              </div>
            </div>
            <div class="col-md-3">
              <div class="sales-metric">
                <p class="sales-label">Monthly</p>
                <h4 class="sales-value text-primary"><?= $monthlyOrders ?></h4>
              </div>
            </div>
            <div class="col-md-3">
              <div class="sales-metric">
                <p class="sales-label">Yearly</p>
                <h4 class="sales-value text-primary"><?= $yearlyOrders ?></h4>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Order Trends Chart -->
  <div class="row g-4 mb-5">
    <div class="col-12">
      <div class="card shadow-sm border-0">
        <div class="card-header header-yellow text-dark">
          <h5 class="mb-0 fw-bold">📉 Order Trends (Last 30 Days)</h5>
        </div>
        <div class="card-body">
          <canvas id="orderTrendsChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Order Status and Category Charts -->
  <div class="row g-4 mb-5">
    <!-- Orders by Status -->
    <div class="col-md-6">
      <div class="card shadow-sm border-0">
        <div class="card-header header-yellow text-dark">
          <h5 class="mb-0 fw-bold">📋 Orders by Status</h5>
        </div>
        <div class="card-body">
          <canvas id="orderStatusChart"></canvas>
        </div>
      </div>
    </div>

    <!-- Orders by Category -->
    <div class="col-md-6">
      <div class="card shadow-sm border-0">
        <div class="card-header header-yellow text-dark">
          <h5 class="mb-0 fw-bold">🗂️ Orders by Category</h5>
        </div>
        <div class="card-body">
          <canvas id="orderCategoryChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- ============= INVENTORY ANALYTICS SECTION ============= -->
  <h3 class="mb-4 mt-5 section-title">Inventory Analytics</h3>

  <!-- Inventory Status Cards -->
  <div class="row g-4 mb-5">
    <div class="col-md-4">
      <div class="card text-center shadow-sm border-0 inventory-card card-in-stock">
        <div class="card-body">
          <div class="inventory-icon">✅</div>
          <h5 class="card-title fw-bold">In Stock</h5>
          <h2 class="inventory-number"><?= $stockLevelCounts['in_stock'] ?></h2>
          <p class="text-muted mb-0">Products available</p>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card text-center shadow-sm border-0 inventory-card card-low-stock">
        <div class="card-body">
          <div class="inventory-icon">⚠️</div>
          <h5 class="card-title fw-bold">Low Stock</h5>
          <h2 class="inventory-number"><?= $stockLevelCounts['low_stock'] ?></h2>
          <p class="text-muted mb-0">Need restocking soon</p>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card text-center shadow-sm border-0 inventory-card card-out-stock">
        <div class="card-body">
          <div class="inventory-icon">❌</div>
          <h5 class="card-title fw-bold">Out of Stock</h5>
          <h2 class="inventory-number"><?= $stockLevelCounts['out_of_stock'] ?></h2>
          <p class="text-muted mb-0">Critical items</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Inventory Charts Row 1 -->
  <div class="row g-4 mb-5">
    <!-- Inventory Turnover Rate -->
    <div class="col-md-6">
      <div class="card shadow-sm border-0">
        <div class="card-header header-yellow text-dark">
          <h5 class="mb-0 fw-bold">🔄 Inventory Turnover Rate</h5>
          <small class="text-muted">Higher turnover = faster sales</small>
        </div>
        <div class="card-body">
          <canvas id="turnoverChart"></canvas>
        </div>
      </div>
    </div>

    <!-- Popular Sizes -->
    <div class="col-md-6">
      <div class="card shadow-sm border-0">
        <div class="card-header header-yellow text-dark">
          <h5 class="mb-0 fw-bold">📏 Popular Sizes</h5>
          <small class="text-muted">Optimize stock based on demand</small>
        </div>
        <div class="card-body">
          <canvas id="sizesChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Popular Colors Chart -->
  <div class="row g-4 mb-5">
    <div class="col-12">
      <div class="card shadow-sm border-0">
        <div class="card-header header-yellow text-dark">
          <h5 class="mb-0 fw-bold">🎨 Popular Colors</h5>
          <small class="text-muted">Best-selling colors to prioritize</small>
        </div>
        <div class="card-body">
          <canvas id="colorsChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Low Stock and Out of Stock Tables -->
  <div class="row g-4 mb-5">
    <!-- Low Stock Products -->
    <div class="col-md-6">
      <div class="card shadow-sm border-0">
        <div class="card-header header-yellow text-dark">
          <h5 class="mb-0 fw-bold">⚠️ Low Stock Products</h5>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>Product</th>
                  <th>Size</th>
                  <th>Color</th>
                  <th>Stock</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($lowStockProducts)): ?>
                  <?php foreach ($lowStockProducts as $product): ?>
                    <tr>
                      <td class="fw-medium"><?= htmlspecialchars($product['name']) ?></td>
                      <td><?= htmlspecialchars($product['size']) ?></td>
                      <td><?= htmlspecialchars($product['color'] ?? 'N/A') ?></td>
                      <td><span class="badge bg-warning text-dark"><?= $product['stock'] ?></span></td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="4" class="text-center text-muted py-4">No low stock products</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Out of Stock Products -->
    <div class="col-md-6">
      <div class="card shadow-sm border-0">
        <div class="card-header header-yellow text-dark">
          <h5 class="mb-0 fw-bold">❌ Out of Stock Products</h5>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>Product</th>
                  <th>Size</th>
                  <th>Color</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($outOfStockProducts)): ?>
                  <?php foreach ($outOfStockProducts as $product): ?>
                    <tr>
                      <td class="fw-medium"><?= htmlspecialchars($product['name']) ?></td>
                      <td><?= htmlspecialchars($product['size']) ?></td>
                      <td><?= htmlspecialchars($product['color'] ?? 'N/A') ?></td>
                      <td><span class="badge bg-danger">Out of Stock</span></td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="4" class="text-center text-muted py-4">All products in stock! 🎉</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Orders -->
  <h4 class="mb-3 mt-5">📋 Recent Orders</h4>
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Customer</th>
          <th>Item</th>
          <th>Status</th>
          <th>Total</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($recentOrders)): ?>
          <?php foreach ($recentOrders as $order): ?>
            <tr>
              <td>#<?= $order['order_id'] ?></td>
              <td><?= htmlspecialchars($order['customer_name']) ?></td>
              <td><?= htmlspecialchars($order['product_name']) ?></td>
              <td>
                <?php
                  $badgeClass = match($order['order_status'] ?? '') {
                      'Completed' => 'bg-success',
                      'Pending' => 'bg-warning text-dark',
                      'Cancelled' => 'bg-danger',
                      'Confirmed' => 'bg-info',
                      'Ready for Pickup' => 'bg-primary',
                      default => 'bg-secondary'
                  };
                ?>
                <span class="badge <?= $badgeClass ?>"><?= $order['order_status'] ?? 'Unknown' ?></span>
              </td>
              <td>₱<?= number_format($order['total_amount'], 2) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="5" class="text-center text-muted">No recent orders found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const chartData = <?= json_encode($chartData) ?>;
</script>

<!-- Dashboard JavaScript -->
<script src="assets/js/dashboard.js"></script>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout/main.php';
?>