<?php
if (!(defined('IN_IA'))) {
    exit('Access Denied');
}

class Report_EweiShopV2Page extends PluginWebPage
{
    public $tb_report = 'ewei_shop_raise_report';
    public $tb_report_cate = 'ewei_shop_raise_report_cate';
    
    public $tb_member = 'ewei_shop_member';
    
    /**
     * 举报类型列表
     */
    public function main(){
        global $_W,$_GPC;
        $pindex = max(1, intval($_GPC['page']));
        $psize = 5;
        $condition = ' where r.uniacid=:uniacid';
        $params = array(':uniacid' => $_W['uniacid']);
        if (!(empty($_GPC['keyword'])))
        {
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $condition .= ' and (c.category like :keyword or m.nickname like :keyword)';
            $params[':keyword'] = '%' . $_GPC['keyword'] . '%';
        }
        $list = pdo_fetchall('select r.id,r.type,r.detch_id,r.createtime,m.id mid,nickname,avatar,mobile,category from ' . tablename($this->tb_report).' r left join '.tablename($this->tb_member).' m on(r.openid = m.openid) left join '.tablename($this->tb_report_cate).' c on(c.id = r.cate) '.  $condition . '  order by r.id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize, $params);
        $total = pdo_fetchcolumn('select count(*) from ' . tablename($this->tb_report).' r left join '.tablename($this->tb_member).' m on(r.openid = m.openid) left join '.tablename($this->tb_report_cate).' c on(c.id = r.cate) '.  $condition, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template();
    }
    
    /**
     * 举报类型删除
     */
    public function delete(){
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);
        if (empty($id)){
            $id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
        }
        $items = pdo_fetchall('select id from ' . tablename($this->tb_report) . ' where id in( ' . $id . ' ) and uniacid=' . $_W['uniacid']);
        foreach ($items as $item ){
            pdo_delete($this->tb_report, array('id' => $item['id']));
            plog('raise.report.delete', '删除举报 ID: ' . $item['id']);
        }
        show_json(1, array('url' => referer()));
    }
    
}