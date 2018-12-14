<?php
if (!(defined('IN_IA'))) 
{
	exit('Access Denied');
}
require IA_ROOT . '/addons/ewei_shopv2/defines.php';
require EWEI_SHOPV2_INC . '/plugin_processor.php';
class AmanageProcessor extends PluginProcessor 
{
	public function __construct() 
	{
		parent::__construct('amanage');
	}
	public function respond($obj = NULL) 
	{
		global $_W;
		$message = $obj->message;
		$content = $obj->message['content'];
		$msgtype = strtolower($message['msgtype']);
		$event = strtolower($message['event']);
		if (($msgtype == 'text') || ($event == 'click')) 
		{
			$data = m('common')->getPluginset('amanage');
			if (empty($data)) 
			{
				return $this->responseEmpty();
			}
			if (empty($data['status']) || empty($data['open'])) 
			{
				return $this->responseEmpty();
			}
			$resp = array('title' => $data['title'], 'description' => $data['desc'], 'picurl' => tomedia($data['thumb']), 'url' => mobileUrl('amanage', array(), true));
			return $obj->respNews($resp);
		}
		return $this->responseEmpty();
	}
	private function responseEmpty() 
	{
		ob_clean();
		ob_start();
		echo '';
		ob_flush();
		ob_end_flush();
		exit(0);
	}
}
?>