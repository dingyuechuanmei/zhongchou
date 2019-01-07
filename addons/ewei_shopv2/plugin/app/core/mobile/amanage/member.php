<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'app/core/page_mobile.php';
class Member_EweiShopV2Page extends AppMobilePage
{
    protected $merchid = 0;

    public function __construct()
    {
        global $_GPC;
        $this->merchid = $_GPC['merchid'];
    }

    public function main()
    {

    }

    public function detail()
    {
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);
        $member = m('member')->getMember($id);
        if (empty($member))
        {
            show_json(0, '会员不存在');
        }
        $member['self_ordercount'] = pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where uniacid=:uniacid and openid=:openid and status=3', array(':uniacid' => $_W['uniacid'], ':openid' => $member['openid']));
        $member['self_ordermoney'] = pdo_fetchcolumn('select sum(price) from ' . tablename('ewei_shop_order') . ' where uniacid=:uniacid and openid=:openid and status=3', array(':uniacid' => $_W['uniacid'], ':openid' => $member['openid']));
        $order = pdo_fetch('select finishtime from ' . tablename('ewei_shop_order') . ' where uniacid=:uniacid and openid=:openid and status>=1 and finishtime>0 limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $member['openid']));
        $member['last_ordertime'] = $order['finishtime'];
        $member['last_ordertime'] = ((empty($member['last_ordertime']) ? '无任何交易' : date('Y-m-d H:i:s', $member['last_ordertime'])));
        $groups = m('member')->getGroups();
        $levels = m('member')->getLevels();
        $shop = $_W['shopset']['shop'];
        $level_title = ((empty($shop['levelname']) ? '普通会员' : $shop['levelname']));
        if (!(empty($member['level'])) && $levels)
        {
            foreach ($levels as $level )
            {
                if ($level['id'] == $member['level'])
                {
                    $level_title = $level['levelname'];
                    break;
                }
            }
        }
        $group_title = '未分组';
        if (!(empty($member['groupid'])) && $groups)
        {
            foreach ($groups as $group )
            {
                if ($member['groupid'] == $group['id'])
                {
                    $group_title = $group['groupname'];
                    break;
                }
            }
        }
        array_unshift($levels,array('levelname'=>'普通会员'));
        array_unshift($groups,array('groupname'=>'未分组'));
        $member['createtime'] = date("Y-m-d H:i:s",$member['createtime']);
        show_json(1,array('member'=>$member,'levels'=>$levels,'groups'=>$groups));
    }

    public function detail_post()
    {
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);
        $data = array('level' => intval($_GPC['level']),'mobile'=>trim($_GPC['mobile']), 'groupid' => intval($_GPC['groupid']), 'realname' => trim($_GPC['realname']), 'weixin' => trim($_GPC['weixin']), 'isblack' => intval($_GPC['isblack']), 'content' => trim($_GPC['content']));
        pdo_update('ewei_shop_member', $data, array('id' => $id, 'uniacid' => $_W['uniacid']));
        show_json(1);
    }
}