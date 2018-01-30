<?php

/*************************** api开发辅助函数 **********************/

/**
 * @param null $msg  返回正确的提示信息
 * @param flag success CURD 操作成功
 * @param array $data 具体返回信息
 * Function descript: 返回带参数，标志信息，提示信息的json 数组
 *
 */
function returnApiSuccess($msg = null,$data = array()){
    $result = array(
        'flag' => 'Success',
        'msg' => $msg,

        'data' =>$data
    );
    $datas=json_encode($result);
    //替换标签
    $datas= str_replace("&lt;","<",$datas);
    $datas= str_replace("&gt;",">",$datas);
    $datas= str_replace('\r\n',"",$datas);

    print $datas;
    exit;
}

/**
 * @param null $msg  返回具体错误的提示信息
 * @param flag success CURD 操作失败
 * Function descript:返回标志信息 ‘Error'，和提示信息的json 数组
 */
function returnApiError($msg = null){
    $result = array(
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
 * $type 0是订单 1是礼品3充值4提现5退款6提成
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
 * @param int $user_id 用户id
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
 * @return array
 */
function xm_ky_djj($user_id){
    if(!isset($user_id)){ return false;}
    $where['user_id']=$user_id;
    $where['state']=0;
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

//------------------------------------------




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
/*
 * 广告
 * $region_id  城市id
 * $field      字段
 * $is_del     是否显示
 * $limit     限制
 * */
function  addata($region_id='180',$field='*',$is_del='0',$limit=null){
    $where['city']=$region_id;
    $where['is_del']=$is_del;
    $field=$field;
    $data= M('flash')->field($field)->where($where)->limit($limit)->select();
    return  $data;
    exit;
}

/*
 * 文章

 * $field      字段
 * $sid        分类 0表示不区分分类
 * $is_del     是否显示
 * $limit     限制
 * $id       查单个消息
* */
function  articledata($field='*',$sid='0',$limit=null,$id=null){

    if($sid){
        $where['sid']=$sid;
    }

    $order="t desc";
    $field=$field;
    if($id){
        $where['aid']=$id;
        $data= M('article')->field($field)->where($where)->find();
    }else{
        $data= M('article')->field($field)->where($where)->limit($limit)->order($order)->select();
    }
  //  echo M("article")->getLastSql();
    return  $data;
    exit;
}

/*
 * 有条件的文章
 * $field      字段
 * $sid        分类 0表示不区分分类
 * $is_del     是否显示
 * $limit     限制
 * $wher       查单条件
* */
function  articlewhere($field='*',$sid='0',$limit=null,$where=null){
    if($sid){
        $where['sid']=$sid;
    }
    $order="t desc";
    $field=$field;
        $data= M('article')->field($field)->where($where)->limit($limit)->order($order)->select();

    return  $data;
    exit;
}


/*单页
 *$field   字段
 *$sid    类别
 *$where
 * **/
function  categorydye($field='*',$sid='0',$where=null){

    $where['id']=$sid;
    $where['type']=1;
    $order="o desc";
    $field=$field;
    $data= M('category')->field($field)->where($where)->order($order)->find();
  //  echo M("category")->getLastSql();exit;
    return  $data;
    exit;
}




/*
 * 视频
* $field     字段
 * $statue   状态
* $limit     限制
 * $id       查单个消息
 * */
function  spdata($field='*',$statue='1',$limit=null,$id=null){
    $where['statue']=$statue;
    $field=$field;
    if($id){
        $where['aid']=$id;
        $data= M('video')->field($field)->where($where)->find();
    }else{
        $data= M('video')->field($field)->where($where)->limit($limit)->select();
    }

    return  $data;
    exit;
}




//分页

function page($p_val=1,$size=10){
    $p = intval($p_val) > 0 ?$p_val : 1;
    $pagesize = $size;#每页数量
    $offset = $pagesize * ($p - 1);//计算记录偏移量
    $cc=$offset . ',' . $pagesize;
    return $cc;
}


//文章类别总页数
/*
 * $lebie 类别
 * */
function zongshu($lebie,$size=10){
    $where['sid']=$lebie;
    $pagesize = $size;#每页数量
$data=M('article')->where($where)->count();
$data=ceil($data/$pagesize);
    return $data;
}

//自定义变量
function zdybl($lebie){
    $where['k']=$lebie;
    $pagesize = 10;#每页数量
    $data=M('setting')->where($where)->find();
    $datas= $data['v'];
    return  $datas;
}