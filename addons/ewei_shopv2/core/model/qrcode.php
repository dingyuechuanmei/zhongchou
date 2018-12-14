<?php
if (!(defined('IN_IA'))) 
{
	exit('Access Denied');
}
class Qrcode_EweiShopV2Model 
{
	public function createShopQrcode($mid = 0, $posterid = 0) 
	{
		global $_W;
		global $_GPC;
		$path = IA_ROOT . '/addons/ewei_shopv2/data/qrcode/' . $_W['uniacid'] . '/';
		if (!(is_dir($path))) 
		{
			load()->func('file');
			mkdirs($path);
		}
		$url = mobileUrl('', array('mid' => $mid), true);
		if (!(empty($posterid))) 
		{
			$url .= '&posterid=' . $posterid;
		}
		$file = 'shop_qrcode_' . $posterid . '_' . $mid . '.png';
		$qrcode_file = $path . $file;
		if (!(is_file($qrcode_file))) 
		{
			require_once IA_ROOT . '/framework/library/qrcode/phpqrcode.php';
			QRcode::png($url, $qrcode_file, QR_ECLEVEL_L, 4);
		}
		return $_W['siteroot'] . 'addons/ewei_shopv2/data/qrcode/' . $_W['uniacid'] . '/' . $file;
	}
	public function createGoodsQrcode($mid = 0, $goodsid = 0, $posterid = 0) 
	{
		global $_W;
		global $_GPC;
		$path = IA_ROOT . '/addons/ewei_shopv2/data/qrcode/' . $_W['uniacid'];
		if (!(is_dir($path))) 
		{
			load()->func('file');
			mkdirs($path);
		}
		$url = mobileUrl('goods/detail', array('id' => $goodsid, 'mid' => $mid), true);
		if (!(empty($posterid))) 
		{
			$url .= '&posterid=' . $posterid;
		}
		$file = 'goods_qrcode_' . $posterid . '_' . $mid . '_' . $goodsid . '.png';
		$qrcode_file = $path . '/' . $file;
		if (!(is_file($qrcode_file))) 
		{
			require_once IA_ROOT . '/framework/library/qrcode/phpqrcode.php';
			QRcode::png($url, $qrcode_file, QR_ECLEVEL_L, 4);
		}
		return $_W['siteroot'] . 'addons/ewei_shopv2/data/qrcode/' . $_W['uniacid'] . '/' . $file;
	}
	public function createQrcode($url) 
	{
		global $_W;
		global $_GPC;
		$path = IA_ROOT . '/addons/ewei_shopv2/data/qrcode/' . $_W['uniacid'] . '/';
		if (!(is_dir($path))) 
		{
			load()->func('file');
			mkdirs($path);
		}
		$file = md5(base64_encode($url)) . '.jpg';
		$qrcode_file = $path . $file;
		if (!(is_file($qrcode_file))) 
		{
			require_once IA_ROOT . '/framework/library/qrcode/phpqrcode.php';
			QRcode::png($url, $qrcode_file, QR_ECLEVEL_L, 4);
		}
		return $_W['siteroot'] . 'addons/ewei_shopv2/data/qrcode/' . $_W['uniacid'] . '/' . $file;
	}

    /**
     * @param $wx_app_path  小程序页面路径     例:'pages/index/index?merchid='.$merchid;
     * @return string       小程序二维码src
     */
    public function createMinAppQrcode($wx_app_path)
    {
        global $_W;
        $path = IA_ROOT . '/addons/ewei_shopv2/data/qrcode/' . $_W['uniacid'] . '/';
        if (!(is_dir($path)))
        {
            load()->func('file');
            mkdirs($path);
        }
        $file = md5(base64_encode($wx_app_path)) . '.jpg';
        $file_path = $path.$file;
        if (!(is_file($file_path)))
        {
            $token_time = time()-7000;
            $token = $this->getToken(1);
            load()->func('communication');
            $wx_url = 'https://api.weixin.qq.com/wxa/getwxacode?access_token='.$token;
            $wx_data = json_encode(['path'=>$wx_app_path]);
            $wx_qrcode = ihttp_request($wx_url,$wx_data);
            file_put_contents($file_path,$wx_qrcode['content']);
        }

        return $_W['siteroot'] . 'addons/ewei_shopv2/data/qrcode/' . $_W['uniacid'] . '/' .$file;
    }

    /**
     * @param $type
     * @return bool     获取token，1小程序token，2公众号token
     */
    public function getToken($type)
    {
        $token_time = time()-7000;
        $token = pdo_fetchcolumn("SELECT token FROM ".tablename('ewei_shop_token')."WHERE `type` = :type AND createtime>:token_time",[':type'=>$type,':token_time'=>$token_time]);
        load()->func('communication');
        if(empty($token)){
            $data = m('common')->getSysset('app');
            if ($type == 1) {
                $appid = $data['appid'];
                $secret = $data['secret'];
            } else {
                $appid = 'wxbfb64546e03feeae';
                $secret = '240026e5f7dfbae8a09ce9c272387a5f';
            }
            $wx_token_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$secret";
            $json_token = ihttp_request($wx_token_url);
            $token_arr = json_decode($json_token['content']);
            $token = $token_arr->access_token;
            pdo_update('ewei_shop_token',['token'=>$token,'createtime'=>time()],['type'=>$type]);
        }
        return $token;
    }

    /**
     * @param $openid
     * @param $template_id
     * @param $msg
     * @return bool|mixed|string        公众号发送模板消息
     */
    public function mySendTplMessage($openid,$template_id,$msg)
    {
        if (empty($openid) || empty($template_id) || empty($msg)) {
            return false;
        }
        load()->func('communication');
        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$this->getToken(2);
        $post = array(
            'touser' => $openid,
            'template_id' => $template_id,
            'data' => $msg
        );
        $response = ihttp_request($url,json_encode($post));
        return json_encode($response);
    }

    /**
     * 发送小程序模板id
     * @param $openid  用户id
     * @param $template_id 模板id
     * @param $form_id  表单提交场景下，为 submit 事件带上的 formId；支付场景下，为本次支付的 prepay_id
     * @param $msg  模板内容
     * @return mixed|string
     */
    public function myAppSendTplMessage($openid,$template_id,$form_id,$msg)
    {
        load()->func('communication');
        $url = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token='.$this->getToken(1);
        $post = array(
            'touser' => $openid,
            'template_id' => $template_id,
            'form_id' => $form_id,
            'data' => $msg
        );
        $response = ihttp_request($url,json_encode($post));
        return json_encode($response);
    }

}
?>