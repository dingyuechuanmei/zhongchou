<?php  

	$dos = array('index','case_api','list_api','get_detail','get_case');
	$do = in_array($do, $dos) ? $do : 'index';

	if($do == 'index'){
		echo 0;
		exit();
	}

	if($do == 'case_api'){
	    $number = $_GPC['num'] ? intval($_GPC['num']) : 10;
	    $list = pdo_fetchall('select id,title,intro,thumb,click from '.tablename('article_news').' where is_display = 1 and type = 2 order by displayorder desc limit 0,'.$number);
	    foreach ($list as &$val){
	        $val['thumb'] = $val['thumb'] ? tomedia($val['thumb']) : '' ;
	    }
	    $data = $list ? array( 'code'=>0,'list'=>$list) : array('code'=>1,'list'=>null);
	    echo $_GPC['callback'].'('.json_encode($data).')';
	    exit();
	}

	if($do == 'list_api'){    
	    $number = $_GPC['num'] ? intval($_GPC['num']) : 6;    
	    $list = pdo_fetchall('select id,title,intro,thumb,click from '.tablename('article_news').' where is_display = 1 and type = 1 order by displayorder desc limit 0,'.$number);    
	    foreach ($list as &$val){        
	        $val['thumb'] = $val['thumb'] ? tomedia($val['thumb']) : '' ;    
	    }    
	    $data = $list ? array( 'code'=>0,'list'=>$list) : array('code'=>1,'list'=>null);
	    echo $_GPC['callback'].'('.json_encode($data).')';
	    exit();
	}

	if($do == 'get_detail'){    
	    $id = intval($_GPC['id']);  
	    $info = pdo_fetch('select id,title,intro,content,click from '.tablename('article_news').' where id =:id limit 1',array(':id'=>$id));    
	    if($info){
	        $click = intval($info['click']);
	        pdo_update('article_news',array('click'=>++$click),array('id'=>$info['id']));
	    }
	    $data = $info ? array( 'code'=>0,'info'=>$info) : array('code'=>1,'info'=>'');    
	    echo $_GPC['callback'].'('.json_encode($data).')';
	    exit();
	}

	if($do == 'get_case'){
	    $id = intval($_GPC['id']);
	    $info = pdo_fetch('select id,title,top_intro,top_thumb,content,click from '.tablename('article_news').' where id =:id limit 1',array(':id'=>$id));
	    if($info){
	        $click = intval($info['click']);
	        $info['top_thumb'] = $info['top_thumb'] ? tomedia($info['top_thumb']) : '' ;
	        pdo_update('article_news',array('click'=>++$click),array('id'=>$info['id']));
	    }
	    $data = $info ? array( 'code'=>0,'info'=>$info) : array('code'=>1,'info'=>'');
	    echo $_GPC['callback'].'('.json_encode($data).')';
	    exit();
	}

?>