<?php

$order = $_SESSION['order_id_liqpay'];
$public_key = "sandbox_i45783735016";
$private_key = "sandbox_OYmy65Yim0vx0oPidLmMbpem3LnfVi0w8XzsTjI1";
//$liqpay = new LiqPay($public_key, $private_key);
//$res = $liqpay->api("request", array(
//    'action'        => 'status',
//    'version'       => '3',
//    'order_id'      => $order
//));
ob_start();
echo '<div class="test1"><pre>';
var_export($_POST);
echo '</pre></div>';
$body = ob_get_clean();
wp_mail('kuzmin.gts@gmail.com', 'from checkout', 'Какое-то сообщение checkout . body = '.$body);
file_put_contents('/checkout.txt',$body);