<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'app/core/page_mobile.php';
class Index_EweiShopV2Page extends AppMobilePage
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

    public function get_info()
    {
        global $_W;
        $info = pdo_get("ewei_shop_merch_user",array('id'=>$this->merchid));
        $info['logo'] = !empty($info['logo']) ? tomedia($info['logo']) : '/static/images/nopic.jpg';
        app_json(array('info'=>$info));
    }

    public function get_merch_info()
    {
        global $_W;
        $order = $this->order(0);
        $totals = m('order')->getTotals($this->merchid);
        $goods = m('goods')->getTotals($this->merchid);
        $goodscount = $goods['sale'] + $goods['out'] + $goods['stock'] + $goods['cycle'];
        $merch = pdo_get("ewei_shop_merch_user",array('id'=>$this->merchid));
        $info = array(
            'today_count' => $order['count'],
            'today_price' => $order['price'],
            'status1' => $totals['status1'],        //待发货
            'status0' => $totals['status0'],        //待付款
            'status4' => $totals['status4'],         //维权订单
            'goodscount' => $goodscount,
            'merchname' => $merch['merchname'],
            'desc' => $merch['desc'],
            'logo' => !empty($merch['logo']) ? tomedia($merch['logo']) : '/static/images/nopic.jpg'
        );
        app_json(array('info'=>$info));
    }

    protected function order($day)
    {
        global $_W;
        $day = (int) $day;
        $orderPrice = $this->selectOrderPrice($day);
        $orderPrice['avg'] = ((empty($orderPrice['count']) ? 0 : round($orderPrice['price'] / $orderPrice['count'], 1)));
        unset($orderPrice['fetchall']);
        return $orderPrice;
    }
    protected function selectOrderPrice($day = 0)
    {
        global $_W;
        $day = (int) $day;
        if ($day != 0)
        {
            $createtime1 = strtotime(date('Y-m-d', time() - ($day * 3600 * 24)));
            $createtime2 = strtotime(date('Y-m-d', time()));
        }
        else
        {
            $createtime1 = strtotime(date('Y-m-d', time()));
            $createtime2 = strtotime(date('Y-m-d', time() + (3600 * 24)));
        }
        $sql = 'select id,price,createtime from ' . tablename('ewei_shop_order') . ' where uniacid = :uniacid and merchid =:merchid and ismr=0 and isparent=0 and (status > 0 or ( status=0 and paytype=3)) and deleted=0 and createtime between :createtime1 and :createtime2';
        $param = array(':uniacid' => $_W['uniacid'],':merchid'=>$this->merchid, ':createtime1' => $createtime1, ':createtime2' => $createtime2);
        $pdo_res = pdo_fetchall($sql, $param);
        $price = 0;
        foreach ($pdo_res as $arr )
        {
            $price += $arr['price'];
        }
        $result = array('price' => round($price, 1), 'count' => count($pdo_res), 'fetchall' => $pdo_res);
        return $result;
    }
}