<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'app/core/page_mobile.php';
class Index_EweiShopV2Page extends AppMobilePage
{
    public $tb_raise_index = 'ewei_shop_raise_index';
    public $tb_raise_apply = 'ewei_shop_raise_apply';
    public $tb_pusher_category = 'ewei_shop_raise_category';
    public $tb_pusher = 'ewei_shop_raise_pusher';
    public $tb_starter = 'ewei_shop_raise_starter';
    public $tb_pusher_banner = 'ewei_shop_raise_pusher_banner';
    public $tb_starter_help = 'ewei_shop_raise_starter_help';
    public $tb_starter_verify = 'ewei_shop_raise_starter_verify';
    public $tb_starter_relation = 'ewei_shop_raise_starter_relation';
    public $tb_member = 'ewei_shop_member';
    public $tb_merch = 'ewei_shop_merch_user';
    public $tb_formid = 'ewei_shop_raise_formid';
    //SELECT * FROM `ims_ewei_shop_merch_user` WHERE openid = 'sns_wa_ojIoH0RU0ltnBTup2mF0nEozY4ec' AND `status` = 1
    public $account_api;
    public $uniacid;
    public $psize = 5;
    public $params;
    public $openid;
    
    //支付成功推送模板消息
    public function send_msg(){
        $this->deal_data();
        $help_id = intval($this->params['help_id']);
        if(!empty($help_id)){
            $order = pdo_fetch("select starter_id,money from ".tablename($this->tb_starter_help)." where id =:id limit 1",array(':id'=>$help_id));
            if($order){
                $item = pdo_fetch("select id,refer_money,support_count from ".tablename($this->tb_starter)." where id =:id limit 1 ",array(':id'=>$order['starter_id']));
                if($item){
                    pdo_update($this->tb_starter,array('refer_money'=>$item['refer_money'] + $order['money'],'support_count'=>$item['support_count'] + 1),array('id'=>$item['id']));
                }
                $json = $this->send_templete($order['starter_id'],$order['money']);
                app_error(1,"模板消息".json_encode($json));
            }
        }
        app_error(0,"参数错误");
    }
    
    //添加表单id
    public function save_fromid(){
        $this->deal_data();
        pdo_insert($this->tb_formid,array(
            'form_id'=>$this->params['form_id'],
            'openid'=>$this->openid,
            'datetime'=>time(),
            'status'=>0,
        ));
        app_error(1,'ok');
    }
    
    //判断是否为商户
    public function judge_merch(){
        $this->deal_data();
        $result = pdo_fetchcolumn('select id from '.tablename($this->tb_merch).' where uniacid=:uniacid and openid=:openid and status =:status limit 1',array(':uniacid'=>$this->uniacid,':openid'=>$this->openid,':status'=>1));
        app_json(array('result'=>$result ? 1 : 0));
    }
    
    //绑定手机号
    public function bind_mobile(){
        $this->deal_data();
        if(!$this->params['moible']){
            app_error(1,'请输入更换昵称');
        }else{
            $status = $this->checkCode($this->params['moible'],$this->params['code']);            
            if($status){
                $res = pdo_update("ewei_shop_raise_codeauth",array('status'=>1),array( 'status'=>0,'openid'=> $this->openid,'mobile'=> $this->params['moible'] ));
            }else{
                app_error(1,"验证码错误或已过期!");
            }
        }

        $result = pdo_update($this->tb_member,array('mobile'=>$this->params['moible']),array('openid'=>$this->openid));

        if($result){
            app_json(array('mobile'=>$this->params['moible'],'message'=>'绑定成功'));
        }else{
            app_error(1,'绑定失败');
        }
    }
    
    //更换昵称
    public function change_nickname(){
        $this->deal_data();
        if(!$this->params['nickname']){
            app_error(1,'请输入更换昵称');
        }
        $result = pdo_update($this->tb_member,array('nickname'=>$this->params['nickname']),array('openid'=>$this->openid));
        if($result){
             app_json(array('nickname'=>$this->params['nickname'],'message'=>'修改成功'));
        }else{
            app_error(1,'修改失败');
        }
    }
    
    //我发起的救助
    public function publish_starter(){
        $this->deal_data();
        $params[':pusher'] = $this->openid;
        $page = $this->params['page'] ? intval($this->params['page']) : 1;
        $starter_list = pdo_fetchall('select id,title,status,target_money,refer_money,audittime,aborttime from '.tablename($this->tb_starter).' where pusher =:pusher order by id desc limit '.(($page-1) * $this->psize).','.$this->psize,$params);
        $total = pdo_fetchcolumn('select count(*) from '.tablename($this->tb_starter).' where pusher =:pusher',$params);
        if($starter_list){
            foreach ($starter_list as &$v) {
                switch ($v['status']) {
                    case 0:
                        $v['statustext'] = '待审核';
                        break;
                    case 1:
                        $v['statustext'] = (time() >= $v['aborttime'] || $v['refer_money'] >= $v['target_money']) ? '已结束' : '筹款中';
                        break;
                    case 2:
                        $v['statustext'] = '审核失败';
                        break;
                }
            }
            unset($v);
            app_json(array('starter_list'=>$starter_list,'total'=>$total));
        }else{
            app_error(1,'暂无数据');
        }
    }
    
    //我参与的救助
    public function part_starter(){
        $this->deal_data();
        $params[':pusher'] = $this->openid;
        $page = $this->params['page'] ? intval($this->params['page']) : 1;
        $cateid = $this->params['category'];

        $condition = " and h.status = 1 and h.deleted = 0 ";
        if(!empty($cateid)){
            $condition .= " and s.category = ".intval($cateid);
        }

        $help_list = pdo_fetchall('select h.id,s.title,s.status,h.money,h.createtime,h.starter_id from '.tablename($this->tb_starter_help)
            .' h inner join '.tablename($this->tb_starter).' s on(h.starter_id = s.id) where h.pusher =:pusher '.$condition.' order by h.id desc limit '.(($page-1) * $this->psize).','.$this->psize,$params);
        $total = pdo_fetchcolumn('select count(*) from '.tablename($this->tb_starter_help).' where pusher =:pusher',$params);
        if($help_list){
            app_json(array('verify_list'=>$help_list,'total'=>$total));
        }else{
            app_error(1,'暂无数据');
        }
    }

    // 删除我的微客
    public function part_starter_delete(){
        global $_W,$_GPC;
        $id = intval($_GPC['id']);

        $help = pdo_fetch("select * from ".tablename($this->tb_starter_help)." where id=:id and status = 1 and deleted = 0 ",array(':id'=>$id));

        if($help){
            pdo_update($this->tb_starter_help,array('deleted'=>1),array('id'=>$id));
            app_json(array('message'=>'删除成功'));
        }else{
            app_error(1,'暂无数据');
        }
    }
    
    //申请提现
    public function apply(){
        $this->deal_data();
        if(!$this->params['apply_money']){
            app_error(1,'请输入提现金额');
        }
        /*
        $purse = pdo_fetchcolumn('select ifnull(credit2,0) from where openid=:openid and uniacid=:uniacid',array('openid'=>$this->openid,'uniacid'=>$this->uniacid)); 
        if(!$purse || $purse<$this->params['apply_money']){
            app_error(1,'余额不足,无法提现');
        }
        */
        load()->model('module');
        $module_info = module_fetch('ewei_shopv2');
        $moduleid =  empty($module_info['mid']) ? '0000' : sprintf("%06d", $module_info['mid']);
        $applyNo = date('YmdHis') . $moduleid . random(8,1);
        $time = time();
        pdo_insert($this->tb_raise_apply,array(
            'uniacid'=>$this->uniacid,
            'applyNo'=>$applyNo,
            'pusher'=>$this->openid,
            'money'=>doubleval($this->params['apply_money']),
            'status'=>0,
            'service' => doubleval($this->params['service']),
            'actual_money' => number_format($this->params['apply_money']-($this->params['apply_money']*$this->params['service']/100),2),
            'apply_time'=>$time,
            'createtime'=>$time,
        ));
        $id = pdo_insertid();
        if($id > 0){
            app_json(array('id'=>$id,'message'=>'提现成功'));
        }else{
            app_error(1,'提现失败!');
        }
    }
    
    //我的钱包
    public function purse(){
        $this->deal_data();
        $purse = pdo_fetchcolumn('select ifnull(credit2,0) from where openid=:openid and uniacid=:uniacid',array('openid'=>$this->openid,'uniacid'=>$this->uniacid));
        app_json(array('purse'=>$purse));
    }
    
    //个人中心
    public function center(){
        $this->deal_data();
        //$this -> openid = 'sns_wa_ojIoH0dhaUzFmQSzG03Jd2X3MdEo';
        $member = pdo_get($this->tb_member,array('openid'=>$this->openid,'uniacid'=>$this->uniacid),array('id','nickname','avatar','mobile','credit2'));
        $params = array(':pusher'=>$this->openid,':uniacid'=>$this->uniacid);
        $starter_count = pdo_fetchcolumn('select count(*) from '.tablename($this->tb_starter).' where pusher=:pusher and uniacid=:uniacid',$params);
        $help_count = pdo_fetchcolumn('select count(*) from '.tablename($this->tb_starter_help).' where pusher=:pusher and uniacid=:uniacid',$params);
        $config = m('common')->getSysset('lexin');
        $member['isopen'] = $config['zhongchou'];
        $merch = pdo_get("ewei_shop_merch_user",array('openid'=>$this->openid));
        //众筹已提现金额
        $sql = "SELECT SUM(money) AS money FROM ims_ewei_shop_raise_apply WHERE uniacid = :uniacid AND pusher = :pusher AND (status = 0 || status = 1)";
        $money = pdo_fetchcolumn($sql,$params);
        //已筹金额
        $sql = "SELECT SUM(refer_money) as refer_money FROM ".tablename($this->tb_starter)." WHERE uniacid = :uniacid AND pusher = :pusher AND `status` = 1";
        $refer_money = pdo_fetchcolumn($sql,$params);
        //可提现金额
        $sql .= " AND (refer_money >= target_money || :time >= aborttime)";
        $params[':time'] = time();
        $postal_money = pdo_fetchcolumn($sql,$params);
        $refer_money = $refer_money - $money <= 0 ?  0 : $refer_money - $money;
        $postal_money = $postal_money - $money <= 0 ? 0 : $postal_money - $money;
        app_json(array('member'=>$member,'starter_count'=>$starter_count,'join_count'=>$help_count,'merch'=>$merch,'refer_money'=>round($refer_money,2),'postal_money'=>round($postal_money,2),'raise_service'=>$config['raise_service']));
    }
    
    //发起众筹
    public function starter_post(){
        $this->deal_data();
        if(!$this->params['target_money']){
            app_error(1,'请输入目标金额');
        }
        if(!$this->params['category_id']){
            app_error(1,'请选择众筹类型');
        }
        if(!$this->params['title']){
            app_error(1,'请填写众筹标题');
        }
        if(!$this->params['video']){
            app_error(1,'请上传众筹视频');
        }
        if(!$this->params['content']){
            app_error(1,'请填写求助说明');
        }
        $this->params['thumbs'] =  m('common')->html_images($this->params['thumbs']);
        $thumbs = $this->params['thumbs'] ? json_decode($this->params['thumbs'],true) : array();
        $thumbs = !empty($thumbs) ? iserializer($thumbs) : '';
        pdo_insert($this->tb_starter,array(
            'uniacid'=>$this->uniacid,
            'category'=>intval($this->params['category_id']),
            'pusher'=>$this->openid,
            'title'=>strval($this->params['title']),
            'status'=>0,
            'video'=>strval($this->params['video']),
            'target_money'=>doubleval($this->params['target_money']),
            'refer_money'=>0,
            'content'=>strval($this->params['content']),
            'thumbs'=>$thumbs,
            'support_count'=>0,
            'verify_count'=>0,
            'createtime'=>time()
        ));
        $id = pdo_insertid();
        if($id > 0){
            app_json(array('id'=>$id,'message'=>'添加成功','thumbs'=>$thumbs,'thumb'=>$this->params['thumbs']));
        }else{
            app_error(1,'证实失败!');
        }
    }
    
    //获取众筹协议
    public function get_protocol(){
        $this->deal_data();
        $protocol = m('cache')->get('cache_protocol',$this->uniacid);
        if($protocol){
            app_json(array('protocol'=>$protocol));
        }else{
            app_error(1,'暂无数据!');
        }
    }
    
    //发起帮助请求
    public function help_post(){
        $this->deal_data();
        if(!$this->params['starter_id']){
            app_error(1,'当前柴火众筹信息不存在');
        }
        if(!$this->params['money']){
            app_error(1,'请输入帮助金额');
        }
        $data = array(
            'uniacid'=>$this->uniacid,
            'starter_id'=>intval($this->params['starter_id']),
            'pusher'=>$this->openid,
            'money'=>doubleval($this->params['money']),
            'status'=>0,
            'hearten'=>strval($this->params['hearten']),
            'createtime'=>time(),
            'ordersn' => m('common')->createNO('raise_starter_help', 'ordersn', 'RSH'),
        );
        // $exit_help = pdo_fetch("select * from".tablename($this->tb_starter_help)." where uniacid=:uniacid and starter_id=:starter_id and pusher=:openid and status = 0 ",array(':uniacid'=>$this->uniacid,':starter_id'=>intval($this->params['starter_id']),':openid'=>$this->openid));
        // if(empty($exit_help)){
        pdo_insert($this->tb_starter_help,$data);
        $id = pdo_insertid();
        // }else{
        //     $data['ordersn'] = $exit_help['ordersn'];
        //     pdo_update($this->tb_starter_help,$data,array('id'=>$exit_help['id']));
        //     $id = $exit_help['id'];
        // }
        // 支付日志
        $log = pdo_fetch('SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniacid`=:uniacid AND `module`=:module AND `tid`=:tid limit 1', array(':uniacid' => $this->uniacid, ':module' => 'ewei_shopv2', ':tid' => $data['ordersn']));
        if (!empty($log) && ($log['status'] != '0')) {
            app_error(AppError::$OrderAlreadyPay);
        }
        $member = pdo_get($this->tb_member,array('openid'=>$this->openid,'uniacid'=>$this->uniacid),array('uid','openid'));
        if (!empty($log) && ($log['status'] == '0')) {
            $res = pdo_update('core_paylog',array('fee'=>$data['money']), array('plid' => $log['plid']));
        }
        if (empty($log)) {
            $log = array('uniacid' => $this->uniacid, 'openid' => $member['uid'] ? $member['uid'] : $this->openid , 'module' => 'ewei_shopv2', 'tid' => $data['ordersn'], 'fee' => $data['money'], 'status' => 0 );
            pdo_insert('core_paylog', $log);
            $plid = pdo_insertid();
        }
        if($id > 0){
            app_json(array('id'=>$id,'message'=>'添加成功'));
        }else{
            app_error(1,'帮助失败!');
        }
    }
    
    //获取柴火众筹帮助人列表
    public function help_list(){
        $this->deal_data();
        $params['id'] = intval($this->params['id']);
        if(empty($params['id'])){
            app_error(1,'当前柴火众筹信息不存在');
        }
        $page = $this->params['page'] ? intval($this->params['page']) : 1;
        $help_list = pdo_fetchall('select h.id,money,hearten,m.nickname,m.avatar,h.createtime from '.tablename($this->tb_starter_help).' h inner join '.tablename($this->tb_member).' m on(h.pusher = m.openid) where starter_id=:id and h.status = 1 order by h.id desc limit '.(($page-1) * $this->psize).','.$this->psize,$params);
        if($help_list){
            app_json(array('help'=>$help_list));
        }else{
            app_error(1,'暂无帮助数据');
        }
    }
    
    //证实
    public function verify_post(){
        $this->deal_data();
        if(!$this->params['starter_id']){
            app_error(1,'当前柴火众筹信息不存在');
        }
        if(!$this->params['realname']){
            app_error(1,'请输入姓名');
        }
        if(!$this->params['card']){
            app_error(1,'请填写身份证');
        }
        if(!$this->params['relation_id']){
            app_error(1,'请选择关系');
        }
        if(!$this->params['intro']){
            app_error(1,'请证实具体详情');
        }
        $isExist = pdo_get($this->tb_starter_verify, array('pusher' => $this->openid,'uniacid' => $this->uniacid,'starter_id' => $this->params['starter_id']), array('id'));
        if(!empty($isExist)){
            app_error(1,'请不要重复证实');
        }
        pdo_insert($this->tb_starter_verify,array(
            'uniacid'=>$this->uniacid,
            'starter_id'=>intval($this->params['starter_id']),
            'pusher'=>$this->openid,
            'realname'=>strval($this->params['realname']),
            'card'=>strval($this->params['card']),
            'relation_id'=>intval($this->params['relation_id']),
            'intro'=>strval($this->params['intro']),
            'createtime'=>time()
        ));
        $id = pdo_insertid();
        if($id > 0){
            app_json(array('id'=>$id,'message'=>'添加成功'));
        }else{
            app_error(1,'证实失败!');
        }
    }
    
    //获取所有关系列表
    public function relation_list(){
        $this->deal_data();
        $relation_list = pdo_getall($this->tb_starter_relation,array('uniacid'=>$this->uniacid,'ifshow'=>1),array('id','relation'));
        if($relation_list){
            app_json(array('relation_list'=>$relation_list));
        }else{
            app_error(1,'暂无证实数据');
        }
    }
    
    //分页获取柴火众筹证实人列表
    public function verify_list(){
        $this->deal_data();
        $params['id'] = intval($this->params['id']);
        if(empty($params['id'])){
            app_error(1,'当前柴火众筹信息不存在');
        }
        $page = $this->params['page'] ? intval($this->params['page']) : 1;
        //$verify_list = pdo_fetchall('select v.id,v.realname,relation,intro,m.nickname,m.avatar from '.tablename($this->tb_starter_verify).' v inner join '.tablename($this->tb_starter_relation).' r on(v.relation_id = r.id) inner join '.tablename($this->tb_member).' m on(v.pusher = m.openid) where starter_id=:id order by v.id desc limit '.(($page-1) * $this->psize).','.$this->psize,$params);
        $verify_list = pdo_fetchall('select v.id,v.realname,relation,intro,m.nickname,m.avatar from '.tablename($this->tb_starter_verify).' v inner join '.tablename($this->tb_starter_relation).' r on(v.relation_id = r.id) inner join '.tablename($this->tb_member).' m on(v.pusher = m.openid) where starter_id=:id order by v.id desc',$params);
        $total = pdo_fetchcolumn('select count(*) from '.tablename($this->tb_starter_verify).' where starter_id=:id',$params);
        if($verify_list){
            app_json(array('verify_list'=>$verify_list,'total'=>$total));
        }else{
            app_error(1,'暂无证实数据');
        }
    }
    
    //获取柴火众筹证实人头像列表
    public function verify_avatar(){
        $this->deal_data();
        $params['id'] = intval($this->params['id']);
        if(empty($params['id'])){
            app_error(1,'当前柴火众筹信息不存在');
        }
        $verify_list = pdo_fetchall('select v.id,intro,m.avatar from '.tablename($this->tb_starter_verify).' v inner join '.tablename($this->tb_member).' m on(v.pusher = m.openid) where starter_id=:id order by v.id desc limit 0,6',$params);
        if($verify_list){
            app_json(array('verify_list'=>$verify_list));
        }else{
            app_error(1,'暂无证实数据');
        }
    }
    
    //获取柴火众筹详情
    public function get_starter(){
        $this->deal_data();
        $params['id'] = intval($this->params['id']);
        if(empty($params['id'])){
            app_error(1,'当前柴火众筹信息不存在1');
        }
        $starter = pdo_fetch('select s.id,s.pusher,title,category,video,m.nickname,avatar,support_count,verify_count,target_money,refer_money,s.status,thumbs,s.content,s.aborttime,s.audittime from '.tablename($this->tb_starter).' s inner join '.tablename($this->tb_member).' m on(s.pusher = m.openid) where s.id=:id limit 1',$params);
        if($starter){
            $starter['video'] = $starter['video'] ? tomedia($starter['video']) : '';
            $starter['thumbs'] = $starter['thumbs'] ? iunserializer($starter['thumbs']) : array();
            if($starter['thumbs']){
                foreach ($starter['thumbs'] as &$item){
                    $item = $item ? tomedia($item) : '';
                }
            }
            $starter['short_time'] = 0;
            if($starter['aborttime'] && $starter['audittime']){
                $short_time = intval($starter['aborttime'] - $starter['audittime']);
                $short_time = $short_time < 0 ? 0 : $short_time;
                $starter['short_time'] = intval($short_time / 86400);
            }
            $starter['merchid'] = pdo_fetchcolumn('select id from '.tablename($this->tb_merch).' where uniacid=:uniacid and openid=:openid and status =:status limit 1',array(':uniacid'=>$this->uniacid,':openid'=>$starter['pusher'],':status'=>1));
            app_json(array('starter'=>$starter));
        }else{
            app_error(1,'当前柴火众筹信息不存在2');
        }
    }
    
    //获取柴火众筹所有分类
    public function get_starter_category_all(){
        $this->deal_data();
        $category_list = pdo_getall($this->tb_pusher_category,array('uniacid'=>$this->uniacid,'passport'=>1),array('id','category'));
        $raise_set = m('common')->getSysset('lexin');
        $customer_service_number = $raise_set['customer_service_number'];       //客服电话
        if($category_list){
            app_json(array('category_list'=>$category_list,'customer_service_number'=>$customer_service_number));
        }else{
            app_error(1,'暂无数据');
        }
    }
    
    //分页显示柴火众筹列表
    public function starter_list(){
        $this->deal_data();
        $condition = ' where s.uniacid =:uniacid and s.status = 1 and s.del != 1 and s.aborttime > :time and s.refer_money < s.target_money';
        $params = array(':uniacid'=>$this->uniacid,':time'=>time());
        $category = intval($this->params['category']);
        if($category){
            $condition .= ' and s.category = :category';
            $params[':category'] = $category;
        }
        $page = $this->params['page'] ? intval($this->params['page']) : 1;
        $starter_list = pdo_fetchall('select s.id,title,thumbs,video,m.nickname,avatar,support_count,verify_count,target_money,refer_money,s.status from '.tablename($this->tb_starter).' s inner join '.tablename($this->tb_member).' m on(s.pusher = m.openid)'.$condition.' order by s.id desc limit '.(($page-1) * $this->psize).','.$this->psize,$params);

        if($starter_list){
            foreach ($starter_list as &$item){
                $item['video'] = $item['video'] ? tomedia($item['video']) : '';
                $thumbs = unserialize($item['thumbs']);
                $item['thumb'] = $thumbs[0];
                $total = pdo_fetchcolumn('select count(*) from '.tablename($this->tb_starter_verify).' where starter_id=:id',[':id' => $item['id']]);
                $item['verify_count'] = $total;         //证实总数
            }
            app_json(array('starter_list'=>$starter_list));
        }else{
            //app_error(1,'暂无数据');
            $result = array(
                'category' => $category,
                'starter_list' => $starter_list,
                'condition' => $condition,
                'params' => $params,
                'sql' => 'select s.id,title,video,m.nickname,avatar,support_count,verify_count,target_money,refer_money,s.status from '.tablename($this->tb_starter).' s inner join '.tablename($this->tb_member).' m on(s.pusher = m.openid)'.$condition.' order by s.id desc ',
            );
            app_error(1,$result);
        }
    }
    
    //获取创匠众推详情
    public function get_pusher(){
        $this->deal_data();
        $params['id'] = intval($this->params['id']);
        if(empty($params['id'])){
            app_error(1,'当前众推信息不存在');
        }
        $pusher = pdo_fetch('select title,video,like_count,m.merchname AS nickname,m.logo AS avatar,p.content,p.createtime,p.like_openid,p.merchid from '.tablename($this->tb_pusher).' p inner join '.tablename($this->tb_merch).' m on(p.merchid = m.id) where p.id =:id limit 1',$params);
        if($pusher){
            $pusher['video'] = $pusher['video'] ? tomedia($pusher['video']) : '';
            $pusher['avatar'] = $pusher['avatar'] ? tomedia($pusher['avatar']) : '';
            //$pusher['createtime'] = $pusher['createtime'] ? date('Y.m.d',$pusher['createtime']) : ''; 

            $result = false;
            $openid_list = array();
            if($pusher['like_openid']){
                $openid_list = iunserializer($pusher['like_openid']);

                $index = is_array($openid_list) && in_array($this->openid, $openid_list);
                if($index){
                    $result = true;
                }
            }

            $pusher['isclick'] = $result;
            app_json(array('pusher'=>$pusher));
        }else{
            app_error(1,'当前众推信息不存在');
        }
    }
    
    //创匠众推点赞
    public function click_like_count(){
        $this->deal_data();
        if(empty($this->openid)){
            app_error(1,'当前用户不存在');
        }
        $params['id'] = intval($this->params['id']);
        if(empty($params['id'])){
            app_error(1,'当前众推信息不存在');
        }
        $pusher_info = pdo_fetch('select id,like_count,like_openid from '.tablename($this->tb_pusher).' where id=:id limit 1',$params);
        if($pusher_info){
            $result = true;
            $openid_list = array();
            if($pusher_info['like_openid']){
                $openid_list = iunserializer($pusher_info['like_openid']);
                $index = is_array($openid_list) && array_keys($openid_list,$this->openid) ? array_keys($openid_list,$this->openid) :0;
                if($index){
                    $result = false;
                    unset($openid_list[$index[0]]);
                }
            }
            if($result){
                $openid_list[] = $this->openid;
            }
            $update_data['like_count'] = $result ? intval($pusher_info['like_count']) + 1 : intval($pusher_info['like_count']) - 1;
            $update_data['like_openid'] = iserializer($openid_list);
            $update_data['like_count'] = $update_data['like_count'] < 0 ? 0 : $update_data['like_count'];
            pdo_update($this->tb_pusher,$update_data,array('id'=>$params));
            app_json(array('status'=>$result,'message'=>'操作成功','like_count'=>$update_data['like_count']));
        }else{
            app_error(1,'当前众推信息不存在'); 
        }
    }
    
    //获取创匠众推所有分类
    public function get_pusher_category_all(){
        $this->deal_data();
        $category_list = pdo_getall($this->tb_pusher_category,array('uniacid'=>$this->uniacid,'passport'=>0),array('id','category'));
        if($category_list){
            app_json(array('category_list'=>$category_list));
        }else{
            app_error(1,'暂无数据');
        }
    }
    
    //分页获取创匠众推页面
    public function get_pusher_list(){
        $this->deal_data();
        $condition = ' where p.uniacid=:uniacid';
        $params = array(':uniacid'=>$this->uniacid);
        $category = intval($this->params['category']);
        if($category){
            $condition = ' and p.category = :category';
            $params[':category'] = $category;
        }
        $page = $this->params['page'] ? intval($this->params['page']) : 1;
        //$pusher_list = pdo_fetchall('select p.id,title,p.video,p.like_count,m.nickname,m.avatar from '.tablename($this->tb_pusher).' p inner join '.tablename($this->tb_member).' m on(p.pusher = m.openid) '.$condition.' order by p.like_count desc limit '.(($page-1) * $this->psize).','.$this->psize,$params);
        $pusher_list = pdo_fetchall('select p.id,title,p.video,p.like_count,m.merchname AS nickname,m.logo AS avatar from '.tablename($this->tb_pusher).' p inner join '.tablename($this->tb_merch).' m on(p.merchid = m.id) '.$condition.' order by p.id desc limit 20',$params);
        if($pusher_list){
            foreach ($pusher_list as &$item){
                $item['like_count'] = intval($item['like_count']);
                $item['video'] = $item['video'] ? tomedia($item['video']) : '';
                $item['like_count'] = $item['like_count'] < 0 ? 0 :$item['like_count'];
                $item['avatar'] = $item['avatar'] ? tomedia($item['avatar']) : '';
            }
            app_json(array('pusher_list'=>$pusher_list));
        }else{
            app_error(1,'暂无数据');
        }
    }
    
    //获取创匠众推幻灯片
    public function get_pusher_banner(){
        $this->deal_data();
        $banner_list = pdo_fetchall('select bannername,link,thumb from '.tablename($this->tb_pusher_banner).' where enabled =:enabled and uniacid=:uniacid order by displayorder desc ',array(':enabled'=>0,':uniacid'=>$this->uniacid));
        if($banner_list){
            foreach ($banner_list as &$item){
                $item['thumb'] = $item['thumb'] ? tomedia($item['thumb']) : '';
            }
            app_json(array('banner_list'=>$banner_list));
        }else{
            app_error(1,'暂无数据');
        }
    }
    
    //获取众筹首页详情
    public function main(){
        $this->deal_data();
        $index_info = pdo_get($this->tb_raise_index,array('uniacid'=>$this->uniacid));
        if($index_info){
            $index_info['banner_list'] = $index_info['banner_list'] ? iunserializer($index_info['banner_list']) : '';
            if($index_info['banner_list'] && is_array($index_info['banner_list'])){
                foreach ($index_info['banner_list'] as &$item){
                    $item = tomedia($item);
                }
            }
            $index_info['left_icon'] = $index_info['left_icon'] ? tomedia($index_info['left_icon']) : '';
            $index_info['center_icon'] = $index_info['center_icon'] ? tomedia($index_info['center_icon']) : '';
            $index_info['middle_icon'] = $index_info['middle_icon'] ? tomedia($index_info['middle_icon']) : '';
            $index_info['right_icon'] = $index_info['right_icon'] ? tomedia($index_info['right_icon']) : '';
            $index_info['video_url'] = $index_info['video_url'] ? tomedia($index_info['video_url']) : '';
            $config = m('common')->getSysset('lexin');
            if (empty($config['zhongchou'])) {
                unset($index_info['center_appid']);
                unset($index_info['center_icon']);
                unset($index_info['center_intro']);
                unset($index_info['center_name']);
                unset($index_info['center_path']);
            }
            app_json(array('index_info'=>$index_info));
        }else{
            app_error(1,'暂无数据');
        }
    }
    
    private function deal_data(){
        global $_W;global $_GPC;
        $this->uniacid = intval($_W['uniacid']);
        $this->openid = $_W['openid'];
        $this->params = $_GPC;
    }
    public function create(){
        $this->crateVideoImg(ATTACHMENT_ROOT.'/videos/51S888piCamj.mp4');
    }
    private function crateVideoImg($video_url){
        $movie = new ffmpeg_movie($video_url);
        echo 'admin';die;
        $frame = $movie->getFrame(1);
        $gd = $frame->toGDImage();
        $path = '/images/ewei_shop/' . $this->uniacid.'/'.rand(1000, 9999).'/';
        if (!is_dir(ATTACHMENT_ROOT . $path)) {
            mkdirs(ATTACHMENT_ROOT . $path);
        }
        $img=$path.date('YmdHis').".jpg";
        imagejpeg($gd, $img);
        imagedestroy($gd);
        print_r($img);
        return ATTACHMENT_ROOT.$img;
    }

    // 支付
    public function pay(){
        $this->deal_data();
        $set = m('common')->getSysset(array('shop', 'pay'));
        $wechat = array('success' => false);
        $help_id = intval($this->params['help_id']);
        if(empty($help_id)){
            app_error(0,"参数错误");
        }
        $order = pdo_fetch("select * from ".tablename($this->tb_starter_help)." where id =:id ",array(':id'=>$help_id));
        if (!empty($set['pay']['wxapp']) && (0 < $order['money']) && $this->iswxapp) {
            $tid = $order['ordersn'];
            $payinfo = array(
                'openid' => $this->openid,
                'title' => $set['shop']['name'] . '帮助',
                'tid' => $tid,
                'fee' => $order['money']
            );
            $res = $this->model->wxpay($payinfo, 18);
            if (!is_error($res)) {
                $wechat = array('success' => true, 'payinfo' => $res);
            }else {
                $wechat['payinfo'] = $res;
            }
        }
        app_json(
            array(
                'order'  => array('id' => $order['id'], 'ordersn' => $order['ordersn'], 'price' => $order['money'], 'title' => $set['shop']['name'] . '帮助'),
                'wechat' => $wechat
            )
        );
    }
    
    //推送模板消息
    private function send_templete($id,$money){
        $templete_id = 'LKk-HSw2lgXZpbDXn6GUmu6tXQrqk0FwJdWUsMEcXf8';
        $starter = pdo_fetch('select * from '.tablename($this->tb_starter).' where id =:sid ',array(':sid'=>$id)); 
        load()->classs('wxapp.account');
        if(!$this->account_api){
            $this->account_api = new \WxappAccount(array('key'=>'wx2c2d379a1806d40f','secret'=>'b45310cd75080f09c510d0cde468d3d0'));
        }
        $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=".$this->account_api->getAccessToken();
        $form_info = pdo_fetch('select id,form_id from '.tablename($this->tb_formid).' where openid =:openid and status = 0 order by id asc',array(':openid'=>$starter['pusher']));
        if($form_info){
            $data = array(
                'keyword1' => array(
                    "value"=> $money.'元',
                    "color"=> '#000'
                ),
                'keyword2' => array(
                    "value"=> date('Y年m月d日 H:i'),
                    "color"=> '#000'
                )
            );
            $post = array(
                "touser"      =>str_replace('sns_wa_', '', $starter['pusher']),
                "template_id" =>$templete_id,
                "page"        =>"pages/raise/pages/zhongchouitemdetail/zhongchouitemdetail?id=".$id,
                "form_id"     =>$form_info['form_id'],
                "data"        =>$data,
            );
            $post = json_encode($post);
            $result = $this->requestApi($url,$post,$this->account_api);
            pdo_update($this->tb_formid,array('status'=>1),array('id'=>$form_info['id']));
            return $result;
        }
    }
    
    private function requestApi($url, $post = '',$account_api) {
        $response = ihttp_request($url, $post);
        $result = @json_decode($response['content'], true);
        if(is_error($response)) {
            return error($result['errcode'], "访问公众平台接口失败, 错误详情: {$account_api->errorCode($result['errcode'])}");
        }
        if(empty($result)) {
            return $response;
        } elseif(!empty($result['errcode'])) {
            return error($result['errcode'], "访问公众平台接口失败, 错误: {$result['errmsg']},错误详情：{$account_api->errorCode($result['errcode'])}");
        }
        return $result;
    }

    // 验证短信验证码
    private function checkCode($mobile,$verifycode){
        global $_GPC,$_W;
        $this->deal_data();

        $params = array(':uniacid'=>$this->uniacid,':openid'=>$this->openid,'mobile'=>$mobile);
        $authcode = pdo_fetch("select id,createtime,status,code from ".tablename("ewei_shop_raise_codeauth")." where uniacid=:uniacid and openid=:openid and mobile=:mobile and status = 0 ",$params);

        if ( empty($authcode) || ($authcode['code'] !== $verifycode) || (($authcode['createtime'] + 1800) < time())) 
        {
            if( ($authcode['createtime'] + 600) < time() ){
                pdo_update("ewei_shop_raise_codeauth",array('status'=>-1),array('id'=>$authcode['id']));
            }
            return false;
        }
        return true;
    }

    // 发送验证码
    public function verifycode(){
        global $_GPC,$_W;
        $this->deal_data();

        $mobile = trim($_GPC['mobile']);
        if (empty($mobile)) 
        {
            app_error(1, '请输入手机号');
        }
        
        $params = array(':uniacid'=>$this->uniacid,':openid'=>$this->openid,'mobile'=>$mobile);
        $authcode = pdo_fetch("select createtime,status,code from ".tablename("ewei_shop_raise_codeauth")." where uniacid=:uniacid and openid=:openid and mobile=:mobile and status = 0 ",$params);

        if ( !empty($authcode['createtime']) ) 
        {
            if(time() < ($authcode['createtime'] + 60)){
               app_error(1, '请求频繁请稍后重试');
            }
        }

        if(empty($this->openid)){
            app_error(1, '当前用户不存在');
        }

        $code = random(4, true);

        $url = "http://cf.51welink.com/submitdata/service.asmx/g_Submit?";

        $config = m('common')->getSysset('lexin');

        $smsg = str_replace("[sign]", "【".$config['lexin_key']."】", $config['lexin_content']);
        $smsg = str_replace("[code]", $code , $smsg);
        
        $post_data = array(
            'sname'   => $config['lexin_username'],
            'spwd'    => $config['lexin_password'],
            'scorpid' => $config['lexin_notice'],
            'sprdid'  => $config['lexin_verify'],
            'sdst'    => $mobile,
            'smsg'    => $smsg,
        );
        load()->func('communication');
        $result = ihttp_post($url,$post_data);
        
        $obj = isimplexml_load_string($result['content'], 'SimpleXMLElement', LIBXML_NOCDATA);
        $result = json_decode(json_encode($obj), true);

        if ($result['State'] == 0){
            $data = array(
                'uniacid' => $this->uniacid,
                'openid' => $this->openid,
                'mobile' => $mobile,
                'code' => $code,
                'status' => 0,
                'createtime' => time()
            );
            pdo_update("ewei_shop_raise_codeauth",array('status'=>-1),array('mobile'=>$mobile,'status'=>0,'openid'=>$this->openid));
            pdo_insert("ewei_shop_raise_codeauth",$data);
            app_error(0, '短信发送成功');
        }
        app_error(1, '短信发送失败(' . $result['MsgState'] . ')');
    }
}