<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../../public/index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard | Drip N' Style</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8f9fa;
    }

    .sidebar {
      width: 250px;
      height: 100vh;
      background: #111827;
      color: white;
      position: fixed;
      left: 0;
      top: 0;
      padding: 20px 0;
      display: flex;
      flex-direction: column;
      transition: all 0.3s;
    }

    .sidebar h2 {
      text-align: center;
      margin-bottom: 30px;
      font-size: 22px;
    }

    .sidebar a {
      color: #9ca3af;
      text-decoration: none;
      display: block;
      padding: 12px 25px;
      transition: 0.3s;
    }

    .sidebar a:hover, .sidebar a.active {
      background: #1f2937;
      color: #fff;
    }

    .content {
      margin-left: 250px;
      padding: 30px;
    }

    .card {
      border: none;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .card h5 {
      font-weight: 600;
    }
  </style>
</head>
<body>

  <!-- SIDEBAR -->
  <div class="sidebar">
    <h2>Drip N' Style</h2>
    <a href="#" class="active">🏠 Dashboard</a>
    <a href="#">👕 Products</a>
    <a href="#">🛒 Orders</a>
    <a href="#">👥 Customers</a>
    <a href="#">💰 Payments</a>
    <a href="#">📦 Inventory</a>
    <a href="#">⚙️ Settings</a>
    <hr style="border-color:#374151;">
    <a href="../../app/controllers/AuthController.php?action=logout">🚪 Logout</a>
  </div>

  <!-- MAIN CONTENT -->
  <div class="content">
    <h2 class="mb-4">Welcome, Admin 👋</h2>

    <div class="row g-4">
      <div class="col-md-3">
        <div class="card p-3 text-center">
          <h5>Total Products</h5>
          <h3>124</h3>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card p-3 text-center">
          <h5>Orders</h5>
          <h3>87</h3>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card p-3 text-center">
          <h5>Customers</h5>
          <h3>56</h3>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card p-3 text-center">
          <h5>Revenue</h5>
          <h3>₱45,320</h3>
        </div>
      </div>
    </div>

    <div class="mt-5">
      <h4>Recent Orders</h4>
      <table class="table table-hover mt-3">
        <thead>
          <tr>
            <th>#</th>
            <th>Customer</th>
            <th>Item</th>
            <th>Status</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>#1021</td>
            <td>Jane Cruz</td>
            <td>Denim Jacket</td>
            <td><span class="badge bg-success">Delivered</span></td>
            <td>₱1,299</td>
          </tr>
          <tr>
            <td>#1020</td>
            <td>Mark Reyes</td>
            <td>Graphic Tee</td>
            <td><span class="badge bg-warning text-dark">Pending</span></td>
            <td>₱599</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

</body>
</html>
