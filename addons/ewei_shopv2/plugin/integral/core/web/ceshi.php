<?php
if (!(defined('IN_IA'))) 
{
	exit('Access Denied');
}
class Ceshi_EweiShopV2Page extends PluginWebPage 
{
	public function main() 
	{
	    include $this->template();
	}
	
	public function detail(){
	    
	}
}
?>