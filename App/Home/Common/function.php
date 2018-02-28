<?php

/*************************** api开发辅助函数 **********************/

/**
 * @param null $msg  返回正确的提示信息
 * @param flag success CURD 操作成功
 * @param array $data 具体返回信息
 * Function descript: 返回带参数，标志信息，提示信息的json 数组
 *
 */
function returnApiSuccess($msg = null,$data = array(),$httpCode=200){
    $result = array(
        'code' => $httpCode,
        'flag' => 'Success',
        'msg' => $msg,
        'data' =>$data
    );
    $datas=json_encode($result);
    //替换标签
//    $datas= str_replace("&lt;","<",$datas);
//    $datas= str_replace("&gt;",">",$datas);
//    $datas= str_replace('\r\n',"",$datas);

    print $datas;
    exit;
}

/**
 * @param null $msg  返回具体错误的提示信息
 * @param flag success CURD 操作失败
 * Function descript:返回标志信息 ‘Error'，和提示信息的json 数组
 */
function returnApiError($msg = null,$httpCode=500){
    $result = array(
        'code' => $httpCode,
        'flag' => 'Error',
        'msg' => $msg,
    );
    print json_encode($result);
    exit;
}

/**
 * @param null $msg  返回具体错误的提示信息
 * @param flag success CURD 操作失败
 * Function descript:返回标志信息 ‘Error'，和提示信息，当前系统繁忙，请稍后重试；
 */
function returnApiErrorExample(){
    $result = array(
        'flag' => 'Error',
        'msg' => '当前系统繁忙，请稍后重试！',
    );
    print json_encode($result);
    exit;
}

/**
 * @param null $data
 * @return array|mixed|null
 * Function descript: 过滤post提交的参数；
 *
 */

function checkDataPost($data = null){
    if(!empty($data)){
        $data = explode(',',$data);
        foreach($data as $k=>$v){
            if((!isset($_POST[$k]))||(empty($_POST[$k]))){
                if($_POST[$k]!==0 && $_POST[$k]!=='0'){
                    returnApiError($k.'值为空！');
                }
            }
        }
        unset($data);
        $data = I('post.');
        unset($data['_URL_'],$data['token']);
        return $data;
    }
}



/**
 * @param null $data
 * @return array|mixed|null
 * Function descript: 过滤get提交的参数；
 *
 */
function checkDataGet($data = null){
    if(!empty($data)){
        $data = explode(',',$data);
        foreach($data as $k=>$v){
            if((!isset($_GET[$k]))||(empty($_GET[$k]))){
                if($_GET[$k]!==0 && $_GET[$k]!=='0'){
                    returnApiError($k.'值为空！');
                }
            }
        }
        unset($data);
        $data = I('get.');
        unset($data['_URL_'],$data['token']);
        return $data;
    }
    exit;
}


///**
// * 通用化API接口数据输出
// * @param int $status 业务状态码
// * @param string $message 信息提示
// * @param [] $data  数据
// * @param int $httpCode http状态码
// * @return array
// */
//function show($status, $message, $data=[], $httpCode=200) {
//
//    $data = [
//        'status' => $status,
//        'message' => $message,
//        'data' => $data,
//    ];
//
//    return json($data, $httpCode);
//}

/*通用查询
 *
 *
 * */
function xm_gf($table='XmTab',$field='*',$where=null,$limit=null)
{
    $data=M($table);
     $field=$field;
    $datas=$data->field($field)->where($where)->limit($limit)->select();
    return  $datas;
    exit;
}
//唯一邀请码
function make_coupon_card() {
    $code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $rand = $code[rand(0,25)]
        .strtoupper(dechex(date('m')))
        .date('d').substr(time(),-5)
        .substr(microtime(),2,5)
        .sprintf('%02d',rand(0,99));
    for(
        $a = md5( $rand, true ),
        $s = '0123456789ABCDEFGHIJKLMNOPQRSTUV',
        $d = '',
        $f = 0;
        $f < 8;
        $g = ord( $a[ $f ] ),
        $d .= $s[ ( $g ^ ord( $a[ $f + 8 ] ) ) - $g & 0x1F ],
        $f++
    );
    return $d;
}
//生成唯一订单号
function build_order_no(){
    return date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
}

// 生成短信验证码
function generate_code($length = 6) {
    return rand(pow(10,($length-1)), pow(10,$length)-1);}
/**
 * 资金流水
 * $user_id
 * $order_id
 * $content  内容
 * $type 0是订单 1是礼品3充值4提现5退款6提成7男取消订单补偿
 * $user_balance 变动余额
 * $zf_fs   支付方式
 * $money
 *
 */
function moneylog($content,$user_id,$type=0, $money,$user_balance,$zf_fs,$order_id){
    $Model = M('XmMoneyFlow');
    $data['t'] = time();
    $data['content'] =$content;
    $data['user_id'] =$user_id;
    $data['type'] =$type;
    $data['money'] =$money;
    $data['user_balance'] =$user_balance;
    $data['zf_type']=$zf_fs;
    $data['order_id'] =$order_id;
    $data['time_add'] =time();
    if($order_id){
        $where['id']=$order_id;
        $data['order_num'] =M('XmOrder')->where($where)->getField('order_number');
    }
    $Model->data($data)->add();
}

/**
 * 充值资金流水
 * $user_id
 * $order_id
 * $content  内容
 * $type 0是订单 1是礼品3充值4提现5退款6提成
 * $user_balance 变动余额
 * $zf_fs   支付方式1支付宝2微信
 * $money
 *
 */
function czmoneylog($user_id,$content,$money,$user_balance,$zf_fs=1,$order_id){
      $data['user_id'] =$user_id;
      $data['content'] =$content;
      $data['money'] =$money;
      $data['user_balance'] =$user_balance;
    if($zf_fs==1){
        $data['zf_type'] ='支付宝';
    }elseif($zf_fs==2){
        $data['zf_type'] ='微信';
    }else{
        $data['zf_type'] ='余额';
    }
      $data['type']=3;
      $data['order_id'] =$order_id;
      $data['time_add'] =get13TimeStamp();
      $Model = M('XmMoneyFlow');
      $Model->data($data)->add();
}



/**
 * @name 查询订单状态
 * @author 熊敏
 * @param int $user_id 用户id
 * @param int $order_id 订单id
 * @return Integer array
 */
function xm_order_start($order_id,$user_id){
    if(!isset($order_id)){ return  -2;}
    if(!isset($user_id)){ return  -2;}

    $where['id']=$order_id;
    $where['user_id']=$user_id;
    $data=M('XmOrder')->where($where)->getField('status');

    if($data===NULL){
        return  -1;
    }else{
        return  $data;
    }
}

/**
 * @name 改变订单状态
 * @author 熊敏
 * @param int $order_id 订单id
 * @param int $start 订单状态
 * @return Integer
 */
function xm_order_g_start($order_id,$start){
    if(!isset($order_id)){ return false;}
    if(!isset($start)){ return  false;}

    $where['id']=$order_id;
    $data['status']=$start;
    $datas=M('XmOrder')->where($where)->save( $data);
    return $datas;
}

/**
 * @name 改变订单时间
 * @author 熊敏
 * @param int $order_id 订单id
 * @return Integer
 */
function xm_order_time($order_id){
    if(!isset($order_id)){ return false;}
    $where['id']=$order_id;
    $data['jisu_time']=get13TimeStamp();
    $datas=M('XmOrder')->where($where)->save( $data);
    return $datas;
}

/**
 * @name 个人信息
 * @author 熊敏
 * @param int $user_id 用户id
 * @param string $field 用来查询数据字段
 * @return Integer
 */
function  xm_user($user_id,$field='*'){
    if(!isset($user_id)){ return  -2;}
    $where['id']=$user_id;
    $data=M('XmMember')->field($field)->where($where)->find();
    if($data){
        return  $data;
    }else{
        return  -1;
    }
}
/**
 * @name 跟新个人信息
 * @author 熊敏
 * @param int $user_id 用户id
 * @param array $data 更新的数组
 * @return Integer
 */
function xm_put_user($user_id,$data){
    $where['id']=$user_id;
    $datas=M('XmMember')->where($where)->save($data);
    if($datas){
        return $datas;
    }else{
        return false;
    }
}

/**
 * @name 扣费或者加钱
 * @author 熊敏
 * @param int $user_id 用户id
 * @param array $money 金额
 * @return Integer
 */
function xm_put_user_money($user_id,$money){
    if(!isset($user_id)){ return false;}
    if(!isset($money)){ return false;}
    $field="is_jkuser,jk_balance,balance";
    $data= xm_user($user_id, $field);
    $where['id']=$user_id;
    if( $data['is_jkuser']){
        //金卡用户
        $qian=$data['jk_balance']+$money;
        $datasj['jk_balance']= $qian;
        $datas=M('XmMember')->where($where)->save($datasj);
    }else{
        $qian=$data['balance']+$money;
        $datasj['balance']=$qian;
        $datas['s']=M('XmMember')->where($where)->save($datasj);
    }
    if($datas['s']){

        $datas['q']=$qian;
        return $datas;
    }else{
        return false;
    }
}



/**
 * 查询个人可用金额如果是金卡返回金卡余额，如果不是返回普通余额
 * @param int $user_id 用户id
 * @return Integer
 */
function xm_is_jk_money($user_id){
    $field="is_jkuser,jk_balance,balance";
     $data= xm_user($user_id, $field);
  if( $data['is_jkuser']){
     //金卡用户
return $data['jk_balance'];
  }else{
      return $data['balance'];
  }

}

/**
 * 个人可用代金券
 * @author 熊敏
 * @param int $user_id 用户id
 * @param int $coupon_id 用户id
 * @return array
 */
function xm_ky_djj($user_id,$coupon_id){
    if(!isset($user_id)){ return false;}
    $where['user_id']=$user_id;
    $where['state']=0;
    if($coupon_id){
        $where['id']=$coupon_id;
    }
$data=M('XmCoupon')->where($where)->find();
    if($data){
        return  $data;
    }else{
        return  false;
    }
}

/**
 * 男生线上需要花费的钱
 */
function xm_xs_kou_money($start,$end){
    if(!isset($start)){ return false;}
    if(!isset($end)){ return false;}
    $time_add = $start;
    $time_completion = $end;
    //根据时间算时长
    $time_limit = $time_completion - $time_add;
    $second = floor($time_limit % 86400 % 60);//秒数
    //少于3分钟扣10元
    $minute = floor($time_limit / 60);//分钟数

    if ($minute < 3) {
        $kouqian = 10;
    } elseif ($minute == 3) {
        $kouqian = 10;
        if ($second > 0) {
            $kouqian = 12;
        }
    } else {
        //多余3分钟，3分钟内扣10元，3分钟后每分钟扣2元
        $kouqian = 10;
        $shijian = $minute - 3;


        $duoqian2 = 2 * $shijian;
        $kouqian2 = $kouqian + $duoqian2;
        if ($second > 0) {
            $kouqian = $kouqian2 + 2;
        }
    }
    return $kouqian;

}

/**
 * 新用户男性送3分钟
 */

function xm_xsxuer_kou_money($start,$end){
    if(!isset($start)){ return false;}
    if(!isset($end)){ return false;}
    $time_add = $start;
    $time_completion = $end;
    //根据时间算时长
    $time_limit = $time_completion - $time_add;
    $second = floor($time_limit % 86400 % 60);//秒数
    //少于3分钟扣10元
    $minute = floor($time_limit / 60);//分钟数
    if ($minute < 3) {
        $kouqian = 0;
    } elseif ($minute == 3) {
        $kouqian = 0;
        if ($second > 0) {
            $kouqian = 2;
        }
    } else {
        //多余3分钟，3分钟内扣10元，3分钟后每分钟扣2元
        $kouqian = 0;
        $shijian = $minute - 3;
        $duoqian2 = 2 * $shijian;
        $kouqian2 = $kouqian + $duoqian2;
        if ($second > 0) {
            $kouqian = $kouqian2 + 2;
        }
    }
    return $kouqian;
}

/**
 * 女生线上要得的钱
 */
function xm_xs_de_money($manmoney,$dengji){
    if(!isset($manmoney)){ return false;}
    if(!isset($dengji)){ return false;}
    $deqian =$manmoney* xm_women_draw($dengji);
    return $deqian;
}

/**
 * @param 用户分钱的等级
 * @param bool $name
 *$user_dj 用户分成等级
 */
function xm_women_draw($user_dj){
    if(!isset($user_dj)){ return  -2;}
     $where['rank']=$user_dj;
    $data=M('XmWomenDraw')->where($where)->getField('ratio');
    if($data){
        return  $data;
    }else{
        return  -1;
    }
}

/**
 * 根据分类id查分类名字
 */
function xm_fl_name($fl_id){
    if(!isset($fl_id)){ return  -2;}
    $where['id']=$fl_id;
    $data=M('XmTrystClassify')->where($where)->getField('title');
    if($data){
        return  $data;
    }else{
        return  -1;
    }
}

/**
 * 获取13位时间戳
 * @return int
 */
 function get13TimeStamp() {
    list($t1, $t2) = explode(' ', microtime());
    return $t2 . ceil($t1 * 1000);
}
/**
 * 获取头消息(系统好像有)
 * */
//function getallheaders(){
//    foreach ($_SERVER as $name => $value)
//    {
//        if (substr($name, 0, 5) == 'HTTP_')
//        {
//            $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
//        }
//    }
//    return $headers;
//}
/**
 * 将字符串转换成数组
 * @author 熊敏
 * @param string $str 字符串
 * @return array
 */
function xm_explod($str){
   $array= explode(",",trim($str));
    return $array;
}

/**
 * 图片文件上传
 * @author 熊敏
 * @return array
 */
function imgup(){
    $upload = new \Think\Upload();// 实例化上传类
    $upload->maxSize   =     3145728 ;// 设置附件上传大小
    $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
    $upload->rootPath  =      './file/'; // 设置附件上传根目录
    $upload->savePath  =      'head'; // 设置附件上传（子）目录
// 上传文件
    $info   =   $upload->upload();
    if(!$info) {// 上传错误提示错误信息
        return false;
     //   $this->error($upload->getError());
    }else{// 上传成功 获取上传文件信息
        return $info;

    }
}

/**
 * 视频文件上传
 * @author 熊敏
 * @return array
 */
function videoup(){
    $upload = new \Think\Upload();// 实例化上传类
    $upload->maxSize   =     3145728000 ;// 设置附件上传大小
    $upload->exts      =     array('mp4');// 设置附件上传类型
    $upload->rootPath  =      './file/'; // 设置附件上传根目录
    $upload->savePath  =      'mp4'; // 设置附件上传（子）目录
// 上传文件
    $info   =   $upload->upload();
    if(!$info) {// 上传错误提示错误信息
        return false;
        //   $this->error($upload->getError());
    }else{// 上传成功 获取上传文件信息
        return $info;

    }
}


/**
 * 分页
 * @author 熊敏
 * @param int $p_val 分页数
 * @param int $size 每页数量
 * @return array
 */
function page($p_val=1,$size=10){
    $p = intval($p_val) > 0 ?$p_val : 1;
    $pagesize = $size;#每页数量
    $offset = $pagesize * ($p - 1);//计算记录偏移量
    $data=$offset . ',' . $pagesize;
    return $data;
}
//-----------------------------------------代金券部分

/**
 * 代金券
 * @author 熊敏
 * @param int $coupon_num 代金券编号
 * @return array
 */
function get_djjs($coupon_num){
    if(!isset($coupon_num)){ return false;}
    $where['coupon_num']=$coupon_num;
    $where['state']=0;
    $data=M('XmCoupon')->where($where)->find();
    if($data){
        return  $data;
    }else{
        return  false;
    }
}


/**
 * 更新代金券状态
 * @author 熊敏
 * @param $coupon_id 代金券id
 * @param int $staurt  代金券状态
 * @return bool|false|int
 */
function xm_put_djj($coupon_id,$staurt=0){
    if(!isset($coupon_id)){ return false;}
    $where['id']=$coupon_id;
    $data['state']=$staurt;
    $datas=M('XmCoupon')->where($where)->save($data);
    if($datas){
        return $datas;
    }else{
        return false;
    }
}

//------------------------------------------订单部分方法

/**
 * 添加异常订单
 * @param int $zf_type 支付方式1支付宝2是微信
 * @param $order_mun  订单编号
 * @param $money      支付金额
 * @param $add_time  添加时间 这里不是时间搓
 * @param $remarks 备注
 * @param $coupon_mun  代金券编号
 */
function add_yc_order($zf_type=1,$order_mun,$money,$add_time,$remarks,$coupon_mun=''){
    //异常订单
    $dataao['zf_type']=$zf_type;
    $dataao['order_mun']=$order_mun;
    $dataao['money']=$money;
    $dataao['stuart']=1;
    $dataao['add_time']=$add_time;
    $dataao['remarks']=$remarks;
    $dataao['coupon_mun']= $coupon_mun;
    $adddata= M("XmAbnormalOrder")->add($dataao);
    if($adddata){
        return  $adddata;
    }else{
        return  false;
    }
}
///**
// * 生成充值订单
// * @author 熊敏
// * @param int $user_id 用户id
// * @param array $data 更新的数组
// * @return Integer
// */
//function xm_put_users($user_id,$data){
//    $datas=M('XmCzOrder')->add($data);
//    if($datas){
//        return $datas;
//    }else{
//        return false;
//    }
//}
/**
 * 数组排序
 * @param $arrays
 * @param $sort_key
 * @param int $sort_order
 * @param int $sort_type
 * @return array|bool
 */
function my_sort($arrays,$sort_key,$sort_order=SORT_ASC,$sort_type=SORT_NUMERIC ){
    if(is_array($arrays)){
        foreach ($arrays as $array){
            if(is_array($array)){
                $key_arrays[] = $array[$sort_key];
            }else{
                return false;
            }
        }
    }else{
        return false;
    }
    array_multisort($key_arrays,$sort_order,$sort_type,$arrays);
    return $arrays;
}
//-----------------------------------------

function addlog($log, $name = false)
{
    $Model = M('log');
    if (!$name) {
        session_start();
        $uid = session('uid');
        if ($uid) {
            $user = M('member')->field('user')->where(array('uid' => $uid))->find();
            $data['name'] = $user['user'];
        } else {
            $data['name'] = '';
        }
    } else {
        $data['name'] = $name;
    }
    $data['t'] = time();
    $data['ip'] = $_SERVER["REMOTE_ADDR"];
    $data['log'] = $log;
    $Model->data($data)->add();
}


/**
 * 查询个人职业和爱好
 * @author 钱晓松
 * @param int $user_id 用户id
 * @return String 职业爱好
 */
function get_hobby($tc_id,$sex){
    if(!isset($sex)){ return  -12;}

    $where['sex']=$sex;
    if($tc_id){
        $where['id']=array('in',$tc_id);
    }
    if($sex){
        $where['type']=1;//女
    }else{
        $where['type']=0;//男
    }
    $xm_tab=M('xm_tab')->field('id,o_username')->where($where)->select();
    if($xm_tab){
        $x=0;
        $data='';
        //多个爱好或职业用逗号隔开
        foreach($xm_tab as $value){
            $x?$str=',':$str='';
            $data.=$str.$value['o_username'];
            $x++;
        }
        return $data;
    }else{
        return -1;
    }
}



/**
 * 通过区域id汉化区域
 * @author 钱晓松
 * @param int $province 省id
 * @param int $city 市id
 * @param int $area 区id
 * @return String 中文省市区
 */
function get_region($province,$city,$area)
{
    if ($province) {
        $province_name = M('qxs_region')->where("region_id=$province")->find();
        $city_name = $city ? M('qxs_region')->where("region_id=$city")->find() : 0;
        $area_name = $area ? M('qxs_region')->where("region_id=$area")->find() : 0;
        $data['province_name'] = $province_name['region_name'];
        $data['city_name'] = $city_name['region_name'];
        $data['area_name'] = $area_name['region_name'];
        return $data;
    }else{
        return false;
    }
}



/**
 * @name 通用---获取数据总条数
 * @author 钱晓松
 * @param int $table 查询表格名称
 * @param int $where 查询条件
 * @return int 总条数
 */
function get_count($table='',$where=null)
{
    if($table&&$where){
        $data=M($table);
        $datas=$data->where($where)->count();
        return  $datas;
    }else{
        return -1;
    }
    exit;
}

/**
 * 通过订单状态获取汉化状态名称
 *
 * @author 钱晓松
 * @version 1.0
 * @param string $status 订单状态
 * @return string 订单名称
 **/
function GetStatusName($status)
{
    if(is_null($status)){return false;}
    if($status == 0){
        $status_name='待生成';
    }elseif($status==1){
        $status_name='已生成待处理';
    }elseif($status==2){
        $status_name='已处理待进行';
    }elseif($status==3){
        $status_name='已进行待修改';
    }elseif($status==4){
        $status_name='已修改待支付';
    }elseif($status==5){
        $status_name='已支付';
    }elseif($status==6){
        $status_name='已完成';
    }elseif($status==7){
        $status_name='已评价';
    }elseif($status==8){
        $status_name='已取消';
    }else{
        $status_name=-1;
    }
    return $status_name;
}


/** 未使用~~~
 * @name 根据用户id获取评论列表
 * @author 钱晓松
 * @param int $user_id 用户id
 * @return array
 */
function get_comment($user_id,$field='*'){
    if(!isset($user_id)){ return  -2;}
    $where['user_id']=$user_id;
    $data=M('XmComment')->field($field)->where($where)->select();
    if($data){
        return  $data;
    }else{
        return  -1;
    }
}


/**
 * @name 获取个人信息
 * @author 钱晓松
 * @param int $mobile 用户id
 * @param string $field 用来查询数据字段
 * @return Integer
 */
function  get_user($mobile,$field='*'){
    if(!isset($mobile)){ return  -2;}
    $where['mobile']=$mobile;
    $data=M('XmMember')->field($field)->where($where)->find();
    if($data){
        return  $data;
    }else{
        return  -1;
    }
}