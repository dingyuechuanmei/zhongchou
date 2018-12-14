<?php
 
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class Index_EweiShopV2Page extends PluginWebPage
{
	public function main()
	{
		global $_W;
		include $this->template();
	}

	public function diypage(){
		message("",webUrl('diypage',array('dwt'=>'wxapp')));
	}
}

?>
