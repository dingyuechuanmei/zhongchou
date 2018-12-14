<?php
if (!(defined('IN_IA'))) {
    exit('Access Denied');
}

class Verify_EweiShopV2Page extends PluginWebPage
{
    public $tb_starter = 'ewei_shop_raise_starter';
    
    public $tb_starter_verify = 'ewei_shop_raise_starter_verify';
    
    public $tb_starter_relation = 'ewei_shop_raise_starter_relation';
    
    public $tb_member = 'ewei_shop_member';
    
    public function main()
    {
        global $_W,$_GPC;
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = ' where v.uniacid=:uniacid';
        $params = array(':uniacid' => $_W['uniacid']);
        if ($_GPC['keyword']){
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $condition .= ' and (s.title like :keyword1 or m.nickname like :keyword2 or v.realname like :keyword3)';
            $params[':keyword1'] = $params[':keyword2'] = $params[':keyword3'] = '%' . $_GPC['keyword'] . '%';
        }
        if($_GPC['realname'] !=''){
            $realname = intval($_GPC['realname']);
            if($realname == 0){
                $condition .= ' and v.realname =\'\'';
            }else{
                $condition .= ' and v.realname !=\'\'';
            }
        }
        if($_GPC['starter_id'] !=''){
            $condition .= ' and v.starter_id = '.intval($_GPC['starter_id']);
        }
        if($_GPC['r_id'] !=''){
            $condition .= ' and v.relation_id = '.intval($_GPC['r_id']);
        }
        if($_GPC['time'] && is_array($_GPC['time'])){
            if($_GPC['time']['start'] && $_GPC['time']['end']){
                $starttime = strtotime($_GPC['time']['start']);
                $endtime = strtotime($_GPC['time']['end']);
                $condition .=' and (v.createtime between '.$starttime.' and '.$endtime.' ) ';
            }
        }
        $sql = 'select v.id,s.id sid,s.title,m.nickname,m.id mid,m.avatar,v.relation_id rid,v.realname,v.card,v.intro,v.createtime,l.relation from '.tablename($this->tb_starter_verify).' v inner join '.tablename($this->tb_starter).' s on(s.id = v.starter_id) inner join '.tablename($this->tb_member).' m on(m.openid = v.pusher) left join '.tablename($this->tb_starter_relation).' l on(l.id = v.relation_id)';
        $list = pdo_fetchall($sql . $condition . ' order by v.id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize, $params);
        $total = pdo_fetchcolumn('select count(*) from '.tablename($this->tb_starter_verify).' v inner join '.tablename($this->tb_starter).' s on(s.id = v.starter_id) inner join '.tablename($this->tb_member).' m on(m.openid = v.pusher) left join '.tablename($this->tb_starter_relation).' l on(l.id = v.relation_id)' . $condition, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template();
    }
}
?>