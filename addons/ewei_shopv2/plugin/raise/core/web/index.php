<?php
if (!(defined('IN_IA'))) {
    exit('Access Denied');
}

class Index_EweiShopV2Page extends PluginWebPage
{
    public $tb_raise_index = 'ewei_shop_raise_index';
    public $uniacid;
    public function main()
    {
        global $_W,$_GPC;
        $this->uniacid = $_W['uniacid'];
        $raise_index = pdo_fetch('select * from '.tablename($this->tb_raise_index).' where uniacid =:uniacid limit 1',array(':uniacid'=>$this->uniacid));
        if($_W['ispost']){
            $data = array(
                'uniacid'=>$this->uniacid,
                'banner_list'=>iserializer($_GPC['banner_list']),
                'left_icon'=>trim($_GPC['left_icon']),
                'left_name'=>trim($_GPC['left_name']),
                'left_intro'=>trim($_GPC['left_intro']),
                'left_path'=>trim($_GPC['left_path']),
                'left_appid'=>trim($_GPC['left_appid']),
                'right_icon'=>trim($_GPC['right_icon']),
                'middle_name'=>trim($_GPC['middle_name']),
                'middle_intro'=>trim($_GPC['middle_intro']),
                'middle_icon'=>trim($_GPC['middle_icon']),
                'middle_path'=>trim($_GPC['middle_path']),
                'middle_appid'=>trim($_GPC['middle_appid']),
                'center_name'=>trim($_GPC['center_name']),
                'center_intro'=>trim($_GPC['center_intro']),
                'center_icon'=>trim($_GPC['center_icon']),
                'center_path'=>trim($_GPC['center_path']),
                'center_appid'=>trim($_GPC['center_appid']),
                'right_name'=>trim($_GPC['right_name']),
                'right_intro'=>trim($_GPC['right_intro']),
                'right_path'=>trim($_GPC['right_path']),
                'right_appid'=>trim($_GPC['right_appid']),
                'video_url'=>trim($_GPC['video_url']),
                'raise_intro'=>m('common')->html_images($_GPC['raise_intro'])
            );
            if($raise_index){
                pdo_update($this->tb_raise_index,$data,array('id'=>$raise_index['id']));
            }else{
                pdo_insert($this->tb_raise_index,$data);
            }
            show_json(1, array('url' => referer()));
        }
        if($raise_index){
            $raise_index['banner_list'] = $raise_index['banner_list'] ? iunserializer($raise_index['banner_list']) : '';
        }
        include $this->template();
    }
}
?>