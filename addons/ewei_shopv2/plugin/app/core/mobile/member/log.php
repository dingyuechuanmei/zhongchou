<?php
 
if (!defined('IN_IA')) {
	exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'app/core/page_mobile.php';
class Log_EweiShopV2Page extends AppMobilePage
{
	public function get_list()
	{
		global $_W;
		global $_GPC;
		$type = intval($_GPC['type']);
		$pindex = max(1, intval($_GPC['page']));
		$psize = 10;
		//充值记录&余额提现记录
		if($type < 2 ){
            $apply_type = array(0 => '微信钱包', 2 => '支付宝', 3 => '银行卡');
            $condition = ' and openid=:openid and uniacid=:uniacid and type=:type';
            $params = array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid'], ':type' => intval($_GPC['type']));
            $list = pdo_fetchall('select * from ' . tablename('ewei_shop_member_log') . ' where 1 ' . $condition . ' order by createtime desc LIMIT ' . (($pindex - 1) * $psize) . ',' . $psize, $params);
            $total = pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_member_log') . ' where 1 ' . $condition, $params);
            $newList = array();
            if (is_array($list) && !empty($list)) {
                foreach ($list as $row) {
                    $newList[] = array('id' => $row['id'], 'type' => $row['type'], 'money' => $row['money'], 'typestr' => $apply_type[$row['applytype']], 'status' => $row['status'], 'deductionmoney' => $row['deductionmoney'], 'realmoney' => $row['realmoney'], 'createtime' => date('Y-m-d H:i', $row['createtime']));
                }
            }
		} else {
            //众筹提现记录
            $condition = ' and pusher=:pusher and uniacid=:uniacid';
            $params = array(':uniacid' => $_W['uniacid'], ':pusher' => $_W['openid']);
            $newList = pdo_fetchall('select * from ' . tablename('ewei_shop_raise_apply') . ' where 1 ' . $condition . ' order by id desc LIMIT ' . (($pindex - 1) * $psize) . ',' . $psize, $params);
            $total = pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_raise_apply') . ' where 1 ' . $condition, $params);
            $status = ['待审核','已打款','拒绝打款','打款无效'];
            foreach ($newList as &$row) {
                $row['type'] = 2;
                $row['createtime'] = date('Y-m-d H:i:s',$row['createtime']);
			}
			unset($row);
		}
        app_json(array('list' => $newList, 'total' => $total, 'pagesize' => $psize, 'page' => $pindex, 'type' => $type, 'isopen' => $_W['shopset']['trade']['withdraw'], 'moneytext' => $_W['shopset']['trade']['moneytext']));
	}
}

?>
