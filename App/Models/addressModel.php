<?php
class AddressModel {
    public $address_id;
    public $user_id;
    public $name;
    public $address;
    public $city;
    public $province;
    public $country;
    public $postal_code;
    public $phone_number;
    public $is_default;
    public $created_at;
    public $updated_at;

    // ✅ Optional: constructor for easy initialization
    public function __construct(array $data = []) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}
?>
