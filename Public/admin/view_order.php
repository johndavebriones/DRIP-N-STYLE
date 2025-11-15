<?php
require_once __DIR__ . '/../../App/Config/database_connect.php';
require_once __DIR__ . '/../../App/DAO/OrderDAO.php';

if (!isset($_GET['order_id'])) die("No order ID provided.");

$order_id = intval($_GET['order_id']);
$db = new Database();
$conn = $db->connect();
$orderDao = new OrderDAO($conn);
$order = $orderDao->getOrderById($order_id);
$orderItems = $orderDao->getOrderItems($order_id);

if (!$order) die("Order not found.");

$swal = null;

if (isset($_POST['update_status'])) {
    $newOrderStatus = $_POST['order_status'] ?? $order['order_status'];
    $newPaymentStatus = $_POST['payment_status'] ?? $order['payment_status'];

    // Sync rules
    if ($newOrderStatus === 'Cancelled') $newPaymentStatus = 'Failed';
    if ($newPaymentStatus === 'Failed') $newOrderStatus = 'Cancelled';

    // Prevent Completed if payment is Pending
    if ($newOrderStatus === 'Completed' && $newPaymentStatus === 'Pending') {
        $swal = [
            'icon' => 'error',
            'title' => 'Cannot Complete Order',
            'text' => 'Order cannot be marked as Completed while payment is Pending.'
        ];
    } else {
        // Update payment
        if (!empty($order['payment_id'])) {
            $stmt = $conn->prepare("UPDATE payments SET payment_status = ? WHERE payment_id = ?");
            $stmt->execute([$newPaymentStatus, $order['payment_id']]);
        }
        // Update order
        $stmt2 = $conn->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
        $stmt2->execute([$newOrderStatus, $order_id]);

        $swal = [
            'icon' => 'success',
            'title' => 'Updated',
            'text' => 'Order and payment statuses updated successfully.'
        ];

        $order = $orderDao->getOrderById($order_id);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order #<?= htmlspecialchars($order_id) ?> | Drip N' Style Admin</title>
    <link href="../assets/vendor/bootstrap5/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="assets/css/view_order.css">
</head>
<body>
<div class="container my-5">

    <!-- Back Button -->
    <a href="orders.php" class="btn btn-outline-dark mb-3">
        <i class="bi bi-arrow-left"></i> Back to Orders
    </a>

    <!-- Header -->
    <div class="order-header mb-4 shadow-sm">
        <h2 class="fw-bold mb-0"><i class="bi bi-receipt me-2"></i>Order #<?= htmlspecialchars($order_id) ?></h2>
        <p class="mb-0">Placed on <?= date("F d, Y", strtotime($order["order_date"])) ?></p>
    </div>

    <!-- Order Summary -->
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-header bg-gradient-warning text-dark fw-bold">
            <h5 class="mb-0"><i class="bi bi-card-list me-2"></i>Order Summary</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <p class="mb-2"><i class="bi bi-currency-dollar me-1"></i><strong>Total Amount:</strong> ₱<?= number_format($order["total_amount"], 2) ?></p>
                    <p class="mb-2"><i class="bi bi-calendar-event me-1"></i><strong>Order Date:</strong> <?= date("F d, Y", strtotime($order["order_date"])) ?></p>
                    <p class="mb-2"><i class="bi bi-clock-history me-1"></i><strong>Pickup Date:</strong> <?= htmlspecialchars($order["pickup_date"] ?? "Not set") ?></p>
                </div>
                <div class="col-md-6">
                    <form method="post" class="mb-3">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="fw-bold">Order Status:</label>
                                <select name="order_status" class="form-select">
                                    <?php
                                    $statuses = ['Pending','Ready for Pickup','Completed','Cancelled'];
                                    foreach ($statuses as $status):
                                        $selected = ($order['order_status'] === $status) ? 'selected' : '';
                                    ?>
                                    <option value="<?= $status ?>" <?= $selected ?>><?= $status ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold">Payment Status:</label>
                                <select name="payment_status" class="form-select">
                                    <?php
                                    $paymentStatuses = ['Pending','Paid','Failed'];
                                    foreach($paymentStatuses as $pStatus):
                                        $selected = ($order['payment_status'] === $pStatus) ? 'selected' : '';
                                    ?>
                                    <option value="<?= $pStatus ?>" <?= $selected ?>><?= $pStatus ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <button type="submit" name="update_status" class="btn btn-warning mt-2 update-status-btn">
                            <i class="bi bi-arrow-repeat me-1"></i> Update Status
                        </button>
                    </form>

                    <p class="mb-1"><i class="bi bi-credit-card me-1"></i><strong>Payment Method:</strong> <?= htmlspecialchars($order["payment_method"] ?? "N/A") ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Items -->
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Items</h5>
            <?php foreach ($orderItems as $item): ?>
                <div class="order-item">
                    <h6 class="fw-bold"><?= htmlspecialchars($item['product_name']) ?></h6>
                    <p class="mb-1"><strong>Description:</strong> <?= htmlspecialchars($item['description'] ?? 'No description') ?></p>
                    <p class="mb-1"><strong>Size:</strong> <?= htmlspecialchars($item['size'] ?? 'N/A') ?></p>
                    <p class="mb-1">
                        <strong>Quantity:</strong> <?= (int)$item['quantity'] ?> | 
                        <strong>Price:</strong> ₱<?= number_format($item['price'], 2) ?> | 
                        <strong>Subtotal:</strong> ₱<?= number_format($item['price'] * $item['quantity'], 2) ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<script>
window.swalData = <?= json_encode($swal) ?>;
</script>
<script src="../assets/vendor/bootstrap5/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/view_order.js"></script>
</body>
</html>
