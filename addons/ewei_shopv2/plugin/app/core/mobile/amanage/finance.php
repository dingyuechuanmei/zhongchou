<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'app/core/page_mobile.php';
class Finance_EweiShopV2Page extends AppMobilePage
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

        // type 0 可提现  1待审核  2待结算 3 已结算 4 已无效
        $type = intval($_GPC['type']);

        switch ($type) {
            case '0':
                $this->post();
                break;
            case '1':
                $applyData = $this->applyData(1, 'status1');
                break;
            case '2':
                $applyData = $this->applyData(2, 'status2');
                break;
            case '3':
                $applyData = $this->applyData(3, 'status3');
                break;
            case '4':
                $applyData = $this->applyData(-1, 'status_1');
                break;
        }
    }

    protected function applyData($status, $st)
    {
        global $_W;
        global $_GPC;
        empty($status) && ($status = 1);
        $merchid = $this->merchid;
        $apply_type = array(0 => '微信钱包', 2 => '支付宝', 3 => '银行卡');
        if ($st == 'main')
        {
            $st = '';
        }
        else
        {
            $st = '.' . $st;
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 10;
        $condition = ' and b.uniacid=:uniacid and b.status=:status and b.merchid=:merchid';
        $params = array(':uniacid' => $_W['uniacid'], ':status' => $status, ':merchid' => $merchid);
        $keyword = trim($_GPC['keyword']);
        if (!(empty($keyword)))
        {
            $condition .= ' and b.applyno like :keyword';
            $params[':keyword'] = '%' . $keyword . '%';
        }
        if (empty($starttime) || empty($endtime))
        {
            $starttime = strtotime('-1 month');
            $endtime = time();
        }
        $timetype = $_GPC['timetype'];
        if (!(empty($_GPC['timetype'])))
        {
            $starttime = strtotime($_GPC['time']['start']);
            $endtime = strtotime($_GPC['time']['end']);
            if (!(empty($timetype)))
            {
                $condition .= ' AND b.' . $timetype . ' >= :starttime AND b.' . $timetype . '  <= :endtime ';
                $params[':starttime'] = $starttime;
                $params[':endtime'] = $endtime;
            }
        }
        if (3 <= $status)
        {
            $orderby = 'paytime';
        }
        else if (2 <= $status)
        {
            $orderby = ' checktime';
        }
        else
        {
            $orderby = 'applytime';
        }
        $applytitle = '';
        if ($status == 1)
        {
            $applytitle = '待审核';
        }
        else if ($status == 2)
        {
            $applytitle = '待打款';
        }
        else if ($status == 3)
        {
            $applytitle = '已打款';
        }
        else if ($status == -1)
        {
            $applytitle = '已无效';
        }
        $sql = 'select b.* from ' . tablename('ewei_shop_merch_bill') . ' b ' . ' left join ' . tablename('ewei_shop_merch_user') . ' u on b.merchid = u.id' . ' where 1 ' . $condition . ' ORDER BY ' . $orderby . ' desc ';
        if (empty($_GPC['export']))
        {
            $sql .= '  limit ' . (($pindex - 1) * $psize) . ',' . $psize;
        }
        $list = pdo_fetchall($sql, $params);
        foreach ($list as &$row) {
            $row['applytime'] = date('Y/m/d H:i:s',$row['applytime']);
            $row['checktime'] = date('Y/m/d H:i:s',$row['checktime']);
            $row['paytime'] = date('Y/m/d H:i:s',$row['paytime']);
            $row['invalidtime'] = date('Y/m/d H:i:s',$row['invalidtime']);
        }
        unset($row);
        $total = pdo_fetchcolumn('select count(b.id) from' . tablename('ewei_shop_merch_bill') . ' b ' . ' left join ' . tablename('ewei_shop_merch_user') . ' u on b.merchid = u.id' . ' where 1 ' . $condition, $params);
        $pager = pagination($total, $pindex, $psize);
        $pageCount = ceil($total/$psize);
        show_json(1, array('list' => $list, 'total' => $total, 'pagesize' => $psize,'pageCount'=>$pageCount));
    }
    /**
     * 可提现
     */
    protected function post()
    {
        global $_W;
        global $_GPC;

        $pindex = max(1, intval($_GPC['page']));
        $psize = 10;

        $merchid = $this->merchid;
        $item = $this->getMerchPrice($merchid, 1);
        $list = $this->getMerchPriceList($merchid, 0, 0,$pindex,$psize);

        $order_num = count($list);
        $set = m('common')->getPluginset('merch');
        if (empty($set))
        {
            $set = $this->getPluginsetByMerch('merch');
        }
        $last_data = $this->getLastApply($merchid);
        $type_array = array();
        if ($set['applycashweixin'] == 1)
        {
            $type_array[0]['title'] = '提现到微信钱包';
        }
        if ($set['applycashalipay'] == 1)
        {
            $type_array[2]['title'] = '提现到支付宝';
            if (!(empty($last_data)))
            {
                if ($last_data['applytype'] != 2)
                {
                    $type_last = $this->getLastApply($merchid, 2);
                    if (!(empty($type_last)))
                    {
                        $last_data['alipay'] = $type_last['alipay'];
                    }
                }
            }
        }
        if ($set['applycashcard'] == 1)
        {
            $type_array[3]['title'] = '提现到银行卡';
            if (!(empty($last_data)))
            {
                if ($last_data['applytype'] != 3)
                {
                    $type_last = $this->getLastApply($merchid, 3);
                    if (!(empty($type_last)))
                    {
                        $last_data['bankname'] = $type_last['bankname'];
                        $last_data['bankcard'] = $type_last['bankcard'];
                    }
                }
            }
            $condition = ' and uniacid=:uniacid';
            $params = array(':uniacid' => $_W['uniacid']);
            $banklist = pdo_fetchall('SELECT * FROM ' . tablename('ewei_shop_commission_bank') . ' WHERE 1 ' . $condition . '  ORDER BY displayorder DESC', $params);
        }
        if (!(empty($last_data)))
        {
            if (array_key_exists($last_data['applytype'], $type_array))
            {
                $type_array[$last_data['applytype']]['checked'] = 1;
            }
        }
        $pageCount = ceil($order_num/$psize);
        show_json(1,array('item'=>$item,'list'=>$list,'order_num'=>$order_num,'set'=>$set,'type_array'=>$type_array,'pageCount'=>$pageCount));
    }

    public function getLastApply($merchid, $applytype = -1)
    {
        global $_W;
        $params = array(':uniacid' => $_W['uniacid'], ':merchid' => $merchid);
        $sql = 'select applytype,alipay,bankname,bankcard,applyrealname from ' . tablename('ewei_shop_merch_bill') . ' where merchid=:merchid and uniacid=:uniacid';
        if (-1 < $applytype)
        {
            $sql .= ' and applytype=:applytype';
            $params[':applytype'] = $applytype;
        }
        $sql .= ' order by id desc Limit 1';
        $data = pdo_fetch($sql, $params);
        return $data;
    }

    protected function getMerchPrice($merchid, $flag = 0)
    {
        global $_W;
        $merch_data = m('common')->getPluginset('merch');
        if (empty($merch_data))
        {
            $merch_data = $this->getPluginsetByMerch('merch');
        }
        if (!(empty($merch_data['deduct_commission'])))
        {
            $deduct_commission = 1;
        }
        else
        {
            $deduct_commission = 0;
        }
        $condition = ' and u.uniacid=:uniacid and u.id=:merchid and o.status=3 and o.isparent=0 and o.merchapply<=0';
        $params = array(':uniacid' => $_W['uniacid'], ':merchid' => $merchid);
        $con = 'u.id,u.merchname,u.payrate,sum(o.price) price,sum(o.goodsprice) goodsprice,sum(o.dispatchprice) dispatchprice,sum(o.discountprice) discountprice,sum(o.deductprice) deductprice,sum(o.deductcredit2) deductcredit2,sum(o.isdiscountprice) isdiscountprice,sum(o.deductenough) deductenough,sum(o.merchdeductenough) merchdeductenough,sum(o.merchisdiscountprice) merchisdiscountprice,sum(o.changeprice) changeprice,sum(o.seckilldiscountprice) seckilldiscountprice';
        $tradeset = m('common')->getSysset('trade');
        $refunddays = intval($tradeset['refunddays']);
        if (0 < $refunddays)
        {
            $finishtime = intval(time() - ($refunddays * 3600 * 24));
            $condition .= ' and o.finishtime<:finishtime';
            $params['finishtime'] = $finishtime;
        }
        $sql = 'select ' . $con . ' from ' . tablename('ewei_shop_merch_user') . ' u ' . ' left join ' . tablename('ewei_shop_order') . ' o on u.id=o.merchid' . ' where 1 ' . $condition . ' limit 1';
        $list = pdo_fetch($sql, $params);
        $merchcouponprice = pdo_fetchcolumn('select sum(o.couponprice) from ' . tablename('ewei_shop_merch_user') . ' u ' . ' left join ' . tablename('ewei_shop_order') . ' o on u.id=o.merchid' . ' where o.couponmerchid>0 ' . $condition . ' limit 1', $params);
        if (0 < $flag)
        {
            $sql = 'select o.id,o.agentid from ' . tablename('ewei_shop_merch_user') . ' u ' . ' left join ' . tablename('ewei_shop_order') . ' o on u.id=o.merchid' . ' where 1 ' . $condition;
            $order = pdo_fetchall($sql, $params);
            $orderids = array();
            $commission = 0;
            if (!(empty($order)))
            {
                foreach ($order as $k => $v )
                {
                    $orderids[] = $v['id'];
                    $commission += m('order')->getOrderCommission($v['id'], $v['agentid']);
                }
            }
            $list['orderids'] = $orderids;
            $list['commission'] = $commission;
        }
        $list['orderprice'] = $list['goodsprice'] + $list['dispatchprice'] + $list['changeprice'];
        $list['realprice'] = $list['orderprice'] - $list['merchdeductenough'] - $list['merchisdiscountprice'] - $merchcouponprice - $list['seckilldiscountprice'];
        if ($deduct_commission)
        {
            $list['realprice'] -= $list['commission'];
        }
        $list['realpricerate'] = ((100 - floatval($list['payrate'])) * $list['realprice']) / 100;
        $list['merchcouponprice'] = $merchcouponprice;
        return $list;
    }

    protected function getMerchPriceList($merchid, $orderid = 0, $flag = 0,$pindex = 0,$psize=0)
    {
        global $_W;
        $merch_data = m('common')->getPluginset('merch');
        if (empty($merch_data))
        {
            $merch_data = $this->getPluginsetByMerch('merch');
        }
        if (!(empty($merch_data['deduct_commission'])))
        {
            $deduct_commission = 1;
        }
        else
        {
            $deduct_commission = 0;
        }
        $condition = ' and u.uniacid=:uniacid and u.id=:merchid and o.status=3 and o.isparent=0 ';
        $params = array(':uniacid' => $_W['uniacid'], ':merchid' => $merchid);
        switch ($flag)
        {
            case 0: $condition .= ' and o.merchapply <= 0';
                break;
            case 1: $condition .= ' and o.merchapply = 1';
                break;
            case 2: $condition .= ' and o.merchapply = 2';
                break;
            case 3: $condition .= ' and o.merchapply = 3';
                break;
            default: $tradeset = m('common')->getSysset('trade');
                $refunddays = intval($tradeset['refunddays']);
        }

        if (0 < $refunddays)
        {
            $finishtime = intval(time() - ($refunddays * 3600 * 24));
            $condition .= ' and o.finishtime<:finishtime';
            $params['finishtime'] = $finishtime;
        }

        $limit = "";
        if (!(empty($orderid)))
        {
            $condition .= ' and o.id=:id Limit 1';
            $params['id'] = $orderid;
        }else{
            if(!empty($pindex) && !empty($psize)){
                $limit = ' Limit ' . (($pindex - 1) * $psize) . ',' . $psize;
            }
        }

        $con = 'o.id,u.merchname,u.payrate,o.price,o.goodsprice,o.dispatchprice,discountprice,' . 'o.deductprice,o.deductcredit2,o.isdiscountprice,o.deductenough,o.changeprice,o.agentid,o.seckilldiscountprice,' . 'o.merchdeductenough,o.merchisdiscountprice,o.couponmerchid,o.couponprice,o.couponmerchid,o.ordersn,o.finishtime,o.merchapply';

        $sql = 'select ' . $con . ' from ' . tablename('ewei_shop_merch_user') . ' u ' . ' left join ' . tablename('ewei_shop_order') . ' o on u.id=o.merchid' . ' where 1 ' . $condition.$limit;
        $order = pdo_fetchall($sql, $params);
        /**
         * 缺少循环
         */
        foreach ($order as &$list) {
            $merchcouponprice = 0;
            if (0 < $list['couponmerchid'])
            {
                $merchcouponprice = $list['couponprice'];
            }
            $list['commission'] = m('order')->getOrderCommission($list['id'], $list['agentid']);
            $list['orderprice'] = $list['goodsprice'] + $list['dispatchprice'] + $list['changeprice'];
            $list['realprice'] = $list['orderprice'] - $list['merchdeductenough'] - $list['merchisdiscountprice'] - $merchcouponprice - $list['seckilldiscountprice'];
            if ($deduct_commission)
            {
                $list['realprice'] -= $list['commission'];
            }
            $list['realpricerate'] = ((100 - floatval($list['payrate'])) * $list['realprice']) / 100;
            $list['merchcouponprice'] = $merchcouponprice;
        }
        unset($list);
        if (!(empty($orderid)))
        {
            return $order[0];
        }

        foreach($order as &$row){
            $row['finishtime'] = date("Y/m/d H:i:s",$row['finishtime']);
        }
        unset($row);

        return $order;
    }

    public function getPluginsetByMerch($key = '')
    {
        global $_W;
        $uniacid = $_W['uniacid'];
        $set = pdo_fetch('select * from ' . tablename('ewei_shop_sysset') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $uniacid));
        if (empty($set))
        {
            $set = array();
        }
        $allset = iunserializer($set['plugins']);
        $retsets = array();
        if (!(empty($key)))
        {
            if (is_array($key))
            {
                foreach ($key as $k )
                {
                    $retsets[$k] = ((isset($allset[$k]) ? $allset[$k] : array()));
                }
            }
            else
            {
                $retsets = ((isset($allset[$key]) ? $allset[$key] : array()));
            }
            return $retsets;
        }
        return $allset;
    }

    public function apply_withdrow()
    {
        global $_W;
        $merchid = $this->merchid;
        $set = m('common')->getPluginset('merch');
        if (empty($set))
        {
            $set = $this->getPluginsetByMerch('merch');
        }
        $last_data = $this->getLastApply($merchid);
        if ($set['applycashweixin'] == 1)
        {
            $type_array[0]['title'] = '提现到微信钱包';
            $type_array[0]['type'] = 0;
        }
        if ($set['applycashalipay'] == 1)
        {
            $type_array[1]['title'] = '提现到支付宝';
            $type_array[1]['type'] = 2;
            if (!(empty($last_data)))
            {
                if ($last_data['applytype'] != 2)
                {
                    $type_last = $this->getLastApply($merchid, 2);
                    if (!(empty($type_last)))
                    {
                        $last_data['alipay'] = $type_last['alipay'];
                    }
                }
            }
        }
        if ($set['applycashcard'] == 1)
        {
            $type_array[2]['title'] = '提现到银行卡';
            $type_array[2]['type'] = 3;
            if (!(empty($last_data)))
            {
                if ($last_data['applytype'] != 3)
                {
                    $type_last = $this->getLastApply($merchid, 3);
                    if (!(empty($type_last)))
                    {
                        $last_data['bankname'] = $type_last['bankname'];
                        $last_data['bankcard'] = $type_last['bankcard'];
                    }
                }
            }
            $condition = ' and uniacid=:uniacid';
            $params = array(':uniacid' => $_W['uniacid']);
            $banklist = pdo_fetchall('SELECT * FROM ' . tablename('ewei_shop_commission_bank') . ' WHERE 1 ' . $condition . '  ORDER BY displayorder DESC', $params);
        }
        $index = 0;
        if (!(empty($last_data)))
        {
            if (array_key_exists($last_data['applytype'], $type_array) || $last_data['applytype'] == 3)
            {
                //$type_array[$last_data['applytype']]['checked'] = 1;
                if ($last_data['applytype'] == 2) {
                    $index = 1;
                } elseif ($last_data['applytype'] == 3) {
                    $index = 2;
                }
            }
        }
        show_json(1,array('type_array'=>$type_array,'banklist'=>$banklist,'last_data'=>$last_data,'index'=>$index));
    }

    public function apply_withdrow_post()
    {
        global $_W;
        global $_GPC;
        $merchid = $this->merchid;
        $item = $this->getMerchPrice($merchid, 1);
        $list = $this->getMerchPriceList($merchid, 0, 0);
        $order_num = count($list);
        if (empty($set))
        {
            $set = $this->getPluginsetByMerch('merch');
        }
        $last_data = $this->getLastApply($merchid);
        if ($set['applycashweixin'] == 1)
        {
            $type_array[0]['title'] = '提现到微信钱包';
        }
        if ($set['applycashalipay'] == 1)
        {
            $type_array[2]['title'] = '提现到支付宝';
            if (!(empty($last_data)))
            {
                if ($last_data['applytype'] != 2)
                {
                    $type_last = $this->getLastApply($merchid, 2);
                    if (!(empty($type_last)))
                    {
                        $last_data['alipay'] = $type_last['alipay'];
                    }
                }
            }
        }
        if ($set['applycashcard'] == 1)
        {
            $type_array[3]['title'] = '提现到银行卡';
            if (!(empty($last_data)))
            {
                if ($last_data['applytype'] != 3)
                {
                    $type_last = $this->getLastApply($merchid, 3);
                    if (!(empty($type_last)))
                    {
                        $last_data['bankname'] = $type_last['bankname'];
                        $last_data['bankcard'] = $type_last['bankcard'];
                    }
                }
            }
        }
        if (($item['realprice'] <= 0) || empty($list))
        {
            show_json(0, '您没有可提现的金额');
        }
        $applytype = intval($_GPC['applytype']);
        if (!(array_key_exists($applytype, $type_array)))
        {
            show_json(0, '未选择提现方式，请您选择提现方式后重试!');
        }
        $insert = array();
        if ($applytype == 2)
        {
            $realname = trim($_GPC['realname']);
            $alipay = trim($_GPC['alipay']);
            $alipay1 = trim($_GPC['alipay1']);
            if (empty($realname))
            {
                show_json(0, '请填写姓名!');
            }
            if (empty($alipay))
            {
                show_json(0, '请填写支付宝帐号!');
            }
            if (empty($alipay1))
            {
                show_json(0, '请填写确认帐号!');
            }
            if ($alipay != $alipay1)
            {
                show_json(0, '支付宝帐号与确认帐号不一致!');
            }
            $insert['applyrealname'] = $realname;
            $insert['alipay'] = $alipay;
        }
        else if ($applytype == 3)
        {
            $realname = trim($_GPC['realname']);
            $bankname = trim($_GPC['bankname']);
            $bankcard = trim($_GPC['bankcard']);
            $bankcard1 = trim($_GPC['bankcard1']);
            if (empty($realname))
            {
                show_json(0, '请填写姓名!');
            }
            if (empty($bankname))
            {
                show_json(0, '请选择银行!');
            }
            if (empty($bankcard))
            {
                show_json(0, '请填写银行卡号!');
            }
            if (empty($bankcard1))
            {
                show_json(0, '请填写确认卡号!');
            }
            if ($bankcard != $bankcard1)
            {
                show_json(0, '银行卡号与确认卡号不一致!');
            }
            $insert['applyrealname'] = $realname;
            $insert['bankname'] = $bankname;
            $insert['bankcard'] = $bankcard;
        }
        $insert['uniacid'] = $_W['uniacid'];
        $insert['merchid'] = $merchid;
        $insert['applyno'] = m('common')->createNO('merch_bill', 'applyno', 'MO');
        $insert['orderids'] = iserializer($item['orderids']);
        $insert['ordernum'] = $order_num;
        $insert['price'] = $item['price'];
        $insert['realprice'] = $item['realprice'];
        $insert['realpricerate'] = $item['realpricerate'];
        $insert['finalprice'] = $item['finalprice'];
        $insert['orderprice'] = $item['orderprice'];
        $insert['payrateprice'] = round(($item['realpricerate'] * $item['payrate']) / 100, 2);
        $insert['payrate'] = $item['payrate'];
        $insert['applytime'] = time();
        $insert['status'] = 1;
        $insert['applytype'] = $applytype;
        pdo_insert('ewei_shop_merch_bill', $insert);
        $billid = pdo_insertid();
        foreach ($list as $k => $v )
        {
            $orderid = $v['id'];
            $insert_data = array();
            $insert_data['uniacid'] = $_W['uniacid'];
            $insert_data['billid'] = $billid;
            $insert_data['orderid'] = $orderid;
            $insert_data['ordermoney'] = $v['realprice'];
            pdo_insert('ewei_shop_merch_billo', $insert_data);
            $change_order_data = array();
            $change_order_data['merchapply'] = 1;
            pdo_update('ewei_shop_order', $change_order_data, array('id' => $orderid));
        }
        show_json(1);
    }
}



























