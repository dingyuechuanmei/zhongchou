<?php
if (!(defined('IN_IA'))) 
{
	exit('Access Denied');
}
class Set_EweiShopV2Page extends PluginWebPage
{
    public function main(){
        $protocol = m('cache')->get('cache_protocol',$this->uniacid);
        $data = m('common')->getSysset('lexin');
        include $this->template();
    }
    
    public function post(){
        global $_W;global $_GPC;

        if($_W['ispost']){
            $data = ((is_array($_GPC['data']) ? $_GPC['data'] : array()));
            m('common')->updateSysset(array('lexin' => $data));
            if($_GPC['protocol']){
                m('cache')->set('cache_protocol',trim($_GPC['protocol']),$this->uniacid);
            }
            show_json(1, array('url' => referer()));
        }
        show_json(0,'操作失败!');
    }

}
?>