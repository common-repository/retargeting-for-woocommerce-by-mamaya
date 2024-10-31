<?php
/*
Author: Mamaya
Author URI: http://www.gomamaya.com/
Plugin Name: Retargeting for WooCommerce by Mamaya
Plugin URI: http://www.gomamaya.com/
Description: 1-Click Retargeting for WooCommerce
License: GPL2
Version: 1.4.9
*/

/**
 * Copyright (c) 2016 Mamaya Inc. (email: support@gomamaya.com). All rights reserved.
 */
 
 
if ( !defined( 'ABSPATH' ) ) exit;
include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); 

function mamaya_products_feed() {  
 $template = dirname(__FILE__) . '/mamaya-feed-products.php';  
 load_template ( $template );  
}  
function mamaya_categories_feed() {  
 $template = dirname(__FILE__) . '/mamaya-feed-categories.php';  
 load_template ( $template );  
}  
  
//Add the product feed RSS  
add_action('do_feed_products', 'mamaya_products_feed', 10, 1);  
add_action('do_feed_categories', 'mamaya_categories_feed', 10, 1);  
  
//Update the Rerewrite rules  
add_action('init', 'mamaya_add_product_feed');  
   
//function to add the rewrite rules  
function my_rewrite_product_rules( $wp_rewrite ) {  
 $new_rules = array(  
 'feed/(.+)' => 'index.php?feed='.$wp_rewrite->preg_index(1)  
 );  
 $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;  
}  
  
//add the rewrite rule  
function mamaya_add_product_feed( ) {  
  global $wp_rewrite;  
  add_action('generate_rewrite_rules', 'my_rewrite_product_rules');  

  add_feed('mmy_products', 'mamaya_products_feed');
  add_feed('mmy_categories', 'mamaya_categories_feed');

  $wp_rewrite->flush_rules();  
}

add_action('wp_head','mamaya_script_head');
function mamaya_script_head() {
  
  global $post;
  global $wp_query;

  $mamaya_guid = get_option('mamaya_guid', '');
  if ($mamaya_guid != '') {

    $is_shop = is_shop();
    if ($is_shop=='') $is_shop=0;

    $is_product_category = is_product_category();
    if ($is_product_category=='') $is_product_category=0;


    $cat_id = '';
    $cat_obj = $wp_query->get_queried_object();
    if ($cat_obj) $cat_id = $cat_obj->term_id;
    if ($cat_id == '') $cat_id = '""';

    $mmy_script="<script type='text/javascript'>\$MMY_PROD_ID=".get_the_ID()."; \$MMY_CAT_ID=".$cat_id."; \$MMY_IS_HOME=".$is_shop."; \$MMY_IS_CAT=".$is_product_category.";</script><script type='text/javascript' src='https://s3.amazonaws.com/mamaya/".$mamaya_guid.".js'></script>";
    echo $mmy_script;
  }
  
}

add_action( 'woocommerce_thankyou', 'mmy_checkout' );
function mmy_checkout( $order_id ) {
  $order = new WC_Order( $order_id );
  $subtotal = $order->get_subtotal();
  $currency = get_woocommerce_currency();
  echo "<script>\$MMY_TOTAL='$subtotal'; \$MMY_CURRENCY='$currency';</script>";
}


// function mamaya_plugin_settings_link($links) { 
//   $settings_link = '<a href="' . admin_url( 'options-general.php?page=mamaya-retargeting%2Fmamaya-retargeting.php' ) . '">Settings</a>'; 
//   array_unshift($links, $settings_link); 
//   return $links; 
// }
 
// $plugin = plugin_basename(__FILE__); 
// add_filter("plugin_action_links_$plugin", 'mamaya_plugin_settings_link' );


function mamaya_options_page() {

  global $mamaya_error;
  global $mamaya_error_msg;
  global $showing_admin_page;
  

?>

	<div class="wrap">

	<h2>MAMAYA</h2>
  
  
  <?php
    if ($mamaya_error) {
?>
  ERROR: <?php echo $mamaya_error_msg; ?>
  <br>
<?php
    }
?>
	


	GUID: <?php print get_option('mamaya_guid',''); ?>
  <br>
	
	
	</div><?php

}




function mamaya_admin_notice(){
echo '<div class="error">
   <p>Mamaya is not configured. Please finish setup <a href="http://agent.gomamaya.com/agent/setuprm">here</a>.</p>
</div>';
}

// if ((get_option('mamaya_pixid','') == '' || get_option('mamaya_cpixid','') == '')) {
//   add_action('admin_notices', 'mamaya_admin_notice');
// }


function mamaya_admin_menu() {
	if (function_exists('add_options_page')) {
    add_options_page('Mamaya plugin', 'Mamaya', 8, __FILE__, 'mamaya_options_page');
	}
}

function getGUID(){
    if (function_exists('com_create_guid')){
        return com_create_guid();
    }else{
        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = chr(123)// "{"
            .substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12)
            .chr(125);// "}"
        return $uuid;
    }
}

add_action('admin_menu', 'mamaya_admin_menu');

$mamaya_guid = get_option('mamaya_guid', '');
if ($mamaya_guid == '') {
  $mamaya_guid = trim(getGUID(), '{}');
  $mamaya_guid = str_replace('-', '', $mamaya_guid);
  update_option('mamaya_guid', $mamaya_guid);
}

if (strpos($_SERVER["REQUEST_URI"], '/mamayaping') != FALSE ) {
  $plugin_data = get_plugin_data( __FILE__ );
  $plugin_version = $plugin_data['Version'];
  
  $vals = array('version' => $plugin_version, 'guid' => get_option('mamaya_guid',''));
  echo "MMYPONG" . json_encode($vals) . "MMYPONG";
  
}


