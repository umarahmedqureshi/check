<?php
// Function to get products with pagination
function getShopifyProducts($shopify_store, $access_token, $api_version, $limit = 50, $page_info = null) {
    $url = "https://$shopify_store/admin/api/$api_version/products.json?limit=$limit";

    if ($page_info) {
        $url .= "&page_info=$page_info";
    }

    $headers = array(
        "Content-Type: application/json",
        "X-Shopify-Access-Token: $access_token"
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HEADER, true); // Include header in the output

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

    if ($http_code != 200) {
        throw new Exception("Shopify API request failed with HTTP code $http_code: " . curl_error($ch));
    }

    $header = substr($response, 0, $header_size);
    $body = substr($response, $header_size);

    curl_close($ch);

    $response_data = json_decode($body, true);

    $products = $response_data['products'];
    $pagination_info = extractPaginationInfo($header);

    return array('products' => $products, 'pagination' => $pagination_info);
}

// Function to extract pagination info from the Link header
function extractPaginationInfo($header) {
    $pagination_info = array();

    if (preg_match_all('/<([^>]+)>; rel="([^"]+)"/', $header, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $url = $match[1];
            $rel = $match[2];
            parse_str(parse_url($url, PHP_URL_QUERY), $query);
            if (isset($query['page_info'])) {
                $pagination_info[$rel] = $query['page_info'];
            }
        }
    }

    return $pagination_info;
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

    $limit = isset($_GET['limit']) ? $_GET['limit'] : 50; // Number of products per page
    $page_info = isset($_GET['page_info']) ? $_GET['page_info'] : null;

    $result = getShopifyProducts($shopify_store, $access_token, $api_version, $limit, $page_info);

    header('Content-Type: application/json');
    echo json_encode(['products' => $result]);
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>