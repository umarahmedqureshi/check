<?php
header('Content-Type: application/json');

// Function to get orders by customer ID with pagination and filtering
function getOrdersByCustomer($shopify_store, $access_token, $api_version, $customer_id, $limit = 50, $page_info = null, $filters = array()) {
    // Truncate "gid://shopify/Customer/" from $customer_id if present
    if (is_string($customer_id) && strpos($customer_id, 'gid://shopify/Customer/') === 0) {
        $customer_id = substr($customer_id, strrpos($customer_id, '/') + 1);
    }

    $url = "https://$shopify_store/admin/api/$api_version/customers/$customer_id/orders.json?limit=$limit";

    if ($page_info) {
        $url .= "&page_info=$page_info";
    }

    // Add filters to the URL
    foreach ($filters as $key => $value) {
        $url .= "&$key=" . urlencode($value);
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

    $orders = $response_data['orders'];
    $pagination_info = extractPaginationInfo($header);

    return array('orders' => $orders, 'pagination' => $pagination_info);
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

try {
    // Get headers
    $headers = getallheaders();

    if (!isset($headers['X-Shopify-Store-Domain']) || !isset($headers['X-Shopify-Access-Token']) || !isset($headers['X-Shopify-Api-Version'])) {
        throw new Exception('Missing required headers: X-Shopify-Store-Domain, X-Shopify-Access-Token, X-Shopify-Api-Version');
    }

    $shopify_store = $headers['X-Shopify-Store-Domain'];
    $access_token = $headers['X-Shopify-Access-Token'];
    $api_version = $headers['X-Shopify-Api-Version'];

    $customer_id = isset($_GET['customer_id']) ? $_GET['customer_id'] : null;
    $limit = isset($_GET['limit']) ? $_GET['limit'] : 50; // Number of orders per page
    $page_info = isset($_GET['page_info']) ? $_GET['page_info'] : null;

    if (!$customer_id) {
        throw new Exception('Customer id is required');
    }

    // Extract filters from GET parameters
    $filters = array();
    if (isset($_GET['status'])) {
        $filters['status'] = $_GET['status'];
    }
    if (isset($_GET['financial_status'])) {
        $filters['financial_status'] = $_GET['financial_status'];
    }
    if (isset($_GET['fulfillment_status'])) {
        $filters['fulfillment_status'] = $_GET['fulfillment_status'];
    }
    if (isset($_GET['created_at_min'])) {
        $filters['created_at_min'] = $_GET['created_at_min'];
    }
    if (isset($_GET['created_at_max'])) {
        $filters['created_at_max'] = $_GET['created_at_max'];
    }
    if (isset($_GET['attribution_app_id'])) {
        $filters['attribution_app_id'] = $_GET['attribution_app_id'];
    }
    if (isset($_GET['ids'])) {
        $filters['ids'] = $_GET['ids'];
    }
    if (isset($_GET['processed_at_max'])) {
        $filters['processed_at_max'] = $_GET['processed_at_max'];
    }
    if (isset($_GET['processed_at_min'])) {
        $filters['processed_at_min'] = $_GET['processed_at_min'];
    }
    if (isset($_GET['since_id'])) {
        $filters['since_id'] = $_GET['since_id'];
    }
    if (isset($_GET['fields'])) {
        $filters['fields'] = $_GET['fields'];
    }

    $result = getOrdersByCustomer($shopify_store, $access_token, $api_version, $customer_id, $limit, $page_info, $filters);

    if (empty($result['orders'])) {
        throw new Exception('Order not found');
    }

    echo json_encode($result);

} catch (Exception $e) {
    header('Content-Type: application/json', true, 400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
