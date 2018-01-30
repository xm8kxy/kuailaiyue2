<?php
namespace Home\Controller;
use Think\Controller;
use Firebase\JWT\JWT;
class ApiComController extends Controller
{

    private $key;
    /**
     * 初始化的方法
     */


    /**
     * 检查每次app请求的数据是否合法
     */
    public function checkRequestCors()
    {
        //跨域
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Headers:Authorization');
        //   header("Access-Control-Allow-Methods: GET, POST, DELETE");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Headers: Content-Type, X-Requested-With, Cache-Control,Authorization");
    }
    public function checkRequestAuth() {

        $headers =  getallheaders();
        $app_type=explode(',',c('app_type'));
        if(!in_array($headers['Apptype'], $app_type)) {
            returnApiError( 'app_type不合法');
        }

    }



    //非法数据
    public function checkRequsetSign(){
        $t = intval($_POST['t']) > 0 ?$_POST['t'] : '';//时间
        $xycs= isset($_POST['verify']) ? trim($_POST['verify']) : '';//mb5(时间+校验参数)
        $xycs_bd= C('token_xm');
        $verify=md5($t.$xycs_bd);
        if ( $t == '') {returnApiError( '时间必须！');}
        if ($xycs == '') {returnApiError( '校验码必须！');}
        //    if ($verify!=$xycs){returnApiError( '非法数据');}
    }

    /**
     * @param 登入必须验证的
     * @param string $可以不认证的方法，数组形式
     */

    public function checkRequsetdr($no_dr)
    {
        if(!in_array(ACTION_NAME, $no_dr)){
            $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
            $str = isset($_POST['token']) ? trim($_POST['token']) : '';
            $key = C('key_xm');
            if ($user_id == '') { returnApiError( '用户id必须');}
            if ($str == '') { returnApiError( 'token必须');}
            if ($key == '') { returnApiError( 'key必须');}
            $this->is_jwt($str,$key,1,$user_id);
        }
}


    /*验证jwt
        *$str  jwt密码
        * $key  jwt参数
        *$is_yz  0是返回数据 1是验证是不是唯一登入
        * */
    public function is_jwt($str='', $key='',$is_yz='0',$user_id=''){
        if($str == ''){
            returnApiError( 'tokan必须！');
        }
        $decoded = JWT::decode($str, $key, array('HS256'));

        if(!is_object($decoded)){
            returnApiError( 'tokan错误！');
        }else{
            if($is_yz){
                //没登入
                $arr = json_decode(json_encode($decoded), true);

                if( $arr['id']!==$user_id ){
                    returnApiError('请登入');
                }
                //有人异步登入
                $user = M("XmMember")->field('id')->where(array('id' => $arr['id'], 'sessionId' => $arr['sessionid']))->find();

                if (!$user) {
                    returnApiError('有人在其他设备登入，请注意自己账号安全');
                }

            } else {
                $arr = json_decode(json_encode($decoded), true);
            }
            return $arr;
        }
    }

    public function yanjwt($token)
    {   $key = C('key_xm');
        return JWT::encode($token, $key);
    }
}