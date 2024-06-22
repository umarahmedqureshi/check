<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Function to fetch active products from Shopify
function fetchActiveProducts($storeDomain, $api_version, $access_token, $cursor = null, &$allProducts = array()) {
    $url = 'https://' . $storeDomain . '/admin/api/' . $api_version . '/products.json?status=active&limit=250';
    
    if ($cursor) {
        $url .= '&since_id=' . $cursor;
    } else {
        $url .= '&order=created_at%20asc';
    }

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'X-Shopify-Access-Token: ' . $access_token
        ),
    ));

    $response = curl_exec($curl);
    if (curl_errno($curl)) {
        echo json_encode(['error' => 'Error fetching active products: ' . curl_error($curl)]);
        curl_close($curl);
        http_response_code(500);
        exit;
    }

    $responseData = json_decode($response, true);
    curl_close($curl);

    if (!isset($responseData['products'])) {
        echo json_encode(['error' => 'Unexpected response structure: ' . $response]);
        http_response_code(500);
        exit;
    }

    $products = $responseData['products'];
    $allProducts = array_merge($allProducts, $products);

    if (count($products) === 250) {
        $lastProduct = end($products);
        $cursor = $lastProduct['id'];
        fetchActiveProducts($storeDomain, $api_version, $access_token, $cursor, $allProducts);
    }

    return $allProducts;
}

// Main logic to handle the request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $storeDomain = isset($_SERVER['HTTP_X_SHOPIFY_STORE_DOMAIN']) ? $_SERVER['HTTP_X_SHOPIFY_STORE_DOMAIN'] : null;
    $api_version = isset($_SERVER['HTTP_X_SHOPIFY_API_VERSION']) ? $_SERVER['HTTP_X_SHOPIFY_API_VERSION'] : null;
    $access_token = isset($_SERVER['HTTP_X_SHOPIFY_ACCESS_TOKEN']) ? $_SERVER['HTTP_X_SHOPIFY_ACCESS_TOKEN'] : null;

    if (!$storeDomain || !$api_version || !$access_token) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required headers']);
        exit;
    }else{
        $products = fetchActiveProducts($storeDomain, $api_version, $access_token);
        echo json_encode($products);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
