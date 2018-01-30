<?php
/**
 * 增加日志
 * @param $log
 * @param bool $name
 */

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
 *
 * 获取用户信息
 *
 **/
function member($uid, $field = false)
{
    $model = M('Member');
    if ($field) {
        return $model->field($field)->where(array('uid' => $uid))->find();
    } else {
        return $model->where(array('uid' => $uid))->find();
    }
}

function Ancestry($data , $pid) {
    static $ancestry = array();
    foreach($data as $key => $value) {
        if($value['region_id'] == $pid) {
            $ancestry[] = $value;
            $this->Ancestry($data , $value['parent_id']);
        }
    }
    return $ancestry;
}




/**
 * 查询分类id对应的分类名称
 *
 * @author 钱晓松
 * @version 1.0
 * @param integer $id 订单状态
 * @return string 订单名称
 **/
function Get_TypeName($id)
{
    if(!$id){return false;}
    $type_name =M('xm_tryst_classify')->where(array('id' => $id))->find();
    return $type_name['title'];
}


/**
 * 查询分类分类名称对应的分类id
 *
 * @author 钱晓松
 * @version 1.0
 * @param string $name 订单状态
 * @return string 订单名称
 **/
function Get_TypeID($name)
{
    if(!$name){return false;}
    $type_name =M('xm_tryst_classify')->where(array('id' => $name))->find();
    return $type_name['title'];
}

/**
 * 通过订单状态获取汉化状态名称
 *
 * @author 钱晓松
 * @version 1.0
 * @param string $status 订单状态
 * @return string 订单名称
 **/
function Get_StatusName($status)
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
        $status_name='未知错误';
    }
    return $status_name;
}

/**
 * 通过订单汉化名称获取状态编号
 *
 * @author 钱晓松
 * @version 1.0
 * @param string $status_name 订单状态名称
 * @return integer 订单状态
 **/
function Get_StatusId($status_name)
{
    if(is_null($status_name)){return false;}

    if(strstr('待生成',$status_name)){
        $status_id[]=0;
    }
    if(strstr('已生成待处理',$status_name)){
        $status_id[]=1;
    }
    if(strstr('已处理待进行',$status_name)){
        $status_id[]=2;
    }
    if(strstr('已进行待修改',$status_name)){
        $status_id[]=3;
    }
    if(strstr('已修改待支付',$status_name)){
        $status_id[]=4;
    }
    if(strstr('已支付',$status_name)){
        $status_id[]=5;
    }
    if(strstr('已完成',$status_name)){
        $status_id[]=6;
    }
    if(strstr('已评价',$status_name)){
        $status_id[]=7;
    }
    if(strstr('已取消',$status_name)){
        $status_id[]=8;
    }
    $sum=count($status_id);

    $data='';
    if($sum){
        for ($x=0; $x<=$sum; $x++) {
            $x>=1&&$x<$sum ?$str=',':$str='';
            $data.=$str.$status_id[$x];
        }
    }else{
        return false;
    }
    return $data;
}



/**
 *  通过订单汉化名称获取状态编号
 *
 * @author 钱晓松
 * @version 1.0
 * @param string $table 表名
 * @param string $where 条件
 * @return Array 订单状态
 **/
function Get_Find_data($table,$where)
{
    if(!$table){return false;}
    if(!$where){return false;}
    $data =M($table)->where($where)->find();


//    echo M($table)->getLastSql();exit;
    return $data;
}



