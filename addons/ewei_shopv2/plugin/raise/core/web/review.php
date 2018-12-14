<?php
if (!(defined('IN_IA'))) {
    exit('Access Denied');
}

class Review_EweiShopV2Page extends PluginWebPage
{
    public $tb_forum = 'ewei_shop_raise_forum';
    public $tb_forum_review = 'ewei_shop_raise_forum_review';
    
    public $tb_member = 'ewei_shop_member';
    
    /**
     * 评论列表
     */
    public function main() {
        global $_W,$_GPC;
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = ' where r.uniacid=:uniacid';
        $params = array(':uniacid' => $_W['uniacid']);
        if($_GPC['r_id'] !=''){
            $condition .= ' and r.id=' . intval($_GPC['r_id']);
        }
        if($_GPC['reply_id'] !=''){
            $condition.=' and reply_id = '.intval($_GPC['reply_id']);
        }else{
            $condition.=' and reply_id = 0';
        }
        if (!(empty($_GPC['keyword']))){
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $condition .= ' and r.context like :keyword';
            $params[':keyword'] = '%' . $_GPC['keyword'] . '%';
        }
        $list = pdo_fetchall('select r.*,m.id mid,m.nickname,m.mobile,m.avatar,f.title,(select count(id) from '.tablename($this->tb_forum_review).' where reply_id = r.id) reply_count from '.tablename($this->tb_forum_review).' r left join '.tablename($this->tb_member).' m on(r.openid = m.openid) left join '.tablename($this->tb_forum).' f on(f.id = r.forum_id)' . $condition. '  order by r.id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize, $params);

        $total = pdo_fetchcolumn('select count(*) from ' . tablename($this->tb_forum_review).' r ' . $condition, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template();
    }
    
    /**
     * 删除评论
     */
    public function delete(){
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);
        if (empty($id)){
            $id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
        }
        $items = pdo_fetchall('select id from ' . tablename($this->tb_forum_review) . ' where id in( ' . $id . ' ) and uniacid=' . $_W['uniacid']);
        foreach ($items as $item ){
            pdo_delete($this->tb_forum_review, array('id' => $item['id']));
            plog('raise.forum_review.delete', '删除论坛评论 ID: ' . $item['id']);
        }
        show_json(1, array('url' => referer()));
    }
}