<?php
if (!(defined('IN_IA'))) {
    exit('Access Denied');
}

class Starter_EweiShopV2Page extends PluginWebPage
{
    public $tb_starter = 'ewei_shop_raise_starter';
    
    public $tb_starter_help = 'ewei_shop_raise_starter_help';
    public $tb_starter_verify = 'ewei_shop_raise_starter_verify';
    
    public $tb_starter_category = 'ewei_shop_raise_category';
    
    public $tb_member = 'ewei_shop_member';
    
    public function main()
    {
        
        global $_W,$_GPC;
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = ' where s.uniacid=:uniacid and s.isdel !=1 ';
        $params = array(':uniacid' => $_W['uniacid']);
        if($_GPC['starter_id'] !=''){
            $condition .= ' and s.id = '.intval($_GPC['starter_id']);
        }
        if($_GPC['category'] !=''){
            $condition .= ' and s.category = ' . intval($_GPC['category']);
        }
        if ($_GPC['status'] !=''){
            $condition .= ' and s.status = ' . intval($_GPC['status']);
        }
        if ($_GPC['keyword']){
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $condition .= ' and (s.title like :keyword1 or m.nickname like :keyword2 or c.category like :keyword3)';
            $params[':keyword1'] = $params[':keyword2'] = $params[':keyword3'] = '%' . $_GPC['keyword'] . '%';
        }
        $sql = 'select s.*,c.category category_name,m.id mid,m.nickname,m.realname,m.avatar,m.mobile from '.tablename($this->tb_starter).' s inner join '.tablename($this->tb_member).' m on(s.pusher = m.openid) inner join '.tablename($this->tb_starter_category).' c on(c.id = s.category)';
        $list = pdo_fetchall($sql . $condition . ' order by s.id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize, $params);
        $total = pdo_fetchcolumn('select count(*) from '.tablename($this->tb_starter).' s inner join '.tablename($this->tb_member).' m on(s.pusher = m.openid) inner join '.tablename($this->tb_starter_category).' c on(c.id = s.category)' . $condition, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template();
    }
    public function detail(){
        global $_W,$_GPC;
        $id = $_GPC['id'];
        $item = pdo_fetch('select s.*,c.category,m.nickname,m.realname,m.avatar,m.mobile from '.tablename($this->tb_starter).' s inner join '.tablename($this->tb_member).' m on(s.pusher = m.openid) inner join '.tablename($this->tb_starter_category).' c on(c.id = s.category) where s.id=:id',array(':id'=>$id));
        if($item){
            $item['video'] = $item['video'] ? tomedia($item['video']) : ''; 
            $item['thumbs'] = $item['thumbs'] ? iunserializer($item['thumbs']) : null;
            if($item['thumbs'] && is_array($item['thumbs'])){
                foreach ($item['thumbs'] as &$index){
                    $index = tomedia($index);
                }
            }
        }
        include $this->template();
    }
    public function audit(){
        global $_W,$_GPC;
        $id = $_GPC['id'];
        if($_W['ispost']){
            $time = time();
            $data['status'] = intval($_GPC['status']);
            if($data['status'] == 1){
                $data['audittime'] = $time;
                $data['aborttime'] = strtotime(trim($_GPC['aborttime']));
            }else{
                $data['rejecttime'] = $time;
                $data['rejectreason'] = trim($_GPC['rejectreason']);
            }
            pdo_update($this->tb_starter,$data,array('id'=>$id));
            show_json(1, array('url' => referer()));
        }
        $item = pdo_fetch('select id,title,content,status,aborttime,rejectreason from '.tablename($this->tb_starter).' where id =:id ',array(':id'=>$id));
        include $this->template();
    }
    
    /**
     * 创匠柴火众筹分类
     */
    public function category(){
        global $_W,$_GPC;
        $list = array(
            array('id' => 'default', 'category' => '无类型', 'startercount' => pdo_fetchcolumn('select count(*) from ' . tablename($this->tb_starter) . ' where uniacid=:uniacid and category = 0 limit 1', array(':uniacid' => $_W['uniacid'])))
        );
        $condition = ' where passport = 1 and uniacid=:uniacid';
        $params = array(':uniacid' => $_W['uniacid']);
        if (!empty($_GPC['keyword'])) {
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $condition .= ' and ( category like :category)';
            $params[':category'] = '%' . $_GPC['keyword'] . '%';
        }
        if($_GPC['cid']){
            $condition .= ' and id ='.intval($_GPC['cid']);
        }
        $alllist = pdo_fetchall('select id,category from ' . tablename($this->tb_starter_category) . $condition . ' order by id asc', $params);
        foreach ($alllist as &$row ) {
            $row['startercount'] = pdo_fetchcolumn('select count(*) from ' . tablename($this->tb_starter) . ' where uniacid=:uniacid and category=:category limit 1', array(':uniacid' => $_W['uniacid'], ':category' => $row['id']));
        }
        unset($row);
        $list = array_merge($list, $alllist);
        include $this->template();
    }
    
    /**
     * 添加创匠柴火众筹分类
     */
    public function categoryadd(){
        $this->categorypost();
    }
    
    /**
     * 编辑创匠柴火众筹分类
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
        $category = pdo_fetch('select * from  ' . tablename($this->tb_starter_category) . ' where id =:id limit 1', array(':id' => $id));
        if ($_W['ispost']) {
            $data = array('uniacid' => $_W['uniacid'], 'category' => trim($_GPC['category']),'passport'=>1);
            if ($id){
                pdo_update($this->tb_starter_category, $data, array('id' => $id));
                plog('raise.starter.categoryedit', '修改柴火众筹分类 ID: ' . $id);
            }
            else {
                pdo_insert($this->tb_starter_category, $data);
                $id = pdo_insertid();
                plog('raise.starter.categoryadd', '添加柴火众筹分类  ID: ' . $id);
            }
            show_json(1, array('url' => webUrl('raise/starter/category')));
        }
        include $this->template('raise/starter/categorypost');
    }
    
    /**
     * 柴火众筹分类删除
     */
    public function categorydelete(){
        global $_W;global $_GPC;
        $id = intval($_GPC['id']);
        if (empty($id)) {
            $id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
        }
        $items = pdo_fetchall('select id,category FROM ' . tablename($this->tb_starter_category) . ' where id in( ' . $id . ' ) and uniacid=' . $_W['uniacid']);
        foreach ($items as $item ) {
            pdo_update($this->tb_starter, array('category' => 0), array('category' => $item['id'],'uniacid' => $_W['uniacid']));
            pdo_delete($this->tb_starter_category, array('id' => $item['id']));
            plog('raise.starter.categorydelete', '删除柴火众筹类型 ID: ' . $item['id'] . ' 名称: ' . $item['category'] . ' ');
        }
        show_json(1, array('url' => referer()));
    }

    /**
     *
     */
    public function delete()
    {
        global $_W,$_GPC;
        $id = intval($_GPC['id']);
        $stater = pdo_fetch("SELECT * FROM ".tablename($this->tb_starter)." WHERE uniacid = :uniacid AND id = :id",array(':uniacid'=>$_W['uniacid'],':id'=>$id));
        if (empty($stater)) {
            show_json(0,'参数错误');
        }
        pdo_update($this->tb_starter,array('isdel'=>1,'deltime'=>time()),array('id'=>$id));
        show_json(1, array('url' => referer()));
    }
}
?>