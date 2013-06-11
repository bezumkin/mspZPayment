<?php
/**
 * Loads system settings into build
 *
 * @package mspzpayment
 * @subpackage build
 */
$settings = array();

$tmp = array(
	'url' => array(
		'xtype' => 'textfield'
		,'value' => 'https://z-payment.com/merchant.php'
	)
	,'shopid' => array(
		'xtype' => 'textfield'
		,'value' => '0000'
	)
	,'key' => array(
		'xtype' => 'text-password'
		,'value' => 'mypassword'

	)
	,'sign' => array(
		'xtype' => 'text-password'
		,'value' => ''

	)
	,'success_id' => array(
		'xtype' => 'numberfield'
		,'value' => 0

	)
	,'failure_id' => array(
		'xtype' => 'numberfield'
		,'value' => 0

	)

);

foreach ($tmp as $k => $v) {
	/* @var modSystemSetting $setting */
	$setting = $modx->newObject('modSystemSetting');
	$setting->fromArray(array_merge(
		array(
			'key' => 'ms2_payment_zp_'.$k
			,'namespace' => 'minishop2'
			,'area' => 'ms2_payment'
		), $v
	),'',true,true);

	$settings[] = $setting;
}

unset($tmp);
return $settings;