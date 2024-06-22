<?php
header("Access-Control-Allow-Origin: *"); 
ini_set('display_errors', '1');

include 'inc/DB.class.php';
include('./constant.php');
function verify_webhook($data, $hmac_header)
{
  $calculated_hmac = base64_encode(hash_hmac('sha256', $data, SHOPIFY_SECRET_KEY, true));
  return hash_equals($calculated_hmac, $hmac_header);
}
$hmac_header = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'];

$data = file_get_contents('php://input');
$verified = verify_webhook($data, $hmac_header);

error_log('Webhook verified: '.var_export($verified, true)); // Check error.log to see the result

if ($verified) {
    // $db = new DB();
    // $table = 'stores_gyata';
    $req = file_get_contents('php://input');
  
    $result = json_decode($req);
    $data = array(
      'install_status' => 0
    );
    // $condition = array('shop_url' => $result->domain);
    // $result1 = $db->update($table, $data ,$condition );
  
    file_put_contents(__DIR__."/customer_create.json", $req);
    die();
  
  } else {
    http_response_code(401);
  }

?>