<?php
if (!(defined('IN_IA'))) 
{
	exit('Access Denied');
}
class Log_EweiShopV2Page extends PluginWebPage 
{
	public function main() 
	{
		global $_W;
		global $_GPC;
		$pindex = max(1, intval($_GPC['page']));
		$psize = 10;
		$params = array(':uniacid' => $_W['uniacid']);
		$condition = ' and log.uniacid=:uniacid  and log.luckdraw_id=' . intval($_GPC['id']);
		$keyword = trim($_GPC['keyword']);
		if (!(empty($keyword))) 
		{
			$condition .= ' AND ( m.nickname LIKE :keyword or m.realname LIKE :keyword or m.mobile LIKE :keyword ) ';
			$params[':keyword'] = '%' . $keyword . '%';
		}
		if (!(empty($_GPC['time']['start'])) && !(empty($_GPC['time']['end']))) 
		{
			$starttime = strtotime($_GPC['time']['start']);
			$endtime = strtotime($_GPC['time']['end']);
			$condition .= ' AND log.addtime >= :starttime AND log.addtime <= :endtime ';
			$params[':starttime'] = $starttime;
			$params[':endtime'] = $endtime;
		}
		$list = pdo_fetchall('SELECT log.*, m.avatar,m.nickname,m.realname,m.mobile FROM ' . tablename('ewei_shop_luckdraw_log') . ' log ' . ' left join ' . tablename('ewei_shop_member') . ' m on m.openid = log.join_user  and m.uniacid = log.uniacid' . ' WHERE 1 ' . $condition . ' ORDER BY log.addtime desc ' . '  LIMIT ' . (($pindex - 1) * $psize) . ',' . $psize, $params);
		$total = pdo_fetchcolumn('SELECT count(*)  FROM ' . tablename('ewei_shop_luckdraw_log') . ' log ' . ' left join ' . tablename('ewei_shop_member') . ' m on m.openid = log.join_user  and m.uniacid = log.uniacid' . ' where 1 ' . $condition . '  ', $params);
		$pager = pagination($total, $pindex, $psize);
		load()->func('tpl');
		include $this->template();
	}

	public function lottery_complain($reward)
	{
		if (isset($reward['credit']))
		{
			return '积分:' . $reward['credit'];
		}
		else if (isset($reward['money']))
		{
			return '奖金:' . $reward['money']['num'] . '元';
		}
		else if (isset($reward['bribery']))
		{
			return '红包:' . $reward['bribery']['num'] . '元';
		}
		else if (isset($reward['goods']))
		{
			foreach ($reward['goods'] as $k => $v )
			{
				$total = $v['total'];
				break;
			}
			return '特惠商品:' . $total . '个';
		}
		else if (isset($reward['coupon']))
		{
			foreach($reward['coupon'] as $v) {
				return '优惠券:' . $v['couponnum'] . '张';
			}

		} else {
			return false;
		}
	}
}
?>