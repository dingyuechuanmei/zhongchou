<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'app/core/page_mobile.php';
class Index_EweiShopV2Page extends AppMobilePage
{
 	public function main(){
 		global $_W,$_GPC;

 		$id = intval($_GPC['id']);

 		$diypage = $_W['shopset']['diypage'];
    	
    	if(!empty($id)){
    		$page = p('diypage')->getPage($id, true);
    	}else{
	    	if(!empty($diypage['page']['home'])){
	        	$page = p('diypage')->getPage($diypage['page']['home'], true);
	    	}else{
	        	$page = array();
	    	}
    	}

    	if(!empty($page)){
    		$page['menus'] = array();

    		if(intval($page['diymenu']) == 0){
                $page['menus'] = $this->model->getDefaultMenus();
            }

    		if($page['diymenu'] > 0){
    			$page_menu = pdo_fetch("select * from ".tablename("ewei_shop_diypage_menu")." where uniacid =:uniacid and id =:id ",array(':uniacid'=>$_W['uniacid'],':id'=>$page['diymenu']));
            
	            if(!empty($page_menu['data'])){
	                $page_menu['data'] = base64_decode($page_menu['data']);
	                $page['menus'] = json_decode($page_menu['data'], true);
	            }
    		}
    	}

 		$result = array(
 			'page' => $page,
            'diypage' => $diypage,
            'shopset' => $_W['shopset'],
            'siteurl' => SITE_URL,
            'menus' => $this->model->getDiypageMenus(),
            'openid' => $_W['openid'],
            'cartcount' => m('goods')->getCartCount()
 		);

 		app_json($result);
 	}
}