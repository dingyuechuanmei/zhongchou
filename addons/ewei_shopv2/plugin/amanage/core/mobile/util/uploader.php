<?php
if (!(defined('IN_IA'))) 
{
	exit('Access Denied');
}
class Uploader_EweiShopV2Page extends MobilePage 
{
	public function main() 
	{
		global $_W;
		global $_GPC;
		
		load()->func('file');
		$field = $_GPC['file'];

		if (!empty($_FILES[$field]['name'])) {
			
			if (is_array($_FILES[$field]['name'])) {
				$files = array();

				foreach ($_FILES[$field]['name'] as $key => $name) {

					$file = array(
						'name' => $name, 
						'type' => $_FILES[$field]['type'][$key], 
						'tmp_name' => $_FILES[$field]['tmp_name'][$key], 
						'error' => $_FILES[$field]['error'][$key], 
						'size' => $_FILES[$field]['size'][$key]
					);

					$ret = $this->uploadFile($file,2);

					if ($ret['status'] == 'error') {
						$ret = array('status' => 0);
					}
					else {
						$ret = array(
							'status' => 'success', 
							'filename' => $ret['filename'], 
							'url' => trim($_W['attachurl'] . $ret['filename'])
						);
					}

					$files[] = $ret;
				}

				$ret = array('status' => 'success', 'files' => $files);
				exit(json_encode($ret));
				return;
			}
		}

		$result['message'] = '请选择要上传的文件！';
		exit(json_encode($result));
	}

	function uploadFile($uploadfile,$type = 1)
	{
		global $_W;
		global $_GPC;
		$result['status'] = 'error';

		if ($uploadfile['error'] != 0) {
			$result['message'] = '上传失败';
			return $result;
		}

		load()->func('file');

		if (!is_dir(ATTACHMENT_ROOT . $path)) {
			mkdirs(ATTACHMENT_ROOT . $path);
		}

		$_W['uploadsetting'] = array();
		if($type == 1){
			$path = '/images/ewei_shopv2/' . $_W['uniacid'];
    		$_W['uploadsetting']['image']['folder'] = $path;
    		$_W['uploadsetting']['image']['extentions'] = $_W['config']['upload']['image']['extentions'];
    		$_W['uploadsetting']['image']['limit'] = $_W['config']['upload']['image']['limit'];
    		$file = file_upload($uploadfile, 'image');
		}

		elseif($type == 2){
			$path = '/videos/ewei_shopv2/' . $_W['uniacid'];
    		$_W['uploadsetting']['video']['folder'] = $path;
    		$_W['uploadsetting']['video']['extentions'] = $_W['config']['upload']['video']['extentions'];
    		$_W['uploadsetting']['video']['limit'] = $_W['config']['upload']['video']['limit'];
    		$file = file_upload($uploadfile, 'video');
		}

		if (is_error($file)) {
			$ext = pathinfo($uploadfile['name'], PATHINFO_EXTENSION);
			$ext = strtolower($ext);
			$result['message'] = $file['message'] . ' 扩展名: ' . $ext . ' 文件名: ' . $uploadfile['name'];
			return $result;
		}

		if (function_exists('file_remote_upload')) {
			$remote = file_remote_upload($file['path']);

			if (is_error($remote)) {
				$result['message'] = $remote['message'];
				return $result;
			}
		}

		$result['status'] = 'success';
		$result['url'] = $file['url'];
		$result['error'] = 0;
		$result['filename'] = $file['path'];
		$result['url'] = trim($_W['attachurl'] . $result['filename']);

		pdo_insert('core_attachment', array('uniacid' => $_W['uniacid'], 'uid' => $_W['member']['uid'], 'filename' => $uploadfile['name'], 'attachment' => $result['filename'], 'type' => $type, 'createtime' => TIMESTAMP));
		return $result;
	}

	public function remove() 
	{
		global $_W;
		global $_GPC;
		load()->func('file');
		$file = $_GPC['file'];
		file_delete($file);
		exit(json_encode(array('status' => 'success')));
	}
}
?>