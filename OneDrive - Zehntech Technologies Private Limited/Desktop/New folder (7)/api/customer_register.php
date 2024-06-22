<?php
    header("Access-Control-Allow-Origin: *");
    ini_set('display_errors', '1');

    include('../inc/DB.class.php');
    include('../inc/functions.php');
    include('../constant.php');

    $store_name = !empty($_REQUEST['shop']) ? $_REQUEST['shop'] : '';
    $last_name = !empty($_REQUEST['last_name']) ? $_REQUEST['last_name'] : '';
    $phone = !empty($_REQUEST['phone']) ? $_REQUEST['phone'] : '';
    $password = !empty($_REQUEST['password']) ? $_REQUEST['password'] : '';
    $password_confirmation = !empty($_REQUEST['password_confirmation']) ? $_REQUEST['password_confirmation'] : '';
    $user_data = [
        'first_name' => null,
        'email' => null,
    ];
    
    $db = new DB();
    $table = 'stores_gyata';
    $conditions['return_type'] = 'single';
    $conditions['where'] = array('shop_url' => $store_name);
    $get_single_result = $db->getRows($table, $conditions);
    $ret_val = $db->createTable('customers');
    $access_token = $get_single_result['access_token'];
    
    $userResponse = [
        'message' => '',
        'data' => []
    ];

    if (empty($store_name)) {
        $userResponse['message'] = 'Store name is empty.';
    } else {
        if (empty($access_token)) {
            $userResponse['message'] = 'Access token is empty';
            
        } else {
            if(!empty($_REQUEST['password']) && !empty($_REQUEST['password_confirmation'])){ //)
                if( $_REQUEST['password'] != $_REQUEST['password_confirmation']){ //$_REQUEST['password_confirmation'])
                    $userResponse['message'] = 'Password and Confirm password should be same';
                    $userJson = json_encode($userResponse);
                    echo $userJson;
                    die;
                }
            }
            foreach ($user_data as $key => $value) {
                if (!empty($_REQUEST[$key])) {
                    $user_data[$key] = $_REQUEST[$key];
                } else {
                    $userResponse['message'] = ucwords(str_replace('_',' ',$key)).' is required';
                    $userJson = json_encode($userResponse);
                    echo $userJson;
                    die;
                }
            }
            $new_customer = [
                'customer' => [
                    'first_name' => $user_data['first_name'],
                    'last_name' =>$last_name,
                    'email' => $user_data['email'],
                    'phone' => $phone,
                    'password' => $password,
                    'password_confirmation' => $password_confirmation,
                    "send_email_welcome" => true
                ]
            ];
            $userShopifyResponse = shopify_call($access_token, $store_name, "/admin/api/" . API_VERSION . "/customers.json", $new_customer, "POST");
            $userArr = json_decode($userShopifyResponse['response'],true);
            if(!empty($userArr) && empty($userArr['errors'])){
                $userResponse['message'] = 'User created sucessfully';
                $userResponse['data'] = $userArr;
                if($ret_val){
                    $db_customers = [
                        'store_id' => $get_single_result['id'],                        
                        'customer_id' => $userArr['customer']['id'],
                        'email' => strtolower($new_customer['customer']['email']),
                        'phone_number' => ($new_customer['customer']['phone'] != '') ? $new_customer['customer']['phone'] : 'NULL',
                        'otp' => mt_rand(100000, 999999), // Generates a 6 digit otp number between 100000 and 999999
                        'password_hash' => password_hash($new_customer['customer']['password'], PASSWORD_DEFAULT)
                    ];
                    $ret_val = $db->insert('customers', $db_customers);
                } else {
                }
            } else {
                $userResponse['message'] = json_encode($userArr['errors']);
            }
        }
    }
    $userJson = json_encode($userResponse);
    echo $userJson;
    die;
?>