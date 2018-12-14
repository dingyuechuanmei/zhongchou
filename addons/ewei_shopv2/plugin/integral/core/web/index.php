<?php
if (!(defined('IN_IA'))) 
{
	exit('Access Denied');
}
class Index_EweiShopV2Page extends PluginWebPage 
{
	public function main() 
	{
		global $_W;
		if (cv('integral.statistics'))
		{
			header('location: ' . webUrl('integral/statistics'));
			exit();
			return;
		}
		if (cv('integral.cover'))
		{
			header('location: ' . webUrl('integral/cover'));
			exit();
			return;
		}
		if (cv('integral.set'))
		{
			header('location: ' . webUrl('integral/set'));
			exit();
		}
	}
	public function notice() 
	{
		global $_W;
		global $_GPC;
		if ($_W['ispost']) 
		{
			$data = ((is_array($_GPC['data']) ? $_GPC['data'] : array()));
			m('common')->updatePluginset(array( 'commission' => array('tm' => $data) ));
			plog('commission.notice.edit', '修改通知设置');
			show_json(1);
		}
		$data = m('common')->getPluginset('commission');
		$template_list = pdo_fetchall('SELECT id,title FROM ' . tablename('ewei_shop_member_message_template') . ' WHERE uniacid=:uniacid and typecode=:typecode ', array(':uniacid' => $_W['uniacid'], ':typecode' => 'commission'));
		include $this->template();
	}
	public function set() 
	{
		global $_W;
		global $_GPC;
		if ($_W['ispost']) 
		{
			$data = ((is_array($_GPC['data']) ? $_GPC['data'] : array()));
			m('common')->updatePluginset(array('integral' => $data));
			m('cache')->set('template_' . $this->pluginname, $data['style']);
			show_json(1, array('url' => webUrl('integral/set')));
		}
		$styles = array();
		$dir = IA_ROOT . '/addons/ewei_shopv2/plugin/' . $this->pluginname . '/template/mobile/';
		$handle = opendir($dir);
		if ($handle)
		{
			while (($file = readdir($handle)) !== false) 
			{
				if (($file != '..') && ($file != '.')) 
				{
					if (is_dir($dir . '/' . $file))
					{
						$styles[] = $file;
					}
				}
			}
			closedir($handle);
		}
		$data = m('common')->getPluginset('integral');
		include $this->template();
	}
}
?>