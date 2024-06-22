<?php
header('Content-Type: application/json');

// Function to cancel an order by ID
function actionShopifyOrder($shopify_store, $access_token, $api_version, $order_id, $order_action) {

    
    $url = "https://$shopify_store/admin/api/$api_version/orders/$order_id/$order_action.json";

    $headers = array(
        "Content-Type: application/json",
        "X-Shopify-Access-Token: $access_token"
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($http_code != 200) {
        throw new Exception("Shopify API request failed with HTTP code $http_code: " . curl_error($ch));
    }

    curl_close($ch);

    return json_decode($response, true);
}

try {
    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    // Get headers
    $headers = getallheaders();

    if (!isset($headers['X-Shopify-Store-Domain']) || !isset($headers['X-Shopify-Access-Token']) || !isset($headers['X-Shopify-Api-Version'])) {
        throw new Exception('Missing required headers: X-Shopify-Store-Domain, X-Shopify-Access-Token, X-Shopify-Api-Version');
    }

    $shopify_store = $headers['X-Shopify-Store-Domain'];
    $access_token = $headers['X-Shopify-Access-Token'];
    $api_version = $headers['X-Shopify-Api-Version'];

    $order_id = isset($_GET['id']) ? $_GET['id'] : null;
    $order_action = isset($_GET['action']) ? $_GET['action'] : null;

    if (!$order_id) {
        throw new Exception('Order id is required');
    }

    $valid_actions = ['close', 'open', 'cancel'];
    if (!in_array($order_action, $valid_actions)) {
        throw new Exception('Invalid action. Allowed actions are: close, open, cancel');
    }

    $result = actionShopifyOrder($shopify_store, $access_token, $api_version, $order_id, $order_action);

    if (empty($result['order'])) {
        throw new Exception('Order not found or action failed');
    }

    echo json_encode($result);

} catch (Exception $e) {
    header('Content-Type: application/json', true, 400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
