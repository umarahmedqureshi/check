<?php
    header("Access-Control-Allow-Origin: *"); 
    ini_set('display_errors', '1');

    include('../inc/DB.class.php');
    include('../inc/functions.php');
    include('../constant.php');

    $store_name = !empty($_REQUEST['shop']) ? $_REQUEST['shop'] : '';
    $email = !empty($_REQUEST['email']) ? $_REQUEST['email'] : '';
    
    $db = new DB();
    $table = 'stores_gyata';
    $conditions['return_type'] = 'single';
    $conditions['where'] = array('shop_url' => $store_name);
    $get_single_result = $db->getRows($table, $conditions);
    $access_token = $get_single_result['access_token'];
    $userResponse = [
        'message' => '',
        'data' => []
    ];
    
    if (empty($store_name)) {
        $userResponse['message'] = 'Store name is required.';
    } else {
        if (empty($email)) {
            $userResponse['message'] = 'Email is required';
        } else {
            $table = 'customers';
            $conditions['where'] = array('store_id' => $get_single_result['id'], 'email' => $email);
            $get_customer_result = $db->getRows($table, $conditions);
            $shop_details = shopify_call($access_token, $store_name, "/admin/api/" . API_VERSION . "/shop.json", null, 'GET');
            $shopArr = json_decode($shop_details['response'],true);
            // print_r($shopArr);
            // die;
            if(empty($get_customer_result)){
                $userResponse['message'] = 'Customer with this email is not found.';
            } else {
                $email_responce = sendOTP($get_single_result, $shopArr, $get_customer_result);
                
            }
        }
     }

    $userJson = json_encode($userResponse);
    echo $userJson;
    die;


?>    