<?php
if (!(defined('IN_IA'))) 
{
	exit('Access Denied');
}
class Apply_EweiShopV2Page extends PluginWebPage
{
	public $global;
    public $params;
    public $psize;
    public $uniacid;
    public $tb_raise_apply = 'ewei_shop_raise_apply';
    public $tb_member = 'ewei_shop_member';
    
    public function __construct(){
        parent::__construct(true);
        global $_W; global $_GPC;
        $this->global = $_W;
        $this->params = $_GPC;
        $this->psize = 20;
        $this->uniacid = $this->global['uniacid'];
    }
    
    public function main(){
        global $_W;
        $pindex = max(1, intval($this->params['page']));
        $status = intval($this->params['status']);
        $condition = ' where a.uniacid =:uniacid and a.status =:status';
        $params = array(':uniacid'=>$this->uniacid,':status'=>$status);
        $keyword = $this->params['keyword'];
        if($this->params['time'] && is_array($this->params['time'])){
            if($this->params['time']['start'] && $this->params['time']['end']){
                $starttime = strtotime($this->params['time']['start']);
                $endtime = strtotime($this->params['time']['end']);
                $time_arr = array('apply_time','pay_time','reject_time');
                $condition .=' and (a.'.$time_arr[$status].' between '.$starttime.' and '.$endtime.' ) ';
            }
        }
        if($keyword){
            $condition.=" and (realname like '%{$keyword}%' or nickname like '%{$keyword}%' or mobile like '%{$keyword}%')";
        }
        $sql = 'select a.id,a.applyNo,a.service,a.actual_money,m.nickname,m.realname,m.avatar,m.mobile,money,a.status,apply_time,pay_time,reject_time,reject_reason,a.createtime from '.tablename($this->tb_raise_apply).' a inner join '.tablename($this->tb_member).' m on(a.pusher = m.openid)';
        $sql_total = 'select count(*) from '.tablename($this->tb_raise_apply).' a inner join '.tablename($this->tb_member).' m on(a.pusher = m.openid)';
        $list = pdo_fetchall($sql.$condition.' order by a.id desc limit '.(($pindex - 1) * $this->psize) . ',' . $this->psize,$params);
        $total = pdo_fetchcolumn($sql_total.$condition,$params);
        $pager = pagination($total, $pindex, $this->psize);
        include $this->template();
    }
    public function pay(){
        global $_W;
        $apply_id = $this->params['id'];
        $apply_info = pdo_get($this->tb_raise_apply,array('id'=>$apply_id));
       /* $refund_param = $this->reufnd_wechat_build($apply_info);
        load()->classs('pay');
        $wechat = Pay::create('wechat');
        $response = $wechat->payment($refund_param);
        unlink(ATTACHMENT_ROOT . $_W['uniacid'] . '_wechat_refund_all.pem');*/
        if ($apply_info['status'] != 0) {
            show_json(0, '非法操作!');
        }
        if (empty($apply_info['pusher']))
        {
            show_json(0, '收款人不存在!');
        }
        $apply_info['pusher'] = substr($apply_info['pusher'],7);
        $payprice = $apply_info['actual_money'] * 100;
        $result = m('finance')->myPay($apply_info['pusher'], $payprice, $apply_info['applyNo'], '微客众筹打款');
        if (is_error($result))
        {
            pdo_update($this->tb_raise_apply,array('status'=>3,'reject_reason'=>$result['message'],'reject_time'=>time()),array('id'=>$apply_id));
            show_json(0, $result['message']);
        }
        pdo_update($this->tb_raise_apply,array('status'=>1,'pay_time'=>time()),array('id'=>$apply_id));
        show_json(1,'打款成功');
        /*
        if ($response['errno'] !=-1) {
           pdo_update($this->tb_raise_apply,array('status'=>1,'pay_time'=>time()),array('id'=>$apply_id));
           show_json(1,$response['message']);
        }else{
            $member = pdo_fetch('select id,credit2 from '.tablename($this->tb_member).' where openid=:openid',array(':openid'=>$apply_info['pusher']));
            if($member){
                $credit = floatval($apply_info['money'] + $member['credit2']);
                pdo_update($this->member,array('credit2'=>$credit),array('id'=>$member['id']));
            }
            pdo_update($this->tb_raise_apply,array('status'=>3,'reject_reason'=>$response['message'],'reject_time'=>time()),array('id'=>$apply_id));
            show_json(0,$response['message']);
        }
        */
    }
    public function manualpay(){
        global $_W;
        $apply_id = $this->params['id'];
        $apply_info = pdo_get($this->tb_raise_apply,array('id'=>$apply_id));

        if ($apply_info['status'] != 0) {
            show_json(0, '非法操作!');
        }
        if (empty($apply_info['pusher']))
        {
            show_json(0, '收款人不存在!');
        }
        pdo_update($this->tb_raise_apply,array('status'=>1,'pay_time'=>time()),array('id'=>$apply_id));
        show_json(1,'打款成功');
    }
    public function reject(){
        $id = $this->params['id'];
        include $this->template();
    }
    public function audit(){
        $id = $this->params['id'];
        $apply_info = pdo_get($this->tb_raise_apply,array('id'=>$id));
        if ($apply_info['status'] != 0) {
            show_json(0, '非法操作!');
        }
        $status = $this->params['status'];
        if($id){
            if(empty($this->params['reject_reason'])){
                show_json(0, '请填写拒绝理由!');
            }
            pdo_update($this->tb_raise_apply,array(
                'status'=>$status,
                'reject_time'=>time(),
                'reject_reason'=>$this->params['reject_reason']
            ),array('id'=>$id));
            show_json(1, array('url' => referer()));
        }
    }
   
    private function reufnd_wechat_build($apply_info) {
        global $_W;
        $setting = uni_setting_load('payment', $_W['uniacid']);
        $refund_setting = $setting['payment']['wechat_refund'];
        if ($refund_setting['switch'] != 1) {
            return error(1, '未开启微信退款功能！');
        }
        if (empty($refund_setting['key']) || empty($refund_setting['cert'])) {
            return error(1, '缺少微信证书！');
        }
        $account = uni_fetch($_W['uniacid']);
        $refund_param = array(
            'mch_appid' => 'wx816a05ab6d20638b',//$account['key'],
            'mchid' => $setting['payment']['wechat']['mchid'],
            'nonce_str' => random(32),
            'partner_trade_no' => $apply_info['applyNo'],
            'openid' => $apply_info['pusher'],
            'check_name'=>'NO_CHECK',
            'amount' => $apply_info['money'] * 100,
            'desc' => '用户申请提现打款',
            'spbill_create_ip' => gethostbyname($_SERVER['HTTP_HOST'])
        );
        $cert = authcode($refund_setting['cert'], 'DECODE');
        $key = authcode($refund_setting['key'], 'DECODE');
        file_put_contents(ATTACHMENT_ROOT . $_W['uniacid'] . '_wechat_refund_all.pem', $cert . $key);
        return $refund_param;
    }
}
?>