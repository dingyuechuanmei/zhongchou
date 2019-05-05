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

    public function weixin_login()
    {
        global $_W;
        global $_GPC;
        if (empty($_GPC['openid']))
        {
            app_error(1, '未获取到当前用户信息，请刷新重试');
        }
        $roleuser = pdo_fetch('SELECT id,status,accountid FROM' . tablename('ewei_shop_merch_user') . 'WHERE openid=:openid AND uniacid=:uniacid LIMIT 1', array(':openid' => $_GPC['openid'], ':uniacid' => $_W['uniacid']));
        if (empty($roleuser))
        {
            app_error(1, '当前用户不存在');
        }
        if (empty($roleuser['status']))
        {
            app_error(1, '此用户暂时无法登录管理后台');
        }
        $account = pdo_get("ewei_shop_merch_account",array('id'=>$roleuser['accountid']));
        if (!($account))
        {
            app_error(1, '当前账号不存在');
        }
        if (empty($account['status']))
        {
            app_error(1, '此账号暂时无法登录管理后台');
        }
        app_json($account);
    }

    /**
     * 客服电话
     */
    public function getTel()
    {
        global $_W;
        $tel = '';
        $raise_set = m('common')->getSysset('lexin');
        $tel = $raise_set['customer_service_number'];       //客服电话
        show_json(0,array('tel'=>$tel));
    }
}