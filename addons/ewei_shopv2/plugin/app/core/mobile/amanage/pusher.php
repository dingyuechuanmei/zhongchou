<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'app/core/page_mobile.php';
class Pusher_EweiShopV2Page extends AppMobilePage
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
    public function getlist()
    {
        global $_W;
        global $_GPC;


        $pindex = max(1, intval($_GPC['page']));
        $psize = 10;
        $list = array();

        $condition = ' WHERE g.`uniacid` = :uniacid and g.`merchid`=:merchid ';
        $params = array(':uniacid' => $_W['uniacid'],':merchid'=>$this->merchid);

        $keywords = trim($_GPC['keywords']);
        if ($keywords)
        {
            $condition .= ' AND (`title` LIKE :keywords OR `content` LIKE :keywords)';
            $params[':keywords'] = '%' . $keywords . '%';
        }

        $sql = 'SELECT count(g.id) FROM ' . tablename('ewei_shop_raise_pusher') . 'g' . $condition;
        $total = pdo_fetchcolumn($sql, $params);
        if (0 < $total)
        {
            $sql = 'SELECT g.* FROM ' . tablename('ewei_shop_raise_pusher') . 'g' . $condition . ' ORDER BY g.`like_count` DESC , g.`id` DESC LIMIT ' . (($pindex - 1) * $psize) . ',' . $psize;
            $list = pdo_fetchall($sql, $params);
        }

        foreach ($list as &$value) {
            $value['member'] = pdo_get("ewei_shop_member",array('openid'=>$value['pusher']),array('nickname','avatar'));
            $value['ifshowval'] = $value['ifshow'] == 1 ? '显示' : '隐藏';
        }
        $pageCount = ceil($total/$psize);
        show_json(1, array('total' => $total, 'list' => $list,'pageCount'=>$pageCount ));
    }
}