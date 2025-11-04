<?php
require __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../App/Config/database_connect.php';
require_once __DIR__ . '/../../App/DAO/cartDAO.php';

session_start();
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

$secretKey = $_ENV['PAYMONGO_SECRET_KEY'];

// ✅ Initialize DB and DAO
$db = new Database();
$conn = $db->connect();
$cartDAO = new CartDAO($conn);

// ✅ Get user cart total automatically
$user_id = $_SESSION['user_id'] ?? 0;
if (!$user_id) {
    die('User not logged in');
}

$cartItems = $cartDAO->getCartItems($user_id);
if (empty($cartItems)) {
    die('Your cart is empty.');
}

// ✅ Build line_items and total
$line_items = [];
$total_amount = 0;

foreach ($cartItems as $item) {
    $amount = intval($item['price_at_time'] * 100); // convert to centavos
    $line_items[] = [
        "name" => $item['name'],
        "quantity" => (int)$item['quantity'],
        "amount" => $amount,
        "currency" => "PHP"
    ];
    $total_amount += $amount * (int)$item['quantity'];
}

// ✅ Checkout payload
$data = [
    "data" => [
        "attributes" => [
            "payment_method_types" => ["gcash", "grab_pay"],
            "line_items" => $line_items,
            "total_amount" => $total_amount,
            "success_url" => "http://localhost/websites/DRIP-N-STYLE/Public/shop/success.php",
            "cancel_url" => "http://localhost/websites/DRIP-N-STYLE/Public/shop/cancel.php",
            "description" => "Customer checkout"
        ]
    ]
];

// ✅ Send to PayMongo
$ch = curl_init('https://api.paymongo.com/v1/checkout_sessions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode($secretKey . ':')
    ]
]);

$response = curl_exec($ch);
curl_close($ch);
$result = json_decode($response, true);

if (isset($result['data']['attributes']['checkout_url'])) {
    header('Location: ' . $result['data']['attributes']['checkout_url']);
    exit;
} else {
    echo "❌ Checkout creation failed<br>";
    echo "<pre>";
    print_r($result);
    echo "</pre>";
}
