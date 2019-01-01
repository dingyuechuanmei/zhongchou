<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'app/core/page_mobile.php';
class Goods_EweiShopV2Page extends AppMobilePage
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
        $condition = ' WHERE g.`uniacid` = :uniacid and g.`merchid`=:merchid and type!=10 ';
        $params = array(':uniacid' => $_W['uniacid'],':merchid'=>$this->merchid);
        $goodsfrom = strtolower(trim($_GPC['status']));
        empty($goodsfrom) && ($_GPC['status'] = $goodsfrom = 'sale');
        if ($goodsfrom == 'sale')
        {
            $condition .= ' AND g.`status` > 0 and g.`checked`=0 and g.`total`>0 and g.`deleted`=0';
        }
        else if ($goodsfrom == 'out')
        {
            $condition .= ' AND g.`status` > 0 and g.`total` <= 0 and g.`deleted`=0';
        }
        else if ($goodsfrom == 'stock')
        {
            $condition .= ' AND (g.`status` = 0 or g.`checked`=1) and g.`deleted`=0';
        }
        else if ($goodsfrom == 'cycle')
        {
            $condition .= ' AND g.`deleted`=1';
        }
        $keywords = trim($_GPC['keywords']);
        if ($keywords)
        {
            $condition .= ' AND (`title` LIKE :keywords OR `keywords` LIKE :keywords)';
            $params[':keywords'] = '%' . $keywords . '%';
        }
        $sql = 'SELECT count(g.id) FROM ' . tablename('ewei_shop_goods') . 'g' . $condition;
        $total = pdo_fetchcolumn($sql, $params);
        if (0 < $total)
        {
            $sql = 'SELECT g.* FROM ' . tablename('ewei_shop_goods') . 'g' . $condition . ' ORDER BY g.`status` DESC, g.`displayorder` DESC,' . "\r\n" . '                g.`id` DESC LIMIT ' . (($pindex - 1) * $psize) . ',' . $psize;
            $list = pdo_fetchall($sql, $params);
        }
        $list = set_medias($list, 'thumb');
        $pageCount = ceil($total/$psize);
        show_json(1, array('total' => $total, 'list' => $list, 'pageCount' => $pageCount));
    }
}