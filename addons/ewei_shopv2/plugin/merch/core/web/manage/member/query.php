<?php
if (!(defined('IN_IA'))) 
{
	exit('Access Denied');
}
require EWEI_SHOPV2_PLUGIN . 'merch/core/inc/page_merch.php';
class Query_EweiShopV2Page extends MerchWebPage 
{
	public function main() 
	{
		global $_W;
		global $_GPC;
		$kwd = trim($_GPC['keyword']);
		$params = array();
		$params[':uniacid'] = $_W['uniacid'];
		$condition = ' and uniacid=:uniacid';
		if (!(empty($kwd))) 
		{
			$condition .= ' AND (`realname` LIKE :keyword or `nickname` LIKE :keyword or `mobile` LIKE :keyword)';
			$params[':keyword'] = '%' . $kwd . '%';
		}
		$ds = pdo_fetchall('SELECT * FROM ' . tablename('ewei_shop_member') . ' WHERE 1 ' . $condition . ' order by id asc', $params);

		//  查找公众号粉丝 start
		if($_W['uniacid'] == 61){
			$condition = ' and uniacid=:uniacid ';
			if (!empty($kwd)) 
			{
				$condition .= ' AND (`openid` LIKE :keyword or `nickname` LIKE :keyword)';
				$params[':keyword'] = '%' . $kwd . '%';
			}

			$ds = pdo_fetchall('SELECT * FROM ' . tablename('mc_mapping_fans') . ' WHERE 1 ' . $condition . ' order by fanid asc', $params);
			foreach ($ds as $key => &$value) {
				$value['avatar'] = pdo_fetchcolumn("select avatar from ".tablename("mc_members")." where uniacid=:uniacid and uid =:uid ",array(':uniacid'=>$_W['uniacid'],':uid'=>$value['uid']));
				if(empty($value['avatar'])){
					$tag = unserialize(base64_decode($value['tag']));
					$value['avatar'] = $tag['headimgurl'];
				}
			}
			unset($value);
		}
		//  查找公众号粉丝 end


		if ($_GPC['suggest']) 
		{
			exit(json_encode(array('value' => $ds)));
		}
		include $this->template();
		exit();
	}
}
?>