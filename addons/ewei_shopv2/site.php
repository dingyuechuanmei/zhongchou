<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
require_once IA_ROOT . '/addons/ewei_shopv2/version.php';
require_once IA_ROOT . '/addons/ewei_shopv2/defines.php';
require_once EWEI_SHOPV2_INC . 'functions.php';
class Ewei_shopv2ModuleSite extends WeModuleSite {
    public function getMenus() {
        global $_W;
        return array(array('title' => '管理后台', 'icon' => 'fa fa-shopping-cart', 'url' => webUrl()));
    }
    public function doWebWeb() {
        m('route')->run();
    }
    public function doMobileMobile() {
        global $_W,$_GPC;
        $mid = intval($_GPC['mid']);
        $openid = $_W['openid'];
        if(empty($openid)){
        	$openid = m('user')->getOpenid();
        }
        if(!empty($mid) && !empty($openid)){
        	$member = m('member')->getMember($openid);
        	if(!empty($member)){
        		if($mid != $member['id']){
	        		$share_log = pdo_fetch("SELECT id,sid FROM ".tablename('ewei_shop_share_log')." WHERE uniacid=:uniacid AND mid=:mid limit 1",array(':uniacid'=>$_W['uniacid'],':mid'=>$member['id']));
        			$data = array(
        		        'uniacid'=>$_W['uniacid'],
        		        'mid'=>$member['id'],
        		        'sid'=>$mid,
        		        'share_type'=>1,
        		        'createtime'=>time(),
        		        'action'=>$_W['action'],
        		        'controller'=>$_W['controller']
        		    );
    		    	$data['share_status'] = empty($share_log) ? 1: 0;
        			$result = pdo_fetch('select id from '.tablename('ewei_shop_order').' where uniacid=:uniacid and openid=:openid and status>=3 limit 1',array(':uniacid'=>$_W['uniacid'],':openid'=>$openid));
        			if($result){
        				$data['share_status'] = 2;
        			}
        			$share_log = pdo_fetch("SELECT id,sid FROM ".tablename('ewei_shop_share_log')." WHERE uniacid=:uniacid AND mid=:mid AND sid=:sid limit 1",array(':uniacid'=>$_W['uniacid'],':mid'=>$member['id'],':sid'=>$mid));
        			if(empty($share_log)){
        				pdo_insert('ewei_shop_share_log',$data);
        			}
	        	}
        	}
        }
        m('route')->run(false);
    }
    public function payResult($params) {
        return m('order')->payResult($params);
    }
}
?>