<?php
require_once __DIR__ . '/../../App/Helpers/SessionHelper.php';
SessionHelper::requireAdminLogin();
SessionHelper::preventCache();

require_once __DIR__ . '/../../App/Controllers/OrderController.php';
require_once __DIR__ . '/../../App/Config/database_connect.php';

$db = new Database();
$conn = $db->connect();
$orderController = new OrderDAO($conn);

$order_id = $_GET['order_id'] ?? null;

if (!$order_id) {
    header('Location: orders.php');
    exit;
}

$order = $orderController->getOrderById($order_id);
$items = $orderController->getOrderItems($order_id);

if (!$order) {
    header('Location: orders.php');
    exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $newOrderStatus = $_POST['order_status'] ?? null;
    $newPaymentStatus = $_POST['payment_status'] ?? null;
    
    $result = $orderController->updateOrderAndPaymentStatus($order_id, $newOrderStatus, $newPaymentStatus);
    
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
        if (isset($result['stock_reduced']) && $result['stock_reduced']) {
            $_SESSION['success_message'] .= ' Stock has been reduced for all items.';
        }
        header('Location: view_order.php?order_id=' . $order_id);
        exit;
    } else {
        $_SESSION['error_message'] = $result['message'];
    }
}

$isOrderLocked = ($order['order_status'] === 'Cancelled' && $order['payment_status'] === 'Failed');

$title = "Order #" . $order_id;
ob_start();
?>

<style>
.page-fade {
    opacity: 0;
    animation: fadeIn 0.6s ease-in-out forwards;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.order-detail-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    padding: 2rem;
    margin-bottom: 1.5rem;
}

.status-badge-large {
    font-size: 1rem;
    padding: 0.5rem 1rem;
    border-radius: 8px;
}

.product-item {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
    border-left: 4px solid #ffc107;
}

.product-image {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
}
</style>

<div class="page-fade">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="orders.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Orders
        </a>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i><?= $_SESSION['success_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i><?= $_SESSION['error_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Order Header -->
    <div class="order-detail-card">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h2 class="fw-bold mb-2">Order #<?= htmlspecialchars($order['order_id']) ?></h2>
                <p class="text-muted mb-0">
                    <i class="bi bi-calendar me-2"></i>
                    <?= date('F d, Y - g:i A', strtotime($order['order_date'])) ?>
                </p>
            </div>
            <div class="text-end">
                <h3 class="text-success fw-bold mb-0">₱<?= number_format($order['total_amount'], 2) ?></h3>
                <small class="text-muted">Total Amount</small>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="row mb-4">
            <div class="col-md-6">
                <h5 class="fw-bold mb-3">Customer Information</h5>
                <p class="mb-2"><strong>Name:</strong> <?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?></p>
                <p class="mb-2"><strong>Email:</strong> <?= htmlspecialchars($order['customer_email'] ?? 'N/A') ?></p>
                <p class="mb-2"><strong>Contact:</strong> <?= htmlspecialchars($order['customer_contact'] ?? 'N/A') ?></p>
            </div>
            <div class="col-md-6">
                <h5 class="fw-bold mb-3">Order Details</h5>
                <p class="mb-2"><strong>Pickup Date:</strong> <?= htmlspecialchars($order['pickup_date'] ?? 'Not set') ?></p>
                <p class="mb-2"><strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method'] ?? 'N/A') ?></p>
                <p class="mb-2">
                    <strong>Payment Status:</strong>
                    <span class="badge bg-<?= $order['payment_status'] === 'Paid' ? 'success text-dark' : ($order['payment_status'] === 'Failed' ? 'danger text-dark' : 'warning text-dark') ?>">
                        <?= htmlspecialchars($order['payment_status']) ?>
                    </span>
                </p>
                <p class="mb-2">
                    <strong>Order Status:</strong>
                    <span class="badge bg-<?php
                        echo match($order['order_status']) {
                            'Pending' => 'warning text-dark',
                            'Ready for Pickup' => 'primary text-dark',
                            'Completed' => 'success text-dark',
                            'Cancelled' => 'danger text-dark',
                            default => 'secondary text-dark'
                        };
                    ?>">
                        <?= htmlspecialchars($order['order_status']) ?>
                    </span>
                </p>
            </div>
        </div>
    </div>

    <!-- Order Items -->
    <div class="order-detail-card">
        <h5 class="fw-bold mb-3">Order Items</h5>
        <?php foreach ($items as $item): ?>
            <div class="product-item">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <?php if (!empty($item['image'])): ?>
                            <img src="../../Public/<?= htmlspecialchars($item['image']) ?>" 
                                 alt="<?= htmlspecialchars($item['product_name']) ?>"
                                 class="product-image">
                        <?php else: ?>
                            <div class="product-image bg-secondary d-flex align-items-center justify-content-center">
                                <i class="bi bi-image text-white"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col">
                        <h6 class="fw-bold mb-1"><?= htmlspecialchars($item['product_name']) ?></h6>
                        <p class="text-muted small mb-1">
                            <strong>Size:</strong> <?= htmlspecialchars($item['size'] ?? 'N/A') ?>
                            <?php if (!empty($item['color'])): ?>
                                | <strong>Color:</strong> <?= htmlspecialchars($item['color']) ?>
                            <?php endif; ?>
                        </p>
                        <p class="mb-0">
                            <strong>Quantity:</strong> <?= (int)$item['quantity'] ?> × 
                            <strong>₱<?= number_format($item['price'], 2) ?></strong>
                        </p>
                    </div>
                    <div class="col-auto text-end">
                        <h5 class="text-success fw-bold mb-0">
                            ₱<?= number_format($item['price'] * $item['quantity'], 2) ?>
                        </h5>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Update Status Form -->
    <div class="order-detail-card">
        <h5 class="fw-bold mb-3">Update Order Status</h5>
        
        <?php if ($isOrderLocked): ?>
            <div class="alert alert-warning">
                <i class="bi bi-lock-fill me-2"></i>
                <strong>Order Locked:</strong> This order has been cancelled with a failed payment and cannot be modified.
            </div>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Order Status</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($order['order_status']) ?>" disabled>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Payment Status</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($order['payment_status']) ?>" disabled>
                </div>
            </div>
            
            <div class="mt-3">
                <a href="orders.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Orders
                </a>
            </div>
        <?php else: ?>
            <form method="POST" id="updateStatusForm">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Order Status</label>
                        <select name="order_status" class="form-select" required>
                            <option value="Pending" <?= $order['order_status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="Ready for Pickup" <?= $order['order_status'] === 'Ready for Pickup' ? 'selected' : '' ?>>Ready for Pickup</option>
                            <option value="Completed" <?= $order['order_status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="Cancelled" <?= $order['order_status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Payment Status</label>
                        <select name="payment_status" class="form-select" required>
                            <option value="Pending" <?= $order['payment_status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="Paid" <?= $order['payment_status'] === 'Paid' ? 'selected' : '' ?>>Paid</option>
                            <option value="Failed" <?= $order['payment_status'] === 'Failed' ? 'selected' : '' ?>>Failed</option>
                        </select>
                    </div>
                </div>

                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Stock Management Rules:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Setting Order Status to <strong>"Completed"</strong> and Payment Status to <strong>"Paid"</strong> will <strong>reduce stock</strong> for all items in this order.</li>
                        <li>Setting Order Status to <strong>"Cancelled"</strong> will automatically set Payment Status to <strong>"Failed"</strong>.</li>
                        <li><strong>Cancelled orders do NOT increase stock</strong> - items remain as-is.</li>
                        <li><strong>Once cancelled with failed payment, the order becomes locked and cannot be modified.</strong></li>
                    </ul>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" name="update_status" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Update Status
                    </button>
                    <a href="orders.php" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
// Confirmation before updating to Completed + Paid
document.getElementById('updateStatusForm')?.addEventListener('submit', function(e) {
    const orderStatus = document.querySelector('select[name="order_status"]').value;
    const paymentStatus = document.querySelector('select[name="payment_status"]').value;
    
    if (orderStatus === 'Completed' && paymentStatus === 'Paid') {
        if (!confirm('This will mark the order as Completed and reduce stock for all items. Continue?')) {
            e.preventDefault();
        }
    }
    
    if (orderStatus === 'Cancelled') {
        if (!confirm('This will cancel the order and mark payment as Failed. Stock will NOT be restored. Once cancelled, this order cannot be modified again. Continue?')) {
            e.preventDefault();
        }
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout/main.php';
?>