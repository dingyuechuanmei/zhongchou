<?php
error_reporting(0);
define('IN_MOBILE', true);
require '../../framework/bootstrap.inc.php';

if (empty($_GET['businessnumber'])) {
	exit('request failed.');
}

load()->app('common');
load()->app('template');

$_GET['query_type'] = 'return';
WeUtility::logging('pay-transfar', var_export($_GET, true));

$sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniontid`=:uniontid';
$params = array();
$params[':uniontid'] = $_GET['businessnumber'];
$log = pdo_fetch($sql, $params);

if($_GET['status'] == '成功') {
    if (!empty($log)) {
        
		$site = WeUtility::createModuleSite($log['module']);
		$method = 'payResult';
		
		if ($log['status'] == 0 && ($_GET['billamount'] == $log['card_fee'])) {
			$log['transaction_id'] = $_GET['transactionnumber'];
			$record = array();
			$record['status'] = '1';
			pdo_update('core_paylog', $record, array('plid' => $log['plid']));
		    
		    if ($log['is_usecard'] == 1 && !empty($log['encrypt_code'])) {
				$coupon_info = pdo_get('coupon', array('id' => $log['card_id']), array('id'));
				$coupon_record = pdo_get('coupon_record', array('code' => $log['encrypt_code'], 'status' => '1'));
				load()->model('activity');
				$status = activity_coupon_use($coupon_info['id'], $coupon_record['id'], $log['module']);
			}
			
			if (!is_error($site)) {
				$site->weid = $_W['weid'];
				$site->uniacid = $_W['uniacid'];
				$site->inMobile = true;
				if (method_exists($site, $method)) {
					$ret = array();
					$ret['weid'] = $log['weid'];
					$ret['uniacid'] = $log['uniacid'];
					$ret['result'] = 'success';
					$ret['type'] = $log['type'];
					$ret['from'] = 'notify';
					$ret['tid'] = $log['tid'];
					$ret['uniontid'] = $log['uniontid'];
					$ret['transaction_id'] = $log['transaction_id'];
					$ret['user'] = $log['openid'];
					$ret['fee'] = $log['fee'];
					$ret['is_usecard'] = $log['is_usecard'];
					$ret['card_type'] = $log['card_type'];
					$ret['card_fee'] = $log['card_fee'];
					$ret['card_id'] = $log['card_id'];
					// $site->$method($ret);
					
					if(!empty($log['module'])){
					    load()->func('communication');
					    $url = "http://".$_SERVER['HTTP_HOST']."/app/index.php?i=".$log['uniacid']."&c=entry&do=payResult&m=".$log['module']."&wxref=mp.weixin.qq.com#wechat_redirect";
					    ihttp_post($url, $ret);
					}
				}
			}
		}
		
	    if(!is_error($site)){
			$ret['tid'] = $log['tid'];
			$ret['fee'] = $log['fee'];
			$ret['result'] = 'success';
			$ret['from'] = 'return';
			
			// $site->$method($ret);
			
			$string = '';
			foreach($ret as $key => $v) {
			    if (empty($v)) {
			        continue;
			    }
			    $string .= "{$key}={$v}&";
			}
				
			header("Location:"."http://".$_SERVER['HTTP_HOST']."/app/index.php?i=".$log['uniacid']."&c=entry&do=payResult&m=".$log['module']."&".$string."wxref=mp.weixin.qq.com#wechat_redirect");
			exit;
		}
	}
} else {
	message('支付异常，请返回微信客户端查看订单状态或是联系管理员', '', 'error');
}