<?php
header('Content-Type: application/json');

// Function to get store policies
function getShopifyPolicies($shopify_store, $access_token, $api_version) {
    $url = "https://$shopify_store/admin/api/$api_version/policies.json";

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

// Usage example
try {
    // Get headers
    $headers = getallheaders();
    
    if (!isset($headers['X-Shopify-Store-Domain']) || !isset($headers['X-Shopify-Access-Token']) || !isset($headers['X-Shopify-Api-Version'])) {
        throw new Exception('Missing required headers: X-Shopify-Store-Domain, X-Shopify-Access-Token, X-Shopify-Api-Version');
    }
    
    $shopify_store = $headers['X-Shopify-Store-Domain'];
    $access_token = $headers['X-Shopify-Access-Token'];
    $api_version = $headers['X-Shopify-Api-Version'];

    $result = getShopifyPolicies($shopify_store, $access_token, $api_version);

    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    header('Content-Type: application/json', true, 400);
    echo json_encode(array('error' => $e->getMessage()));
}
?>
