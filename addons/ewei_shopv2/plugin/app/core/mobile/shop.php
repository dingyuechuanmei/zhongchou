<?php

if (!defined('IN_IA')) {
	exit('Access Denied');
}
 
require EWEI_SHOPV2_PLUGIN . 'app/core/page_mobile.php';
class Shop_EweiShopV2Page extends AppMobilePage
{
	// 获取是否设置diy首页
	public function get_isdiyhome(){
		global $_W;
		global $_GPC;

		$diypage = $_W['shopset']['diypage'];
		$isdiyhome = 0;
		if($diypage['page']['home'] > 0){
			$isdiyhome = 1;
		}
		$result = array(
			'isdiyhome' => $isdiyhome
		);
		app_json($result);
	}

	public function get_shopindex()
	{
		global $_W;
		global $_GPC;
		$uniacid = $_W['uniacid'];
		$defaults = array(
			'adv'       => array('text' => '幻灯片', 'visible' => 1),
			'search'    => array('text' => '搜索栏', 'visible' => 1),
			'nav'       => array('text' => '导航栏', 'visible' => 1),
			'notice'    => array('text' => '公告栏', 'visible' => 1),
			'cube'      => array('text' => '魔方栏', 'visible' => 1),
			'banner'    => array('text' => '广告栏', 'visible' => 1),
			'recommand' => array('text' => '推荐栏', 'visible' => 1)
		);
		$appsql = '';

		if ($this->iswxapp) {
			$appsql = ' and iswxapp = 1';
		}

		$shop_adv = "ewei_shop_adv";
		$shop_nav = "ewei_shop_nav";
		$shop_banner = "ewei_shop_banner";
		$shop_notice = "ewei_shop_notice";

		$merchid = 0;
		$indexset = '';
		$parms = array(
			':uniacid' => $uniacid
		);
		if(!empty($_GPC['merchid'])){
			$shop_adv = "ewei_shop_merch_adv";
			$shop_nav = "ewei_shop_merch_nav";
			$shop_banner = "ewei_shop_merch_banner";
			$shop_notice = "ewei_shop_merch_notice";
			$merchid = intval($_GPC['merchid']);
			$appsql = " and merchid = :merchid";
			$parms[':merchid'] = $merchid;
			$this->iswxapp = false;
			$sets =  pdo_fetchcolumn('select sets from ' . tablename('ewei_shop_merch_user') . ' where id=:id and uniacid=:uniacid', array(':id' => $merchid, ':uniacid' => $uniacid));
            $merch_cubes = iunserializer($sets)['shop']['cubes'];
        }

		$sorts = ($this->iswxapp ? $_W['shopset']['shop']['indexsort_wxapp'] : $_W['shopset']['shop']['indexsort']);
		$sorts = (isset($sorts) ? $sorts : $defaults);
		$sorts['recommand'] = array('text' => '系统推荐', 'visible' => 1);
		$advs = pdo_fetchall('select id,advname,link,thumb from ' . tablename($shop_adv) . ' where uniacid=:uniacid' . $appsql . ' and enabled=1 order by displayorder desc', $parms);
		$advs = set_medias($advs, 'thumb');
		$navs = pdo_fetchall('select id,navname,url,icon from ' . tablename($shop_nav) . ' where uniacid=:uniacid' . $appsql . ' and status=1 order by displayorder desc', $parms);
		$navs = set_medias($navs, 'icon');
		$cubes = ($this->iswxapp ? $_W['shopset']['shop']['cubes_wxapp'] : $merch_cubes);
		$cubes = set_medias($cubes, 'img');
		$banners = pdo_fetchall('select id,bannername,link,thumb from ' . tablename($shop_banner) . ' where uniacid=:uniacid' . $appsql . ' and enabled=1 order by displayorder desc', $parms);
		$banners = set_medias($banners, 'thumb');
		$bannerswipe = ($this->iswxapp ? intval($_W['shopset']['shop']['bannerswipe_wxapp']) : intval($_W['shopset']['shop']['bannerswipe']));
		$_W['shopset']['shop']['indexrecommands'] = $this->iswxapp ? $_W['shopset']['shop']['indexrecommands_wxapp'] : $_W['shopset']['shop']['indexrecommands'];

		if (!empty($_W['shopset']['shop']['indexrecommands'])) {
			$goodids = implode(',', $_W['shopset']['shop']['indexrecommands']);

			if (!empty($goodids)) {
				$indexrecommands = pdo_fetchall('select id, title, thumb, marketprice,ispresell,presellprice, productprice, minprice, total from ' . tablename('ewei_shop_goods') . ' where id in( ' . $goodids . ' ) and uniacid=:uniacid and status=1 order by instr(\'' . $goodids . '\',id),displayorder desc', array(':uniacid' => $uniacid));
				$indexrecommands = set_medias($indexrecommands, 'thumb');

				foreach ($indexrecommands as $key => $value) {
					$indexrecommands[$key]['marketprice'] = (double) $indexrecommands[$key]['marketprice'];
					$indexrecommands[$key]['minprice'] = (double) $indexrecommands[$key]['minprice'];
					$indexrecommands[$key]['presellprice'] = (double) $indexrecommands[$key]['presellprice'];
					$indexrecommands[$key]['productprice'] = (double) $indexrecommands[$key]['productprice'];

					if (0 < $value['ispresell']) {
						$indexrecommands[$key]['minprice'] = $value['presellprice'];
					}
				}
			}
		}

		$goodsstyle = ($this->iswxapp ? $_W['shopset']['shop']['goodsstyle_wxapp'] : $_W['shopset']['shop']['goodsstyle']);
		$notices = pdo_fetchall('select id, title, link, thumb from ' . tablename($shop_notice) . ' where uniacid=:uniacid' . $appsql . ' and status=1 order by displayorder desc limit 5', $parms);
		$notices = set_medias($notices, 'thumb');
		$seckillinfo = plugin_run('seckill::getTaskSeckillInfo');
		$copyright = m('common')->getCopyright();
		$newsorts = array();

		foreach ($sorts as $key => $old) {
			$old['type'] = $key;

			if ($key == 'adv') {
				$old['data'] = !empty($advs) ? $advs : array();
			}
			else if ($key == 'nav') {
				$old['data'] = !empty($navs) ? $navs : array();
			}
			else if ($key == 'cube') {
				$old['data'] = !empty($cubes) ? $cubes : array();
			}
			else if ($key == 'banner') {
				$old['data'] = !empty($banners) ? $banners : array();
				$old['bannerswipe'] = !empty($bannerswipe) ? $bannerswipe : array();
			}
			else if ($key == 'notice') {
				$old['data'] = !empty($notices) ? $notices : array();
			}
			else if ($key == 'seckillinfo') {
				$old['data'] = !empty($seckillinfo) ? $seckillinfo : array();
			}
			else {
				if ($key == 'recommand') {
					$old['data'] = !empty($indexrecommands) ? $indexrecommands : array();
				}
			}

			$newsorts[] = $old;
			if (($key == 'notice') && !isset($sorts['seckill'])) {
				$newsorts[] = array('text' => '秒杀栏', 'visible' => 0);
			}
		}

		app_json(array(
			'uniacid' => $uniacid,
			'sorts' => $newsorts, 
			'goodsstyle' => $goodsstyle,
			'merchid' => $merchid,
			'menus' => $this->model->getDiypageMenus(),
			'cartcount' => m('goods')->getCartCount(),
			'copyright' => !empty($copyright) && !empty($copyright['copyright']) ? $copyright['copyright'] : '',
			//'indexset' => $indexset
			)
		);
	}

	public function get_recommand()
	{
		global $_W;
		global $_GPC;
		$merchid = $_GPC['merchid'] ? $_GPC['merchid'] : '';
		$args = array(
			'page' => $_GPC['page'],
			'pagesize' => 10,
			'order' => 'displayorder desc,createtime desc',
			//'isrecommand' => 1,
			'by' => '',
			'merchid' => $merchid
		);
		$recommand = m('goods')->getList($args);

		if (!empty($recommand['list'])) {
			foreach ($recommand['list'] as &$item) {
				$item['marketprice'] = (double) $item['marketprice'];
				$item['minprice'] = (double) $item['minprice'];
				$item['presellprice'] = (double) $item['presellprice'];
				$item['productprice'] = (double) $item['productprice'];
			}

			unset($item);
		}
		app_json(array('list' => $recommand['list'], 'pagesize' => $args['pagesize'], 'total' => $recommand['total'], 'page' => intval($_GPC['page'])));
	}

	/**
     * 检测是否关闭
     */
	public function check_close()
	{
		global $_W;
		$close = (isset($_W['shopset']['close']) ? $_W['shopset']['close'] : array('flag' => 0, 'url' => '', 'detail' => ''));
		$close['detail'] = base64_encode($close['detail']);
		app_json(array('close' => $close));
	}

	/**
     * 获取分类
     */
	public function get_category()
	{
		global $_W;
		global $_GPC;
		$refresh = intval($_GPC['refresh']);
		$category_set = $_W['shopset']['category'];
		$category_set['advimg'] = tomedia($category_set['advimg']);
		$level = intval($category_set['level']);
		$category = m('shop')->getCategory();
		$recommands = array();

		foreach ($category['children'] as $k => $v) {
			foreach ($v as $r) {
				if ($r['isrecommand'] == 1) {
					$r['thumb'] = tomedia($r['thumb']);
					$rec = array(
						'id'     => $r['id'],
						'name'   => $r['name'],
						'thumb'  => $r['thumb'],
						'advurl' => $r['advurl'],
						'advimg' => $r['advimg'],
						'child'  => array(),
						'level'  => $r['level']
						);

					if (isset($category['children'][$r['id']])) {
						foreach ($category['children'][$r['id']] as $c) {
							$c['thumb'] = tomedia($c['thumb']);
							$child = array(
								'id'     => $c['id'],
								'name'   => $c['name'],
								'thumb'  => $c['thumb'],
								'advurl' => $c['advurl'],
								'advimg' => $c['advimg'],
								'child'  => array()
								);
							$rec['child'][] = $child;
						}
					}

					$recommands[] = $rec;
				}
			}
		}

		$allcategory = array();

		foreach ($category['parent'] as $p) {
			$p['thumb'] = tomedia($p['thumb']);
			$p['advimg'] = tomedia($p['advimg']);
			$parent = array(
				'id'     => $p['id'],
				'name'   => $p['name'],
				'thumb'  => $p['thumb'],
				'advurl' => $p['advurl'],
				'advimg' => $p['advimg'],
				'child'  => array()
				);

			if (is_array($category['children'][$p['id']])) {
				foreach ($category['children'][$p['id']] as $c) {
					if (!empty($c['thumb'])) {
						$c['thumb'] = tomedia($c['thumb']);
					}

					if (!empty($c['thumb'])) {
						$c['advimg'] = tomedia($c['advimg']);
					}

					if (!empty($c['id'])) {
						$child = array(
							'id'     => $c['id'],
							'name'   => $c['name'],
							'thumb'  => $c['thumb'],
							'advurl' => $c['advurl'],
							'advimg' => $c['advimg'],
							'child'  => array(),
							'level'  => $c['level']
							);
					}

					if (is_array($category['children'][$c['id']])) {
						foreach ($category['children'][$c['id']] as $t) {
							if (!empty($t['thumb'])) {
								$t['thumb'] = tomedia($t['thumb']);
							}

							if (!empty($t['id'])) {
								$child['child'][] = array('id' => $t['id'], 'name' => $t['name'], 'thumb' => $t['thumb'], 'advurl' => $t['advurl'], 'advimg' => $t['advimg']);
							}
						}
					}

					$parent['child'][] = $child;
				}
			}

			$allcategory[] = $parent;
		}
		app_json(array(
			'set' => $category_set, 
			'recommands' => $recommands, 
			'category' => $allcategory,
			'menus' => $this->model->getDiypageMenus(),
			'cartcount' => m('goods')->getCartCount(),
		));
	}

	/**
     * 获取设置
     */
	public function get_set()
	{
		global $_W;
		global $_GPC;
		$sets = array();
		$global_set = m('cache')->getArray('globalset', 'global');

		if (empty($global_set)) {
			$global_set = m('common')->setGlobalSet();
		}

		empty($global_set['trade']['credittext']) && $global_set['trade']['credittext'] = '积分';
		empty($global_set['trade']['moneytext']) && $global_set['trade']['moneytext'] = '余额';
		$merch_plugin = p('merch');
		$merch_data = m('common')->getPluginset('merch');
		$openmerch = $merch_plugin && $merch_data['is_openmerch'];
		$sets = array(
			'shop'               => array('name' => $global_set['shop']['name'], 'logo' => tomedia($global_set['shop']['logo']), 'description' => $global_set['shop']['description'], 'img' => tomedia($global_set['shop']['img'])),
			'share'              => array('title' => empty($global_set['share']['title']) ? $global_set['shop']['name'] : $global_set['share']['title'], 'img' => empty($global_set['share']['icon']) ? tomedia($global_set['shop']['logo']) : tomedia($global_set['share']['icon']), 'desc' => empty($global_set['share']['desc']) ? $global_set['shop']['description'] : $global_set['share']['desc'], 'link' => empty($global_set['share']['url']) ? mobileUrl('', array('appfrom' => 1), true) : $global_set['share']['url']),
			'trade'              => array('closerecharge' => intval($global_set['trade']['closerecharge']), 'minimumcharge' => floatval($global_set['trade']['minimumcharge']), 'withdraw' => intval($global_set['trade']['withdraw']), 'withdrawmoney' => floatval($global_set['trade']['withdrawmoney']), 'closecomment' => intval($global_set['trade']['withdraw']), 'closecommentshow' => intval($global_set['trade']['closecommentshow'])),
			'payset'             => array('weixin' => intval($global_set['pay']['weixin']), 'alipay' => intval($global_set['pay']['alipay']), 'credit' => intval($global_set['pay']['credit']), 'cash' => intval($global_set['pay']['cash'])),
			'contact'            => array('phone' => isset($global_set['contact']['phone']) ? $global_set['contact']['phone'] : '', 'province' => isset($global_set['contact']['phone']) ? $global_set['contact']['province'] : '', 'city' => isset($global_set['contact']['phone']) ? $global_set['contact']['city'] : '', 'address' => isset($global_set['contact']['phone']) ? $global_set['contact']['address'] : ''),
			'menu'               => $this->model->diyMenu('shop'),
			'cancelorderreasons' => array('不取消了', '我不想买了', '信息填写错误，重新拍', '同城见面交易', '其他原因'),
			'openmerch'          => $openmerch,
			'texts'              => array('credittext' => $global_set['trade']['credittext'], 'moneytext' => $global_set['trade']['moneytext'])
			);
		app_json(array('sets' => $sets));
	}

	public function get_areas()
	{
		$areas = m('common')->getAreas();
		app_json(array('areas' => $areas));
	}

	/**
	 * 获取个人商户商品列表
	 */
	public function get_merchant_goods()
	{
		global $_W;
		global $_GPC;
		$merchid = $_GPC['merchid'] ? $_GPC['merchid'] : '';
		$type = $_GPC['type'] ? 'is'.$_GPC['type'] : 'isrecommand';
		$pageSize = 10;
		$args = array(
			'page' => $_GPC['page'],
			'pagesize' => $pageSize,
			'order' => 'displayorder desc,createtime desc',
			'by' => '',
			'merchid' => $merchid
		);
		$args[$type] = 1;
		$recommand = m('goods')->getList($args);

		if (!empty($recommand['list'])) {
			foreach ($recommand['list'] as &$item) {
				$item['marketprice'] = (double) $item['marketprice'];
				$item['minprice'] = (double) $item['minprice'];
				$item['presellprice'] = (double) $item['presellprice'];
				$item['productprice'] = (double) $item['productprice'];
			}

			unset($item);
		}
		$pageCount = ceil($recommand['total']/$pageSize);
		if (empty($merchid)) {
			$recommand['list'] = [];
		}
		app_json(array('list' => $recommand['list'], 'pagesize' => $args['pagesize'], 'total' => $recommand['total'], 'pageCount' => $pageCount));
	}

	/**
	 * 获取商户信息和全部商品,新品总数
	 */
	public function get_merchant_info()
	{
		global $_W;
		global $_GPC;
		$merchid = $_GPC['merchid'];
		$sql = "SELECT * FROM ".tablename('ewei_shop_merch_user')." WHERE uniacid = :uniacid AND id = :merchid";
		$info = pdo_fetch($sql,array(':uniacid'=>$_W['uniacid'],':merchid'=>$merchid));
		$info['logo'] = tomedia($info['logo']);
		$all = m('goods')->getList(array('merchid' => $merchid));
		$new = m('goods')->getList(array('isnew'=>1,'merchid' => $merchid));
		$isfavorite = pdo_fetchcolumn('select id from ' . tablename('ewei_shop_member_merch_favorite') . ' where uniacid=:uniacid and merchid=:merchid and openid=:openid and deleted = 0 limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid'], ':merchid' => $merchid));
		app_json(array('info'=>$info,'all_num'=>$all['total'],'new_num'=>$new['total'],'isfavorite'=>!empty($isfavorite) ? 1 : 0));
	}

	/**
	 * 收藏店铺
	 */
	public function favorite_merchant()
	{
		global $_W;
		global $_GPC;
		$merchid = intval($_GPC['merchid']);
		if (empty($merchid)) {
			app_error(AppError::$ParamsError);
		}
		$isfavorite = intval($_GPC['isfavorite']);
		$merch = pdo_fetch('select * from ' . tablename('ewei_shop_merch_user') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $merchid, ':uniacid' => $_W['uniacid']));

		if (empty($merch)) {
			app_error(AppError::$GoodsNotFound);
		}

		$data = pdo_fetch('select id,deleted from ' . tablename('ewei_shop_member_merch_favorite') . ' where uniacid=:uniacid and merchid=:merchid and openid=:openid limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid'], ':merchid' => $merchid));
		if (empty($data)) {
			if (!empty($isfavorite)) {
				$data = array('uniacid' => $_W['uniacid'], 'merchid' => $merchid, 'openid' => $_W['openid'], 'createtime' => time());
				pdo_insert('ewei_shop_member_merch_favorite', $data);
			}
		}
		else {
			pdo_update('ewei_shop_member_merch_favorite', array('deleted' => $isfavorite ? 0 : 1), array('id' => $data['id'], 'uniacid' => $_W['uniacid']));
		}

		app_json(array('isfavorite' => $isfavorite == 1));
	}

	/**
	 * 已收藏店铺列表
	 */
	public function get_follow_list()
	{
		global $_W;
		global $_GPC;
		$pindex = max(1, intval($_GPC['page']));
		$psize = 10;
		$condition = ' and f.uniacid = :uniacid and f.openid=:openid and f.deleted=0';
		$params = array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']);
		$sql = 'SELECT COUNT(*) FROM ' . tablename('ewei_shop_member_merch_favorite') . ' f where 1 ' . $condition;
		$total = pdo_fetchcolumn($sql, $params);
		$list = array();
		$result = array(
			'list'     => array(),
			'total'    => $total,
			'pagesize' => $psize
		);

		if (!empty($total)) {
			$sql = 'SELECT f.id,f.merchid,m.merchname,m.logo FROM ' . tablename('ewei_shop_member_merch_favorite') . ' f ' . ' left join ' . tablename('ewei_shop_merch_user') . ' m on f.merchid = m.id ' . ' where 1 ' . $condition . ' ORDER BY `id` DESC LIMIT ' . (($pindex - 1) * $psize) . ',' . $psize;
			$list = pdo_fetchall($sql, $params);
			$list = set_medias($list, 'logo');
		}

		$result['list'] = $list;
		app_json($result);

	}

	/**
	 * 删除收藏店铺列表
	 */
	public function remove_follow()
	{
		global $_W;
		global $_GPC;
		$ids = $_GPC['ids'];
		if (empty($ids) || !is_array($ids)) {
			app_error(AppError::$ParamsError);
		}
		$sql = 'update ' . tablename('ewei_shop_member_merch_favorite') . ' set deleted=1 where openid=:openid and id in (' . implode(',', $ids) . ')';
		pdo_query($sql, array(':openid' => $_W['openid']));
		app_json();
	}
}

?>
