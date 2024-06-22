<?php
    header('Content-Type: application/json');
    include('../inc/DB.class.php');
    include('../inc/functions.php');
    include('../constant.php');

    $store_name = !empty($_POST['shop']) ? $_POST['shop'] : '';
    $email = !empty($_POST['email']) ? $_POST['email'] : '';
    $password = !empty($_POST['password']) ? $_POST['password'] : '';
    $password_match = false;

    $db = new DB();
    $table = 'stores_gyata';
    $conditions['return_type'] = 'single';
    $conditions['where'] = array('shop_url' => $store_name);
    $get_single_result = $db->getRows($table, $conditions);
    $access_token = $get_single_result['access_token'];
    $table = 'customers';
    $conditions['where'] = array('email' => strtolower($email));
    $get_customer_result = $db->getRows($table, $conditions);
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
            if(empty($email)){
                $userResponse['message'] = 'Customer\'s email is required';
            } else {
                if(empty($password)){
                    $userResponse['message'] = 'Password is required';
                } else{
                    $endpoint = '';
                    if(empty($get_customer_result)){
                        $userResponse['message'] = 'Customer not found';
                    } else {
                        if(empty($get_customer_result['password_hash'])){
                            $endpoint .= 'customers/search.json?query=email:'.$email;
                        } else{
                            if(password_verify($password, $get_customer_result['password_hash'])){
                                $endpoint .= 'customers/search.json?query=email:'.$email;
                            } else {
                                $userResponse['message'] = 'Password is invalid';
                                $userJson = json_encode($userResponse);
                                echo $userJson;
                                die;
                            }
                            $userShopifyResponse = shopify_call($access_token, $store_name, "/admin/api/" . API_VERSION . "/".$endpoint, null, "GET");
                            $userArr = json_decode($userShopifyResponse['response'],true);
                            if(!empty($userArr) && empty($userArr['errors'])){
                                $userResponse['message'] = 'Customer found sucessfully';
                                $userResponse['data'] = $userArr;
                            } else {
                                $userResponse['message'] = 'Customer not found';
                            }
                        }
                    }
                }
            }
        }
    }
    $userJson = json_encode($userResponse);
    echo $userJson;
    die;
?>