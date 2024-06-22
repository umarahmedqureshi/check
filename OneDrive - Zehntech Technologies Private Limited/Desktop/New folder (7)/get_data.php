<?php 
include ('inc/DB.class.php');
include('inc/functions.php');
include('./constant.php');
$db = new DB();
$shop_url = $_GET['shop'];
$table = 'stores_gyata';

// $access_token = get_access_token($db, $table, $shop_url);
$access_token = $_GET['at'];
$data_array= array();
$end_point = $_GET['end_point'];
if(isset($_GET['id'])){
    $end_point_id = '/'.$_GET['id'];
}else{
    $end_point_id = '';
}
// $end_point_id = '';

$get_shop_details = shopify_call($access_token, $shop_url, "/admin/api/".API_VERSION."/".$end_point.$end_point_id.".json", $data_array, 'GET');
$get_shop_details = json_decode($get_shop_details['response'], true);

echo "<pre>";
print_r($get_shop_details);
echo "</pre>";


