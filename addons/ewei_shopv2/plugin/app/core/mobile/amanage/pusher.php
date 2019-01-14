<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'app/core/page_mobile.php';
class Pusher_EweiShopV2Page extends AppMobilePage
{
    protected $merchid = 0;

    public function __construct()
    {
        global $_GPC;
        $this->merchid = $_GPC['merchid'];
    }

    public function main()
    {

    }
    public function getlist()
    {
        global $_W;
        global $_GPC;


        $pindex = max(1, intval($_GPC['page']));
        $psize = 10;
        $list = array();

        $condition = ' WHERE g.`uniacid` = :uniacid and g.`merchid`=:merchid ';
        $params = array(':uniacid' => $_W['uniacid'],':merchid'=>$this->merchid);

        $keywords = trim($_GPC['keywords']);
        if ($keywords)
        {
            $condition .= ' AND (`title` LIKE :keywords OR `content` LIKE :keywords)';
            $params[':keywords'] = '%' . $keywords . '%';
        }

        $sql = 'SELECT count(g.id) FROM ' . tablename('ewei_shop_raise_pusher') . 'g' . $condition;
        $total = pdo_fetchcolumn($sql, $params);
        if (0 < $total)
        {
            $sql = 'SELECT g.* FROM ' . tablename('ewei_shop_raise_pusher') . 'g' . $condition . ' ORDER BY g.`like_count` DESC , g.`id` DESC LIMIT ' . (($pindex - 1) * $psize) . ',' . $psize;
            $list = pdo_fetchall($sql, $params);
        }

        foreach ($list as &$value) {
            $value['member'] = pdo_get("ewei_shop_member",array('openid'=>$value['pusher']),array('nickname','avatar'));
            $value['ifshowval'] = $value['ifshow'] == 1 ? '显示' : '隐藏';
            $value['video_cover'] = tomedia($value['video_cover']);
        }
        unset($value);
        $pageCount = ceil($total/$psize);
        show_json(1, array('total' => $total, 'list' => $list,'pageCount'=>$pageCount ));
    }

    public function status()
    {
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);
        $ids = trim($_GPC['ids']);
        if (empty($id))
        {
            if (!(empty($ids)) && strexists($ids, ','))
            {
                $id = $ids;
            }
        }
        $items = pdo_fetchall('SELECT id,title FROM ' . tablename('ewei_shop_raise_pusher') . ' WHERE id in( ' . $id . ' ) AND merchid='.$this->merchid.' AND uniacid=' . $_W['uniacid']);
        foreach ($items as $item )
        {
            pdo_update('ewei_shop_raise_pusher', array('ifshow' => intval($_GPC['status'])), array('id' => $item['id']));
            plog('goods.edit', (('修改众推状态<br/>ID: ' . $item['id'] . '<br/>众推名称: ' . $item['title'] . '<br/>状态: ' . $_GPC['status']) == 1 ? '显示' : '隐藏'));
        }
        show_json(1);
    }

    public function delete()
    {
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);
        $ids = trim($_GPC['ids']);
        if (empty($id))
        {
            if (!(empty($ids)) && strexists($ids, ','))
            {
                $id = $ids;
            }
        }
        $items = pdo_fetchall('SELECT id,title FROM ' . tablename('ewei_shop_raise_pusher') . ' WHERE id in( ' . $id . ' ) AND merchid='.$this->merchid.' AND uniacid=' . $_W['uniacid']);
        foreach ($items as $item )
        {
            pdo_delete('ewei_shop_raise_pusher',array('id'=>$item['id']));
            plog('goods.delete', '删除众推 ID: ' . $item['id'] . ' 众推名称: ' . $item['title'] . ' ');
        }
        show_json(1);
    }

    public function cate()
    {
        global $_W;
        $category = pdo_fetchall('select id,category from '.tablename("ewei_shop_raise_category").' where uniacid=:uniacid ',array(':uniacid'=>$_W['uniacid']));
        show_json(1,array('cate'=>$category));
    }

    public function detail()
    {
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);
        $merchid = intval($this->merchid);
        $uniacid = intval($_W['uniacid']);
        if (!(empty($id)))
        {
            $item = pdo_fetch('SELECT * FROM ' . tablename('ewei_shop_raise_pusher') . ' WHERE id = :id and uniacid = :uniacid and merchid=:merchid', array(':id' => $id, ':uniacid' => $_W['uniacid'],':merchid'=>$merchid));
            $item['video_url'] = tomedia($item['video']);
            $item['member'] = pdo_get("ewei_shop_member",array('openid'=>$item['pusher']),array('nickname','avatar'));
        }
        $member = pdo_getall("ewei_shop_member"," uniacid = {$uniacid} and nickname != '' ",array('id','nickname','openid'));
        show_json(1,array('item'=>$item));
    }

    public function post()
    {
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);
        $merchid = intval($this->merchid);
        $uniacid = intval($_W['uniacid']);
        $data = array(
            'uniacid' 	=> $_W['uniacid'],
            'title' 	=> trim($_GPC['title']),
            'pusher' 	=> $_GPC['pusher'],
            'category' 	=> intval($_GPC['category']),
            'video' => trim($_GPC['video']),
            'video_cover' => m('common')->getCoverImages($_GPC['video']),
            'shop_url' 	=> $_GPC['shop_url'],
            'like_count'=> 0,
            'ifshow' 	=> intval($_GPC['ifshow']),
            'merchid' 	=> $merchid,
            'content' 	=> trim($_GPC['content']),
        );
        if ($id){
            pdo_update("ewei_shop_raise_pusher", $data, array('id' => $id));
            plog('raise.pusher.edit', '修改众推ID: ' . $id);
        }else{
            $data['createtime'] = time();
            pdo_insert("ewei_shop_raise_pusher", $data);
            plog('raise.pusher.add', '添加众推 ID: ' . pdo_insertid());
        }
        show_json(1);
    }


    public function getCoverImages($fileUrl){
        $result = array();
        $filePath = '/data/wwwroot/zhongchouchuangke/attachment/'.$fileUrl;
        if(!empty($filePath)){
            if(is_file($filePath)){
                $result = $this->execCommandLine($filePath);
            }
        }
        return $result;
    }

    public function execCommandLine($file){
        $filename = '/data/wwwroot/zhongchouchuangke/attachment/images/73/2019/01/'.md5($file).'123.jpg';
        $command = "/usr/local/ffmpeg/bin/ffmpeg -i {$file} -ss 00:00:01 -f image2 -s 320x240 {$filename}";
        exec($command,$arr);
        return array('filename'=>$filename,'command'=>$command,'arr'=>$arr);
    }

}