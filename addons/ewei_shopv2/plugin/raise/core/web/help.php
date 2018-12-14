<?php
if (!(defined('IN_IA'))) {
    exit('Access Denied');
}

class Help_EweiShopV2Page extends PluginWebPage
{
    public $tb_starter = 'ewei_shop_raise_starter';
    
    public $tb_starter_help = 'ewei_shop_raise_starter_help';
    
    public $tb_member = 'ewei_shop_member';
    
    public function main(){
        global $_W,$_GPC;
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = ' where h.uniacid=:uniacid';
        $params = array(':uniacid' => $_W['uniacid']);
        if($_GPC['starter_id'] !=''){
            $condition .= ' and h.starter_id = '.intval($_GPC['starter_id']);
        }
        if($_GPC['status'] !=''){
            $condition .= ' and h.status = '.intval($_GPC['status']);
        }
        if($_GPC['time'] && is_array($_GPC['time'])){
            if($_GPC['time']['start'] && $_GPC['time']['end']){
                $starttime = strtotime($_GPC['time']['start']);
                $endtime = strtotime($_GPC['time']['end']);
                $condition .=' and (h.createtime between '.$starttime.' and '.$endtime.' ) ';
            }
        }
        if ($_GPC['keyword']){
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $condition .= ' and (s.title like :keyword1 or m.nickname like :keyword2)';
            $params[':keyword1'] = $params[':keyword2'] = '%' . $_GPC['keyword'] . '%';
        }
        $sql = 'select h.id,s.title,s.id sid,m.id mid,m.avatar,m.nickname,m.mobile,h.money,h.status,h.hearten,h.createtime from '.tablename($this->tb_starter_help).' h inner join '.tablename($this->tb_starter).' s on(s.id = h.starter_id) inner join '.tablename($this->tb_member).' m on(m.openid = h.pusher)';
        $list = pdo_fetchall($sql . $condition . ' order by h.id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize, $params);
        $total = pdo_fetchcolumn('select count(*) from '.tablename($this->tb_starter_help).' h inner join '.tablename($this->tb_starter).' s on(s.id = h.starter_id) inner join '.tablename($this->tb_member).' m on(m.openid = h.pusher)' . $condition, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template();
    }
}
?>