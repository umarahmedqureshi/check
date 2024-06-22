<?php 
$shopifyDomain = $_GET['shop']; // Replace with your Shopify store domain
$access_token = $_GET['at']; // Replace with your Shopify access token
// echo $shopifyDomain ;

// API endpoint URL
$apiUrl = "https://$shopifyDomain/admin/api/2023-04/webhooks.json";

// cURL request to retrieve all webhooks
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-Shopify-Access-Token: ' . $access_token
]);
$response = curl_exec($ch);
curl_close($ch);

// Handle the response
if ($response) {
    $responseData = json_decode($response, true);
    if (isset($responseData['webhooks'])) {
        $webhooks = $responseData['webhooks'];
        echo "<pre>";
        print_r($webhooks);
        echo "</pre>";
        if (count($webhooks) > 0) {
            echo "Webhooks created in your store:<br>";
            foreach ($webhooks as $webhook) {
                echo "- Topic: " . $webhook['topic'] . ", URL: " . $webhook['address'] . "<br>";
            }
        } else {
            echo "No webhooks found in your store.";
        }
    } else {
        echo "Error retrieving webhooks: " . $responseData['errors'];
    }
} else {
    echo "Failed to retrieve webhooks. Please check your request.";
}

