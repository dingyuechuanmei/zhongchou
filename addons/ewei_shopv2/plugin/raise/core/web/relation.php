<?php
if (!(defined('IN_IA'))) {
    exit('Access Denied');
}

class Relation_EweiShopV2Page extends PluginWebPage
{
    public $tb_starter_relation = 'ewei_shop_raise_starter_relation';
    
    public $tb_starter_verify = 'ewei_shop_raise_starter_verify';
    
    public $tb_member = 'ewei_shop_member';
    
    /**
     * 关系列表
     */
    public function main(){
        global $_W,$_GPC;
        $condition = ' where uniacid=:uniacid';
        $params = array(':uniacid' => $_W['uniacid']);
        if (!empty($_GPC['keyword'])) {
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $condition .= ' and relation like :relation';
            $params[':relation'] = '%' . $_GPC['keyword'] . '%';
        }
        if($_GPC['r_id'] !=''){
            $condition .= ' and id = '.intval($_GPC['r_id']);
        }
        $list = pdo_fetchall('select * from ' . tablename($this->tb_starter_relation) . $condition . ' order by id asc', $params);
        include $this->template();
    }
    
    /**
     * 添加创匠柴火众筹分类
     */
    public function add(){
        $this->post();
    }
    
    /**
     * 编辑创匠柴火众筹分类
     */
    public function edit(){
        $this->post();
    }
    
    /**
     * 分类数据处理
     */
    public function post(){
        global $_W;global $_GPC;
        $id = intval($_GPC['id']);
        $relation = pdo_fetch('select * from  ' . tablename($this->tb_starter_relation) . ' where id =:id limit 1', array(':id' => $id));
        if ($_W['ispost']) {
            $data = array(
                'uniacid' => $_W['uniacid'], 
                'relation' => trim($_GPC['relation']),
                'ifshow'=>intval($_GPC['ifshow'])
            );
            if ($id){
                pdo_update($this->tb_starter_relation, $data, array('id' => $id));
                plog('raise.starter.relation', '修改柴火众筹证实人关系 ID: ' . $id);
            }else {
                $data['createtime'] = time();
                pdo_insert($this->tb_starter_relation, $data);
                $id = pdo_insertid();
                plog('raise.starter.relation', '添加柴火众筹证实人关系  ID: ' . $id);
            }
            show_json(1, array('url' => webUrl('raise/relation')));
        }
        include $this->template('raise/relation/post');
    }
    
    /**
     * 更改柴火众筹证实人关系状态
     */
    public function ifshow(){
        global $_W; global $_GPC;
        $id = intval($_GPC['id']);
        if (empty($id))
        {
            $id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
        }
        $items = pdo_fetchall('select id,relation from ' . tablename($this->tb_starter_relation) . ' where id in( ' . $id . ' ) and uniacid=' . $_W['uniacid']);
        foreach ($items as $item )
        {
            pdo_update($this->tb_starter_relation, array('ifshow' => intval($_GPC['ifshow'])), array('id' => $item['id']));
            plog('raise.starter.relation', (('修改柴火众筹证实人关系状态<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['relation'] . '<br/>状态: ' . $_GPC['ifshow']) == 1 ? '显示' : '隐藏'));
        }
        show_json(1, array('url' => referer()));
    }
    
    /**
     * 柴火众筹证实人关系删除
     */
    public function delete(){
        global $_W;global $_GPC;
        $id = intval($_GPC['id']);
        if (empty($id)) {
            $id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
        }
        $items = pdo_fetchall('select id,relation from ' . tablename($this->tb_starter_relation) . ' where id in( ' . $id . ' ) and uniacid=' . $_W['uniacid']);
        foreach ($items as $item ) {
            pdo_update($this->tb_starter_verify, array('relation' => 0), array('relation' => $item['id'],'uniacid' => $_W['uniacid']));
            pdo_delete($this->tb_starter_relation, array('id' => $item['id']));
            plog('raise.starter.relation.delete', '删除柴火众筹类型 ID: ' . $item['id'] . ' 名称: ' . $item['relation'] . ' ');
        }
        show_json(1, array('url' => referer()));
    }
}
?>