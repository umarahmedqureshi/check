<?php
header('Content-Type: application/json');

// Function to get products based on search query
function searchProducts($shopify_store, $storefront_access_token, $api_version, $email, $password) {
    $url = "https://$shopify_store/api/$api_version/graphql.json";

    $graphql_query = [
        'query' => "mutation SignInWithEmailAndPassword(\$email: String!, \$password: String!) {
            customerAccessTokenCreate(input: {email: \$email, password: \$password}) {
                customerAccessToken {
                    accessToken
                    expiresAt
                }
                customerUserErrors {
                    code
                    message
                }
            }
        }",
        'variables' => [
            'email' => $email,
            'password' => $password
        ]
    ];
    $payload = json_encode($graphql_query);

    $headers = [
        "Content-Type: application/json",
        "X-Shopify-Storefront-Access-Token: $storefront_access_token"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($http_code != 200) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        throw new Exception("Shopify API request failed with HTTP code $http_code: $error_msg");
    }

    curl_close($ch);
    return json_decode($response, true);
}

try {
    // Get headers
    $headers = getallheaders();
    if (!isset($headers['X-Shopify-Store-Domain']) || !isset($headers['X-Shopify-Storefront-Access-Token']) || !isset($headers['X-Shopify-Api-Version'])) {
        throw new Exception('Missing required headers: X-Shopify-Store-Domain, X-Shopify-Storefront-Access-Token, X-Shopify-Api-Version');
    }

    $shopify_store = $headers['X-Shopify-Store-Domain'];
    $storefront_access_token = $headers['X-Shopify-Storefront-Access-Token'];
    $api_version = $headers['X-Shopify-Api-Version'];

    // Get query parameters
    $email = isset($_GET['email']) ? $_GET['email'] : null;
    $password = isset($_GET['password']) ? $_GET['password'] : null;
    $fields = isset($_GET['fields']) ? explode(',', $_GET['fields']) : ['id', 'title'];

    if (!$email || !$password) {
        throw new Exception('Email and password are required');
    }

    $result = searchProducts($shopify_store, $storefront_access_token, $api_version, $email, $password);

    echo json_encode($result);

} catch (Exception $e) {
    header('Content-Type: application/json', true, 400);
    echo json_encode(['error' => $e->getMessage()]);
}
