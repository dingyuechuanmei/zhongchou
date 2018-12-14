<?php
define('IN_SYS', true);
require '../framework/bootstrap.inc.php';
load()->web('common');
load()->web('template');
header('Content-Type: text/html; charset=UTF-8');
$uniacid = intval($_GPC['i']);
$cookie = $_GPC['__uniacid'];
// if (empty($uniacid) && empty($cookie)) {
// 	die('Access Denied.');
// }
session_start();
if (!empty($uniacid)) {
	$_SESSION['__agent_uniacid'] = $uniacid;
	isetcookie('__uniacid', $uniacid, 7 * 86400);
}
$site = WeUtility::createModuleSite('sea_childreservation');

if (!is_error($site)) {
	$method = 'doWebWeb';
	$site->uniacid = $uniacid;
	$site->inMobile = false;

	if (method_exists($site, $method)) {
		
		$site->{$method}();
		die;
	}
}
