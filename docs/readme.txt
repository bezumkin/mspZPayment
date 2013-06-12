--------------------
Z-Payment method for miniShop2
--------------------
Author: Vasiliy Naumkin <bezumkin@yandex.ru>
--------------------


--------------------
From version 1.0.0 pl you can set payments link in users email.
Just use this snippet zpLink:
--------------------
<?php
if (empty($id)) {return false;}

$miniShop2 = $modx->getService('minishop2','miniShop2', MODX_CORE_PATH . 'components/minishop2/model/minishop2/', array());
$miniShop2->loadCustomClasses('payment');

if (!$order = $modx->getObject('msOrder', $id)) {return false;}
if (!$payment = $order->getOne('Payment')) {return false;}
if ($payment->get('class') != 'ZPayment') {return false;}

if (class_exists('ZPayment')) {
	$handler = new ZPayment($order);
	$link = $handler->getPaymentLink($order);
	return $modx->lexicon('ms2_payment_zp_link', array('link' => $link));
}