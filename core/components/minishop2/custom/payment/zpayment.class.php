<?php

if (!class_exists('msPaymentInterface')) {
	require_once dirname(dirname(dirname(__FILE__))) . '/model/minishop2/mspaymenthandler.class.php';
}

class ZPayment extends msPaymentHandler implements msPaymentInterface {
	public $config;

	function __construct(xPDOObject $object, $config = array()) {
		$this->modx = & $object->xpdo;

		$siteUrl = $this->modx->getOption('site_url');
		$assetsUrl = $this->modx->getOption('minishop2.assets_url', $config, $this->modx->getOption('assets_url').'components/minishop2/');
		$paymentUrl = $siteUrl . substr($assetsUrl, 1) . 'payment/zpayment.php';

		$this->config = array_merge(array(
			'paymentUrl' => $paymentUrl
			,'checkoutUrl' => $this->modx->getOption('ms2_payment_zp_url', null, 'https://z-payment.com/merchant.php', true)
			,'shopId' => $this->modx->getOption('ms2_payment_zp_shopid')
			,'shopKey' => $this->modx->getOption('ms2_payment_zp_key')
			,'shopSign' => $this->modx->getOption('ms2_payment_zp_sign')
			,'json_response' => false
		), $config);
	}


	/* @inheritdoc} */
	public function send(msOrder $order) {
		$link = $this->getPaymentLink($order);

		return $this->success('', array('redirect' => $link));
	}


	public function getPaymentLink(msOrder $order) {
		$id = $order->get('id');
		$sum = number_format($order->get('cost'), 2, '.', '');
		$request = array(
			'LMI_PAYMENT_NO' => $id
			,'LMI_PAYMENT_AMOUNT' => $sum
			,'CLIENT_MAIL' => $order->getOne('UserProfile')->get('email')
			,'LMI_PAYMENT_DESC' => 'Payment #'.$id
			,'LMI_PAYEE_PURSE' => $this->config['shopId']
			,'ZP_CMS' => 'MODXMS2'
			,'ZP_DEVELOPER' => 'ZP97337015'
		);
		if (!empty($this->config['shopSign'])) {
			$request['ZP_SIGN'] = md5($this->config['shopId'] . $id . $sum . $this->config['shopSign']);
		}
		$link = $this->config['checkoutUrl'] .'?'. http_build_query($request);

		return $link;
	}


	/* @inheritdoc} */
	public function receive(msOrder $order, $params = array()) {
		$hash = md5(
			$params['LMI_PAYEE_PURSE']
			.$params['LMI_PAYMENT_AMOUNT']
			.$params['LMI_PAYMENT_NO']
			.$params['LMI_MODE']
			.$params['LMI_SYS_INVS_NO']
			.$params['LMI_SYS_TRANS_NO']
			.$params['LMI_SYS_TRANS_DATE']
			.$this->config['shopKey']
			.$params['LMI_PAYER_PURSE']
			.$params['LMI_PAYER_WM']
		);

		if ($params['LMI_HASH'] == strtoupper($hash)) {
			/* @var miniShop2 $miniShop2 */
			$miniShop2 = $this->modx->getService('miniShop2');
			@$this->modx->context->key = 'mgr';
			$miniShop2->changeOrderStatus($order->get('id'), 2);
			exit('OK');
		}
		else {
			$this->paymentError('Wrong HASH', $params);
		}
	}


	public function paymentError($text, $request = array()) {
		$this->modx->log(modX::LOG_LEVEL_ERROR,'[miniShop2:ZPayment] ' . $text . ', request: '.print_r($request,1));
		header("HTTP/1.0 400 Bad Request");

		die('ERR: ' . $text);
	}
}