<?php
if (!(defined('IN_IA'))) {
    exit('Access Denied');
}

class Streamer_EweiShopV2Page extends PluginWebPage
{
    
    public $tb_forum_banner = 'ewei_shop_raise_forum_banner';
    
    /**
     * 众筹论坛横幅
     */
    public function main(){
        global $_W,$_GPC;
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = ' where uniacid=:uniacid';
        $params = array(':uniacid' => $_W['uniacid']);
        if ($_GPC['enabled'] != '')
        {
            $condition .= ' and enabled=' . intval($_GPC['enabled']);
        }
        if (!(empty($_GPC['keyword'])))
        {
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $condition .= ' and bannername  like :keyword';
            $params[':keyword'] = '%' . $_GPC['keyword'] . '%';
        }
        $list = pdo_fetchall('select * from ' . tablename($this->tb_forum_banner) .  $condition . '  order by displayorder desc limit ' . (($pindex - 1) * $psize) . ',' . $psize, $params);
        $total = pdo_fetchcolumn('select count(*) from ' . tablename($this->tb_forum_banner) . $condition, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template();
    }
    
    /**
     * 众筹论坛横幅添加
     */
    public function add(){
        $this->post();
    }
    
    /**
     * 众筹论坛横幅编辑
     */
    public function edit(){
        $this->post();
    }
    
    /**
     * 众筹论坛横幅数据处理
     */
    protected function post(){
        global $_W,$_GPC;
        $id = intval($_GPC['id']);
        if ($_W['ispost']){
            $data = array(
                'uniacid' => $_W['uniacid'],
                'bannername' => trim($_GPC['bannername']),
                'link' => trim($_GPC['link']),
                'enabled' => intval($_GPC['enabled']),
                'displayorder' => intval($_GPC['displayorder']),
                'thumb' => tomedia(save_media($_GPC['thumb']))
            );
            if ($id){
                pdo_update($this->tb_forum_banner, $data, array('id' => $id));
                plog('raise.pusher.edit', '修改论坛横幅 ID: ' . $id);
            }
            else{
                pdo_insert($this->tb_forum_banner, $data);
                plog('raise.pusher.add', '添加论坛横幅 ID: ' . pdo_insertid());
            }
            show_json(1, array('url' => webUrl('raise/streamer')));
        }
        $item = pdo_fetch('select * from ' . tablename($this->tb_forum_banner) . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $id, ':uniacid' => $_W['uniacid']));
        include $this->template('raise/streamer/post');
    }
     
    /**
     * 众筹论坛横幅删除
     */
    public function delete(){
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);
        if (empty($id))
        {
            $id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
        }
        $items = pdo_fetchall('select id,bannername from ' . tablename($this->tb_forum_banner) . ' where id in( ' . $id . ' ) and uniacid=' . $_W['uniacid']);
        foreach ($items as $item )
        {
            pdo_delete($this->tb_forum_banner, array('id' => $item['id']));
            plog('raise.streamer.delete', '删除论坛横幅 ID: ' . $item['id'] . ' 标题: ' . $item['bannername'] . ' ');
        }
        show_json(1, array('url' => referer()));
    }
    
    /**
     * 众筹论坛横幅排序
     */
    public function displayorder(){
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);
        $displayorder = intval($_GPC['value']);
        $item = pdo_fetchall('select id,bannername from ' . tablename($this->tb_forum_banner) . ' where id in( ' . $id . ' ) and uniacid=' . $_W['uniacid']);
        if (!(empty($item)))
        {
            pdo_update($this->tb_forum_banner, array('displayorder' => $displayorder), array('id' => $id));
            plog('raise.streamer.edit', '修改论坛横幅排序 ID: ' . $item['id'] . ' 标题: ' . $item['bannername'] . ' 排序: ' . $displayorder . ' ');
        }
        show_json(1);
    }
    
    /**
     * 众筹论坛横幅状态修改
     */
    public function enabled(){
        global $_W; global $_GPC;
        $id = intval($_GPC['id']);
        if (empty($id))
        {
            $id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
        }
        $items = pdo_fetchall('select id,bannername from ' . tablename($this->tb_forum_banner) . ' where id in( ' . $id . ' ) and uniacid=' . $_W['uniacid']);
        foreach ($items as $item )
        {
            pdo_update($this->tb_forum_banner, array('enabled' => intval($_GPC['enabled'])), array('id' => $item['id']));
            plog('raise.streamer.edit', (('修改论坛横幅状态<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['bannername'] . '<br/>状态: ' . $_GPC['enabled']) == 1 ? '显示' : '隐藏'));
        }
        show_json(1, array('url' => referer()));
    }
}