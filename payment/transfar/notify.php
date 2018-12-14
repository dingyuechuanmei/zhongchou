<?php
error_reporting(0);
define('IN_MOBILE', true);

require '../../framework/bootstrap.inc.php';
if(!empty($_POST)) {
	$out_trade_no = $_POST['businessnumber'];
	
	load()->web('common');
	load()->classs('coupon');

	$_POST['query_type'] = 'notify';
	
	WeUtility::logging('pay-transfar', var_export($_POST, true));
	
    $sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniontid`=:uniontid';
    $params = array();
    $params[':uniontid'] = $out_trade_no;
    $log = pdo_fetch($sql, $params);
	
	if(!empty($log) && $log['status'] == '0' && ($_POST['billamount'] == $log['card_fee'])) {
		$log['transaction_id'] = $_POST['transactionnumber'];
		$record = array();
		$record['status'] = '1';
		pdo_update('core_paylog', $record, array('plid' => $log['plid']));
		
		if ($log['is_usecard'] == 1 && !empty($log['encrypt_code'])) {
			$coupon_info = pdo_get('coupon', array('id' => $log['card_id']), array('id'));
			$coupon_record = pdo_get('coupon_record', array('code' => $log['encrypt_code'], 'status' => '1'));
			load()->model('activity');
		 	$status = activity_coupon_use($coupon_info['id'], $coupon_record['id'], $log['module']);
		}
		
		$site = WeUtility::createModuleSite($log['module']);
		if(!is_error($site)) {
			$method = 'payResult';
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
				
				//$site->$method($ret);
				
				if(!empty($log['module'])){
				    load()->func('communication');
				    $url = "http://".$_SERVER['HTTP_HOST']."/app/index.php?i=".$log['uniacid']."&c=entry&do=payResult&m=".$log['module']."&wxref=mp.weixin.qq.com#wechat_redirect";
				    ihttp_post($url, $ret);
				}
				
				$result = array(
				    'result' => 'success',
				    'msg' => '请求成功'
				);
				
				echo json_encode($result);
			}
		}
	}
}
exit('fail');
