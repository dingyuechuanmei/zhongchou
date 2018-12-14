<?php
if (!(defined('IN_IA'))) {
    exit('Access Denied');
}

class Forum_cate_EweiShopV2Page extends PluginWebPage
{
    public $tb_forum_cate = 'ewei_shop_raise_forum_cate';
    
    /**
     * 论坛类型列表
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
            $condition .= ' and (context like :keyword or title like :keyword)';
            $params[':keyword'] = '%' . $_GPC['keyword'] . '%';
        }
        $list = pdo_fetchall('select * from ' . tablename($this->tb_forum_cate) .  $condition . '  order by id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize, $params);
        foreach ($list as &$row) {
            $row['forum_count'] = pdo_fetchcolumn("SELECT count(*) FROM ".tablename('ewei_shop_raise_forum')." WHERE cate = :cate",array(':cate'=>$row['id']));
        }
        unset($row);
        $total = pdo_fetchcolumn('select count(*) from ' . tablename($this->tb_forum_cate) . $condition, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template();
    }
    
    /**
     * 处理论坛类型数据请求
     */
    public function post(){
        global $_W,$_GPC;
        $id = intval($_GPC['id']);
        if ($_W['ispost']){
            $data = array(
                'uniacid' => $_W['uniacid'],
                'title' => trim($_GPC['title']),
                'context' => trim($_GPC['context']),
                'ico' => save_media($_GPC['ico']),
                'thumb' => save_media($_GPC['thumb']),
                'is_show' => intval($_GPC['is_show'])
            );
            if ($id){
                pdo_update($this->tb_forum_cate, $data, array('id' => $id));
                plog('raise.forum_cate.edit', '修改论坛类型 ID: ' . $id);
            }else{
                $data['createtime'] = time();
                pdo_insert($this->tb_forum_cate, $data);
                plog('raise.forum_cate.add', '添加论坛类型 ID: ' . pdo_insertid());
            }
            show_json(1, array('url' => webUrl('raise/forum_cate')));
        }
        $item = pdo_fetch('select * from ' . tablename($this->tb_forum_cate) . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $id, ':uniacid' => $_W['uniacid']));
        include $this->template();
    }
    
    /**
     * 论坛类型显示状态修改
     */
    public function is_show(){
        global $_W; global $_GPC;
        $id = intval($_GPC['id']);
        if (empty($id))
        {
            $id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
        }
        $items = pdo_fetchall('select id,title from ' . tablename($this->tb_forum_cate) . ' where id in( ' . $id . ' ) and uniacid=' . $_W['uniacid']);
        foreach ($items as $item ){
            pdo_update($this->tb_forum_cate, array('is_show' => intval($_GPC['is_show'])), array('id' => $item['id']));
            plog('raise.forum_cate.edit', (('修改论坛类型显示状态<br/>ID: ' . $item['id'] . '<br/>类型标题: ' . $item['title'] . '<br/>状态: ' . $_GPC['is_show']) == 1 ? '显示' : '隐藏'));
        }
        show_json(1, array('url' => referer()));
    }
    
    /**
     * 论坛类型删除
     */
    public function delete(){
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);
        if (empty($id)){
            $id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
        }
        $items = pdo_fetchall('select id,title from ' . tablename($this->tb_forum_cate) . ' where id in( ' . $id . ' ) and uniacid=' . $_W['uniacid']);
        foreach ($items as $item ){
            pdo_delete($this->tb_forum_cate, array('id' => $item['id']));
            plog('raise.forum_cate.delete', '删除论坛类型 ID: ' . $item['id'] . ' 类型标题: ' . $item['title'] . ' ');
        }
        show_json(1, array('url' => referer()));
    }
    
}