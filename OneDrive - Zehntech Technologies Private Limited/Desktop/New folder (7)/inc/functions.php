

<?php
// include('./constant.php');
// include('DB.class.php');

function shopify_call($token, $shop, $api_endpoint, $query = array(), $method = 'GET', $request_headers = array()) {
   
    // Build URL
    $url = "https://" . $shop. $api_endpoint;
    if (!is_null($query) && in_array($method, array('GET',  'DELETE'))) $url = $url . "?" . http_build_query($query);
 
    // Configure cURL
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, TRUE);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($curl, CURLOPT_MAXREDIRS, 3);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    // curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 3);
    // curl_setopt($curl, CURLOPT_SSLVERSION, 3);
    curl_setopt($curl, CURLOPT_USERAGENT, 'My New Shopify App v.1');
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
 
    // Setup headers
    $request_headers[] = "";
    if (!is_null($token)) $request_headers[] = "X-Shopify-Access-Token: " . $token;
    curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);
 
    if ($method != 'GET' && in_array($method, array('POST', 'PUT'))) {
        if (is_array($query)) $query = http_build_query($query);
        curl_setopt ($curl, CURLOPT_POSTFIELDS, $query);
    }
   
    // Send request to Shopify and capture any errors
    $response = curl_exec($curl);
    $error_number = curl_errno($curl);
    $error_message = curl_error($curl);
 
    // Close cURL to be nice
    curl_close($curl);
 
    // Return an error is cURL has a problem
    if ($error_number) {
        return $error_message;
    } else {
 
        // No error, return Shopify's response by parsing out the body and the headers
        $response = preg_split("/\r\n\r\n|\n\n|\r\r/", $response, 2);
 
        // Convert headers into an array
        $headers = array();
        $header_data = explode("\n",$response[0]);
        $headers['status'] = $header_data[0]; // Does not contain a key, have to explicitly set
        array_shift($header_data); // Remove status, we've already set it above
        foreach($header_data as $part) {
            $h = explode(":", $part,2);
            $headers[trim($h[0])] = trim($h[1]);
        }
 
        // Return headers and Shopify's response
        return array('headers' => $headers, 'response' => $response[1]);
 
    }
   
}
//shopify_call() { ... } <--- make sure you add the code outside this function
function str_btwn($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}
 
// This function is respomsible for get the access token from db
function get_access_token($db, $table, $shop_url){

    $conditions['return_type'] = 'single';
    $conditions['where'] = array('shop_url' => $shop_url);
    $get_single_result = $db->getRows($table, $conditions);
    return $get_single_result['access_token'];
    
    }

/*----------------------- Create API URL Query -------------------------*/
function apiQuery($apifor){
	$queryParams = [
		'status' => ($apifor == 'prducts') ? 'active' : 'any',
        'limit' => null,
		'title' => null,
        'product_type' => null,
        'vendor' => null,
        'tags' => null,
        'published_status' => null,
        'since_id' => null,
        'order' => null,
		'name' => null,
		'financial_status' => null,
		'fulfillment_status' => null,
		'customer_id' => null
    ];
	foreach ($queryParams as $key => $defaultValue) {
		if (!empty($_REQUEST[$key])) {
			$queryParams[$key] = ($key != 'name') ? $_REQUEST[$key] : str_replace('#', '', $_REQUEST[$key]);
        }
    }
	
	$filter_query = '?status=' . urlencode($queryParams['status']);
    unset($queryParams['status']);
	foreach ($queryParams as $key => $value) {
        if ($key !== 'since_id') {
            $filter_query .= '&order=created_at%20asc';
        }
		if ($value !== null) {
			$filter_query .= '&' . $key . '=' . urlencode($value);
		}
	}
	
	return $filter_query;
}

/*----------------------- Store All Customers to DB -------------------------*/
function storeAllCustomer($db, $storeData,$customerArr){
    if(empty($storeData)){
        return false;
    } else {
        $store_id = $storeData['id'];
        if(empty($customerArr)){
            return false;
        } else {
            $customer_data = [];
            foreach($customerArr['customers'] as $key =>$value){
                $customer_id = $value['id'];
                $customer_email = $value['email'];
                $customer_phone = !empty($value['phone']) ? $value['phone'] : 'NULL';
                $conditions['where'] = array('customer_id' => $customer_id, 'store_id' => $store_id);
                $get_single_result = $db->getRows('customers', $conditions);
                if ($get_single_result == false) {
                    array_push($customer_data, array('store_id' => $store_id, 'customer_id' => $customer_id, 'email' => $customer_email, 'phone_number' => $customer_phone, 'otp' => mt_rand(100000, 999999)));    
                } else {
                }
            }
            $get_cus_result = $db->customerBulkUpload('customers', $customer_data);
            if($get_cus_result){
            } else {
                return false;
            }
        }
    }
}

/*----------------------- Send OTP Customer's Email -------------------------*/
function sendOTP($storeData, $shopArr, $customerArr){
    if(empty($storeData)){
        return false;
    } else {
        $store_id = $storeData['id'];
        if(empty($customerArr)){
            return false;
        } else {
            $headers = 'From:'.$shopArr['shop']['email']; //$from = 'store+'.$shopArr['shop']['id'].'@t.shopifyemail.com'; store+'.$shopArr['shop']['id'].'@t.shopifyemail.com
            $to = $customerArr['email'];
            $subject = "Your OTP with store ".$shopArr['shop']['name'];
            $message = "Your One Time Password (OTP) is: " . $customerArr['otp'];
            $emailRes = mail($to, $subject, $message, $headers);
            print_r($emailRes);
            die('send OTP');
            if (mail($to, $subject, $message, $headers)) {
                echo 'Email sent successfully!';
            } else {
                echo 'Failed to send email.';
            }

        }
    }
}