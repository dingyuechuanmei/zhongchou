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

    public function status()
    {
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);
        $ids = trim($_GPC['ids']);
        if (empty($id))
        {
            if (!(empty($ids)) && strexists($ids, ','))
            {
                $id = $ids;
            }
        }
        $items = pdo_fetchall('SELECT id,title FROM ' . tablename('ewei_shop_goods') . ' WHERE id in( ' . $id . ' ) AND merchid='.$this->merchid.' AND uniacid=' . $_W['uniacid']);
        foreach ($items as $item )
        {
            pdo_update('ewei_shop_goods', array('status' => intval($_GPC['status'])), array('id' => $item['id']));
            plog('goods.edit', (('修改商品状态<br/>ID: ' . $item['id'] . '<br/>商品名称: ' . $item['title'] . '<br/>状态: ' . $_GPC['status']) == 1 ? '上架' : '下架'));
        }
        show_json(1);
    }

    public function delete()
    {
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);
        $ids = trim($_GPC['ids']);
        if (empty($id))
        {
            if (!(empty($ids)) && strexists($ids, ','))
            {
                $id = $ids;
            }
        }
        $items = pdo_fetchall('SELECT id,title FROM ' . tablename('ewei_shop_goods') . ' WHERE id in( ' . $id . ' ) AND merchid='.$this->merchid.' AND uniacid=' . $_W['uniacid']);
        foreach ($items as $item )
        {
            pdo_update('ewei_shop_goods', array('deleted' => 1), array('id' => $item['id']));
            plog('goods.delete', '删除商品 ID: ' . $item['id'] . ' 商品名称: ' . $item['title'] . ' ');
        }
        show_json(1);
    }

    public function restore()
    {
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);
        $ids = trim($_GPC['ids']);
        if (empty($id))
        {
            if (!(empty($ids)) && strexists($ids, ','))
            {
                $id = $ids;
            }
        }
        $items = pdo_fetchall('SELECT id,title FROM ' . tablename('ewei_shop_goods') . ' WHERE id in( ' . $id . ' ) AND merchid='.$this->merchid.' AND uniacid=' . $_W['uniacid']);
        foreach ($items as $item )
        {
            pdo_update('ewei_shop_goods', array('deleted' => 0, 'status' => 0), array('id' => $item['id']));
            plog('goods.restore', '从回收站恢复商品<br/>ID: ' . $item['id'] . '<br/>商品名称: ' . $item['title']);
        }
        show_json(1);
    }

    public function detail()
    {
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);
        $merchid = intval($this->merchid);
        if (!(empty($id)))
        {
            $item = pdo_fetch('SELECT * FROM ' . tablename('ewei_shop_goods') . ' WHERE id = :id and uniacid = :uniacid and merchid=:merchid', array(':id' => $id, ':uniacid' => $_W['uniacid'],':merchid'=>$merchid));
            if (!(empty($item['thumb'])))
            {
                $thumb = array_merge(array($item['thumb']), iunserializer($item['thumb_url']));
                for ($i=0;$i<count($thumb);$i++) {
                    $piclist[$i] = tomedia($thumb[$i]);
                }
            }
            show_json(1,array('item'=>$item,'thumb'=>$thumb,'piclist'=>$piclist));
        }
        show_json(0,'商品信息错误');
    }

    public function post()
    {
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);
        $merchid = intval($this->merchid);
        if (!(empty($id)))
        {
            $item = pdo_fetch('SELECT * FROM ' . tablename('ewei_shop_goods') . ' WHERE id = :id and uniacid = :uniacid and merchid=:merchid', array(':id' => $id, ':uniacid' => $_W['uniacid'],':merchid'=>$merchid));
        }
        $data = array('title' => trim($_GPC['title']),'merchid'=>$merchid, 'subtitle' => trim($_GPC['subtitle']), 'unit' => trim($_GPC['unit']), 'status' => intval($_GPC['status']), 'showtotal' => intval($_GPC['showtotal']), 'cash' => intval($_GPC['cash']), 'invoice' => intval($_GPC['invoice']), 'isnodiscount' => intval($_GPC['isnodiscount']), 'nocommission' => intval($_GPC['nocommission']), 'isrecommand' => intval($_GPC['isrecommand']), 'isnew' => intval($_GPC['isnew']), 'ishot' => intval($_GPC['ishot']), 'issendfree' => intval($_GPC['issendfree']), 'totalcnf' => intval($_GPC['totalcnf']), 'dispatchtype' => intval($_GPC['dispatchtype']), 'showlevels' => trim($_GPC['showlevels']), 'showgroups' => trim($_GPC['showgroups']), 'buylevels' => trim($_GPC['buylevels']), 'buygroups' => trim($_GPC['buygroups']), 'maxbuy' => intval($_GPC['maxbuy']), 'minbuy' => intval($_GPC['minbuy']), 'usermaxbuy' => intval($_GPC['usermaxbuy']), 'diypage' => intval($_GPC['diypage']), 'displayorder' => intval($_GPC['displayorder']));
        if (empty($item))
        {
            $data['type'] = intval($_GPC['type']);
        }
        $thumbs = explode(',',trim($_GPC['thumbs'],','));
        if (is_array($thumbs))
        {
            $thumb_url = array();
            foreach ($thumbs as $th )
            {
                $thumb_url[] = trim($th);
            }
            $data['thumb'] = save_media($thumb_url[0]);
            unset($thumb_url[0]);
            $data['thumb_url'] = serialize(m('common')->array_images($thumb_url));
        }
        if (empty($item['hasoption']))
        {
            $data['hasoption'] = 0;
            $data['marketprice'] = trim($_GPC['marketprice']);
            $data['productprice'] = trim($_GPC['productprice']);
            $data['costprice'] = trim($_GPC['costprice']);
            $data['total'] = intval($_GPC['total']);
            $data['weight'] = trim($_GPC['weight']);
            $data['goodssn'] = trim($_GPC['goodssn']);
            $data['productsn'] = trim($_GPC['productsn']);
        }
        $result = array();
        if (!(empty($item)))
        {
            pdo_update('ewei_shop_goods', $data, array('id' => $item['id'], 'uniacid' => $_W['uniacid']));
            plog('goods.edit', '编辑商品 ID: ' . $id . '<br>' . ((!(empty($data['nocommission'])) ? '是否参与分销 -- 否' : '是否参与分销 -- 是')));
        }
        else
        {
            $data['createtime'] = time();
            $data['uniacid'] = $_W['uniacid'];
            pdo_insert('ewei_shop_goods', $data);
            $id = pdo_insertid();
            $result['id'] = $id;
            plog('goods.add', '添加商品 ID: ' . $id . '<br>' . ((!(empty($data['nocommission'])) ? '是否参与分销 -- 否' : '是否参与分销 -- 是')));
        }
        if (!(empty($item['hasoption'])))
        {
            $sql = 'update ' . tablename('ewei_shop_goods') . ' g set' . "\r\n" . '            g.minprice = (select min(marketprice) from ' . tablename('ewei_shop_goods_option') . ' where goodsid = ' . $id . '),' . "\r\n" . '            g.maxprice = (select max(marketprice) from ' . tablename('ewei_shop_goods_option') . ' where goodsid = ' . $id . ')' . "\r\n" . '            where g.id = ' . $id . ' and g.hasoption=1';
            pdo_query($sql);
        }
        else
        {
            pdo_query('delete from ' . tablename('ewei_shop_goods_option') . ' where goodsid=' . $id);
            $sql = 'update ' . tablename('ewei_shop_goods') . ' set minprice = marketprice,maxprice = marketprice where id = ' . $id . ' and hasoption=0;';
            pdo_query($sql);
        }
        $sqlgoods = 'SELECT id,title,thumb,marketprice,productprice,minprice,maxprice,isdiscount,isdiscount_time,isdiscount_discounts,sales,total,description,merchsale FROM ' . tablename('ewei_shop_goods') . ' where id=:id and uniacid=:uniacid limit 1';
        $goodsinfo = pdo_fetch($sqlgoods, array(':id' => $id, ':uniacid' => $_W['uniacid']));
        $goodsinfo = m('goods')->getOneMinPrice($goodsinfo);
        pdo_update('ewei_shop_goods', array('minprice' => $goodsinfo['minprice'], 'maxprice' => $goodsinfo['maxprice']), array('id' => $id, 'uniacid' => $_W['uniacid']));
        show_json(1, $result);
    }
}