<?php
header('Content-Type: application/json');
// Function to get a single product by ID
function getShopifyProduct($shopify_store, $access_token, $api_version, $product_id) {
    // truncate gid://shopify/Product/ from $product_id if present
    if (is_string($product_id) && strpos($product_id, 'gid://shopify/Product/') === 0) {
        $product_id = substr($product_id, strrpos($product_id, '/') + 1);
    }

    $url = "https://$shopify_store/admin/api/$api_version/products/$product_id.json";

    $headers = array(
        "Content-Type: application/json",
        "X-Shopify-Access-Token: $access_token"
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
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
    // Get headers
    $headers = getallheaders();

    if (!isset($headers['X-Shopify-Store-Domain']) || !isset($headers['X-Shopify-Access-Token']) || !isset($headers['X-Shopify-Api-Version'])) {
        throw new Exception('Missing required headers: X-Shopify-Store-Domain, X-Shopify-Access-Token, X-Shopify-Api-Version');
    }

    $shopify_store = $headers['X-Shopify-Store-Domain'];
    $access_token = $headers['X-Shopify-Access-Token'];
    $api_version = $headers['X-Shopify-Api-Version'];

    $product_id = isset($_GET['id']) ? $_GET['id'] : null;

    // if (is_string($product_id) && strpos($product_id, 'gid://shopify/Product/') === 0) {
    //     $product_id = substr($product_id, strrpos($product_id, '/') + 1);
    // }

    if (!$product_id) {
        throw new Exception('Product id is required');
    }

    $result = getShopifyProduct($shopify_store, $access_token, $api_version, $product_id);

    if (empty($result['product'])) {
        throw new Exception('Product not found');
    }

    echo json_encode($result);

} catch (Exception $e) {
    header('Content-Type: application/json', true, 400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
