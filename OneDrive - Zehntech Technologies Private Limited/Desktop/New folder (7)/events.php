<?php
header("Access-Control-Allow-Origin: *");
include ('inc/DB.class.php');
include('inc/functions.php');
include('constant.php');
$shop_url = $_REQUEST['shop_url'];

$db = new DB();
$table = 'stores_gyata';
$conditions['return_type'] = 'single';
$conditions['where'] = array('shop_url' => $shop_url, 'install_status' => 1);
$db_veri_key = (object) $db->getRows($table, $conditions);
$application_id = $db_veri_key->application_id;
$access_token = $db_veri_key->access_token;

if (isset($_POST['disconnect_account'])) {
  $data = array(
    'application_id' => "",
    'widget_script' => ""
  );
  $condition = array('shop_url' => $_REQUEST['shop']);
  $result = $db->update($table, $data ,$condition );

  header('Content-Type: application/json');
  echo json_encode(array('success' => true));
  exit();
}
else{
  return json_encode(array("data" => "Some thing went wrong"));
}