<?php
$shopifyDomain = $params['shop'];

$webhookUrl = GYATAAI_APP_URL.'/uninstall_hook_response.php';
$webhookTopic = 'app/uninstalled';

$apiUrl = "https://$shopifyDomain/admin/api/".API_VERSION."/webhooks.json";

$requestPayload = [
    'webhook' => [
        'topic' => $webhookTopic,
        'address' => $webhookUrl,
        'format' => 'json'
    ]
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-Shopify-Access-Token: ' . $access_token
]);

curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestPayload));
$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $responseData = json_decode($response, true);
    if (isset($responseData['webhook'])) {
        $webhookId = $responseData['webhook']['id'];
        echo "Webhook created successfully with ID: $webhookId";
    } else {
        echo "Error creating webhook: " . print_r($responseData['errors']);
    }
} else {
    echo "Failed to create webhook. Please check your request.";
}
