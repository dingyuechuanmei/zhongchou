<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'app/core/page_mobile.php';
class Index_EweiShopV2Page extends AppMobilePage
{
    public $tb_forum = 'ewei_shop_raise_forum';
    public $tb_forum_banner = 'ewei_shop_raise_forum_banner';
    public $tb_forum_cate = 'ewei_shop_raise_forum_cate';
    public $tb_forum_review = 'ewei_shop_raise_forum_review';
    public $tb_report_cate = 'ewei_shop_raise_report_cate';
    public $tb_report = 'ewei_shop_raise_report';
    public $tb_favorite = 'ewei_shop_raise_favorite';
    
    public $tb_goods = 'ewei_shop_goods';
    
    public $tb_member = 'ewei_shop_member';
    
    public $uniacid;
    public $psize = 5;
    public $params;
    public $openid;
    
    /**
     * 获取关注列表
     */
    public function follow_list(){
        $this->deal_data();
        if(!empty($this->params['user_id'])){
            $params = array('id'=>intval($this->params['user_id']));
        }elseif(!empty($this->params['openid_'])){
            $params = array('openid'=>$this->params['openid_']);
        }else{
            $params = array('openid'=>$this->openid);
        }
        $member = pdo_get($this->tb_member,$params,array('follow_list'));
        if($member && $member['follow_list']){
            $list = iunserializer($member['follow_list']);
            if(is_array($list)){
                app_json(array('list'=>$list));
            }
        }
        app_error(1,'暂无数据');
    }
    
    /**
     * 获取粉丝列表
     */
    public function fans_list(){
        $this->deal_data();
        if(!empty($this->params['user_id'])){
            $params = array('id'=>intval($this->params['user_id']));
        }elseif(!empty($this->params['openid_'])){
            $params = array('openid'=>$this->params['openid_']);
        }else{
            $params = array('openid'=>$this->openid);
        }
        $member = pdo_get($this->tb_member,$params,array('fans_list'));
        if($member && $member['fans_list']){
            $list = iunserializer($member['fans_list']);
            if(is_array($list)){
                app_json(array('list'=>$list));
            }
        }
        app_error(1,'暂无数据');
    }
    
    /**
     * 修改签名
     */
    public function signature(){
        $this->deal_data();
        if(empty($this->params['signature'])){
            app_error(1,'签名错误');
        }
        $member = pdo_get($this->tb_member,array('openid'=>$this->openid),array('id','openid','signature'));
        if($member){
            pdo_update($this->tb_member,array('signature'=>$this->params['signature']),array('id'=>$member['id']));
            app_json(array('msg'=>'修改成功!'));
        }else{
            app_error(1,'数据错误');
        }
    }
    
    /**
     * 获取当前传入用户评论列表
     */
    public function review_list(){
        $this->deal_data();
        if(empty($this->params['openid_'])){
            app_error(1,'参数错误');
        }
        $page = $this->params['page'] ? intval($this->params['page']) : 1;
        $list = pdo_fetchall('select f.id,r.id rid,title,thumbs,review_count from '.tablename($this->tb_forum_review).' r left join '.tablename($this->tb_forum).' f on(r.forum_id = f.id) where r.openid=:openid order by r.id desc limit '.(($page-1) * $this->psize).','.$this->psize,array(':openid'=>$this->params['openid_']));
        if($list){
            foreach ($list as &$val){
                $val['favorite'] = pdo_fetchcolumn("select count(*) from ".tablename($this->tb_favorite)." where forum_id=:forumId ",array(":forumId"=>$val['id']));
                $temp = iunserializer($val['thumbs']);
                $val['img'] = is_array($temp) ? $temp[0] : '';
                unset($val['thumbs']);
            }
            app_json(array('list'=>$list));
        }else{
            app_error(1,'暂无数据');
        }
    } 
    
    
    /**
     * 获取当前传入用户收藏列表
     */
    public function favorite_list(){
        $this->deal_data();
        if(empty($this->params['openid_'])){
            app_error(1,'参数错误');
        }
        $page = $this->params['page'] ? intval($this->params['page']) : 1;
        $list = pdo_fetchall('select f.id,r.id fid,title,thumbs,review_count from '.tablename($this->tb_favorite).' r left join '.tablename($this->tb_forum).' f on(r.forum_id = f.id) where r.openid=:openid order by r.id desc limit '.(($page-1) * $this->psize).','.$this->psize,array(':openid'=>$this->params['openid_']));
        if($list){
            foreach ($list as &$val){
                $val['favorite'] = pdo_fetchcolumn("select count(*) from ".tablename($this->tb_favorite)." where forum_id=:forumId ",array(":forumId"=>$val['id']));
                $temp = iunserializer($val['thumbs']);
                $val['img'] = is_array($temp) ? $temp[0] : '';
                unset($val['thumbs']);
            }
            app_json(array('list'=>$list));
        }else{
            app_error(1,'暂无数据');
        }
    }
    
    /**
     * 获取当前传入用户的帖子
     */
    public function posts_list(){
        $this->deal_data();
        if(empty($this->params['openid_'])){
            app_error(1,'参数错误');
        }
        $page = $this->params['page'] ? intval($this->params['page']) : 1;
        $list = pdo_fetchall('select id,title,thumbs,review_count from '.tablename($this->tb_forum).' where openid=:openid order by id desc limit '.(($page-1) * $this->psize).','.$this->psize,array(':openid'=>$this->params['openid_']));
        if($list){
            foreach ($list as &$val){
                $val['favorite'] = pdo_fetchcolumn("select count(*) from ".tablename($this->tb_favorite)." where forum_id=:forumId ",array(":forumId"=>$val['id']));
                $temp = iunserializer($val['thumbs']);
                $val['img'] = is_array($temp) ? $temp[0] : '';
                unset($val['thumbs']);

                $params['forum_id'] = $val['id'];
                $val['comment_count'] = pdo_fetchcolumn('select count(1) from '.tablename($this->tb_forum_review)." where forum_id=:forum_id and reply_id = 0 ",$params);


            }
            app_json(array('list'=>$list));
        }else{
            app_error(1,'暂无数据');
        }
    }

    // 获取未评论的回复数量
    public function getReplyCount(){
        $this->deal_data();

        $openid = $this->openid;
        //$openid = 'sns_wa_ojIoH0dhaUzFmQSzG03Jd2X3MdEo';
        // 获取我的所有帖子
        $posts_list = pdo_fetchall('select id from '.tablename($this->tb_forum).' where openid = :openid ',array(':openid'=>$openid));
        $posts_list = $this->returnArr($posts_list);
        // 获取所有他人回复
        $replay_list = pdo_fetchall('select id from '.tablename($this->tb_forum_review).' where reply_id = 0 and forum_id in ('.implode(",", $posts_list).') ');
        $replay_list = $this->returnArr($replay_list);
        // 获得回复数量
        $re_count = pdo_fetchcolumn('select count(distinct forum_id) from '.tablename($this->tb_forum_review).' where reply_id in ('.implode(",", $replay_list).') and forum_id in ('.implode(",", $posts_list).') ');
        $count = 0;
        if(!empty($replay_list)){
            $count = intval(count($replay_list)) - intval($re_count);
        }
        app_json(array('replay_list'=>$replay_list,'count'=>$count));
    }
    public function returnArr($arr){
        if(empty($arr)){
            return false;
        }
        $result = [];
        foreach ($arr as $k=>$v){
            $result[] = $v['id'];
        }
        return $result;
    }
    /**
     * 个人中心
     */
    public function center(){
        $this->deal_data();
        if(!empty($this->params['user_id'])){
            $params = array('id'=>intval($this->params['user_id']));
        }elseif(!empty($this->params['openid_'])){
            $params = array('openid'=>$this->params['openid_']);
        }else{
            $params = array('openid'=>$this->openid);
        }
        $member = pdo_get($this->tb_member,$params,array('id','openid','nickname','avatar','follow_list','fans_list','signature'));
        if($member){
            $is_follow = 0;
            $member['follow_count'] = 0;
            $member['fans_count'] = 0;
            if($member['fans_list']){
                $fans_list = iunserializer($member['fans_list']);
                if(is_array($fans_list)){
                    $member['fans_count'] = count($fans_list);
                    foreach ($fans_list as $key=>$val){
                        if('sns_wa_ojIoH0e5g2N9vhWQQzbDyd_wWAb4' == $val['openid']){
                            $is_follow = 1;
                            break;
                        }
                    }
                }
            }
            $params = array(":openid"=>$member['openid']);
            //总帖子数
            $member['total_posts'] = pdo_fetchcolumn("select count(*) from ".tablename($this->tb_forum).' where openid =:openid',$params);
            //总收藏数
            $member['total_favorite'] = pdo_fetchcolumn("select count(*) from ".tablename($this->tb_favorite).' where openid =:openid',$params);
            //总评论数
            $member['total_review'] = pdo_fetchcolumn("select count(*) from ".tablename($this->tb_forum_review).' where openid =:openid',$params);
            if($member['follow_list']){
                $follow_list = iunserializer($member['follow_list']);
                if(is_array($follow_list)){
                    $member['follow_count'] = count($follow_list);
                }
            }
            unset($member['fans_list']);
            unset($member['follow_list']);
            $member['is_follow'] = $is_follow;
            app_json(array('member'=>$member));
        }else{
            app_error(1,'数据错误');
        }
    }
    
    /**
     * 收藏帖子
     */
    public function favorite(){
        $this->deal_data();
        if(empty($this->params['forum_id'])){
            app_error(1,'参数错误');
        }
        $favorite = pdo_get($this->tb_favorite,array('forum_id'=>$this->params['forum_id'],'openid'=>$this->openid),array('id'));
        if($favorite){
            pdo_delete($this->tb_favorite,array('id'=>$favorite['id']));
            $msg = '取消收藏';
        }else{
            pdo_insert($this->tb_favorite,array(
                'uniacid'=>$this->uniacid,
                'openid'=>$this->openid,
                'forum_id'=>intval($this->params['forum_id']),
                'createtime'=>time()
            ));
            $msg = '收藏成功';
        }
        app_json(array('msg'=>$msg));
    }
    
    /**
     * 举报
     */
    public function report_info(){
        $this->deal_data();
        if(empty($this->params['cate'])){
            app_error(1,'参数错误');
        }
        if(empty($this->params['detch_id'])){
            app_error(1,'参数错误');
        }
        /*if(empty($this->params['type'])){
            app_error(1,'参数错误');
        }*/
        pdo_insert('ewei_shop_raise_report',array(
            'uniacid'=>$this->uniacid,
            'openid'=>$this->openid,
            'cate'=>intval($this->params['cate']),
            'type'=>intval($this->params['type']),
            'detch_id'=>intval($this->params['detch_id']),
            'createtime'=>time()
        ));
        $id = pdo_insertid();
        if($id){
            app_json(array('msg'=>'举报成功'));
        }else{
            app_error(1,'举报失败');
        }
    }
    
    /**
     * 关注  —— 修改关注者粉丝列表  —— 修改自身关注列表 
     */
    public function follow(){
        $this->deal_data();
        if(empty($this->params['user_id']) || empty($this->openid)){
            app_error(1,'参数错误');
        }
        $info = pdo_get($this->tb_member,array('id'=>$this->params['user_id'],'uniacid'=>$this->uniacid),array('id','openid','nickname','avatar','fans_list'));
        $member = pdo_get($this->tb_member,array('openid'=>$this->openid,'uniacid'=>$this->uniacid),array('id','openid','nickname','avatar','follow_list'));
        if(!empty($info) && !empty($member)){
            $is_follow = 0;
            if($info['fans_list']){
                $fans_list = iunserializer($info['fans_list']);
                foreach ($fans_list as $key=>$val){
                    if($val && $this->openid == $val['openid']){
                        $is_follow = 1;
                        unset($fans_list[$key]);
                        break;
                    }
                }
                $msg = '取消关注';
            }
            $follow_list = array();
            if($member['follow_list']){
                $follow_list = iunserializer($member['follow_list']);
            }
            if(!$is_follow){
                $fans_list[] = array(
                    'openid'=>$member['openid'],
                    'nickname'=>$member['nickname'],
                    'avatar'=>$member['avatar'],
                );
                $msg = '关注成功';
                $follow_list[] = array(
                    'openid'=>$info['openid'],
                    'nickname'=>$info['nickname'],
                    'avatar'=>$info['avatar']
                );
            }else{
                //修改自身关注列表
                if(!empty($follow_list) && is_array($follow_list)){
                    foreach ($follow_list as $key=>$val){
                        if($info['openid'] == $val['openid']){
                            unset($follow_list[$key]);
                            break;
                        }
                    }
                }
            }
            //修改关注者粉丝列表
            pdo_update($this->tb_member,array(
                'fans_list'=>iserializer($fans_list)
            ),array('id'=>$info['id']));
            //修改自身关注列表
            pdo_update($this->tb_member,array(
                'follow_list'=>iserializer($follow_list)
            ),array('id'=>$member['id']));
            app_json(array('msg'=>$msg));
        }else{
            app_error(1,'数据错误');
        }
    }
    
    /**
     * 评论帖子——或回复评论
     */
    public function review_posts(){
        $this->deal_data();
        if(!$this->params['forum_id']){
            app_error(1,'帖子编号不能为空');
        }
        if(!$this->params['context']){
            app_error(1,'评论内容不能为空');
        }
        pdo_insert($this->tb_forum_review,array(
            'uniacid'=>$this->uniacid,
            'openid'=>$this->openid,
            'forum_id'=>intval($this->params['forum_id']),
            'reply_id'=>intval($this->params['reply_id']),
            'context'=>$this->params['context'],
            'createtime'=>time()
        ));
        $id = pdo_insertid();
        if($id > 0){
            $review_count = pdo_fetchcolumn('select review_count from '.tablename($this->tb_forum_review).' where id=:forumId',array(':forumId'=>$this->params['forum_id']));
            $review_count++;
            pdo_update($this->tb_forum_review,array('review_count'=>$review_count),array('id'=>$this->params['forum_id']));
            app_json(array('id'=>$id,'msg'=>'评论成功'));
        }else{
            app_error(1,'评论失败!');
        }
    }
    
    /**
     * 删除帖子
     */
    public function delete_posts(){
        $this->deal_data();
        if(!$this->params['forum_id']){
            app_error(1,'帖子不存在');
        }
        $params = array(
            'forum_id'=>intval($this->params['forum_id'])
        );
        $forum = pdo_get($this->tb_forum,array('id'=>$params['forum_id']));
        //删除收藏
        pdo_delete($this->tb_favorite,$params);
        //删除评论
        pdo_delete($this->tb_forum_review,$params);
        //清理类型数
        $forum_count = pdo_fetchcolumn("select forum_count from ".tablename($this->tb_forum_cate)." where id=:id",array(":id"=>$forum['cate']));
        //删除 帖子
        pdo_delete($this->tb_forum,array('id'=>$params['forum_id']));
        pdo_update($this->tb_forum_cate,array('forum_count'=>++$forum_count),array(":id"=>$forum['cate']));
        app_json(array('msg'=>'删除成功'));
    }
    
    /**
     * 发布帖子
     */
    public function issue_posts(){
        $this->deal_data();
        $leng = mb_strlen($this->params['title'],'utf8');
        if(!$this->params['title'] || !( $leng > 3 && $leng < 18)){
            app_error(1,'标题在 3 ~ 18 字内');
        }
        if(!$this->params['cate']){
            app_error(1,'请选择版块');
        }
        $thumbs = $tecom_good = '';
        if($this->params['thumbs']){
            $temp = explode(',',$this->params['thumbs']);
            if($temp && is_array($temp)){
                $thumbs = iserializer($temp);
            }
        }
        if($this->params['tecom_good']){
            $temp = explode(',',$this->params['tecom_good']);
            foreach ($temp as $k=>$v) {
                $v = intval($v);
                if(empty($v)){
                    unset($temp[$k]);
                }
            }
            $goodsIn = implode(',',$temp);
            $list = pdo_fetchall("select id,title as name,thumb as img,marketprice as price from ".tablename($this->tb_goods)." where id in ($goodsIn)");
            if($list && is_array($list)){
                foreach ($list as &$row){
                    $row['img'] = 'https://xiaochengxu.bmwtech.cn/attachment/'.$row['img'];
                }
                unset($row);
                $tecom_good = iserializer($list);
            }
        }
        pdo_insert($this->tb_forum,array(
            'uniacid'=>$this->uniacid,
            'openid'=>$this->openid,
            'title'=>$this->params['title'],
            'cate'=>$this->params['cate'],
            'context'=>$this->params['context'],
            'thumbs'=>$thumbs,
            'recom_list'=>$tecom_good,
            'is_show'=>1,
            'createtime'=>time()
        ));
        $id = pdo_insertid();
        if($id > 0){
            $forum_count = pdo_fetchcolumn("select forum_count from ".tablename($this->tb_forum_cate)." where id=:id ",array(":id"=>$this->params['cate']));
            $forum_count++;
            pdo_update($this->tb_forum_cate,array('forum_count'=>$forum_count),array('id'=>$this->params['cate']));
            app_json(array('id'=>$id,'msg'=>'发帖成功'));
        }else{
            app_error(1,'发帖失败!');
        }
    }
    
    /**
     * 帖子点赞
     */
    public function posts_prase(){
        $this->deal_data();
        if(empty($this->params['forum_id'])){
            app_error(1,'参数错误');
        }
        $forum = pdo_get($this->tb_forum,array('id'=>$this->params['forum_id'],'uniacid'=>$this->uniacid),array('id','praise_list'));
        if($forum){
            $is_prase = 0;
            if($forum['praise_list']){
                $prase_list = iunserializer($forum['praise_list']);
                foreach ($prase_list as $key=>$val){
                    if($this->openid == $val['openid']){
                        $is_prase = 1;
                        unset($prase_list[$key]);
                        break;
                    }
                }
                $msg = '取消点赞';
            }
            if(!$is_prase){
                $member = pdo_get($this->tb_member,array('openid'=>$this->openid,'uniacid'=>$this->uniacid),array('openid','nickname','avatar'));
                $prase_list[] = $member;
                $msg = '点赞成功';
            }
            pdo_update($this->tb_forum,array(
                'praise_list'=>iserializer($prase_list),
            ),array('id'=>$forum['id']));
            app_json(array('msg'=>$msg));
        }else{
            app_error(1,'数据错误');
        }
    }
    
    /**
     * 评论点赞
     */
    public function review_prase(){
        $this->deal_data();
        if(empty($this->params['review_id'])){
            app_error(1,'参数错误');
        }
        $review = pdo_get($this->tb_forum_review,array('id'=>$this->params['review_id'],'uniacid'=>$this->uniacid),array('id','prase_count','prase_list'));
        if($review){
            $is_prase = 0;
            if($review['prase_list']){
                $prase_list = explode(',',$review['prase_list']);
                if(in_array($this->openid,$prase_list)){
                    $is_prase = 1;
                }
            }
            if($is_prase){
                foreach ($prase_list as $key=>$val){
                    if($this->openid == $val){
                        $review['prase_count']--;
                        unset($prase_list[$key]);
                        break;
                    }
                }
                $msg = '取消点赞';
            }else{
                $prase_list[] = $this->openid;
                $review['prase_count']++;
                $msg = '点赞成功';
            }
            pdo_update($this->tb_forum_review,array(
                'prase_count'=>$review['prase_count'],
                'prase_list'=>implode(',',$prase_list),  
            ),array('id'=>$review['id']));
            app_json(array('msg'=>$msg));
        }else{
            app_error(1,'数据错误');
        }
    }
    
    /**
     * 论坛评论
     */    
    public function forum_review(){
        $this->deal_data();
        if(empty($this->params['forum_id'])){
            app_error(1,'参数错误');
        }
        $condition = ' where forum_id=:fid and r.uniacid = :uniacid and reply_id = 0';
        $params = array(':fid'=>$this->params['forum_id'],':uniacid'=>$this->uniacid);
        $review_list = pdo_fetchall('select r.id,r.context,r.prase_count,r.prase_list,r.createtime,m.avatar,m.nickname from '.tablename($this->tb_forum_review).' r left join '.tablename($this->tb_member).' m on(r.openid = m.openid)' .$condition,$params);
        if($review_list){
            foreach ($review_list as &$item){
                $item['createtime'] = date('m-d',$item['createtime']);
                $item['is_prase'] = 0;
                if($item['prase_list']){
                    $prase_list = explode(',',$item['prase_list']);
                    if(in_array($this->openid,$prase_list)){
                        $item['is_prase'] = 1;
                    }
                }
                $condition = " where forum_id=:fid and r.uniacid = :uniacid and reply_id = {$item['id']}";
                $item['child_review'] = pdo_fetchall('select r.id,r.context,r.prase_count,r.prase_list,r.createtime,m.avatar,m.nickname from '.tablename($this->tb_forum_review).' r left join '.tablename($this->tb_member).' m on(r.openid = m.openid)' .$condition,$params);
                foreach ($item['child_review'] as &$v){
                    $v['createtime'] = date('m-d',$v['createtime']);
                    $v['is_prase'] = 0;
                    if($v['prase_list']){
                        $prase_list = explode(',',$v['prase_list']);
                        if(in_array($this->openid,$prase_list)){
                            $v['is_prase'] = 1;
                        }
                    }
                    unset($v['prase_list']);
                }
                unset($item['prase_list']);
            }
            app_json(array('review_list'=>$review_list));
        }else{
            app_error(1,'暂无数据');
        }
    }
    
    /**
     * 获取帖子详情
     */
    public function posts_info(){
        $this->deal_data();
        if(empty($this->params['forum_id'])){
            app_error(1,'参数错误');
        }
        $condition = ' where f.id=:fid and f.uniacid = :uniacid';
        $params = array(':fid'=>$this->params['forum_id'],':uniacid'=>$this->uniacid);
        $forum_info = pdo_fetch('select f.id,f.title,f.context,thumbs,f.createtime,recom_list,praise_list,m.id mid,m.avatar,m.nickname,m.fans_list,view_count,review_count,is_top,c.title source from '.tablename($this->tb_forum).' f left join '.tablename($this->tb_member).' m on(f.openid = m.openid) left join '.tablename($this->tb_forum_cate).' c on(c.id = f.cate) '.$condition.' limit 1',$params);
        if($forum_info){
            $forum_info['thumbs'] = $forum_info['thumbs'] ? iunserializer($forum_info['thumbs']) : array();
            $forum_info['recom_list'] = $forum_info['recom_list'] ? iunserializer($forum_info['recom_list']) : array();
            $forum_info['praise_list'] = $forum_info['praise_list'] ? iunserializer($forum_info['praise_list']) : array();
            $forum_info['praise_count'] = count($forum_info['praise_list']);
            $forum_info['fans_list'] = $forum_info['fans_list'] ? iunserializer($forum_info['fans_list']) : array();
            $forum_info['createtime'] = date("m-d",$forum_info['createtime']);
            $forum_info['is_favorite'] = $forum_info['is_praise'] = $forum_info['is_follow'] = 0;
            if(!empty($forum_info['fans_list'])){
                foreach ($forum_info['fans_list'] as $val){
                    if($val['openid'] == $this->openid){
                        $forum_info['is_follow'] = 1;
                        break;
                    }
                }
            }
            if(!empty($forum_info['praise_list'])){
                foreach ($forum_info['praise_list'] as $val){
                    if($val['openid'] == $this->openid){
                        $forum_info['is_praise'] = 1;
                        break;
                    }
                }
            }
            $id = pdo_fetchcolumn("select id from ".tablename($this->tb_favorite)." where forum_id=:fid and openid=:openid ",array(":fid"=>$forum_info['id'],":openid"=>$this->openid));
            if($id){
                $forum_info['is_favorite'] = 1;
            }
            
            
            //观察数加一
            pdo_update($this->tb_forum,"view_count = view_count + 1",array('id'=>$forum_info['id']));
            unset($forum_info['view_count']);
            unset($forum_info['fans_list']);
            app_json(array('forum_info'=>$forum_info));
        }else{
            app_error(1,'暂无数据');
        }
    }
    /**
     * 热门帖子
     */
    public function hot_posts(){
        $this->deal_data();
        $condition = ' where f.uniacid = :uniacid and f.is_show = 1';
        $params = array(':uniacid'=>$this->uniacid);
        $forum_list = pdo_fetchall('select f.id,m.id mid,m.avatar,m.nickname,f.title,thumbs,view_count,review_count,is_top,c.title source from '.tablename($this->tb_forum).' f left join '.tablename($this->tb_member).' m on(f.openid = m.openid) left join '.tablename($this->tb_forum_cate).' c on(c.id = f.cate) '.$condition.' order by f.view_count desc limit 0,3',$params);
        if($forum_list){
            foreach($forum_list as &$item){
                $item['thumbs'] = $item['thumbs'] ? iunserializer($item['thumbs']) : array();
            }
            app_json(array('forum_list'=>$forum_list));
        }else{
            app_error(1,'暂无数据');
        }
    }
    
    /**
     * 查询当前类型
     */
    public function forum_cateinfo(){
        $this->deal_data();
        if(empty($this->params['cate_id'])){
            app_error(1,'参数错误');
        }
        $forum_cate = pdo_fetch('select id,title,context,ico,thumb,forum_count from '.tablename($this->tb_forum_cate).' where id=:id and uniacid=:uniacid and is_show = 1 limit 1 ',array(':id'=>$this->params['cate_id'],':uniacid'=>$this->uniacid));
        if($forum_cate){
            $forum_cate['ico'] = tomedia($forum_cate['ico']);
            $forum_cate['thumb'] = tomedia($forum_cate['thumb']);
            $forum_cate['forum_count'] = pdo_fetchcolumn("SELECT count(*) FROM ".tablename($this->tb_forum)." WHERE cate = :cate",array(':cate'=>$forum_cate['id']));
            app_json(array('forum_cate'=>$forum_cate));
        }else{
            app_error(1,'暂无数据');
        }
    } 
    
    /**
     * 帖子列表分页显示—— +搜索
     */
    public function forum_list(){
        $this->deal_data();
        $page = $this->params['page'] ? intval($this->params['page']) : 1;
        $condition = ' where f.uniacid = :uniacid and f.is_show = 1';
        $params = array(':uniacid'=>$this->uniacid);
        if($this->params['keyword'] !=''){
            //$condition.=' and (f.title like :keyword or f.context like :keyword)';
            $condition.=' and (f.title like :keyword)';
            $params[':keyword'] = '%' . trim($this->params['keyword']) . '%';
        }
        if($this->params['cate'] !='' && $this->params['cate'] !=0){
            $condition.=' and f.cate = '.intval($this->params['cate']);
        }
        $forum_list = pdo_fetchall('select f.id,m.id mid,m.avatar,m.nickname,m.mobile,f.title,thumbs,view_count,review_count,is_top,c.title source from '.tablename($this->tb_forum).' f left join '.tablename($this->tb_member).' m on(f.openid = m.openid) left join '.tablename($this->tb_forum_cate).' c on(c.id = f.cate) '.$condition.' order by f.is_top desc,f.id desc limit '.(($page-1) * $this->psize).','.$this->psize,$params);
        if($forum_list){
            foreach($forum_list as &$item){
                $item['thumbs'] = $item['thumbs'] ? iunserializer($item['thumbs']) : array();

                $params = array(':fid'=>$item['id'],':uniacid'=>$this->uniacid);
                $item['review_list'] = pdo_fetchall('select r.id,r.context,r.prase_count,r.prase_list,r.createtime,m.avatar,m.nickname from '.tablename($this->tb_forum_review).' r left join '.tablename($this->tb_member).' m on(r.openid = m.openid) where forum_id=:fid and r.uniacid = :uniacid and reply_id = 0 order by id desc limit 2',$params);
            }
            app_json(array('forum_list'=>$forum_list));
        }else{
            app_error(1,'暂无数据');
        }
    }
    
    /**
     * 分页获取推荐商品
     */
    public function recom_good(){
        $this->deal_data();
        $page = $this->params['page'] ? intval($this->params['page']) : 1;
        $condition = "";
        if($this->params['keyword']){
            $condition = " and title like '%".$this->params['keyword']."%'";
        }
        $goods_list = pdo_fetchall('select id,title,thumb,marketprice from '.tablename($this->tb_goods).' where uniacid=:uniacid '.$condition.' and status = 1 order by id desc limit '.(($page-1) * $this->psize).','.$this->psize,array(':uniacid'=>$this->uniacid));
        if($goods_list){
            foreach($goods_list as &$item){
                $item['thumb'] = tomedia($item['thumb']);
            }
            app_json(array('goods_list'=>$goods_list));
        }else{
            app_error(1,'暂无商品数据');
        }
    }
    
    /**
     * 获取所有横幅
     */
    public function forum_banner(){
        $this->deal_data();
        $banner_list = pdo_fetchall('select id,bannername,thumb from '.tablename($this->tb_forum_banner).' where uniacid=:uniacid and enabled =1',array(':uniacid'=>$this->uniacid));
        if($banner_list){
            app_json(array('banner_list'=>$banner_list));
        }else{
            app_error(1,'暂无横幅数据');
        }
    }
    
    /**
     * 获取论坛类型
     */
    public function forum_cate(){
        $this->deal_data();
        $cate_list = pdo_fetchall('select id,title,ico from '.tablename($this->tb_forum_cate).' where uniacid=:uniacid',array(':uniacid'=>$this->uniacid));
        if($cate_list){
            foreach ($cate_list as &$val){
                $val['ico'] = tomedia($val['ico']);
            }
            app_json(array('cate_list'=>$cate_list));
        }else{
            app_error(1,'暂无举报数据');
        }
    }
    
    /**
     * 获取所有举报类型
     */
    public function report_cate(){
        $this->deal_data();
        $cate_list = pdo_fetchall('select id,category from '.tablename($this->tb_report_cate).' where uniacid=:uniacid',array(':uniacid'=>$this->uniacid));
        if($cate_list){
            app_json(array('cate_list'=>$cate_list));
        }else{
            app_error(1,'暂无举报数据');
        }
    }
    
    
    private function deal_data(){
        global $_W;global $_GPC;
        $this->uniacid = intval($_W['uniacid']);
        $this->openid = $_W['openid'];
        $this->params = $_GPC;
    }
}