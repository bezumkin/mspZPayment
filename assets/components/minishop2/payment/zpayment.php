<?php
define('MODX_API_MODE', true);
require dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/index.php';

$modx->getService('error','error.modError');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('FILE');

/* @var miniShop2 $miniShop2 */
$miniShop2 = $modx->getService('minishop2','miniShop2',$modx->getOption('minishop2.core_path',null,$modx->getOption('core_path').'components/minishop2/').'model/minishop2/', array());
$miniShop2->loadCustomClasses('payment');

if (!class_exists('ZPayment')) {exit('Error: could not load payment class "ZPayment".');}
$context = '';
$params = array();

/* @var msPaymentInterface|ZPayment $handler */
$handler = new ZPayment($modx->newObject('msOrder'));

if (!empty($_REQUEST['LMI_PAYEE_PURSE']) && !empty($_REQUEST['LMI_PAYMENT_NO']) && empty($_REQUEST['action'])) {
	if ($_REQUEST['LMI_PAYEE_PURSE'] != $handler->config['shopId']) {
		$handler->paymentError('Invalid shop Id '.$_REQUEST['LMI_PAYEE_PURSE'], $_REQUEST);
	}
	else if ($order = $modx->getObject('msOrder', $_REQUEST['LMI_PAYMENT_NO'])) {
		if (!empty($_REQUEST['LMI_PREREQUEST'])) {
			exit('YES');
		}
		else {
			$handler->receive($order, $_REQUEST);
		}
	}
	else {
		$modx->log(modX::LOG_LEVEL_ERROR, '[miniShop2:ZPayment] Could not retrieve order with id '.$_REQUEST['LMI_PAYMENT_NO']);
	}
}

if (!empty($_REQUEST['LMI_PAYMENT_NO'])) {$params['msorder'] = $_REQUEST['LMI_PAYMENT_NO'];}

$success = $failure = $modx->getOption('site_url');
if ($id = $modx->getOption('ms2_payment_zp_success_id', null, 0)) {
	$success = $modx->makeUrl($id, $context, $params, 'full');
}
if ($id = $modx->getOption('ms2_payment_zp_failure_id', null, 0)) {
	$failure = $modx->makeUrl($id, $context, $params, 'full');
}

$redirect = !empty($_REQUEST['action']) && $_REQUEST['action'] == 'success' ? $success : $failure;
header('Location: ' . $redirect);