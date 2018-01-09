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

/*
 * /验证消息来源
 * $verify    lxb
 * uid        用户id
 * */
function verifys($verify='')
{
    $t = intval($_POST['t']) > 0 ?$_POST['t'] : '';//时间
    $xycs= isset($_POST['verify']) ? trim($_POST['verify']) : '';//mb5(时间+校验参数)
    if ($verify != '51cc') {
        returnApiError('非法数据！');
    }

};

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
//------------------------------------------

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