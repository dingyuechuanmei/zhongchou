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


		$roleuser = pdo_fetch('SELECT * FROM' . tablename('ewei_shop_merch_user') . 'WHERE id=:merchid AND uniacid=:uniacid', array(':merchid' => $_W['merchid'], ':uniacid' => $_W['uniacid']));

		if (!(empty($roleuser['openid'])))
		{
			$member = m('member')->getMember($roleuser['openid']);
			//if ($_W['openid'] == $roleuser['openid'])
			//{
				$member['bindrole'] = true;
			//}
		}
        if (empty($member['avatar'])) {
		    $member['avatar'] = tomedia($roleuser['logo']);
        }
		if ($_W['ispost']) 
		{
			$realname = trim($_GPC['realname']);
			$mobile = trim($_GPC['mobile']);
			$password = trim($_GPC['password']);
			$password2 = trim($_GPC['password2']);
			if (empty($realname)) 
			{
				show_json(0, '请输入真实姓名');
			}
			if (empty($realname)) 
			{
				show_json(0, '请输入手机号');
			}
			if (!(empty($password)) || !(empty($password2))) 
			{
				if (empty($password)) 
				{
					show_json(0, '请输入密码');
				}
				if (empty($password2)) 
				{
					show_json(0, '请重复输入密码');
				}
				if ($password != $password2) 
				{
					show_json(0, '两次输入的密码不一致');
				}
				$changepass = true;
			}

			$account = pdo_get("ewei_shop_merch_account",array('id'=>$roleuser['accountid']));
			if ($changepass) 
			{
				$changepassresult = pdo_update("ewei_shop_merch_account",array('pwd'=>md5($password.$account['salt'])),array('id'=>$account['id']));
				$data['upass'] = md5($password.$account['salt']);
			}

			$data = array('realname' => $realname, 'mobile' => $mobile);
			pdo_update('ewei_shop_merch_user', $data, array('id' => $roleuser['id'], 'uniacid' => $_W['uniacid']));

			show_json(1, array('changepass' => intval($changepassresult)));
		}
		include $this->template();
	}
}
?>