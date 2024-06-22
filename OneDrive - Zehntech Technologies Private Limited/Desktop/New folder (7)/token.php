<?php
include('./constant.php');
include 'inc/DB.class.php';
include('./inc/functions.php');

$db = new DB();
$table_name = 'stores_gyata';

$client_id = SHOPIFY_API_KEY;
$client_secret = SHOPIFY_SECRET_KEY;
$params = $_GET; // Retrieve all request parameters
$hmac = $_GET['hmac']; // Retrieve HMAC request parameter

// Remove hmac from params
$params = array_diff_key($params, array('hmac' => ''));

// Sort params lexographically
ksort($params);


// Compute HMAC
$computed_hmac = hash_hmac('sha256', http_build_query($params), $client_secret);

// Use HMAC data to check if the response is from Shopify
if (hash_equals($hmac, $computed_hmac)) {
    // Set variables for our request
    $query = array(
        "client_id" => $client_id, // Your Client ID
        "client_secret" => $client_secret, // Your app credentials (CLIENT SECRET)
        "code" => $params['code'] // Grab the access key from the URL
    );

    // Generate access token URL
    $access_token_url = "https://" . $params['shop'] . "/admin/oauth/access_token";

    // Configure curl client and execute request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $access_token_url);
    curl_setopt($ch, CURLOPT_POST, count($query));
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query));
    

    // Execute the cURL request and handle errors
    $result = curl_exec($ch);
    if ($result === false) {
        // Handle cURL error
        $error_message = curl_error($ch);
        // Log or display the error message
        die('cURL error: ' . $error_message);
    }
    curl_close($ch);

    // Store the access token
    $result = json_decode($result, true);
    $access_token = $result['access_token'];
    // echo $access_token;
    
    $sfat_data = array(
        'storefront_access_token' => array(
            'title' => 'Gyatagpt'
        )
    );
    $sfat = shopify_call($access_token, $params['shop'] , "/admin/api/".API_VERSION."/storefront_access_tokens.json", $sfat_data, 'POST');
    $sfat = json_decode($sfat['response'], TRUE);
    $sfat = $sfat['storefront_access_token']['access_token'];

	$data = array(
		'shop_url ' => $params['shop'],
		'access_token ' => $access_token,
        'storefront_access_token' => $sfat,
		'install_status' => 1
	);
    
	$conditions['where'] = array('shop_url' => $params['shop']);
	$conditions['return_type'] = 'count';
	$row = $db->getRows($table_name,$conditions);

	if ($row < 1 || empty($row)){
		$result = $db->insert($table_name, $data );
	}else{
		$condition = array('shop_url' => $params['shop']);
        $result = $db->update($table_name, $data ,$condition );
	}

	if ($result){
        include "create_uninstall_webhook.php";
        include "create_customer_create_webhook.php";
        include "customer_delete_webhook.php";

        $customer_details = shopify_call($access_token, $params['shop'], "/admin/api/" . API_VERSION . "/customers.json?fields=id,email,phone", null, 'GET');
        $customerArr = json_decode($customer_details['response'],true);
        $db_customer = storeAllCustomer($db, $get_single_result, $customerArr);
        header("Location: https://" . $params['shop'] . "/admin/apps/gyatagpt");
		exit();
	}else{
		echo "error test";
	}

    exit;
} else {
    // Someone is trying to be shady!
    die('This request is NOT from Shopify!');
}

