<?php
header("Access-Control-Allow-Origin: *"); 
include ('inc/DB.class.php');

$db = new DB();
$table = 'stores_gyata';
$conditions['return_type'] = 'single';
$conditions['where'] = array('shop_url' => $_GET['shop']);
$get_single_result = $db->getRows($table, $conditions);
$access_token = $get_single_result['access_token'];

$shop = $_GET['shop'];
$token = $access_token;
$name = $_GET['name'];

function graphpQL_shopify_call($token, $shop, $query = [], $version) {
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://' . $shop . '/admin/api/' . $version . '/graphql.json',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode(['query' => $query]),
        CURLOPT_HTTPHEADER => array(
            'X-Shopify-Access-Token: '.$token,
            'Content-Type: application/json'
        ),
    ));

    $response = curl_exec($curl);
    if ($response === false) {
        echo 'Curl error: ' . curl_error($curl);
    }

    curl_close($curl);
    return $response;
}

function idtojson($shop, $prodid){
    $shop = explode('.', $shop)[0];
    $url_p = 'https://admin.shopify.com/store/' . $shop . '/products';
    $parts = explode('/', $prodid);
    $lastValue = end($parts);
    $url_p = $url_p . '/' . $lastValue . '.json';

    // echo "<pre>";
    
    header("Location: " . $url_p);
    // print_r($response."here is it:- \n" . $url_p); 

    // echo "</pre>";
    return $url_p;
}

$searchproduct = '
{
  products(first: 100, query: "'.$name.'") {
    edges {
      node {
        id
        title
        description
        variants(first: 1) {
          edges {
            node {
              id
              title
            }
          }
        }
      }
    }
  }
}
';

$result = graphpQL_shopify_call($token, $shop, $searchproduct, "2024-01");
echo "dharti";
$result = json_decode($result, true);

$products = $result['data']['products']['edges'];
$responses = array();
$curl = curl_init();
foreach ($products as $i){
    echo "<pre>";
    // print_r($i['node']["id"]);
    print_r($i);
    echo "</pre>";

    $url = idtojson($shop, $i['node']["id"]);

    // curl_setopt($curl, CURLOPT_URL, $url);
    // curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    // $response = curl_exec($curl);
    // if ($response === false) {
    //     echo "Failed to fetch $url: " . curl_error($curl) . "\n";
    // } else {
    //     $responses[] = $response;
    // }

    // $curl = curl_init($url);
    // curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    // $response = curl_exec($curl);

    // if ($response === false) {
    //     echo 'Error: ' . curl_error($curl);
    // } else {
    //     echo 'Response: ' . $response;
    // }

    // curl_close($curl);

}

print_r($responses);

curl_close($curl);
?>
