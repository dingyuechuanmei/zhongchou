<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'app/core/page_mobile.php';
class Login_EweiShopV2Page extends AppMobilePage
{
    public function main()
    {
        global $_W;
        global $_GPC;
        $username = trim($_GPC['username']);
        $password = trim($_GPC['password']);
        if (empty($username))
        {
            app_error(1, '请填写用户名');
        }
        if (empty($password))
        {
            app_error(1, '请填写密码');
        }
        $account = pdo_get("ewei_shop_merch_account",array('uniacid'=>$_W['uniacid'],'username'=>$username));
        if (empty($account))
        {
            app_error(1, '用户不存在');
        }
        if ($account['pwd'] != md5($password . $account['salt']))
        {
            app_error(1, '用户名或密码错误');
        }
        app_json($account);
    }
}