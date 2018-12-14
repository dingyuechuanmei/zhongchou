<?php

if (!defined('IN_IA')) {
	exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'app/core/page_mobile.php';
class Uploader_EweiShopV2Page extends AppMobilePage
{
	public function upload()
	{
		global $_W;
		global $_GPC;
		load()->func('file');
		$field = $_GPC['file'];

		if (!empty($_FILES[$field]['name'])) {
			if (is_array($_FILES[$field]['name'])) {
				$files = array();

				foreach ($_FILES[$field]['name'] as $key => $name) {
					$file = array('name' => $name, 'type' => $_FILES[$field]['type'][$key], 'tmp_name' => $_FILES[$field]['tmp_name'][$key], 'error' => $_FILES[$field]['error'][$key], 'size' => $_FILES[$field]['size'][$key]);
					$ret = $this->uploadFile($file,1);

					if ($ret['status'] == 'error') {
						$ret = array('status' => 0);
					}
					else {
						$ret = array('status' => 1, 'filename' => $ret['path'], 'url' => trim($_W['attachurl'] . $ret['filename']));
					}

					$files[] = $ret;
				}

				app_json(array('files' => $files));
			}
			else {
				$result = $this->uploadFile($_FILES[$field],1);

				if ($result['status'] == 'error') {
					app_error(AppError::$UploadFail, $result['message']);
				}

				$files = array(
					array('status' => 1, 'url' => trim($_W['attachurl'] . $result['filename']), 'filename' => $result['filename'])
					);
				app_json(array('files' => $files));
			}
		}
		else {
			app_error(AppError::$UploadNoFile, '未选择图片');
		}
	}
	
	public function video(){
	    global $_W;
	    global $_GPC;
	    load()->func('file');
	    $field = $_GPC['file'];
	    
	    if (!empty($_FILES[$field]['name'])) {
	        if (!is_array($_FILES[$field]['name'])) {
	            
	            $result = $this->uploadFile($_FILES[$field],2);
	    
	            if ($result['status'] == 'error') {
	                app_error(AppError::$UploadFail, $result['message']);
	            }
	    
	            $files = array(
	                array('status' => 1, 'url' => trim($_W['attachurl'] . $result['filename']), 'filename' => $result['filename'])
	            );
	            app_json(array('files' => $files));
	        }
	    }
	    else {
	        app_error(AppError::$UploadNoFile, '未选择视频文件');
	    }
	}

	protected function uploadFile($uploadfile,$type = 1)
	{
		global $_W;
		global $_GPC;
		$result['status'] = 'error';

		if ($uploadfile['error'] != 0) {
			$result['message'] = '上传失败';
			return $result;
		}

		load()->func('file');
		$path = $type == 1 ? '/images/ewei_shop/' . $_W['uniacid'] : '/videos/ewei_shop/' . $_W['uniacid'];

		if (!is_dir(ATTACHMENT_ROOT . $path)) {
			mkdirs(ATTACHMENT_ROOT . $path);
		}

		$_W['uploadsetting'] = array();
		if($type == 1){
    		$_W['uploadsetting']['image']['folder'] = $path;
    		$_W['uploadsetting']['image']['extentions'] = $_W['config']['upload']['image']['extentions'];
    		$_W['uploadsetting']['image']['limit'] = $_W['config']['upload']['image']['limit'];
    		$file = file_upload($uploadfile, 'image');
		}else{
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
		app_json();
	}
}

?>
