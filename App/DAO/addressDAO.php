<?php
require_once __DIR__ . '/../Config/database_connect.php';
require_once __DIR__ . '/../Models/addressModel.php';

class AddressDAO {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    //  Get all addresses for a user
    public function getByUserId($user_id) {
        $sql = "SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, address_id ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $addresses = [];
        while ($row = $result->fetch_assoc()) {
            $addresses[] = new AddressModel($row);
        }
        return $addresses;
    }

    // Get one address
    public function getById($address_id, $user_id = null) {
        $sql = "SELECT * FROM addresses WHERE address_id = ?";
        if ($user_id !== null) $sql .= " AND user_id = ?";

        $stmt = $this->conn->prepare($sql);
        if ($user_id !== null) {
            $stmt->bind_param("ii", $address_id, $user_id);
        } else {
            $stmt->bind_param("i", $address_id);
        }
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return $res ? new AddressModel($res) : null;
    }

    // Insert new address
    public function create(AddressModel $address) {
        $sql = "INSERT INTO addresses (user_id, name, address, city, province, country, postal_code, phone_number, is_default)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "isssssssi",
            $address->user_id,
            $address->name,
            $address->address,
            $address->city,
            $address->province,
            $address->country,
            $address->postal_code,
            $address->phone_number,
            $address->is_default
        );
        if (!$stmt->execute()) return false;

        $newId = $stmt->insert_id;
        if ($address->is_default) {
            $this->setDefault($newId, $address->user_id);
        }
        return $newId;
    }

    // Update existing
    public function update(AddressModel $address) {
        $sql = "UPDATE addresses 
                SET name=?, address=?, city=?, province=?, country=?, postal_code=?, phone_number=?, is_default=? 
                WHERE address_id=? AND user_id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "sssssssiii",
            $address->name,
            $address->address,
            $address->city,
            $address->province,
            $address->country,
            $address->postal_code,
            $address->phone_number,
            $address->is_default,
            $address->address_id,
            $address->user_id
        );
        $ok = $stmt->execute();
        if ($ok && $address->is_default) {
            $this->setDefault($address->address_id, $address->user_id);
        }
        return $ok;
    }

    //Delete
    public function delete($address_id, $user_id) {
        $sql = "DELETE FROM addresses WHERE address_id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $address_id, $user_id);
        return $stmt->execute();
    }

    //Set as default
    public function setDefault($address_id, $user_id) {
        $this->conn->begin_transaction();
        try {
            $stmt = $this->conn->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();

            $stmt2 = $this->conn->prepare("UPDATE addresses SET is_default = 1 WHERE address_id = ? AND user_id = ?");
            $stmt2->bind_param("ii", $address_id, $user_id);
            $stmt2->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }
}
?>
