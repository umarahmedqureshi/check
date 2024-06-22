<?php
header('Content-Type: application/json');

// Function to get products based on search query
function searchProducts($shopify_store, $storefront_access_token, $api_version, $query, $first, $fields) {
    $url = "https://$shopify_store/api/$api_version/graphql.json";

    // Construct the dynamic fields part of the query
    $fields_query = implode("\n", array_map(function($field) {
        return $field;
    }, $fields));

    $graphql_query = [
        'query' => "query searchProducts(\$query: String!, \$first: Int) {
            search(query: \$query, first: \$first, types: PRODUCT) {
                totalCount
                pageInfo {
                    endCursor
                    hasNextPage
                    hasPreviousPage
                    startCursor
                }
                edges {
                    node {
                        ... on Product {
                            id
                            title
                            handle
                            featuredImage {
                                url
                            }
                            variants(first: 100) {
                                nodes {
                                    price {
                                        amount
                                        currencyCode
                                    }
                                    id
                                    image {
                                      url
                                    }
                                    title
                                }
                            }
                            $fields_query
                        }
                    }
                }
            }
        }",
        'variables' => [
            'query' => $query,
            'first' => $first
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
    $query = isset($_GET['query']) ? $_GET['query'] : null;
    $first = isset($_GET['first']) ? (int)$_GET['first'] : 5;
    $fields = isset($_GET['fields']) ? explode(',', $_GET['fields']) : ['id', 'title'];

    if (!$query) {
        throw new Exception('Search query parameter is required');
    }

    $result = searchProducts($shopify_store, $storefront_access_token, $api_version, $query, $first, $fields);

    echo json_encode($result);

} catch (Exception $e) {
    header('Content-Type: application/json', true, 400);
    echo json_encode(['error' => $e->getMessage()]);
}
