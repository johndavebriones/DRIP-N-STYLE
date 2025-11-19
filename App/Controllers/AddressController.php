<?php
require_once __DIR__ . '/../DAO/AddressDAO.php';
require_once __DIR__ . '/../Models/addressModel.php';
require_once __DIR__ . '/../Helpers/SessionHelper.php';

SessionHelper::requireCustomerLogin();
$user_id = $_SESSION['user_id'];

$action = $_GET['action'] ?? $_POST['action'] ?? '';

$addressDAO = new AddressDAO();

switch ($action) {

    // ➕ Create new address
    case 'create':
        $address_name = $_POST['address_name'] ?? '';
        $address_phone = $_POST['address_phone'] ?? '';
        $address = $_POST['address'] ?? '';
        $city = $_POST['city'] ?? '';
        $province = $_POST['province'] ?? '';
        $country = $_POST['country'] ?? '';
        $postal_code = $_POST['postal_code'] ?? '';
        $is_default = isset($_POST['is_default']) ? 1 : 0;

        $newAddress = new AddressModel([
            'user_id' => $user_id,
            'name' => $address_name,
            'phone_number' => $address_phone,
            'address' => $address,
            'city' => $city,
            'province' => $province,
            'country' => $country,
            'postal_code' => $postal_code,
            'is_default' => $is_default
        ]);

        $id = $addressDAO->create($newAddress);
        if ($id) {
            header("Location: ../../public/profile.php?success=address_added");
        } else {
            header("Location: ../../public/profile.php?error=address_failed");
        }
        exit;
        break;

    // ✏️ Edit existing address
    case 'update':
        $address_id = $_POST['address_id'] ?? 0;
        $address_name = $_POST['address_name'] ?? '';
        $address_phone = $_POST['address_phone'] ?? '';
        $address_text = $_POST['address'] ?? '';
        $city = $_POST['city'] ?? '';
        $province = $_POST['province'] ?? '';
        $country = $_POST['country'] ?? '';
        $postal_code = $_POST['postal_code'] ?? '';
        $is_default = isset($_POST['is_default']) ? 1 : 0;

        $updateAddress = new AddressModel([
            'address_id' => $address_id,
            'user_id' => $user_id,
            'name' => $address_name,
            'phone_number' => $address_phone,
            'address' => $address_text,
            'city' => $city,
            'province' => $province,
            'country' => $country,
            'postal_code' => $postal_code,
            'is_default' => $is_default
        ]);

        if ($addressDAO->update($updateAddress)) {
            header("Location: ../../public/profile.php?success=address_updated");
        } else {
            header("Location: ../../public/profile.php?error=address_failed");
        }
        exit;
        break;

    // 🗑 Delete
    case 'delete':
        $address_id = $_POST['address_id'] ?? 0;
        $addressDAO->delete($address_id, $user_id);
        header("Location: ../../public/profile.php?success=address_deleted");
        exit;
        break;

    // ⭐ Set as default
    case 'setDefault':
        $address_id = $_POST['address_id'] ?? 0;
        $addressDAO->setDefault($address_id, $user_id);
        header("Location: ../../public/profile.php?success=default_set");
        exit;
        break;

    default:
        header("HTTP/1.1 400 Bad Request");
        echo "Invalid action";
        exit;
}
