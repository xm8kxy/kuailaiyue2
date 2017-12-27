<?php
namespace Qwadmin\Controller;

class SWFuploadController extends ComController
{
public function photo_add(){
		$id = I('get.store');
		if(preg_match("/[^\d-., ]/",$id)){
			echo "<script type='text/javascript'> alert('请选择正确店铺上传图片!'); </script>";
		}
		$this->assign('id',$id);
		$this->display('SWFupload');
	}
	
	//提交图片上传
	
	public function addphoto() {

        $type = array('.jpg','.jpeg','.png','.gif');
        if(in_array(strrchr($FILES['Filedata']['name'],'.'),$type)){
			$this->error('文件类型不允许');
        }
        $store_id = $this->_get('store');
        $path = C('UPLOAD_PATH') . '/storeimg/' . ($store_id % 1000) . '/' . $store_id . '/tmp';//文件路径
         if(!is_dir($path)) {
		    if(!mkdir ( $path, 0777, true )) { //创建临时文件夹
                $this->error('创建目录失败');
            }
        }
        move_uploaded_file ( $_FILES ["Filedata"] ["tmp_name"], $path . "/" . $_FILES ["Filedata"] ["name"] ); //移动文件
        
        echo $FILES["Filedata"]['name'];
	}
	
	//移动文件写入库
	public function photo_save(){
		$store_id = $this->_get('store');
		if(preg_match("/[^\d-., ]/",$store_id)){
			unlink(C('UPLOAD_PATH') . 'storeimg/0'); //删除全部文件
			@rmdir(C('UPLOAD_PATH') . 'storeimg/0'); //删除文件夹
			$this->error("请选择正确的店铺" , $_SESSION['accountInfo']['list_url']);
		}
		$from = C('UPLOAD_PATH') . 'storeimg/' . ($store_id % 1000) . '/' . $store_id . '/tmp/';//获取文件存放的临时文件夹
        $rpath = C('UPLOAD_PATH') . 'storeimg/' . ($store_id % 1000) . '/' . $store_id . '/';  //将文件存放到的目录
        
        if(!is_dir($from)){	//判断文件夹是否存在
        	echo "<script type='text/javascript'> alert('请选择上传的图片!'); window.location.href = '?m=Audit&a=photo_add'; </script>";
        }
        $files = scandir($from);
        
        foreach($files as $k=>$file){
			if(in_array($file,array('.','..'))){
			continue;
			}
			$new_file_name = 'album_'.time().'_'.rand(1000, 9999).$k.'.jpg';
			$subpath = $from.$file;
			$topath = $rpath.$new_file_name;
			if( rename( $subpath, $topath )){
				$db_photo = M('photo');
				$data['store_id'] = $store_id;
				$data['title'] = '';
				$data['imgpath'] = ($store_id % 1000) . '/' . $store_id . '/'.$new_file_name;
				$data['addtime'] = time();
				$data['status'] = '1';
				if($db_photo->add($data)){
					echo "<script type='text/javascript'> alert('上传图片成功!'); window.location.href = '".$_SESSION['accountInfo']['list_url']."'; </script>";
				}else{
					echo "<script type='text/javascript'> alert('错误1，上传图片失败，请联系管理员!'); window.location.href = '?m=Audit&a=photo_add'; </script>";
				}
			}else{
				echo "<script type='text/javascript'> alert('错误2，上传图片失败，请联系管理员!'); window.location.href = '?m=Audit&a=photo_add'; </script>";
			}
		}
		rmdir($from); //删除临时文件夹
	}


}

?>