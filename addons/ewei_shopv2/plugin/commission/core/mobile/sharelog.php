<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
require EWEI_SHOPV2_PLUGIN . 'commission/core/page_login_mobile.php';
class Sharelog_EweiShopV2Page extends CommissionMobileLoginPage {
    public function main(){
        global $_W;
        global $_GPC;
        $openid = $_W['openid'];
        $member = m('member')->getMember($openid);
        include $this->template();
    }
    
    public function get_list(){
        global $_W, $_GPC;
        $log_list = pdo_fetchall("SELECT slg.id,mem.nickname,mem.avatar,slg.createtime,share_type FROM `ims_ewei_shop_share_log` slg LEFT JOIN `ims_ewei_shop_member` mem ON(slg.mid = mem.id) WHERE slg.uniacid = :uniacid AND sid = :sid",array(':uniacid'=>$_W['uniacid'],':sid'=>$member['id']));
        $logcount = count($log_list);
        $pindex = max(1, intval($_GPC['page']));
        $psize = 8;
        $list = array_slice($log_list, ($pindex - 1) * $psize, $psize);
        foreach ($list as $key=>&$val){
            $val['createtime'] =  date('H:i',$val['createtime']);
            if(empty($val['share_type'])){
                $val['showtext'] = $val['nickname'].'通过你分享的海报访问了'.$_W['account']['name'];
            }else{
                $val['showtext'] = $val['nickname'].'通过你分享的链接访问了'.$_W['account']['name'];
            }
        }
        show_json(1, array(
            'list' => $list,
            'pagesize' => $psize,
            'total' => $logcount
        ));
    }
}
?>