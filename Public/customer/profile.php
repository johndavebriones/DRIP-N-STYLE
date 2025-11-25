<?php
require_once __DIR__ . '/../../App/Helpers/SessionHelper.php';
require_once __DIR__ . '/../../App/Config/database_connect.php';
require_once __DIR__ . '/../../App/DAO/UserDAO.php';
require_once __DIR__ . '/../../App/DAO/OrderDAO.php';
require_once __DIR__ . '/../../App/DAO/AddressDAO.php';

SessionHelper::requireCustomerLogin();

$user_id = $_SESSION['user_id'];

$userDAO = new UserDAO();
$user = $userDAO->findById($user_id);
if (!$user) {
    die("User not found in database");
}

// Orders
$db = new Database();
$conn = $db->connect();
$orderDAO = new OrderDAO($conn);
$orders = $orderDAO->getUserOrders($user_id);

// Addresses
$addressDAO = new AddressDAO();
$addresses = $addressDAO->getByUserId($user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Profile | Drip N' Style</title>
  <link href="../assets/vendor/bootstrap5/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="../assets/css/navbar.css">
  <link rel="stylesheet" href="assets/css/profile.css">
  <link rel="stylesheet" href="../assets/css/footer.css">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    .profile-wrapper { padding: 50px 0; }
    .profile-card {
      background: #fff;
      border-radius: 15px;
      padding: 30px;
      box-shadow: 0 5px 25px rgba(0, 0, 0, 0.05);
      transition: 0.3s;
    }
    .profile-card:hover { transform: translateY(-3px); }
    .profile-avatar {
      width: 110px; height: 110px; border-radius: 50%;
      background-color: var(--primary);
      display: flex; align-items: center; justify-content: center;
      font-size: 2.5rem; color: var(--secondary); font-weight: 700;
      margin: 0 auto 15px auto;
    }
    .tab-btns {
      display: flex; justify-content: center; flex-wrap: wrap;
      gap: 10px; margin-bottom: 25px;
    }
    .tab-btns button {
      border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600;
      background: var(--secondary); color: var(--primary); transition: 0.3s;
    }
    .tab-btns button.active, .tab-btns button:hover {
      background: var(--primary); color: var(--secondary);
    }
    .order-table th { background-color: var(--primary); color: var(--secondary); }
    .address-card {
      border: 1px solid #eee; border-radius: 10px;
      padding: 15px; margin-bottom: 10px; background: #fffef5;
    }
    .badge.bg-yellow {
      background-color: #ffc107;
      color: #000;
    }
  </style>
</head>
<body>
  <?php include '../partials/navbar.php'; ?>

  <section class="profile-wrapper container">
    <div class="text-center mb-5">
      <div class="profile-avatar">
        <i class="bi bi-person-fill"></i>
      </div>
      <h3 class="fw-bold"><?= htmlspecialchars($user['name'] ?? 'Guest User') ?></h3>
      <p class="text-muted mb-0"><?= htmlspecialchars($user['email'] ?? 'No email') ?></p>
      <p class="text-muted"><?= htmlspecialchars($user['contact_number'] ?? 'No phone number') ?></p>
    </div>

    <div class="tab-btns">
      <button class="active" onclick="showTab('info')">Profile Info</button>
      <button onclick="showTab('orders')">Orders</button>
      <button onclick="showTab('address')">Addresses</button>
      <button onclick="showTab('security')">Security</button>
    </div>

    <!-- === Profile Info === -->
    <div id="info" class="profile-card tab-content shadow-soft">
      <h5 class="fw-bold mb-3">Edit Profile</h5>
      <form method="post" action="../../App/Controllers/ProfileController.php?action=update">
        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <input type="text" name="name" class="form-control" 
                value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" 
                value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Contact No.</label>
          <input type="text" name="contact_number" class="form-control" 
                value="<?= htmlspecialchars($user['contact_number'] ?? '') ?>" required>
        </div>
        <button class="btn btn-warning" type="submit">
          <i class="bi bi-save me-1"></i>Save Changes
        </button>
      </form>
    </div>

    <!-- === Orders === -->
    <div id="orders" class="profile-card tab-content shadow-soft" style="display:none;">
      <h5 class="fw-bold mb-3">Order History</h5>
      <?php if (empty($orders)): ?>
        <p class="text-muted">You have no orders yet.</p>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-striped order-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Date</th>
                <th>Status</th>
                <th>Total</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($orders as $order): ?>
                <tr>
                  <td>#<?= htmlspecialchars($order['order_id']) ?></td>
                  <td><?= htmlspecialchars(date('M d, Y', strtotime($order['order_date']))) ?></td>
                  <td><?= htmlspecialchars($order['order_status']) ?></td>
                  <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                  <td><a href="order_details.php?order_id=<?= urlencode($order['order_id']) ?>" class="btn btn-outline-drip btn-sm">View</a></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <!-- === Addresses === -->
    <div id="address" class="profile-card tab-content shadow-soft" style="display:none;">
      <h5 class="fw-bold mb-3">Saved Addresses</h5>
      <?php if (empty($addresses)): ?>
        <p class="text-muted">No saved addresses yet.</p>
      <?php else: ?>
        <?php foreach ($addresses as $addr): ?>
          <div class="address-card">
            <p class="mb-0">
              <?= htmlspecialchars($addr->address) ?>
              <?= $addr->city ? ', ' . htmlspecialchars($addr->city) : '' ?>
            </p>
            <?php if (!empty($addr->is_default)): ?>
              <span class="badge bg-yellow">Default</span>
            <?php endif; ?>
            <div class="mt-2">
              <a href="edit_address.php?id=<?= urlencode($addr->address_id) ?>" class="btn btn-sm btn-warning">Edit</a>

              <form method="post" action="../../App/Controllers/AddressController.php?action=delete" style="display:inline;">
                <input type="hidden" name="address_id" value="<?= htmlspecialchars($addr->address_id) ?>">
                <button class="btn btn-sm btn-danger" type="submit">Delete</button>
              </form>

              <?php if (empty($addr->is_default)): ?>
                <form method="post" action="../../App/Controllers/AddressController.php?action=setDefault" style="display:inline;">
                  <input type="hidden" name="address_id" value="<?= htmlspecialchars($addr->address_id) ?>">
                  <button class="btn btn-sm btn-outline-dark" type="submit">Set Default</button>
                </form>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <button class="btn btn-outline-drip mt-3" data-bs-toggle="modal" data-bs-target="#addAddressModal">
        <i class="bi bi-plus-lg"></i> Add Address
      </button>
    </div>

    <!-- === Security === -->
    <div id="security" class="profile-card tab-content shadow-soft" style="display:none;">
      <h5 class="fw-bold mb-3">Change Password</h5>
      <form method="post" action="../../App/Controllers/ProfileController.php?action=changePassword">
        <div class="mb-3">
          <label class="form-label">Current Password</label>
          <input type="password" name="current_password" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">New Password</label>
          <input type="password" name="new_password" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Confirm Password</label>
          <input type="password" name="confirm_password" class="form-control" required>
        </div>
        <button class="btn btn-warning"><i class="bi bi-lock"></i> Update Password</button>
      </form>
    </div>
  </section>

  <!-- Add Address Modal -->
  <div class="modal fade" id="addAddressModal" tabindex="-1" aria-labelledby="addAddressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-geo-alt-fill me-2"></i> Add New Address</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form method="post" action="../../App/Controllers/AddressController.php?action=create">

            <input type="hidden" name="user_id" value="<?= $user_id ?>">

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Full Name</label>
                <input type="text" name="address_name" class="form-control" required>
              </div>

              <div class="col-md-6">
                <label class="form-label">Phone Number</label>
                <input type="tel" name="address_phone" class="form-control" required>
              </div>

              <div class="col-12">
                <label class="form-label">Street Address</label>
                <input type="text" name="address" class="form-control" required>
              </div>

              <div class="col-md-4">
                <label class="form-label">City</label>
                <input type="text" name="city" class="form-control" required>
              </div>

              <div class="col-md-4">
                <label class="form-label">Province</label>
                <input type="text" name="province" class="form-control">
              </div>

              <div class="col-md-4">
                <label class="form-label">Postal Code</label>
                <input type="text" name="postal_code" class="form-control">
              </div>

              <div class="col-md-6">
                <label class="form-label">Country</label>
                <input type="text" name="country" class="form-control" value="Philippines" required>
              </div>

              <div class="col-12 mt-2">
                <label><input type="checkbox" name="is_default" value="1"> Set as default</label>
              </div>
            </div>

            <div class="mt-4 d-flex justify-content-end">
              <button type="submit" class="btn btn-warning">
                <i class="bi bi-save2 me-1"></i> Save Address
              </button>
            </div>

          </form>
        </div>
      </div>
    </div>
  </div>

<?php include '../partials/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/profile.js"></script>

</body>
</html>
