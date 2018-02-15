<?php
session_start();
require_once('./vendor/coingate/coingate-php/init.php');

use CoinGate\CoinGate;
$amount = $_REQUEST['amount'];
$plan_id = $_POST['custom'];
$item_name = $_POST['item_name'];


//if(($_POST['action'] ) && $_POST['action'] == 'createorder')  
//{
   $post_params = array(
                   'order_id'          => $plan_id,
                   'price'             => $amount,
        'currency'          => 'USD',
        'receive_currency'  => 'USD',
        //'callback_url'      => 'http://rakesh.crystalbiltech.com/bitcoin/callback.php',
        'cancel_url'        => 'http://rakesh.crystalbiltech.com/bitcoin/',
    'success_url'       => 'http://rakesh.crystalbiltech.com/bitcoin/orders.php',
                   'title'             => $item_name,
                   //'description'       => 'Apple Iphone 6'
               );

$order = \CoinGate\Merchant\Order::create($post_params,array(  
  'environment' => 'sandbox', // sandbox OR live
  'app_id'      => '840', 
  'api_key'     => 'tXJnb0s428WUDTMBAcQCvZ', 
  'api_secret'  => 'pmoZNFnwjuQcybxI8fPKXOGA73aRqzY6'
));

if ($order) {
   // print_r($order);
    
    $_SESSION['Orderid'] = $order->id;
    
    $order->payment_url;
    $yourURL=$order->payment_url;
   // echo  $yourURL;
    echo ("<script>location.href='$yourURL'</script>");
    exit;
} else {
    echo "Order Is Not Valid";
} 
//}

