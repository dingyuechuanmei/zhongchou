<?php
if (!(defined('IN_IA'))) {
    exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'merch/core/inc/page_merch.php';
class Pusher_EweiShopV2Page extends MerchWebPage 
{
    public $tb_pusher_banner = 'ewei_shop_raise_pusher_banner';
    public $tb_pusher = 'ewei_shop_raise_pusher';
    public $tb_pusher_category = 'ewei_shop_raise_category';
    
    public $tb_member = 'ewei_shop_member';
    public $uniacid;
    
    /**
     * 创匠众推首页
     */
    public function main(){
        global $_W,$_GPC;
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = ' where p.uniacid=:uniacid and merchid =:merchid ';
        $params = array(':uniacid' => $_W['uniacid'],':merchid'=>$_W['merchid']);
        if($_GPC['category'] !=''){
            $condition .= ' and p.category = ' . intval($_GPC['category']);
        }
        if ($_GPC['ifshow'] !=''){
            $condition .= ' and p.ifshow = ' . intval($_GPC['ifshow']);
        }
        if ($_GPC['keyword']){
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $condition .= ' and (title like :keyword1)';
            $params[':keyword1'] = '%' . $_GPC['keyword'] . '%';
        }
        $sql = 'select p.id,p.title,p.pusher,p.like_count,p.ifshow,p.createtime from '.tablename($this->tb_pusher).' p ';
        $list = pdo_fetchall($sql . $condition . ' order by id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize, $params);
        $total = pdo_fetchcolumn('select count(*) from '.tablename($this->tb_pusher).' p ' . $condition, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template();
    }
    
    /**
     * 创匠众推添加
     */
    public function add(){
        $this->post();
    }
    
    /**
     * 创匠众推编辑
     */
    public function edit(){
        $this->post();
    }
    
    /**
     * 创匠众推数据处理
     */
    protected function post(){
        global $_W,$_GPC;
        $id = intval($_GPC['id']);
        if ($_W['ispost']){
            $data = array(
                'uniacid' => $_W['uniacid'],
                'title' => trim($_GPC['title']),
                //'pusher' => $_GPC['pusher'],
                'category' => intval($_GPC['category']),
                'video' => $_GPC['video'],
                'shop_url' => $_GPC['shop_url'],
                'like_count' => 0,
                'ifshow' => intval($_GPC['ifshow']),
                'merchid' => intval($_W['merchid']),
                'content' => trim($_GPC['content']),
            );
            if ($id){
                pdo_update($this->tb_pusher, $data, array('id' => $id));
                plog('raise.pusher.edit', '修改众推ID: ' . $id);
            }
            else{
                $data['createtime'] = time();
                pdo_insert($this->tb_pusher, $data);
                plog('raise.pusher.add', '添加众推 ID: ' . pdo_insertid());
            }
            show_json(1, array('url' => webUrl('raise/pusher')));
        }
        $pusher = pdo_fetch('select * from ' . tablename($this->tb_pusher) . ' where id=:id limit 1', array(':id' => $id));
        if($pusher){
            $push_arr = pdo_fetch('select id,nickname,openid,realname,avatar from '.tablename($this->tb_member).' where openid =:openid ',array(':openid'=>$pusher['pusher']));
        }
        $category = pdo_fetchall('select id,category from '.tablename($this->tb_pusher_category).' where uniacid=:uniacid ',array(':uniacid'=>$_W['uniacid']));
        include $this->template();
    }
    
    /**
     * 更改众推状态
     */
    public function ifshow(){
        global $_W; global $_GPC;
        $id = intval($_GPC['id']);
        if (empty($id))
        {
            $id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
        }
        $items = pdo_fetchall('select id,title from ' . tablename($this->tb_pusher) . ' where id in( ' . $id . ' ) and uniacid=' . $_W['uniacid']);
        foreach ($items as $item )
        {
            pdo_update($this->tb_pusher, array('ifshow' => intval($_GPC['ifshow'])), array('id' => $item['id']));
            plog('raise.pusher.edit', (('修改众推状态<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['title'] . '<br/>状态: ' . $_GPC['ifshow']) == 1 ? '显示' : '隐藏'));
        }
        show_json(1, array('url' => referer()));
    }
    
    /**
     * 创匠众推删除
     */
    public function delete(){
        global $_W;global $_GPC;
        $id = intval($_GPC['id']);
        if (empty($id))
        {
            $id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
        }
        $items = pdo_fetchall('select id,title from ' . tablename($this->tb_pusher) . ' where id in( ' . $id . ' ) and uniacid=' . $_W['uniacid']);
        foreach ($items as $item )
        {
            pdo_delete($this->tb_pusher, array('id' => $item['id']));
            plog('raise.pusher.delete', '删除众推横幅 ID: ' . $item['id'] . ' 标题: ' . $item['title'] . ' ');
        }
        show_json(1, array('url' => referer()));
    }
    
    /**
     * 创匠众推分类
     */
    public function category(){
        global $_W,$_GPC;
        $list = array(
            array('id' => 'default', 'category' => '无类型', 'pushercount' => pdo_fetchcolumn('select count(*) from ' . tablename($this->tb_pusher) . ' where uniacid=:uniacid and category=0 limit 1', array(':uniacid' => $_W['uniacid'])))
        );
        $condition = ' where passport = 0 and uniacid=:uniacid';
        $params = array(':uniacid' => $_W['uniacid']);
        if (!empty($_GPC['keyword'])) {
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $condition .= ' and ( category like :category)';
            $params[':category'] = '%' . $_GPC['keyword'] . '%';
        }
        $alllist = pdo_fetchall('select id,category from ' . tablename($this->tb_pusher_category) . $condition . ' order by id asc', $params);
        foreach ($alllist as &$row ) {
            $row['pushercount'] = pdo_fetchcolumn('select count(*) from ' . tablename($this->tb_pusher) . ' where uniacid=:uniacid and category=:category limit 1', array(':uniacid' => $_W['uniacid'], ':category' => $row['id']));
        }
        unset($row);
        $list = array_merge($list, $alllist);
        include $this->template();
    }
    
    /**
     * 添加创匠众推分类
     */
    public function categoryadd(){
        $this->categorypost();
    }
    
    /**
     * 编辑创匠众推分类
     */
    public function categoryedit(){
        $this->categorypost();
    }
    
    /**
     * 分类数据处理
     */
    public function categorypost(){
        global $_W;global $_GPC;
        $id = intval($_GPC['id']);
        $category = pdo_fetch('select * from  ' . tablename($this->tb_pusher_category) . ' where id =:id limit 1', array(':id' => $id));
        if ($_W['ispost']) {
            $data = array('uniacid' => $_W['uniacid'], 'category' => trim($_GPC['category']),'passport'=>0);
            if ($id){
                pdo_update($this->tb_pusher_category, $data, array('id' => $id));
                plog('raise.pusher.categoryedit', '修改众推分类 ID: ' . $id);
            }
            else {
                pdo_insert($this->tb_pusher_category, $data);
                $id = pdo_insertid();
                plog('raise.pusher.categoryadd', '添加众推分类  ID: ' . $id);
            }
            show_json(1, array('url' => webUrl('raise/pusher/category')));
        }
        include $this->template('raise/pusher/categorypost');
    }
    
    /**
     * 众推分类删除
     */
    public function categorydelete(){
        global $_W;global $_GPC;
        $id = intval($_GPC['id']);
        if (empty($id)) {
            $id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
        }
        $items = pdo_fetchall('select id,category FROM ' . tablename($this->tb_pusher_category) . ' where id in( ' . $id . ' ) and uniacid=' . $_W['uniacid']);
        foreach ($items as $item ) {
            pdo_update($this->tb_pusher, array('category' => 0), array('category' => $item['id'],'uniacid' => $_W['uniacid']));
            pdo_delete($this->tb_pusher_category, array('id' => $item['id']));
            plog('raise.pusher.categorydelete', '删除众推类型 ID: ' . $item['id'] . ' 名称: ' . $item['category'] . ' ');
        }
        show_json(1, array('url' => referer()));
    }
    
    /**
     * 创匠众推横幅
     */
    public function banner(){
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
        $list = pdo_fetchall('select * from ' . tablename($this->tb_pusher_banner) .  $condition . '  order by displayorder desc limit ' . (($pindex - 1) * $psize) . ',' . $psize, $params);
        $total = pdo_fetchcolumn('select count(*) from ' . tablename($this->tb_pusher_banner) . $condition, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template();
    }
    
    /**
     * 创匠众推横幅添加
     */
    public function banner_add(){
        $this->banner_post();
    }
    
    /**
     * 创匠众推横幅编辑
     */
    public function banner_edit(){
        $this->banner_post();
    }
    
    /**
     * 创匠众推横幅数据处理
     */
    protected function banner_post(){
        global $_W,$_GPC;
        $id = intval($_GPC['id']);
        if ($_W['ispost']){
            $data = array(
                'uniacid' => $_W['uniacid'], 
                'bannername' => trim($_GPC['bannername']), 
                'link' => trim($_GPC['link']), 
                'enabled' => intval($_GPC['enabled']), 
                'displayorder' => intval($_GPC['displayorder']), 
                'thumb' => save_media($_GPC['thumb'])
            );
            if ($id){
                pdo_update($this->tb_pusher_banner, $data, array('id' => $id));
                plog('raise.pusher.banner_edit', '修改众推横幅 ID: ' . $id);
            }
            else{
                pdo_insert($this->tb_pusher_banner, $data);
                plog('raise.pusher.banner_add', '添加众推横幅 ID: ' . pdo_insertid());
            }
            show_json(1, array('url' => webUrl('raise/pusher/banner')));
        }
        $item = pdo_fetch('select * from ' . tablename($this->tb_pusher_banner) . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $id, ':uniacid' => $_W['uniacid']));
        include $this->template('raise/pusher/banner_post');
    }
   
    /**
     * 创匠众推横幅删除
     */
    public function banner_delete(){
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);
        if (empty($id))
        {
            $id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
        }
        $items = pdo_fetchall('select id,bannername from ' . tablename($this->tb_pusher_banner) . ' where id in( ' . $id . ' ) and uniacid=' . $_W['uniacid']);
        foreach ($items as $item )
        {
            pdo_delete($this->tb_pusher_banner, array('id' => $item['id']));
            plog('raise.pusher.banner_delete', '删除众推横幅 ID: ' . $item['id'] . ' 标题: ' . $item['bannername'] . ' ');
        }
        show_json(1, array('url' => referer()));
    }
    
    /**
     * 创匠众推横幅排序
     */
    public function banner_displayorder(){
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);
        $displayorder = intval($_GPC['value']);
        $item = pdo_fetchall('select id,bannername from ' . tablename($this->tb_pusher_banner) . ' where id in( ' . $id . ' ) and uniacid=' . $_W['uniacid']);
        if (!(empty($item)))
        {
            pdo_update($this->tb_pusher_banner, array('displayorder' => $displayorder), array('id' => $id));
            plog('raise.pusher.banner_edit', '修改众推横幅排序 ID: ' . $item['id'] . ' 标题: ' . $item['bannername'] . ' 排序: ' . $displayorder . ' ');
        }
        show_json(1);
    }
    
    /**
     * 创匠众推横幅状态修改
     */
    public function banner_enabled(){
        global $_W; global $_GPC;
        $id = intval($_GPC['id']);
        if (empty($id))
        {
            $id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
        }
        $items = pdo_fetchall('select id,bannername from ' . tablename($this->tb_pusher_banner) . ' where id in( ' . $id . ' ) and uniacid=' . $_W['uniacid']);
        foreach ($items as $item )
        {
            pdo_update($this->tb_pusher_banner, array('enabled' => intval($_GPC['enabled'])), array('id' => $item['id']));
            plog('raise.pusher.banner_edit', (('修改众推横幅状态<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['bannername'] . '<br/>状态: ' . $_GPC['enabled']) == 1 ? '显示' : '隐藏'));
        }
        show_json(1, array('url' => referer()));
    }
}
?>