<?php
if (!(defined('IN_IA'))) 
{
	exit('Access Denied');
}
class AmanageMobilePage extends PluginMobilePage 
{
	public function __construct($_com = '', $_init = false) 
	{
		global $_W;
		global $_GPC;
		if (empty($_GPC['i'])) 
		{
			$this->message('公众号参数错误');
		}
		$GLOBALS['_W']['uniacid'] = intval($_GPC['i']);
		parent::__construct(false);
		$this->set = m('common')->getPluginset('amanage');
		if (empty($this->set['open'])) 
		{
			$this->message('暂未开放', mobileUrl());
		}
		$this->checkLogin();
		$this->setShare();
	}
	protected function checkLogin() 
	{
		global $_W;
		global $_GPC;
		if ($_W['controller'] != 'login') 
		{
			
			load()->model('user');
			$islogin = $this->isLogin();
			
			if ($islogin) 
			{
				$GLOBALS['_W']['amanage'] = $islogin;
				$GLOBALS['_W']['role'] = "manager";
				$GLOBALS['_W']['uid'] = $islogin['uid'];
				return;
			}

			unset($GLOBALS['_W']['amanage']);
			$session_key = '__amanage_' . $_W['uniacid'] . '_session';
			isetcookie($session_key, false, -100);
			$backurl = urlencode(base64_encode($_SERVER['QUERY_STRING']));
			header('location: ' . mobileUrl('amanage/login', array('backurl' => $backurl)));
			exit();
		}
	}
	protected function isLogin() 
	{
		global $_W;
		global $_GPC;

		$session_key = '__amanage_' . $_W['uniacid'] . '_session';
		$session = json_decode(base64_decode($_GPC[$session_key]), true);
		if (is_array($session)) 
		{
			$account = pdo_get("ewei_shop_merch_account",array('username'=>$session['username']));
			if (is_array($account) && ($session['hash'] == md5($account['pwd'] . $account['salt']))) 
			{
				$_W['merchid'] = $session['merchid'];
				return $account;
			}
		}
		return false;
	}
	protected function setShare() 
	{
		global $_W;
		$shopset = $_W['shopset']['shop'];
		$set = $this->set;
		$GLOBALS['_W']['shopshare'] = array('title' => (!(empty($set['title'])) ? $set['title'] : $shopset['name'] . '管理后台'), 'imgUrl' => (!(empty($set['thumb'])) ? tomedia($set['thumb']) : tomedia($shopset['logo'])), 'desc' => (!(empty($set['desc'])) ? $set['desc'] : $shopset['description']), 'link' => mobileUrl('amanage', array(), true));
	}
}
?>