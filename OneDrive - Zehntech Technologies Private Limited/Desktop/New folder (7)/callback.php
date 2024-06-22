<?php
header('Content-Type: application/json');
require_once('./inc/DB.class.php');
$db = new DB();
$table = 'stores_gyata';
// Function to send JSON response
function sendResponse($status, $message) {
    http_response_code($status);
    echo json_encode(['message' => $message]);
    exit();
}
// Check if the required fields are present
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST["app_id"]) || !isset($_POST["embedded_script"])) {
        sendResponse(400, "app_id & embedded_script not found");
    }

    if (!isset($_REQUEST["shop"])) {
        sendResponse(400, "shop is required");
    }

    $data = array(
        'application_id' => $_POST["app_id"],
        'widget_script' => $_POST["embedded_script"]
    );
    $conditions = array(
        'shop_url' => $_REQUEST['shop'],
        'install_status' => 1
    );

    $row = $db->update($table, $data, $conditions);

    if ($row) {
        sendResponse(200, "registered and app_id & embedded_script updated in DB");
    } else {
        sendResponse(500, "Failed to update the database");
    }
} else {
    sendResponse(405, "Method Not Allowed");
}
?>
