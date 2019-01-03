<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'app/core/page_mobile.php';
class Refund_EweiShopV2Page extends AppMobilePage
{
    protected $merchid = 0;

    public function __construct()
    {
        global $_GPC;
        $this->merchid = $_GPC['merchid'];
    }

    public function main()
    {
        global $_W;
        global $_GPC;
        global $_S;
        $opdata = $this->opData();
        extract($opdata);
        if ($_W['ispost'])
        {
            if (!(cv('order.op.refund.submit')))
            {
                $this->message('您没有维权处理权限');
            }
            $this->submit();
        }
        $step_array = array();
        $step_array[1]['step'] = 1;
        $step_array[1]['title'] = '客户申请维权';
        $step_array[1]['time'] = $refund['createtime'];
        $step_array[1]['done'] = 1;
        $step_array[2]['step'] = 2;
        $step_array[2]['title'] = '商家处理维权申请';
        $step_array[2]['done'] = 1;
        $step_array[3]['step'] = 3;
        $step_array[3]['done'] = 0;
        if (0 <= $refund['status'])
        {
            if ($refund['rtype'] == 0)
            {
                $step_array[3]['title'] = '退款完成';
            }
            else if ($refund['rtype'] == 1)
            {
                $step_array[3]['title'] = '客户退回物品';
                $step_array[4]['step'] = 4;
                $step_array[4]['title'] = '退款退货完成';
            }
            else if ($refund['rtype'] == 2)
            {
                $step_array[3]['title'] = '客户退回物品';
                $step_array[4]['step'] = 4;
                $step_array[4]['title'] = '商家重新发货';
                $step_array[5]['step'] = 5;
                $step_array[5]['title'] = '换货完成';
            }
            if ($refund['status'] == 0)
            {
                $step_array[2]['done'] = 0;
                $step_array[3]['done'] = 0;
            }
            if ($refund['rtype'] == 0)
            {
                if (0 < $refund['status'])
                {
                    $step_array[2]['time'] = $refund['refundtime'];
                    $step_array[3]['done'] = 1;
                    $step_array[3]['time'] = $refund['refundtime'];
                }
            }
            else
            {
                $step_array[2]['time'] = $refund['operatetime'];
                if (($refund['status'] == 1) || (4 <= $refund['status']))
                {
                    $step_array[3]['done'] = 1;
                    $step_array[3]['time'] = $refund['sendtime'];
                }
                if (($refund['status'] == 1) || ($refund['status'] == 5))
                {
                    $step_array[4]['done'] = 1;
                    if ($refund['rtype'] == 1)
                    {
                        $step_array[4]['time'] = $refund['refundtime'];
                    }
                    else if ($refund['rtype'] == 2)
                    {
                        $step_array[4]['time'] = $refund['returntime'];
                        if ($refund['status'] == 1)
                        {
                            $step_array[5]['done'] = 1;
                            $step_array[5]['time'] = $refund['refundtime'];
                        }
                    }
                }
            }
        }
        else if ($refund['status'] == -1)
        {
            $step_array[2]['done'] = 1;
            $step_array[2]['time'] = $refund['endtime'];
            $step_array[3]['done'] = 1;
            $step_array[3]['title'] = '拒绝' . $r_type[$refund['rtype']];
            $step_array[3]['time'] = $refund['endtime'];
        }
        else if ($refund['status'] == -2)
        {
            if (!(empty($refund['operatetime'])))
            {
                $step_array[2]['done'] = 1;
                $step_array[2]['time'] = $refund['operatetime'];
            }
            $step_array[3]['done'] = 1;
            $step_array[3]['title'] = '客户取消' . $r_type[$refund['rtype']];
            $step_array[3]['time'] = $refund['refundtime'];
        }
        $goods = pdo_fetchall('SELECT g.*, o.goodssn as option_goodssn, o.productsn as option_productsn,o.total,g.type,o.optionname,o.optionid,o.price as orderprice,o.realprice,o.changeprice,o.oldprice,o.commission1,o.commission2,o.commission3,o.commissions ' . $diyformfields . ' FROM ' . tablename('ewei_shop_order_goods') . ' o left join ' . tablename('ewei_shop_goods') . ' g on o.goodsid=g.id ' . ' WHERE o.orderid=:orderid and o.uniacid=:uniacid', array(':orderid' => $id, ':uniacid' => $_W['uniacid']));
        foreach ($goods as &$r )
        {
            if (!(empty($r['option_goodssn'])))
            {
                $r['goodssn'] = $r['option_goodssn'];
            }
            if (!(empty($r['option_productsn'])))
            {
                $r['productsn'] = $r['option_productsn'];
            }
            if (p('diyform'))
            {
                $r['diyformfields'] = iunserializer($r['diyformfields']);
                $r['diyformdata'] = iunserializer($r['diyformdata']);
            }
        }
        unset($r);
        $item['goods'] = $goods;
        $member = m('member')->getMember($item['openid']);
        $express_list = m('express')->getExpressList();
        $refund_address = pdo_fetchall('select * from ' . tablename('ewei_shop_refund_address') . ' where uniacid=:uniacid and merchid=0', array(':uniacid' => $_W['uniacid']));
        show_json(1,array('refund'=>$refund,'item'=>$item,'member'=>$member));
    }

    protected function opData()
    {
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);
        $refundid = intval($_GPC['refundid']);
        $item = pdo_fetch('SELECT * FROM ' . tablename('ewei_shop_order') . ' WHERE id = :id and uniacid=:uniacid Limit 1', array(':id' => $id, ':uniacid' => $_W['uniacid']));
        if (empty($item))
        {
            if ($_W['isajax'])
            {
                show_json(0, '未找到订单');
            }
            $this->message('未找到订单', '', 'error');
        }
        if (empty($refundid))
        {
            $refundid = $item['refundid'];
        }
        if (!(empty($refundid)))
        {
            $refund = pdo_fetch('select * from ' . tablename('ewei_shop_order_refund') . ' where id=:id limit 1', array(':id' => $refundid));
            $refund['imgs'] = iunserializer($refund['imgs']);
        }
        $r_type = array('退款', '退货退款', '换货');
        $refund['rtype_text'] = $r_type[$refund['rtype']];
        $order['paytime'] = date('Y-m-d H:i:s', $item['paytime']);
        return array('id' => $id, 'item' => $item, 'refund' => $refund, 'r_type' => $r_type);
    }
}