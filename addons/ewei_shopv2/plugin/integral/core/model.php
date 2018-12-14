<?php
if (!(defined('IN_IA'))){
    exit('Access Denied');
}
if (!(class_exists('IntegralModel'))){
    class IntegralModel extends PluginModel{
        public function getSet($uniacid = 0)
        {
            $set = parent::getSet($uniacid);
            $set['texts'] = array('agent' => (empty($set['texts']['agent']) ? '分销商' : $set['texts']['agent']), 'shop' => (empty($set['texts']['shop']) ? '小店' : $set['texts']['shop']), 'myshop' => (empty($set['texts']['myshop']) ? '我的小店' : $set['texts']['myshop']), 'center' => (empty($set['texts']['center']) ? '分销中心' : $set['texts']['center']), 'become' => (empty($set['texts']['become']) ? '成为分销商' : $set['texts']['become']), 'withdraw' => (empty($set['texts']['withdraw']) ? '提现' : $set['texts']['withdraw']), 'commission' => (empty($set['texts']['commission']) ? '佣金' : $set['texts']['commission']), 'commission1' => (empty($set['texts']['commission1']) ? '分销佣金' : $set['texts']['commission1']), 'commission_total' => (empty($set['texts']['commission_total']) ? '累计佣金' : $set['texts']['commission_total']), 'commission_ok' => (empty($set['texts']['commission_ok']) ? '可提现佣金' : $set['texts']['commission_ok']), 'commission_apply' => (empty($set['texts']['commission_apply']) ? '已申请佣金' : $set['texts']['commission_apply']), 'commission_check' => (empty($set['texts']['commission_check']) ? '待打款佣金' : $set['texts']['commission_check']), 'commission_lock' => (empty($set['texts']['commission_lock']) ? '未结算佣金' : $set['texts']['commission_lock']), 'commission_detail' => (empty($set['texts']['commission_detail']) ? '提现明细' : (($set['texts']['commission_detail'] == '佣金明细' ? '提现明细' : $set['texts']['commission_detail']))), 'commission_pay' => (empty($set['texts']['commission_pay']) ? '成功提现佣金' : $set['texts']['commission_pay']), 'commission_wait' => (empty($set['texts']['commission_wait']) ? '待收货佣金' : $set['texts']['commission_wait']), 'commission_fail' => (empty($set['texts']['commission_fail']) ? '无效佣金' : $set['texts']['commission_fail']), 'commission_charge' => (empty($set['texts']['commission_charge']) ? '扣除个人所得税' : $set['texts']['commission_charge']), 'order' => (empty($set['texts']['order']) ? '分销订单' : $set['texts']['order']), 'c1' => (empty($set['texts']['c1']) ? '一级' : $set['texts']['c1']), 'c2' => (empty($set['texts']['c2']) ? '二级' : $set['texts']['c2']), 'c3' => (empty($set['texts']['c3']) ? '三级' : $set['texts']['c3']), 'mydown' => (empty($set['texts']['mydown']) ? '我的下线' : $set['texts']['mydown']), 'down' => (empty($set['texts']['down']) ? '下线' : $set['texts']['down']), 'up' => (empty($set['texts']['up']) ? '推荐人' : $set['texts']['up']), 'yuan' => (empty($set['texts']['yuan']) ? '元' : $set['texts']['yuan']));
            return $set;
        }
        //计算积分比例
        public function calculateIntegral($price = 0){
            if(intval($price)<=0)return;
            $order = array();
            $set = $this->getset();
            if($set['switch'] == 1){
                $total_integral = round($price);//总积分
                $day_inte = round($total_integral * $set['profit']);//每日积分
                $refund_day = ($total_integral / $day_inte); //返回天数
                $order = array(
                    'integral' => $day_inte,
                    'refund_day' => $refund_day,
                    'surplus_day' => $refund_day
                );
            }
            return $order;
        }
        
        //全返积分
        public function returnIntegral($orderid = 0){
            $set = $this->getSet();
            if(empty($orderid))return;
            if($set['switch'] != 1)return;
            $order  = pdo_fetch('select id,uniacid,openid,price,integral,refund_day,surplus_day from '.tablename('ewei_shop_order').' where id = :id',array(':id'=>$orderid));
            if($order){
                if(intval($order['refund_day'])<=0)return;
                pdo_insert('ewei_shop_integral',array(
                    'uniacid'=>$order['uniacid'],
                    'openid'=>$order['openid'],
                    'orderid'=>$order['id'],
                    'total_integral'=>round($order['price']),//返还总积分
                    'refund_integral'=>$order['integral'],//本次返回积分
                    'none_integral'=>$order['integral'] * $order['surplus_day'],//剩余积分
                    'refund_time'=>time(),
                    'remark'=>$set['refund_remark']
                ));
                $id = pdo_insertid();
                if($id > 0){
                    $refund_day = $order['refund_day'] - 1;
                    pdo_update('ewei_shop_order',array('refund_day'=>$refund_day),array('id'=>$order['id']));
                }
            }
        }
    }
}
?>