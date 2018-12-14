<?php
if (!(defined('IN_IA'))) 
{
	exit('Access Denied');
}
require EWEI_SHOPV2_PLUGIN . 'amanage/core/inc/page_amanage.php';
class Index_EweiShopV2Page extends AmanageMobilePage 
{
	public function main() 
	{
		global $_W;
		global $_GPC;

		$shopset = pdo_get("ewei_shop_merch_user",array('id'=>$_W['merchid']));
		if ($_W['ispost']) 
		{
			$shopname = trim($_GPC['shopname']);
			$shoplogo = trim($_GPC['shoplogo']);
			$shopdesc = trim($_GPC['shopdesc']);
			$shopclose = intval($_GPC['shopclose']);

			if (empty($shopname)) 
			{
				show_json(0, '请填写商城名称');
			}

			$data = array();
			$data['merchname'] = $shopname;
			$data['desc'] = $shopdesc;
			$data['status'] = $shopclose;
			$data['logo'] = $shoplogo;

			pdo_update("ewei_shop_merch_user",$data,array('id'=>$_W['merchid']));

			plog('merch.shop.edit', '修改系统设置-商城设置');
			show_json(1);
		}
		include $this->template();
	}
}
?>