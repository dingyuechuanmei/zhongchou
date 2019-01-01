<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'app/core/page_mobile.php';
class Set_EweiShopV2Page extends AppMobilePage
{
    public function main()
    {
        global $_W;
        global $_GPC;


        $info = pdo_fetch('SELECT * FROM' . tablename('ewei_shop_merch_user') . 'WHERE id=:merchid AND uniacid=:uniacid', array(':merchid' => $_GPC['merchid'], ':uniacid' => $_W['uniacid']));
        $info['uname'] = pdo_fetchcolumn('SELECT username FROM' . tablename('ewei_shop_merch_account') . 'WHERE merchid=:merchid AND uniacid=:uniacid', array(':merchid' => $_GPC['merchid'], ':uniacid' => $_W['uniacid']));
        if (!(empty($info['openid'])))
        {
            $member = m('member')->getMember($info['openid']);
            $info['bindrole'] = true;
            $info['member_avatar'] = tomedia($member['avatar']);
            $info['member_nickname'] = $member['nickname'];
        }
        if (empty($member['avatar'])) {
            $member['avatar'] = tomedia($info['logo']);
        }

        show_json(0,array('info'=>$info));
    }

    public function saveInfo()
    {
        global $_W;
        global $_GPC;
        $info = pdo_fetch('SELECT * FROM' . tablename('ewei_shop_merch_user') . 'WHERE id=:merchid AND uniacid=:uniacid', array(':merchid' => $_GPC['merchid'], ':uniacid' => $_W['uniacid']));
        $realname = trim($_GPC['realname']);
        $mobile = trim($_GPC['mobile']);
        $password = trim($_GPC['password']);
        $password2 = trim($_GPC['password2']);
        if (empty($realname))
        {
            show_json(0,'请输入真实姓名');
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

        $account = pdo_get("ewei_shop_merch_account",array('id'=>$info['accountid']));
        if ($changepass)
        {
            $changepassresult = pdo_update("ewei_shop_merch_account",array('pwd'=>md5($password.$account['salt'])),array('id'=>$account['id']));
            $data['upass'] = md5($password.$account['salt']);
        }

        $data = array('realname' => $realname, 'mobile' => $mobile);
        pdo_update('ewei_shop_merch_user', $data, array('id' => $info['id'], 'uniacid' => $_W['uniacid']));

        show_json(1, array('changepass' => intval($changepassresult)));
    }
}