<?php

/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */

defined('IN_IA') or exit('Access Denied');

define('ALIPAY_GATEWAY', 'https://mapi.alipay.com/gateway.do');
define('TRANSFAR_PREORDER', 'https://openapi.tf56.com/service/api?service_id=tf56pay.cashier.preOrder');

function transfar_build($params, $transfar = array()){
    global $_W;

    $tid = $params['uniontid'];
    $package = array();
    $package['appid'] = $transfar['appid'];
    $package['tf_timestamp'] = date('YmdHis',time());
    $package['service_id'] = 'tf56pay.cashier.preOrder';
    $package['terminal'] = 'H5';
    $package['fronturl'] = $_W['siteroot'] . 'payment/transfar/return.php';
    $package['backurl']  = $_W['siteroot'] . 'payment/transfar/notify.php';
    $package['businessnumber'] = $tid;
    $package['billamount'] = $params['fee'];
    $package['transactionamount'] = $params['fee'];
    $package['subject'] = $params['title'];
    $package['businesstype'] = '网关和代付';
    $package['kind'] = '购物';
    $package['toaccountnumber'] = $transfar['account'];
    $package['body'] = $_W['uniacid'];
    $package['clientip'] = gethostbyname($_SERVER['HTTP_HOST']);

    $package['dog_sk'] = $transfar['dog_sk'];
    $_param = array_keys($package);
    sort($_param);
    $str = '';
    $c = count($_param);
    for ($i = $c -1; $i >= 0; $i--) {
        $str .= $package[$_param[$i]];
    }
    $package['tf_sign'] = strtoupper(md5($str));

    $response = ihttp_post(TRANSFAR_PREORDER, $package);
    $response = json_decode($response['content'],true);
    if (empty($response) || $response['result'] == 'error') {
        $response['data']['payurl'] = "";
    }

    return array('url' => $response['data']['payurl']);
}

function alipay_build($params, $alipay = array()) {
	global $_W;
	$tid = $params['uniontid'];
	$set = array();
	$set['service'] = 'alipay.wap.create.direct.pay.by.user';
	$set['partner'] = $alipay['partner'];
	$set['_input_charset'] = 'utf-8';
	$set['sign_type'] = 'MD5';
	$set['notify_url'] = $_W['siteroot'] . 'payment/alipay/notify.php';
	$set['return_url'] = $_W['siteroot'] . 'payment/alipay/return.php';
	$set['out_trade_no'] = $tid;
	$set['subject'] = $params['title'];
	$set['total_fee'] = $params['fee'];
	$set['seller_id'] = $alipay['account'];
	$set['payment_type'] = 1;
	$set['body'] = $_W['uniacid'];
	$set['app_pay'] = 'Y';
	$prepares = array();
	foreach($set as $key => $value) {
		if($key != 'sign' && $key != 'sign_type') {
			$prepares[] = "{$key}={$value}";
		}
	}
	sort($prepares);
	$string = implode('&', $prepares);
	$string .= $alipay['secret'];
	$set['sign'] = md5($string);

	$response = ihttp_request(ALIPAY_GATEWAY . '?' . http_build_query($set, '', '&'), array(), array('CURLOPT_FOLLOWLOCATION' => 0));
	if (empty($response['headers']['Location'])) {
		exit(iconv('gbk', 'utf-8', $response['content']));
		return;
	}
	return array('url' => $response['headers']['Location']);
}

function wechat_proxy_build($params, $wechat) {
	global $_W;
	$uniacid = !empty($wechat['service']) ? $wechat['service'] : $wechat['borrow'];
	$oauth_account = uni_setting($uniacid, array('payment'));
	if (intval($wechat['switch']) == '2') {
		$_W['uniacid'] = $uniacid;
		$wechat['signkey'] = $oauth_account['payment']['wechat']['signkey'];
		$wechat['mchid'] = $oauth_account['payment']['wechat']['mchid'];
		unset($wechat['sub_mch_id']);
	} else {
		$wechat['signkey'] = $oauth_account['payment']['wechat_facilitator']['signkey'];
		$wechat['mchid'] = $oauth_account['payment']['wechat_facilitator']['mchid'];
	}
	$acid = pdo_getcolumn('uni_account', array('uniacid' => $uniacid), 'default_acid');
	$wechat['appid'] = pdo_getcolumn('account_wechats', array('acid' => $acid), 'key');
	$wechat['version'] = 2;
	return wechat_build($params, $wechat);
}

function wechat_build($params, $wechat) {
	global $_W;
	load()->func('communication');
	if (empty($wechat['version']) && !empty($wechat['signkey'])) {
		$wechat['version'] = 1;
	}
	$wOpt = array();
	if ($wechat['version'] == 1) {
		$wOpt['appId'] = $wechat['appid'];
		$wOpt['timeStamp'] = strval(TIMESTAMP);
		$wOpt['nonceStr'] = random(8);
		$package = array();
		$package['bank_type'] = 'WX';
		$package['body'] = $params['title'];
		$package['attach'] = $_W['uniacid'];
		$package['partner'] = $wechat['partner'];
		$package['out_trade_no'] = $params['uniontid'];
		$package['total_fee'] = $params['fee'] * 100;
		$package['fee_type'] = '1';
		$package['notify_url'] = $_W['siteroot'] . 'payment/wechat/notify.php';
		$package['spbill_create_ip'] = CLIENT_IP;
		$package['time_start'] = date('YmdHis', TIMESTAMP);
		$package['time_expire'] = date('YmdHis', TIMESTAMP + 600);
		$package['input_charset'] = 'UTF-8';
		if (!empty($wechat['sub_mch_id'])) {
			$package['sub_mch_id'] = $wechat['sub_mch_id'];
		}
		ksort($package);
		$string1 = '';
		foreach($package as $key => $v) {
			if (empty($v)) {
				continue;
			}
			$string1 .= "{$key}={$v}&";
		}
		$string1 .= "key={$wechat['key']}";
		$sign = strtoupper(md5($string1));

		$string2 = '';
		foreach($package as $key => $v) {
			$v = urlencode($v);
			$string2 .= "{$key}={$v}&";
		}
		$string2 .= "sign={$sign}";
		$wOpt['package'] = $string2;

		$string = '';
		$keys = array('appId', 'timeStamp', 'nonceStr', 'package', 'appKey');
		sort($keys);
		foreach($keys as $key) {
			$v = $wOpt[$key];
			if($key == 'appKey') {
				$v = $wechat['signkey'];
			}
			$key = strtolower($key);
			$string .= "{$key}={$v}&";
		}
		$string = rtrim($string, '&');
		$wOpt['signType'] = 'SHA1';
		$wOpt['paySign'] = sha1($string);
		return $wOpt;
	} else {
				if (!empty($params['user']) && is_numeric($params['user'])) {
			$params['user'] = mc_uid2openid($params['user']);
		}
		$package = array();
		$package['appid'] = $wechat['appid'];
		$package['mch_id'] = $wechat['mchid'];
		$package['nonce_str'] = random(8);
		$package['body'] = cutstr($params['title'], 26);
		$package['attach'] = $_W['uniacid'];
		$package['out_trade_no'] = $params['uniontid'];
		$package['total_fee'] = $params['fee'] * 100;
		$package['spbill_create_ip'] = CLIENT_IP;
		$package['time_start'] = date('YmdHis', TIMESTAMP);
		$package['time_expire'] = date('YmdHis', TIMESTAMP + 600);
		$package['notify_url'] = $_W['siteroot'] . 'payment/wechat/notify.php';
		$package['trade_type'] = 'JSAPI';
		$package['openid'] = empty($params['user']) ? $_W['fans']['from_user'] : $params['user'];
		if (!empty($wechat['sub_mch_id'])) {
			$package['sub_mch_id'] = $wechat['sub_mch_id'];
		}
		if (!empty($params['sub_user'])) {
			$package['sub_openid'] = $params['sub_user'];
			unset($package['openid']);
		}
		ksort($package, SORT_STRING);
		$string1 = '';
		foreach($package as $key => $v) {
			if (empty($v)) {
				continue;
			}
			$string1 .= "{$key}={$v}&";
		}
		$string1 .= "key={$wechat['signkey']}";
		$package['sign'] = strtoupper(md5($string1));
		$dat = array2xml($package);
		$response = ihttp_request('https://api.mch.weixin.qq.com/pay/unifiedorder', $dat);
		if (is_error($response)) {
			return $response;
		}
		$xml = @isimplexml_load_string($response['content'], 'SimpleXMLElement', LIBXML_NOCDATA);
		if (strval($xml->return_code) == 'FAIL') {
			return error(-1, strval($xml->return_msg));
		}
		if (strval($xml->result_code) == 'FAIL') {
			return error(-1, strval($xml->err_code).': '.strval($xml->err_code_des));
		}
		$prepayid = $xml->prepay_id;
		$wOpt['appId'] = $wechat['appid'];
		$wOpt['timeStamp'] = strval(TIMESTAMP);
		$wOpt['nonceStr'] = random(8);
		$wOpt['package'] = 'prepay_id='.$prepayid;
		$wOpt['signType'] = 'MD5';
		ksort($wOpt, SORT_STRING);
		foreach($wOpt as $key => $v) {
			$string .= "{$key}={$v}&";
		}
		$string .= "key={$wechat['signkey']}";
		$wOpt['paySign'] = strtoupper(md5($string));
		return $wOpt;
	}
}

function payment_proxy_pay_account() {
	global $_W;
	$setting = uni_setting($_W['uniacid'], array('payment'));
	$setting['payment']['wechat']['switch'] = intval($setting['payment']['wechat']['switch']);
	
	if ($setting['payment']['wechat']['switch'] == PAYMENT_WECHAT_TYPE_SERVICE) {
		$uniacid = intval($setting['payment']['wechat']['service']);
	} elseif ($setting['payment']['wechat']['switch'] == PAYMENT_WECHAT_TYPE_BORROW) {
		$uniacid = intval($setting['payment']['wechat']['borrow']);
	} else {
		$uniacid = 0;
	}
	$pay_account = uni_fetch($uniacid);
	if (empty($uniacid) || empty($pay_account)) {
		return error(1);
	}
	return WeAccount::create($pay_account);
}