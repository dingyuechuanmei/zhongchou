<?php
if (!(defined('IN_IA'))) {
    exit('Access Denied');
}

class Report_cate_EweiShopV2Page extends PluginWebPage
{
    public $tb_report_cate = 'ewei_shop_raise_report_cate';
    
    /**
     * 举报类型列表
     */
    public function main(){
        global $_W,$_GPC;
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = ' where uniacid=:uniacid';
        $params = array(':uniacid' => $_W['uniacid']);
        if ($_GPC['enabled'] != '')
        {
            $condition .= ' and is_show=' . intval($_GPC['is_show']);
        }
        if (!(empty($_GPC['keyword'])))
        {
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $condition .= ' and category like :keyword ';
            $params[':keyword'] = '%' . $_GPC['keyword'] . '%';
        }
        $list = pdo_fetchall('select * from ' . tablename($this->tb_report_cate) .  $condition . '  order by id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize, $params);
        $total = pdo_fetchcolumn('select count(*) from ' . tablename($this->tb_report_cate) . $condition, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template();
    }
    
    /**
     * 处理举报类型数据请求
     */
    public function post(){
        global $_W,$_GPC;
        $id = intval($_GPC['id']);
        if ($_W['ispost']){
            $data = array(
                'uniacid' => $_W['uniacid'],
                'category' => trim($_GPC['category']),
            );
            if ($id){
                pdo_update($this->tb_report_cate, $data, array('id' => $id));
                plog('raise.report_cate.edit', '修改举报类型 ID: ' . $id);
            }else{
                $data['createtime'] = time();
                pdo_insert($this->tb_report_cate, $data);
                plog('raise.report_cate.add', '添加举报类型 ID: ' . pdo_insertid());
            }
            show_json(1, array('url' => webUrl('raise/report_cate')));
        }
        $item = pdo_fetch('select * from ' . tablename($this->tb_report_cate) . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $id, ':uniacid' => $_W['uniacid']));
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
        $items = pdo_fetchall('select id,category from ' . tablename($this->tb_report_cate) . ' where id in( ' . $id . ' ) and uniacid=' . $_W['uniacid']);
        foreach ($items as $item ){
            pdo_delete($this->tb_report_cate, array('id' => $item['id']));
            plog('raise.report_cate.delete', '删除举报类型 ID: ' . $item['id'] . ' 类型标题: ' . $item['category'] . ' ');
        }
        show_json(1, array('url' => referer()));
    }
    
}