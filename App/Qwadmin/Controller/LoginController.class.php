<?php


namespace Qwadmin\Controller;

use Qwadmin\Controller\ComController;

class LoginController extends ComController
{
    public function index()
    {
        $flag = $this->check_login();
        if ($flag) {
            $this->error('您已经登录,正在跳转到主页', U("index/index"));
        }

        $this->display();
    }

    public function login()
    {
        $verify = isset($_POST['verify']) ? trim($_POST['verify']) : '';
        if (!$this->check_verify($verify, 'login')) {
            $this->error('验证码错误！', U("login/index"));
        }

        $username = isset($_POST['user']) ? trim($_POST['user']) : '';
        $password = isset($_POST['password']) ? password(trim($_POST['password'])) : '';
        $remember = isset($_POST['remember']) ? $_POST['remember'] : 0;
        if ($username == '') {
            $this->error('用户名不能为空！', U("login/index"));
        } elseif ($password == '') {
            $this->error('密码必须！', U("login/index"));
        }

        $model = M("Member");
        $user = $model->field('uid,user')->where(array('user' => $username, 'password' => $password))->find();

        if ($user) {
            $salt = C("COOKIE_SALT");
            $ip = get_client_ip();
            $ua = $_SERVER['HTTP_USER_AGENT'];
            session_start();
            session('uid',$user['uid']);
            //加密cookie信息
            $auth = password($user['uid'].$user['user'].$ip.$ua.$salt);
            if ($remember) {
                cookie('auth', $auth, 3600 * 24 * 365);//记住我
            } else {
                cookie('auth', $auth);
            }
            addlog('登录成功。');
            $url = U('index/index');
            header("Location: $url");
            exit(0);
        } else {
            addlog('登录失败。', $username);
            $this->error('登录失败，请重试！', U("login/index"));
        }
    }

    function check_verify($code, $id = '')
    {
        $verify = new \Think\Verify();
        return $verify->check($code, $id);
    }

    public function verify()
    {
        $config = array(
            'fontSize' => 14, // 验证码字体大小
            'length' => 4, // 验证码位数
            'useNoise' => false, // 关闭验证码杂点
            'imageW' => 100,
            'imageH' => 30,
        );
        $verify = new \Think\Verify($config);
        $verify->entry('login');
    }

    public function upload(){

        if (isset($_POST["PHPSESSID"])) {
            session_id($_POST["PHPSESSID"]);
        } else if (isset($_GET["PHPSESSID"])) {
            session_id($_GET["PHPSESSID"]);
        }

        session_start();

        $POST_MAX_SIZE = ini_get('post_max_size');
        $unit = strtoupper(substr($POST_MAX_SIZE, -1));
        $multiplier = ($unit == 'M' ? 1048576 : ($unit == 'K' ? 1024 : ($unit == 'G' ? 1073741824 : 1)));

        if ((int)$_SERVER['CONTENT_LENGTH'] > $multiplier*(int)$POST_MAX_SIZE && $POST_MAX_SIZE) {
            header("HTTP/1.1 500 Internal Server Error");
            echo "POST exceeded maximum allowed size.";
            exit(0);
        }

// Settings
        $pathd="/file/".date('Ymd',time()).'/';
        $path=getcwd() . $pathd;

        if(!is_dir($path)) {
            if(!mkdir ( $path, 0777, true )) { //创建临时文件夹

                $this->HandleError("创建目录失败");
                exit(0);
            }

        }
        $save_path =  $path;
     //   $save_path = getcwd() . "/file/";
     //   $save_path = getcwd() . "/file/";				// The path were we will save the file (getcwd() may not be reliable and should be tested in your environment)
        $upload_name = "Filedata";

        $max_file_size_in_bytes = 2147483647;				// 2GB in bytes
        $extension_whitelist = array("mp4");	// Allowed file extensions
        $valid_chars_regex = '.A-Z0-9_ !@#$%^&()+={}\[\]\',~`-';				// Characters allowed in the file name (in a Regular Expression format)

// Other variables
        $MAX_FILENAME_LENGTH = 260;
        $file_name = "";
        $file_extension = "";
        $uploadErrors = array(
            0=>"文件上传成功",
            1=>"上传的文件超过了 php.ini 文件中的 upload_max_filesize directive 里的设置",
            2=>"上传的文件超过了 HTML form 文件中的 MAX_FILE_SIZE directive 里的设置",
            3=>"上传的文件仅为部分文件",
            4=>"没有文件上传",
            6=>"缺少临时文件夹"
        );


     //   $_FILES[$upload_name]['name']=date('Y-m-d H:i:s',time()).'_'.rand();
      //  $_FILES[$upload_name]['name']=I('get.uid').'_'.date('Y-m-d H:i:s',time()).'_'.rand();

        if (!isset($_FILES[$upload_name])) {
            $this->HandleError("No upload found in \$_FILES for " . $upload_name);
            exit(0);
        } else if (isset($_FILES[$upload_name]["error"]) && $_FILES[$upload_name]["error"] != 0) {
            $this->HandleError($uploadErrors[$_FILES[$upload_name]["error"]]);
            exit(0);
        } else if (!isset($_FILES[$upload_name]["tmp_name"]) || !@is_uploaded_file($_FILES[$upload_name]["tmp_name"])) {
            $this->HandleError("Upload failed is_uploaded_file test.");
            exit(0);
        } else if (!isset($_FILES[$upload_name]['name'])) {
            $this->HandleError("File has no name.");
            exit(0);
        }
        $_FILES[$upload_name]['name']=I('get.uid').'_'.date('Y-m-dH:i:s',time()).'_'.rand().".mp4";

        $file_size = @filesize($_FILES[$upload_name]["tmp_name"]);
        if (!$file_size || $file_size > $max_file_size_in_bytes) {
            $this->HandleError("File exceeds the maximum allowed size");
            exit(0);
        }

        if ($file_size <= 0) {
            $this->HandleError("File size outside allowed lower bound");
            exit(0);
        }


// Validate file name (for our purposes we'll just remove invalid characters)
        $file_name = preg_replace('/[^'.$valid_chars_regex.']|\.+$/i', "", basename($_FILES[$upload_name]['name']));
        if (strlen($file_name) == 0 || strlen($file_name) > $MAX_FILENAME_LENGTH) {
            $this->HandleError("Invalid file name");
            exit(0);
        }



        if (file_exists($save_path . $file_name)) {
            $this->HandleError("File with this name already exists");
            exit(0);
        }


        $path_info = pathinfo($_FILES[$upload_name]['name']);
        $file_extension = $path_info["extension"];
        $is_valid_extension = false;
        foreach ($extension_whitelist as $extension) {
            if (strcasecmp($file_extension, $extension) == 0) {
                $is_valid_extension = true;
                break;
            }
        }
        if (!$is_valid_extension) {
            $this->HandleError("Invalid file extension");
            exit(0);
        }



        if (!@move_uploaded_file($_FILES[$upload_name]["tmp_name"], $save_path.$file_name)) {
            $this->HandleError("文件无法保存.");
            exit(0);
        }
        $dz = C('URL');
        $datas['url'] =$dz.$pathd.$file_name;

        $datas['t']=time();

        $datas['user_id']=I('get.uid');
        $aid = M('video')->data($datas)->add();
        if ($aid) {
            $user = M('member')->field('user')->where(array('uid' =>$datas['user_id']))->find();
            if($user){
                addlog('新增视频，AID：' . $aid,$user['user']);
            }else{
                addlog('新增视频，AID：' . $aid);
            }


        }else{
            $this->HandleError("数据无法保存.");
            exit(0);
        }




        echo "File Received";
        exit(0);



    }
    function HandleError($message) {
        header("HTTP/1.1 500 Internal Server Error");
        echo $message;
    }
}
