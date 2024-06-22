<?php
 $shopify_domain = $params['shop'];

 // Webhook data
 $webhook_topic = 'customers/delete';
 $webhook_callback_url = GYATAAI_APP_URL.'/customer_delete_webhook_handler.php';
 $webhook_format = 'json';
 
 // Build webhook registration request
 $request_url = "https://".$shopify_domain."/admin/api/2021-10/webhooks.json";
 $request_body = [
     'webhook' => [
         'topic' => $webhook_topic,
         'address' => $webhook_callback_url,
         'format' => $webhook_format
         ]
 ];
  // Create cURL request
 $ch = curl_init($request_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-Shopify-Access-Token: ' . $access_token
    ]);

    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_body));
    $response = curl_exec($ch);
    curl_close($ch);
    // Handle response
    $response_data = json_decode($response, true);

    if (isset($response_data['webhook'])) {
        echo "Webhook registered successfully.";
    } else {
        echo "Failed to register webhook. Error: " . $response_data['errors'];
    }
?>