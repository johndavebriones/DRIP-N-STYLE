<?php
require_once __DIR__ . '/../DAO/AddressDAO.php';

class AddressController {
    private $dao;

    public function __construct() {
        $this->dao = new AddressDAO();
    }


    public function index($user_id) {
        return $this->dao->getByUserId($user_id);
    }


    public function store($data) {
        $address = new AddressModel($data);
        return $this->dao->create($address);
    }


    public function update($data) {
        $address = new AddressModel($data);
        return $this->dao->update($address);
    }


    public function destroy($address_id, $user_id) {
        return $this->dao->delete($address_id, $user_id);
    }

    // Set default
    public function makeDefault($address_id, $user_id) {
        return $this->dao->setDefault($address_id, $user_id);
    }
}
?>
