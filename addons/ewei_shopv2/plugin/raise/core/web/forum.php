<?php
if (!(defined('IN_IA'))) {
    exit('Access Denied');
}

class Forum_EweiShopV2Page extends PluginWebPage
{
    public $tb_forum = 'ewei_shop_raise_forum';
    public $tb_forum_cate = 'ewei_shop_raise_forum_cate';
    
    public $tb_member = 'ewei_shop_member';
    
    /**
     * 论坛列表
     */
    public function main(){
        global $_W,$_GPC;
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = ' where f.uniacid=:uniacid';
        $params = array(':uniacid' => $_W['uniacid']);
        if($_GPC['f_id'] !=''){
            $condition .= ' and f.id=' . intval($_GPC['f_id']);
        }
        if($_GPC['cate'] !=''){
            $condition .= ' and f.cate=' . intval($_GPC['cate']);
        }
        if ($_GPC['enabled'] != '')
        {
            $condition .= ' and f.is_show=' . intval($_GPC['is_show']);
        }
        if (!(empty($_GPC['keyword'])))
        {
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $condition .= ' and ( f.context like :keyword or f.title like :keyword)';
            $params[':keyword'] = '%' . $_GPC['keyword'] . '%';
        }
        $list = pdo_fetchall('select f.id,m.id mid, title,nickname,praise_list,view_count,review_count,is_top,is_show,f.createtime from ' . tablename($this->tb_forum).' f left join '.tablename($this->tb_member).' m on(f.openid = m.openid) ' .  $condition . '  order by id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize, $params);
        $total = pdo_fetchcolumn('select count(*) from ' . tablename($this->tb_forum).' f ' . $condition, $params);
        $pager = pagination($total, $pindex, $psize);
        foreach ($list as $key=>&$val){
            $temp = $val['praise_list'] ? iunserializer($val['praise_list']) : array();
            $val['praise_count'] = count($temp);
            unset($val['praise_list']);
        }
        include $this->template();
    }
    
    /**
     * 详情页
     */
    public function detail(){
        global $_W,$_GPC;
        $id = $_GPC['id'];
        $item = pdo_fetch('select f.*,c.title cate,m.nickname,m.realname,m.avatar,m.mobile from '.tablename($this->tb_forum).' f left join '.tablename($this->tb_member).' m on(f.openid = m.openid) left join '.tablename($this->tb_forum_cate).' c on(c.id = f.cate) where f.id=:id',array(':id'=>$id));
        if($item){
            $item['praise_list'] = $item['praise_list'] ? iunserializer($item['praise_list']) : array();
            $item['recom_list'] = $item['recom_list'] ? iunserializer($item['recom_list']) : array();
            $item['thumbs'] = $item['thumbs'] ? iunserializer($item['thumbs']) : array();
            $item['praise_count'] = count($item['praise_list']);
        }
        include $this->template();
    }
    
    /**
     * 论坛显示状态修改
     */
    public function is_show(){
        global $_W; global $_GPC;
        $id = intval($_GPC['id']);
        if (empty($id))
        {
            $id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
        }
        $items = pdo_fetchall('select id,title from ' . tablename($this->tb_forum) . ' where id in( ' . $id . ' ) and uniacid=' . $_W['uniacid']);
        foreach ($items as $item ){
            pdo_update($this->tb_forum, array('is_show' => intval($_GPC['is_show'])), array('id' => $item['id']));
            plog('raise.forum.is_show', (('修改论坛显示状态<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['title'] . '<br/>状态: ' . $_GPC['is_show']) == 1 ? '显示' : '隐藏'));
        }
        show_json(1, array('url' => referer()));
    }
    
    /**
     * 论坛置顶修改
     */
    public function is_top(){
        global $_W; global $_GPC;
        $id = intval($_GPC['id']);
        if (empty($id))
        {
            $id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
        }
        $items = pdo_fetchall('select id,title from ' . tablename($this->tb_forum) . ' where id in( ' . $id . ' ) and uniacid=' . $_W['uniacid']);
        foreach ($items as $item ){
            pdo_update($this->tb_forum, array('is_top' => intval($_GPC['is_top'])), array('id' => $item['id']));
            plog('raise.forum.is_top', (('修改论坛置顶<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['title'] . '<br/>状态: ' . $_GPC['is_top']) == 1 ? '置顶' : '正常'));
        }
        show_json(1, array('url' => referer()));
    }
    
    /**
     * 论坛删除
     */
    public function delete(){
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);
        if (empty($id)){
            $id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
        }
        $items = pdo_fetchall('select id,title from ' . tablename($this->tb_forum) . ' where id in( ' . $id . ' ) and uniacid=' . $_W['uniacid']);
        foreach ($items as $item ){
            pdo_delete($this->tb_forum, array('id' => $item['id']));
            plog('raise.forum_cate.delete', '删除论坛 ID: ' . $item['id'] . ' 标题: ' . $item['title'] . ' ');
        }
        show_json(1, array('url' => referer()));
    }
}