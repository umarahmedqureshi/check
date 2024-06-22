<?php
include('./constant.php');
$shop = $_GET['shop'];
$client_id = SHOPIFY_API_KEY;

$scopes = "read_orders,write_orders,read_all_orders,write_products,read_products,read_product_listings,read_themes,write_themes,read_content,write_content,read_checkouts,write_checkouts,read_customers,write_customers,read_resource_feedbacks,write_resource_feedbacks,read_script_tags,write_script_tags,read_price_rules,write_price_rules";
$unauthenticated_scopes = ",unauthenticated_read_content,unauthenticated_read_customer_tags,unauthenticated_read_product_tags,unauthenticated_read_product_listings,unauthenticated_write_checkouts,unauthenticated_read_checkouts,unauthenticated_write_customers,unauthenticated_read_customers";
$redirect_uri = GYATAAI_APP_URL."/token.php";
 
$oauth_url = "https://". $shop."/admin/oauth/authorize?client_id=".$client_id."&scope=".$scopes.$unauthenticated_scopes."&redirect_uri=".urlencode($redirect_uri);
header("Location: " . $oauth_url);
exit();
