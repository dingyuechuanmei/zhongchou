<?php

if (!defined('IN_IA')) {
	exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'app/core/page_mobile.php';
class Merch_EweiShopV2Page extends AppMobilePage
{

	public function mian(){
		app_json(0);
	}


	// 验证是否申请
	public function check_register(){
		global $_W;
		global $_GPC;
		$set = $_W['shopset']['merch'];
		if (empty($set['apply_openmobile'])) 
		{
			$this->message('未开启商户入驻申请', '', 'error');
		}

		$reg = pdo_fetch('select * from ' . tablename('ewei_shop_merch_reg') . ' where openid=:openid and uniacid=:uniacid limit 1', array(':openid' => $_W['openid'], ':uniacid' => $_W['uniacid']));
		$user = false;
		if (!(empty($reg['status']))) 
		{
			$user = pdo_fetch('select * from ' . tablename('ewei_shop_merch_user') . ' where openid=:openid and uniacid=:uniacid limit 1', array(':openid' => $_W['openid'], ':uniacid' => $_W['uniacid']));
		}
		if (!(empty($user)) && (1 <= $user['status'])) 
		{

			// app_error(AppError::$ParamsError, '您已经申请，无需重复申请');

				
		}

		app_json(array('user'=>$user,'reg'=>$reg));
	}

	// 商户入驻
	public function register(){
		global $_W;
		global $_GPC;
		$set = $_W['shopset']['merch'];

		if (empty($set['apply_openmobile'])) 
		{
			app_error(AppError::$ParamsError, '未开启商户入驻申请');
		}

		$reg = pdo_fetch('select * from ' . tablename('ewei_shop_merch_reg') . ' where openid=:openid and uniacid=:uniacid limit 1', array(':openid' => $_W['openid'], ':uniacid' => $_W['uniacid']));
		$user = false;
		if (!(empty($reg['status']))) 
		{
			$user = pdo_fetch('select * from ' . tablename('ewei_shop_merch_user') . ' where openid=:openid and uniacid=:uniacid limit 1', array(':openid' => $_W['openid'], ':uniacid' => $_W['uniacid']));
		}
		if (!(empty($user)) && (1 <= $user['status'])) 
		{
			app_error(AppError::$ParamsError, '您已经申请，无需重复申请');
		}

		$uname = trim($_GPC['uname']);
		$upass = $_GPC['upass'];
		if (empty($uname)) 
		{
			app_error(-2, '请填写帐号!');
		}
		if (empty($upass)) 
		{
			app_error(-2, '请填写密码!');
		}
		$where1 = ' uname=:uname';
		$params1 = array(':uname' => $uname);
		if (!(empty($reg))) 
		{
			$where1 .= ' and id<>:id';
			$params1[':id'] = $reg['id'];
		}
		$usercount1 = pdo_fetchcolumn('select count(1) from ' . tablename('ewei_shop_merch_reg') . ' where ' . $where1 . ' limit 1', $params1);
		$where2 = ' username=:username';
		$params2 = array(':username' => $uname);
		$usercount2 = pdo_fetchcolumn('select count(1) from ' . tablename('ewei_shop_merch_account') . ' where ' . $where2 . ' limit 1', $params2);
		if ((0 < $usercount1) || (0 < $usercount2)) 
		{
			app_json(-2, '帐号 ' . $uname . ' 已经存在,请更改!');
		}
		$upass = m('util')->pwd_encrypt($upass, 'E');
		$data = array(
			'uniacid' => $_W['uniacid'], 
			'openid' => $_W['openid'], 
			'status' => 0, 
			'realname' => trim($_GPC['realname']), 
			'mobile' => trim($_GPC['mobile']), 
			'uname' => $uname, 
			'upass' => $upass, 
			'merchname' => trim($_GPC['merchname']), 
			'salecate' => trim($_GPC['salecate']),
			'desc' => trim($_GPC['desc']),
		);
		if (empty($reg)) 
		{
			$data['applytime'] = time();
			$data['license_img'] = trim($_GPC['license_img']);
			pdo_insert('ewei_shop_merch_reg', $data);
		}
		else 
		{
			pdo_update('ewei_shop_merch_reg', $data, array('id' => $reg['id']));
		}

		app_json(0,$reg);
	}

	// 获取商户列表
	public function getMerchUsers(){
		global $_W,$_GPC;

		$data = array();
		$data = array_merge($data, array( 'status' => 1, 'orderby' => array('displayorder' => 'desc', 'id' => 'asc') ));
		$category = $this->getCategory($data);
		
		$category = set_medias($category,'thumb');

		$result = array(
			'category' 		 => $category,
		);

		app_json($result);

	}

	// 获取分类信息
	public function getMerchCagetory(){
		global $_W,$_GPC;

		$data = array();
		if (!(empty($_GPC['keyword']))) 
		{
			$data['likecatename'] = $_GPC['keyword'];
		}
		$data = array_merge($data, array( 'status' => 1, 'orderby' => array('displayorder' => 'desc', 'id' => 'asc') ));
		$category = $this->getCategory($data);

		$category = set_medias($category,'thumb');

		$result = array(
			'category' 		 => $category,
		);

		app_json($result);
	}

	// 获取首页信息
	public function getMerchIndex(){
		global $_W,$_GPC;
		$category = $this->getCategory(array( 'isrecommand' => 1, 'status' => 1, 'orderby' => array('displayorder' => 'desc', 'id' => 'asc') ));
		$merchuser = $this->getMerch(array( 'isrecommand' => 1, 'status' => 1, 'field' => 'id,uniacid,merchname,desc,logo,groupid,cateid', 'orderby' => array('id' => 'asc') ));
		$category_swipe = $this->getCategorySwipe(array( 'status' => 1, 'orderby' => array('displayorder' => 'desc', 'id' => 'asc') ));

		$category = set_medias($category,'thumb');
		$merchuser = set_medias($merchuser,'logo');
		$category_swipe = set_medias($category_swipe,'thumb');

		$result = array(
			'category_swipe' => $category_swipe,
			'category' 		 => $category,
			'merchuser' 	 => $merchuser,
		);

		app_json($result);
	}

	// 前段传来的是火星坐标
	// 需要转百度坐标（待确定）
	public function ajaxmerchuser() 
	{
		global $_W;
		global $_GPC;

		$data = array();
		$pindex = max(1, intval($_GPC['page']));
		$psize = 30;
		$lat = floatval($_GPC['lat']);
		$lng = floatval($_GPC['lng']);
		$sorttype = $_GPC['sorttype'];
		$range = $_GPC['range'];
		if (empty($range)) 
		{
			$range = 10;
		}
		if (!(empty($_GPC['keyword']))) 
		{
			$data['like'] = array('merchname' => $_GPC['keyword']);
		}
		if (!(empty($_GPC['cateid']))) 
		{
			$data['cateid'] = $_GPC['cateid'];
		}
		$data = array_merge($data, array('status' => 1, 'field' => 'id,uniacid,merchname,desc,logo,groupid,cateid,address,tel,lng,lat'));
		if (!(empty($sorttype))) 
		{
			$data['orderby'] = array('id' => 'desc');
		}
		$merchuser = $this->getMerch($data);
		if (!(empty($merchuser))) 
		{
			$data = array();
			$data = array_merge($data, array( 'status' => 1, 'orderby' => array('displayorder' => 'desc', 'id' => 'asc') ));
			$category = $this->getCategory($data);
			$cate_list = array();
			if (!(empty($category))) 
			{
				foreach ($category as $k => $v ) 
				{
					$cate_list[$v['id']] = $v;
				}
			}
			foreach ($merchuser as $k => $v ) 
			{
				if (($lat != 0) && ($lng != 0) && !(empty($v['lat'])) && !(empty($v['lng']))) 
				{
					$distance = m('util')->GetDistance($lat, $lng, $v['lat'], $v['lng'], 2);
					if ((0 < $range) && ($range < $distance)) 
					{
						unset($merchuser[$k]);
						continue;
					}
					$merchuser[$k]['distance'] = $distance;
				}
				else 
				{
					$merchuser[$k]['distance'] = 100000;
				}
				$merchuser[$k]['catename'] = $cate_list[$v['cateid']]['catename'];
				$merchuser[$k]['logo'] = tomedia($v['logo']);
			}
		}
		$total = count($merchuser);
		if ($sorttype == 0) 
		{
			$merchuser = m('util')->multi_array_sort($merchuser, 'distance');
		}
		$start = ($pindex - 1) * $psize;
		if (!(empty($merchuser))) 
		{
			$merchuser = array_slice($merchuser, $start, $psize);
		}

		$result = array(
			'list' => $merchuser, 
			'total' => $total, 
			'pagesize' => $psize
		);

		app_json($result);
	}

	private function getCategory($data = array()) 
	{
		global $_W;
		$condition = ' WHERE `uniacid` = :uniacid';
		$params = array(':uniacid' => $_W['uniacid']);
		$res = $this->model->build($condition, $params, $data);
		return pdo_fetchall('select ' . $res['field'] . ' from ' . tablename('ewei_shop_merch_category') . $res['condition'], $res['params'], $res['column']);
	}
	private function getCategorySwipe($data = array()) 
	{
		global $_W;
		$condition = ' WHERE `uniacid` = :uniacid';
		$params = array(':uniacid' => $_W['uniacid']);
		$res = $this->model->build($condition, $params, $data);
		return pdo_fetchall('select ' . $res['field'] . ' from ' . tablename('ewei_shop_merch_category_swipe') . $res['condition'], $res['params'], $res['column']);
	}

	private function getMerch($data = array()) 
	{
		global $_W;
		$condition = ' WHERE `uniacid` = :uniacid';
		$params = array(':uniacid' => $_W['uniacid']);
		$res = $this->model->build($condition, $params, $data);
		return pdo_fetchall('select ' . $res['field'] . ' from ' . tablename('ewei_shop_merch_user') . $res['condition'], $res['params'], $res['column']);
	}

}