<?php
require_once __DIR__ . '/../Config/database_connect.php';

class PaymentDAO {
    private $conn;
    private $table = 'payments';

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    /** Get all payments with order and customer info */
    public function getAllPayments() {
        $query = "
                SELECT 
                    p.payment_id,
                    p.order_id,
                    p.payment_method,
                    p.payment_status,
                    p.amount,
                    p.payment_ref,
                    p.payment_date,
                    p.proof_image,
                    o.total_amount AS order_total,
                    o.order_status,
                    o.order_date,
                    u.user_id,
                    u.name AS customer_name,
                    u.email,
                    u.contact_number
                FROM payments p
                INNER JOIN orders o ON p.order_id = o.order_id
                INNER JOIN users u ON o.user_id = u.user_id
                ORDER BY p.payment_date DESC
                ";

        $result = $this->conn->query($query);
        $payments = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $payments[] = $row;
            }
        }
        return $payments;
    }

    /** Get payment by ID */
    public function getPaymentById($payment_id) {
        $query = "SELECT 
                    p.*,
                    o.order_id,
                    o.total_amount as order_total,
                    o.order_status,
                    o.order_date,
                    u.user_id,
                    u.name,
                    u.email,
                    CONCAT(u.first_name, ' ', u.last_name) AS customer_name
                  FROM {$this->table} p
                  INNER JOIN orders o ON p.order_id = o.order_id
                  INNER JOIN users u ON o.user_id = u.user_id
                  WHERE p.payment_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $payment_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    /** Get payment statistics */
    public function getPaymentStats() {
        $query = "SELECT 
                    COUNT(*) AS total_payments,
                    SUM(payment_status='Paid') AS completed_payments,
                    SUM(payment_status='Pending') AS pending_payments,
                    SUM(payment_status='Failed') AS failed_payments,
                    SUM(CASE WHEN payment_status='Paid' THEN amount ELSE 0 END) AS total_revenue,
                    SUM(CASE WHEN payment_status='Pending' THEN amount ELSE 0 END) AS pending_revenue
                  FROM {$this->table}";

        $result = $this->conn->query($query);
        return $result ? $result->fetch_assoc() : null;
    }

    /** Update payment status */
    public function updatePaymentStatus($payment_id, $status) {
        $query = "UPDATE {$this->table} SET payment_status = ? WHERE payment_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("si", $status, $payment_id);
        return $stmt->execute();
    }

    /** Update payment proof image */
    public function updatePaymentProof($payment_id, $proof_image) {
        $query = "UPDATE {$this->table} SET proof_image = ? WHERE payment_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("si", $proof_image, $payment_id);
        return $stmt->execute();
    }

    /** Create new payment */
    public function createPayment($data) {
        $query = "INSERT INTO {$this->table} 
                  (order_id, payment_method, payment_ref, amount, payment_status, proof_image)
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "issdss",
            $data['order_id'],
            $data['payment_method'],
            $data['payment_ref'],
            $data['amount'],
            $data['payment_status'],
            $data['proof_image']
        );
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    /** Delete payment */
    public function deletePayment($payment_id) {
        $query = "DELETE FROM {$this->table} WHERE payment_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $payment_id);
        return $stmt->execute();
    }

    /** Search payments */
    public function searchPayments($search_term) {
        $like = "%$search_term%";
        $query = "SELECT 
                    p.payment_id,
                    p.order_id,
                    p.payment_method,
                    p.payment_status,
                    p.amount,
                    p.payment_ref,
                    p.payment_date,
                    o.order_number,
                    CONCAT(u.first_name,' ',u.last_name) AS customer_name,
                    u.name,
                    u.email
                  FROM {$this->table} p
                  INNER JOIN orders o ON p.order_id = o.order_id
                  INNER JOIN users u ON o.user_id = u.user_id
                  WHERE o.order_number LIKE ?
                     OR u.name LIKE ?
                     OR u.email LIKE ?
                     OR p.payment_ref LIKE ?
                     OR CONCAT(u.first_name,' ',u.last_name) LIKE ?
                  ORDER BY p.payment_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sssss", $like, $like, $like, $like, $like);
        $stmt->execute();
        $result = $stmt->get_result();
        $payments = [];
        while ($row = $result->fetch_assoc()) $payments[] = $row;
        return $payments;
    }

    /** Filter payments by status */
    public function filterPaymentsByStatus($status) {
        $query = "SELECT 
                    p.*,
                    o.order_number,
                    CONCAT(u.first_name,' ',u.last_name) AS customer_name,
                    u.name
                  FROM {$this->table} p
                  INNER JOIN orders o ON p.order_id = o.order_id
                  INNER JOIN users u ON o.user_id = u.user_id
                  WHERE p.payment_status = ?
                  ORDER BY p.payment_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $status);
        $stmt->execute();
        $result = $stmt->get_result();
        $payments = [];
        while ($row = $result->fetch_assoc()) $payments[] = $row;
        return $payments;
    }

    /** Get payments by date range */
    public function getPaymentsByDateRange($start_date, $end_date) {
        $query = "SELECT 
                    p.*,
                    o.order_number,
                    CONCAT(u.first_name,' ',u.last_name) AS customer_name,
                    u.name
                  FROM {$this->table} p
                  INNER JOIN orders o ON p.order_id = o.order_id
                  INNER JOIN users u ON o.user_id = u.user_id
                  WHERE DATE(p.payment_date) BETWEEN ? AND ?
                  ORDER BY p.payment_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        $payments = [];
        while ($row = $result->fetch_assoc()) $payments[] = $row;
        return $payments;
    }
}
?>