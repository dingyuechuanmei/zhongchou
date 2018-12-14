<?php
if (!(defined('IN_IA'))) {
	exit('Access Denied');
}

class RaiseModel extends PluginModel
{
    public function getTotals() 
	{
		global $_W;
		return array(
		    'total0' => pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_raise_apply') . ' where status=0 and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'])), 
		    'total1' => pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_raise_apply') . ' where status=1 and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'])), 
		    'total2' => pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_raise_apply') . ' where status=2 and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'])),
		    'total3' => pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_raise_apply') . ' where status=3 and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']))
	    );
	}
}
?>