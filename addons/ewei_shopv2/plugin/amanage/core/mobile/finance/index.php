<?php
if (!(defined('IN_IA'))) 
{
	exit('Access Denied');
}
require EWEI_SHOPV2_PLUGIN . 'amanage/core/inc/page_amanage.php';
class Index_EweiShopV2Page extends AmanageMobilePage 
{
	public function main() 
	{
		global $_W;
		global $_GPC;
		$type = intval($_GPC['type']);
		if (($type == 0) && !(cv('finance.log.recharge'))) 
		{
			$this->message('您没有查看充值记录的权限');
		}
		else if (($type == 1) && !(cv('finance.log.withdraw'))) 
		{
			$this->message('您没有查看提现申请的权限');
		}
		else if (($type == 2) && !(cv('finance.credit.credit1'))) 
		{
			$this->message('您没有查看积分明细的权限');
		}
		else if (($type == 3) && !(cv('finance.credit.credit2'))) 
		{
			$this->message('您没有查看余额明细的权限');
		}
		include $this->template();
	}
	public function getlist() 
	{
		global $_W;
		global $_GPC;

		// type 0 可提现  1待审核  2待结算 3 已结算 4 已无效
		$type = intval($_GPC['type']);

		switch ($type) {
			case '0':
				$this->post();
				break;
			case '1':
				$applyData = $this->applyData(1, 'status1');
				break;
			case '2':
				$applyData = $this->applyData(2, 'status2');
				break;
			case '3':
				$applyData = $this->applyData(3, 'status3');
				break;
			case '4':
				$applyData = $this->applyData(-1, 'status_1');
				break;
		}

		$this->log($type);
	}

	protected function applyData($status, $st) 
	{
		global $_W;
		global $_GPC;
		empty($status) && ($status = 1);
		$merchid = $_W['merchid'];
		$apply_type = array(0 => '微信钱包', 2 => '支付宝', 3 => '银行卡');
		if ($st == 'main') 
		{
			$st = '';
		}
		else 
		{
			$st = '.' . $st;
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$condition = ' and b.uniacid=:uniacid and b.status=:status and b.merchid=:merchid';
		$params = array(':uniacid' => $_W['uniacid'], ':status' => $status, ':merchid' => $merchid);
		$keyword = trim($_GPC['keyword']);
		if (!(empty($keyword))) 
		{
			$condition .= ' and b.applyno like :keyword';
			$params[':keyword'] = '%' . $keyword . '%';
		}
		if (empty($starttime) || empty($endtime)) 
		{
			$starttime = strtotime('-1 month');
			$endtime = time();
		}
		$timetype = $_GPC['timetype'];
		if (!(empty($_GPC['timetype']))) 
		{
			$starttime = strtotime($_GPC['time']['start']);
			$endtime = strtotime($_GPC['time']['end']);
			if (!(empty($timetype))) 
			{
				$condition .= ' AND b.' . $timetype . ' >= :starttime AND b.' . $timetype . '  <= :endtime ';
				$params[':starttime'] = $starttime;
				$params[':endtime'] = $endtime;
			}
		}
		if (3 <= $status) 
		{
			$orderby = 'paytime';
		}
		else if (2 <= $status) 
		{
			$orderby = ' checktime';
		}
		else 
		{
			$orderby = 'applytime';
		}
		$applytitle = '';
		if ($status == 1) 
		{
			$applytitle = '待审核';
		}
		else if ($status == 2) 
		{
			$applytitle = '待打款';
		}
		else if ($status == 3) 
		{
			$applytitle = '已打款';
		}
		else if ($status == -1) 
		{
			$applytitle = '已无效';
		}
		$sql = 'select b.* from ' . tablename('ewei_shop_merch_bill') . ' b ' . ' left join ' . tablename('ewei_shop_merch_user') . ' u on b.merchid = u.id' . ' where 1 ' . $condition . ' ORDER BY ' . $orderby . ' desc ';
		if (empty($_GPC['export'])) 
		{
			$sql .= '  limit ' . (($pindex - 1) * $psize) . ',' . $psize;
		}
		$list = pdo_fetchall($sql, $params);
		if ($_GPC['export'] == '1') 
		{
			plog('member.list', '导出结算数据');
			foreach ($list as &$row ) 
			{
				$row['applytime'] = date('Y-m-d H:i', $row['applytime']);
				$row['paytime'] = date('Y-m-d H:i', $row['paytime']);
				$row['typestr'] = $apply_type[$row['applytype']];
			}
			unset($row);
			$columns = array();
			$columns[] = array('title' => '商城信息', 'field' => 'merchname', 'width' => 12);
			$columns[] = array('title' => '姓名', 'field' => 'realname', 'width' => 12);
			$columns[] = array('title' => '手机号', 'field' => 'mobile', 'width' => 12);
			$columns[] = array('title' => '申请金额', 'field' => 'realprice', 'width' => 12);
			$columns[] = array('title' => '申请抽成后金额', 'field' => 'realpricerate', 'width' => 12);
			$columns[] = array('title' => '申请订单个数', 'field' => 'ordernum', 'width' => 16);
			$columns[] = array('title' => '提现方式', 'field' => 'typestr', 'width' => 12);
			if (1 < $status) 
			{
				$columns[] = array('title' => '通过申请金额', 'field' => 'passrealprice', 'width' => 12);
				$columns[] = array('title' => '通过申请抽成后金额', 'field' => 'passrealpricerate', 'width' => 12);
				$columns[] = array('title' => '通过申请订单个数', 'field' => 'passordernum', 'width' => 16);
			}
			if ($status == 3) 
			{
				$columns[] = array('title' => '实际打款金额', 'field' => 'finalprice', 'width' => 12);
			}
			$columns[] = array('title' => '抽成比例%', 'field' => 'payrate', 'width' => 12);
			$columns[] = array('title' => '申请时间', 'field' => 'applytime', 'width' => 16);
			if ($status == 3) 
			{
				$columns[] = array('title' => '最终打款时间', 'field' => 'paytime', 'width' => 12);
			}
			m('excel')->export($list, array('title' => '提现申请数据', 'columns' => $columns));
		}
		$total = pdo_fetchcolumn('select count(b.id) from' . tablename('ewei_shop_merch_bill') . ' b ' . ' left join ' . tablename('ewei_shop_merch_user') . ' u on b.merchid = u.id' . ' where 1 ' . $condition, $params);
		$pager = pagination($total, $pindex, $psize);
		show_json(1, array('list' => $list, 'total' => $total, 'pagesize' => $psize));
	}

	protected function post() 
	{
		global $_W;
		global $_GPC;

		$pindex = max(1, intval($_GPC['page']));
		$psize = 10;

		$merchid = $_W['merchid'];
		$item = $this->model->getMerchPrice($merchid, 1);
		$list = $this->model->getMerchPriceList($merchid, 0, 0,$pindex,$psize);

		$order_num = count($list);
		$set = m('common')->getPluginset('merch');
		if (empty($set)) 
		{
			$set = $this->model->getPluginsetByMerch('merch');
		}
		$last_data = $this->getLastApply($merchid);
		$type_array = array();
		if ($set['applycashweixin'] == 1) 
		{
			$type_array[0]['title'] = '提现到微信钱包';
		}
		if ($set['applycashalipay'] == 1) 
		{
			$type_array[2]['title'] = '提现到支付宝';
			if (!(empty($last_data))) 
			{
				if ($last_data['applytype'] != 2) 
				{
					$type_last = $this->getLastApply($merchid, 2);
					if (!(empty($type_last))) 
					{
						$last_data['alipay'] = $type_last['alipay'];
					}
				}
			}
		}
		if ($set['applycashcard'] == 1) 
		{
			$type_array[3]['title'] = '提现到银行卡';
			if (!(empty($last_data))) 
			{
				if ($last_data['applytype'] != 3) 
				{
					$type_last = $this->getLastApply($merchid, 3);
					if (!(empty($type_last))) 
					{
						$last_data['bankname'] = $type_last['bankname'];
						$last_data['bankcard'] = $type_last['bankcard'];
					}
				}
			}
			$condition = ' and uniacid=:uniacid';
			$params = array(':uniacid' => $_W['uniacid']);
			$banklist = pdo_fetchall('SELECT * FROM ' . tablename('ewei_shop_commission_bank') . ' WHERE 1 ' . $condition . '  ORDER BY displayorder DESC', $params);
		}
		if (!(empty($last_data))) 
		{
			if (array_key_exists($last_data['applytype'], $type_array)) 
			{
				$type_array[$last_data['applytype']]['checked'] = 1;
			}
		}

		show_json(1,array('item'=>$item,'list'=>$list,'order_num'=>$order_num,'set'=>$set,'type_array'=>$type_array));
	}

	// 申请提现
	public function apply_withdrow(){
		global $_W,$_GPC;

		$pindex = max(1, intval($_GPC['page']));
		$psize = 10;

		$merchid = $_W['merchid'];
		$item = $this->model->getMerchPrice($merchid, 1);
		$list = $this->model->getMerchPriceList($merchid, 0, 0,$pindex,$psize);

		$order_num = count($list);
		$set = m('common')->getPluginset('merch');
		if (empty($set)) 
		{
			$set = $this->model->getPluginsetByMerch('merch');
		}
		$last_data = $this->getLastApply($merchid);
		$type_array = array();
		if ($set['applycashweixin'] == 1) 
		{
			$type_array[0]['title'] = '提现到微信钱包';
		}
		if ($set['applycashalipay'] == 1) 
		{
			$type_array[2]['title'] = '提现到支付宝';
			if (!(empty($last_data))) 
			{
				if ($last_data['applytype'] != 2) 
				{
					$type_last = $this->getLastApply($merchid, 2);
					if (!(empty($type_last))) 
					{
						$last_data['alipay'] = $type_last['alipay'];
					}
				}
			}
		}
		if ($set['applycashcard'] == 1) 
		{
			$type_array[3]['title'] = '提现到银行卡';
			if (!(empty($last_data))) 
			{
				if ($last_data['applytype'] != 3) 
				{
					$type_last = $this->getLastApply($merchid, 3);
					if (!(empty($type_last))) 
					{
						$last_data['bankname'] = $type_last['bankname'];
						$last_data['bankcard'] = $type_last['bankcard'];
					}
				}
			}
			$condition = ' and uniacid=:uniacid';
			$params = array(':uniacid' => $_W['uniacid']);
			$banklist = pdo_fetchall('SELECT * FROM ' . tablename('ewei_shop_commission_bank') . ' WHERE 1 ' . $condition . '  ORDER BY displayorder DESC', $params);
		}
		if (!(empty($last_data))) 
		{
			if (array_key_exists($last_data['applytype'], $type_array)) 
			{
				$type_array[$last_data['applytype']]['checked'] = 1;
			}
		}

		if($_W['ispost']){
			if (($item['realprice'] <= 0) || empty($list)) 
			{
				show_json(0, '您没有可提现的金额');
			}
			$applytype = intval($_GPC['applytype']);
			if (!(array_key_exists($applytype, $type_array))) 
			{
				show_json(0, '未选择提现方式，请您选择提现方式后重试!');
			}
			$insert = array();
			if ($applytype == 2) 
			{
				$realname = trim($_GPC['realname']);
				$alipay = trim($_GPC['alipay']);
				$alipay1 = trim($_GPC['alipay1']);
				if (empty($realname)) 
				{
					show_json(0, '请填写姓名!');
				}
				if (empty($alipay)) 
				{
					show_json(0, '请填写支付宝帐号!');
				}
				if (empty($alipay1)) 
				{
					show_json(0, '请填写确认帐号!');
				}
				if ($alipay != $alipay1) 
				{
					show_json(0, '支付宝帐号与确认帐号不一致!');
				}
				$insert['applyrealname'] = $realname;
				$insert['alipay'] = $alipay;
			}
			else if ($applytype == 3) 
			{
				$realname = trim($_GPC['realname']);
				$bankname = trim($_GPC['bankname']);
				$bankcard = trim($_GPC['bankcard']);
				$bankcard1 = trim($_GPC['bankcard1']);
				if (empty($realname)) 
				{
					show_json(0, '请填写姓名!');
				}
				if (empty($bankname)) 
				{
					show_json(0, '请选择银行!');
				}
				if (empty($bankcard)) 
				{
					show_json(0, '请填写银行卡号!');
				}
				if (empty($bankcard1)) 
				{
					show_json(0, '请填写确认卡号!');
				}
				if ($bankcard != $bankcard1) 
				{
					show_json(0, '银行卡号与确认卡号不一致!');
				}
				$insert['applyrealname'] = $realname;
				$insert['bankname'] = $bankname;
				$insert['bankcard'] = $bankcard;
			}
			$insert['uniacid'] = $_W['uniacid'];
			$insert['merchid'] = $merchid;
			$insert['applyno'] = m('common')->createNO('merch_bill', 'applyno', 'MO');
			$insert['orderids'] = iserializer($item['orderids']);
			$insert['ordernum'] = $order_num;
			$insert['price'] = $item['price'];
			$insert['realprice'] = $item['realprice'];
			$insert['realpricerate'] = $item['realpricerate'];
			$insert['finalprice'] = $item['finalprice'];
			$insert['orderprice'] = $item['orderprice'];
			$insert['payrateprice'] = round(($item['realpricerate'] * $item['payrate']) / 100, 2);
			$insert['payrate'] = $item['payrate'];
			$insert['applytime'] = time();
			$insert['status'] = 1;
			$insert['applytype'] = $applytype;
			pdo_insert('ewei_shop_merch_bill', $insert);
			$billid = pdo_insertid();
			foreach ($list as $k => $v ) 
			{
				$orderid = $v['id'];
				$insert_data = array();
				$insert_data['uniacid'] = $_W['uniacid'];
				$insert_data['billid'] = $billid;
				$insert_data['orderid'] = $orderid;
				$insert_data['ordermoney'] = $v['realprice'];
				pdo_insert('ewei_shop_merch_billo', $insert_data);
				$change_order_data = array();
				$change_order_data['merchapply'] = 1;
				pdo_update('ewei_shop_order', $change_order_data, array('id' => $orderid));
			}
			$merch_user = pdo_fetch('select * from ' . tablename('ewei_shop_merch_user') . ' where uniacid=:uniacid and id=' . $merchid, array(':uniacid' => $_W['uniacid']));
			$this->model->sendMessage(array('merchname' => $merch_user['merchname'], 'money' => $insert['realprice'], 'realname' => $merch_user['realname'], 'mobile' => $merch_user['mobile'], 'applytime' => time()), 'merch_apply_money');
			$this->message("提现申请成功");
		}
		include $this->template();
	}
	public function ajaxgettotals() 
	{
		global $_W;
		$merchid = $_W['merchid'];
		$totals = $this->model->getMerchApplyTotals($merchid);
		$result = ((empty($totals) ? array() : $totals));
		show_json(1, $result);
	}
	public function getLastApply($merchid, $applytype = -1) 
	{
		global $_W;
		$params = array(':uniacid' => $_W['uniacid'], ':merchid' => $merchid);
		$sql = 'select applytype,alipay,bankname,bankcard,applyrealname from ' . tablename('ewei_shop_merch_bill') . ' where merchid=:merchid and uniacid=:uniacid';
		if (-1 < $applytype) 
		{
			$sql .= ' and applytype=:applytype';
			$params[':applytype'] = $applytype;
		}
		$sql .= ' order by id desc Limit 1';
		$data = pdo_fetch($sql, $params);
		return $data;
	}
	
}
?>