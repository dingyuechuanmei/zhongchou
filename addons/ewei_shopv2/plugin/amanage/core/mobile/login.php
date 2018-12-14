<?php
if (!(defined('IN_IA'))) 
{
	exit('Access Denied');
}
require EWEI_SHOPV2_PLUGIN . 'amanage/core/inc/page_amanage.php';
class Login_EweiShopV2Page extends AmanageMobilePage 
{
	public function main() 
	{
		global $_W;
		global $_GPC;
		$check = $this->isLogin();
		if ($check) 
		{
			header('location: ' . mobileUrl('amanage'));
		}
		$backurl = trim($_GPC['backurl']);

		if ($_W['ispost']) 
		{
			$type = trim($_GPC['type']);
			if (!(empty($backurl))) 
			{
				$backurl = base64_decode(urldecode($backurl));
				$backurl = './index.php?' . $backurl;
			}

			if ($type == 'wechat') 
			{
				if (empty($_W['openid'])) 
				{
					show_json(0, '未获取到当前用户信息，请刷新重试');
				}

				$roleuser = pdo_fetch('SELECT id, uid, username, status FROM' . tablename('ewei_shop_merch_user') . 'WHERE openid=:openid AND uniacid=:uniacid LIMIT 1', array(':openid' => $_W['openid'], ':uniacid' => $_W['uniacid']));
				if (empty($roleuser)) 
				{
					show_json(0, '当前用户不存在');
				}
				if (empty($roleuser['status'])) 
				{
					show_json(0, '此用户暂时无法登录管理后台');
				}
				$account = pdo_get("ewei_shop_merch_account",array('id'=>$roleuser['accountid']));
				if (!($account)) 
				{
					show_json(0, '当前账号不存在');
				}
				if (empty($account['status'])) 
				{
					show_json(0, '此账号暂时无法登录管理后台');
				}
				$account['hash'] = md5($account['pwd'] . $account['salt']);
				$session = base64_encode(json_encode($account));
				$session_key = '__amanage_' . $_W['uniacid'] . '_session';
				isetcookie($session_key, $session, 7200);
				show_json(1, array('backurl' => $backurl));
			}
			else 
			{
				$username = trim($_GPC['username']);
				$password = trim($_GPC['password']);
				if (empty($username)) 
				{
					show_json(0, '请填写用户名');
				}
				if (empty($password)) 
				{
					show_json(0, '请填写密码');
				}
				$account = pdo_get("ewei_shop_merch_account",array('uniacid'=>$_W['uniacid'],'username'=>$username));
				if (empty($account)) 
				{
					show_json(0, '用户不存在');
				}
				if ($account['pwd'] != md5($password . $account['salt'])) 
				{
					show_json(0, '用户名或密码错误');
				}
				$account['hash'] = md5($account['pwd'] . $account['salt']);
				$session = base64_encode(json_encode($account));
				$session_key = '__amanage_' . $_W['uniacid'] . '_session';
				isetcookie($session_key, $session, 7200);
				show_json(1, array('backurl' => $backurl));
			}
		}
		$shopset = $_W['shopset'];
		$logo = tomedia($shopset['shop']['logo']);
		if (is_weixin() || (!(empty($shopset['wap']['open'])) && empty($shopset['wap']['inh5app']))) 
		{
			$goshop = true;
		}
		include $this->template();
	}
	public function logout() 
	{
		global $_W;
		global $_GPC;
		$session_key = '__amanage_' . $_W['uniacid'] . '_session';
		isetcookie($session_key, false, -100);
		unset($GLOBALS['_W']['amanage']);
		if ($_W['isajax']) 
		{
			show_json(1);
		}
		else 
		{
			header('location: ' . mobileUrl('amanage/login'));
		}
	}
}
?>