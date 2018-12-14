<?php
if (!(defined('IN_IA'))) 
{
	exit('Access Denied');
}
class Statistics_EweiShopV2Page extends PluginWebPage 
{
    public $all;
    public $params;
    public $psize;
    public $uniacid;
    public $tb_integral = 'ewei_shop_integral';
    public $tb_member = 'ewei_shop_member';
    
    public function __construct(){
        global $_W; global $_GPC;
        $this->all = $_W;
        $this->params = $_GPC;
        $this->psize = 20;
        $this->uniacid = $_W['uniacid'];
    }
	public function main() 
	{
	    $pindex = max(1, intval($this->params['page']));
	    $sql = 'select i.id,m.id mid,i.openid,avatar,nickname,realname,mobile,refund_integral,remark,refund_time from '.tablename($this->tb_integral).' i inner join '.tablename($this->tb_member).' m on(i.openid = m.openid)';
	    $sql_total = 'select count(*) from '.tablename($this->tb_integral).' i inner join '.tablename($this->tb_member).' m on(i.openid = m.openid)';
	    $condition = ' where i.uniacid =:uniacid ';
	    $params = array(':uniacid'=>$this->uniacid);
	    if (empty($starttime) || empty($endtime)) {
	        $starttime = strtotime('-1 month');
	        $endtime = time();
	    }
	    if (is_array($this->params['time'])) {
	        $starttime = strtotime($this->params['time']['start']);
	        $endtime = strtotime($this->params['time']['end']);
	        $condition .= ' and c.' . refund_time . ' between :starttime and :endtime';
	        $params[':starttime'] = $starttime;
	        $params[':endtime'] = $endtime;
	    }
	    $keyword = trim($this->params['keyword']);
	    $searchfield = trim($this->params['searchfield']);
	    if(!empty($searchfield) && !empty($keyword)){
	        $searchfield = strtolower($searchfield);
            $condition.=' and m.'.$searchfield.' like "%'.$keyword.'%"';
	    }
	    $list = pdo_fetchall($sql.$condition.' order by i.id desc  limit '.(($pindex - 1) * $this->psize) . ',' . $this->psize,$params);
	    $total = pdo_fetchcolumn($sql_total.$condition,$params);
	    $pager = pagination($total, $pindex, $this->psize);
	    include $this->template();
	}
	
	public function detail(){
	    
	}
}
?>