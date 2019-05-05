<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'app/core/page_mobile.php';
class Index_EweiShopV2Page extends AppMobilePage
{
    public function main()
    {

    }

    /**
     * 用户信息和奖品信息
     */
    public function memberInfo()
    {
        global $_W;
        global $_GPC;
        $member = $this->member;
        $task_sql = 'SELECT * FROM ' . tablename('ewei_shop_luckdraw') . ' WHERE uniacid=:uniacid AND is_default=1 AND `is_delete`=0  ';
        $luckdraw = pdo_fetch($task_sql, array(':uniacid' => $_W['uniacid']));
        $reward = unserialize($luckdraw['luckdraw_data']);
        //奖品摆放位置
        $awardList = [];
        $topAward = 0;
        $leftAward = 0;
        for ($i = 0;$i < 8; $i++) {
            if ($i == 0) {
                $topAward = 10;
                $leftAward = 10;
            } else if ($i < 3) {
                $topAward = $topAward;
                $leftAward = $leftAward + 180 + 10;
            } else if ($i < 5) {
                $topAward = $topAward + 170 + 6;
                $leftAward = $leftAward;
            } else if ($i < 7) {
                $topAward = $topAward;
                $leftAward = $leftAward - 180 - 10;
            } else if ($i < 8) {
                $topAward = $topAward - 170 - 6;
                $leftAward = $leftAward;
            }
            $awardList[$i]['topAward'] = $topAward;
            $awardList[$i]['leftAward'] = $leftAward;
            $awardList[$i]['imageAward'] = $reward[$i]['icon'];
            $awardList[$i]['textAward'] = $reward[$i]['title'];
            $awardList[$i]['probability'] = $reward[$i]['probability'];
        }
        app_json(array('member'=>$member,'awardList'=>$awardList,'luckdraw'=>$luckdraw,'reward'=>$reward));
    }

    /**
     * 抽奖结果处理
     */
    public function start()
    {
        global $_W;
        global $_GPC;
        $luckdraw_id = intval($_GPC['luckdraw_id']);
        $member = $this->member;
        if (empty($member)) {
            app_error(AppError::$UserNotFound);
        }
        if (empty($luckdraw_id)) {
            app_error(1,'网络错误');
        }
        $sql = "SELECT * FROM ".tablename('ewei_shop_luckdraw')." WHERE luckdraw_id = :luckdraw_id";
        $luckdraw = pdo_fetch($sql,array(':luckdraw_id'=>$luckdraw_id));
        if ($member['credit2'] == 0 || $member['credit2'] < $luckdraw['luckdraw_consume']) {
            app_error(1,'余额不足');
        }
        //插入抽奖记录
        $reward = unserialize($luckdraw['luckdraw_data']);
        $temreward = array();
        foreach ($reward as $key => $value )
        {
            if (isset($value['reward']['goods']))
            {
                $pass = 0;
                foreach ($value['reward']['goods'] as $val )
                {
                    if ($val['total'] <= $val['count'])
                    {
                        $pass = 1;
                    }
                }
                if ($pass == 1)
                {
                    $temreward[$key] = $value['probability'];
                }
            }
            else if (isset($value['reward']['money']))
            {
                if ($value['reward']['money']['num'] <= $value['reward']['money']['total'])
                {
                    $temreward[$key] = $value['probability'];
                }
            }
            else if (isset($value['reward']['bribery']))
            {
                if ($value['reward']['bribery']['num'] <= $value['reward']['bribery']['total'])
                {
                    $temreward[$key] = $value['probability'];
                }
            }
            else if (isset($value['reward']['coupon']))
            {
                $pass = 0;
                foreach ($value['reward']['coupon'] as $val )
                {
                    if (!(empty($val['count'])) && ($val['couponnum'] <= $val['count']))
                    {
                        $pass = 1;
                    }
                }
                if ($pass == 1)
                {
                    $temreward[$key] = $value['probability'];
                }
            }
            else
            {
                $temreward[$key] = $value['probability'];
            }
        }
        if (empty($temreward))
        {
            $data = array('status' => 0, 'info' => '很遗憾,奖品库存不足了!');
            app_error(1,'很遗憾,奖品库存不足了');
        }
        $reward_id = intval($_GPC['index']);
        $reward_info = $reward[$reward_id]['reward'];
        $is_reward = 0;
        if (empty($reward_info))
        {
            $is_reward = 0;
            $reward_info = '很遗憾,没有中奖';
        }
        else
        {
            $is_reward = 1;
            $this->reward($reward_info, $member['openid']);
            if (isset($reward_info['money']))
            {
                $reward_info['money']['total'] -= $reward_info['money']['num'];
            }
            //优惠券
            if (isset($reward_info['coupon']))
            {
                foreach ($reward_info['coupon'] as $key => $val )
                {
                    @$reward_info['coupon'][$key]['count'] -= $val['couponnum'];
                }
            }
            //商品
            if (isset($reward_info['goods']))
            {
                foreach ($reward_info['goods'] as $key => $val )
                {
                    if (empty($val['spec']))
                    {
                        $reward_info['goods'][$key]['count'] -= $val['total'];
                    }
                    else
                    {
                        foreach ($val['spec'] as $k => $v )
                        {
                            $total = $v['total'];
                        }
                        $reward_info['goods'][$key]['count'] -= $total;
                    }
                }
            }
            $temreward = unserialize($luckdraw['luckdraw_data']);
            $temreward[$reward_id]['reward'] = $reward_info;
            $luckdraw_data = array('luckdraw_data' => serialize($temreward));
            $res = pdo_update('ewei_shop_luckdraw', $luckdraw_data, array('uniacid' => $_W['uniacid'], 'luckdraw_id' => $luckdraw_id));
        }
        $log_data = array('uniacid' => $_W['uniacid'], 'luckdraw_id' => $luckdraw_id,'consume' => $luckdraw['luckdraw_consume'], 'join_user' => $member['openid'], 'luckdraw_data' => serialize($reward_info), 'is_reward' => $is_reward, 'addtime' => time());
        $pid = pdo_insert('ewei_shop_luckdraw_log', $log_data);
        //扣款
        if (!empty($pid)) {
            m('member')->setCredit($_GPC['openid'], 'credit2', -$luckdraw['luckdraw_consume']);
        }
        app_json(0);
    }
    /**
     * 抽奖规则
     */
    public function rules()
    {
        global $_W;
        $set = pdo_fetchcolumn('SELECT data FROM ' . tablename('ewei_shop_luckdraw_default') . ' WHERE uniacid =:uniacid LIMIT 1', array(':uniacid' => $_W['uniacid']));
        if (!(empty($set)))
        {
            $detail = unserialize($set);
            show_json(0,array('detail'=>unserialize($detail['info'])));
        }
    }

    /**
     * 抽奖记录
     */
    public function myReward()
    {
        global $_W;
        global $_GPC;
        $psize = 10;
        $pindex = max(1, intval($_GPC['page']));
        $member = $this->member;
        if (empty($member)) {
            app_error(AppError::$UserNotFound);
        }
        $parms = array(
            ':uniacid'=>$_W['uniacid'],
            ':openid'=>$_GPC['openid']
        );
        $rewardList = [];
        $sql = "SELECT * FROM ".tablename('ewei_shop_luckdraw_log')." WHERE uniacid = :uniacid AND is_reward = 1 AND join_user = :openid ORDER BY log_id DESC LIMIT ".(($pindex - 1) * $psize) . ',' . $psize;
        $rewardList = pdo_fetchall($sql,$parms);
        $total = pdo_fetchcolumn("SELECT count(*) FROM ".tablename('ewei_shop_luckdraw_log')." WHERE uniacid = :uniacid AND is_reward = 1 AND join_user = :openid",$parms);
        $pageCount = ceil($total/$psize);
        foreach ($rewardList as $k => $v) {
            $rewardList[$k]['addtime'] = date('Y-m-d H:i:s',$v['addtime']);
            $luckdraw_data = unserialize($v['luckdraw_data']);
            if (isset($luckdraw_data['credit'])) {
                $rewardList[$k]['title'] = '积分:' . $luckdraw_data['credit'];
                $rewardList[$k]['rewarded'] = 1;
                $rewardList[$k]['icon'] = $_W['siteroot'].'addons/ewei_shopv2/plugin/lottery/static/images/jifen.png';
            } else if (isset($luckdraw_data['money'])) {
                $rewardList[$k]['title'] = '奖金:' . $luckdraw_data['money']['num'];
                $rewardList[$k]['rewarded'] = 1;
                $rewardList[$k]['icon'] = $_W['siteroot'].'addons/ewei_shopv2/plugin/lottery/static/images/jiangjin.png';
            } else if (isset($luckdraw_data['bribery'])) {
                $rewardList[$k]['title'] = '红包:' . $luckdraw_data['bribery']['num'];
                $rewardList[$k]['rewarded'] = 1;
                $rewardList[$k]['icon'] = $_W['siteroot'].'addons/ewei_shopv2/plugin/lottery/static/images/hongbao.png';
            } else if (isset($luckdraw_data['goods'])) {
                $goods = array_shift($luckdraw_data['goods']);
                $rewardList[$k]['title'] = '特惠商品:' . $goods['title'];
                $rewardList[$k]['rewarded'] = 0;
                $rewardList[$k]['icon'] = $_W['siteroot'].'addons/ewei_shopv2/plugin/lottery/static/images/shangpin.png';
            } else if (isset($luckdraw_data['coupon'])) {
                $coupon = array_shift($luckdraw_data['coupon']);
                $rewardList[$k]['title'] = '优惠券:' . $coupon['couponname'];
                $rewardList[$k]['rewarded'] = 1;
                $rewardList[$k]['icon'] = $_W['siteroot'].'addons/ewei_shopv2/plugin/lottery/static/images/quan.png';
            }
        }
        app_json(array('member'=>$member,'rewardList'=>$rewardList,'pageCount'=>$pageCount));
    }

    public function reward($poster, $openid)
    {
        if (empty($poster) || empty($openid))
        {
            return false;
        }
        global $_W;
        if (isset($poster['credit']) && (0 < $poster['credit']))
        {
            m('member')->setCredit($openid, 'credit1', $poster['credit'], array(0, '任务活动积分奖励+' . $poster['credit']));
        }
        if (isset($poster['money']) && (0 < $poster['money']['num']))
        {
            $pay = $poster['money']['num'];
            if ($poster['money']['type'] == 1)
            {
                $pay *= 100;
            }
            m('finance')->pay($openid, $poster['money']['type'], $pay, '', '任务活动推荐奖励', false);
        }
        if (isset($poster['coupon']) && !(empty($poster['coupon'])))
        {
            $cansendreccoupon = false;
            $plugin_coupon = com('coupon');
            unset($poster['coupon']['total']);
            foreach ($poster['coupon'] as $k => $v )
            {
                if ($plugin_coupon)
                {
                    if (!(empty($v['id'])) && (0 < $v['couponnum']))
                    {
                        $reccoupon = $plugin_coupon->getCoupon($v['id']);
                        if (!(empty($reccoupon)))
                        {
                            $cansendreccoupon = true;
                        }
                    }
                }
                if ($cansendreccoupon)
                {
                    $plugin_coupon->taskposter(array('openid' => $openid), $v['id'], $v['couponnum'], $reccoupon['merchid']);
                }
            }
        }
    }
}