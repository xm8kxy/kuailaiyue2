<?php
/**
 * 女性api
 * @author 熊敏
 * @version 1.0
 */
namespace Home\Controller;
use Think\Upload;
use Vendor\Page;
use XmClass\Ucpassxm;
//require_once "./comm_function.php";
class ApiwomenController extends ApiComController
{

    private $man_xx_time; //线下发单限制时间
    private $options;//环信配置

    private $sms_accountsid;
    private $sms_token;
    private $sms_templateid;
    private $sms_appid;

    public function _initialize()
    {
        parent::checkRequestCors();
        $this->man_xx_time=C('man_xx_time');//10分钟
        //sms
        $this->sms_accountsid=C('sms_accountsid');
        $this->sms_token=C('sms_token');
        $this->sms_appid=C('sms_appid');
        $this->sms_templateid=C('sms_templateid');

        //环信
        $this->options['client_id']=C('client_id');
        $this->options['client_secret']=C('client_secret');
        $this->options['org_name']=C('org_name');
        $this->options['app_name']=C('app_name');

        //验证
        parent::checkRequestAuth();
        parent::checkRequsetSign();
       //不用验证是方法
        $no_dr=array();
       // $no_dr[]='getSMSCode'; //发送短信
     parent::checkRequsetdr($no_dr);
    }

    //短信发送
    public function getSMSCode(){
        $mobile =$_POST['mobile'];
        if (empty($mobile)) {
          returnApiError( '电话号码必须！');
        }
        $options['accountsid']=$this->sms_accountsid;
        $options['token']= $this->sms_token;
        $ucpass = new Ucpassxm($options);
        $appid =  $this->sms_appid;	//应用的ID，可在开发者控制台内的短信产品下查看
        $templateid = $this->sms_templateid;    //可在后台短信产品→选择接入的应用→短信模板-模板ID，查看该模板ID

        $param = generate_code(); //多个参数使用英文逗号隔开（如：param=“a,b,c”），如为参数则留空
        $mobile =  $mobile;
        $uid = "";

//70字内（含70字）计一条，超过70字，按67字/条计费，超过长度短信平台将会自动分割为多条发送。分割后的多条短信将按照具体占用条数计费。

     $datas= $ucpass->SendSms($appid,$templateid,$param,$mobile,$uid);
       $data_o= json_decode($datas,true);
if($data_o['code']=='0'){
            $data['cord'] = $param;
            $data['time_add'] = $data_o['create_date'];
            $data['mobile'] =$data_o['mobile'];
            $smscode = D('XmCord');
            $smscodeObj = $smscode->where("mobile='$mobile'")->find();
            if($smscodeObj){
                $data['content'] ='修改后的验证码';
                $success = $smscode->where("mobile='$mobile'")->save($data);
                if($success !== false){
                    $smscodeObjs = $smscode->where("mobile='$mobile'")->find();
                    $result = array(
                        'code' => '0',
                        'ext' => '修改成功',
                        'obj' => $smscodeObjs
                    );
                }
                returnApiSuccess('1',$result);
            }else{
                $data['content'] ='第一次使用验证码';
                $id = $smscode->add($data);
                if($id){
                    $smscode_temp = $smscode->where("id='$id'")->find();
                    $result = array(
                        'code'=> '0',
                        'ext'=> '创建成功',
                        'obj'=>$smscode_temp
                    );
                    returnApiSuccess('1',$result);
                }
            }
}else{
    returnApiError( '发送失败');
}

    }

    //首页
    public function syindex()
    {

        $table="Flash";
        $field='id,pic,tp_sp';
        $where['sid']=3;
        $limit=3;
        $data=xm_gf($table,$field,$where,$limit);
        if($data){
            returnApiSuccess('请求成功',$data);
        }else{
            returnApiError('请求失败');
        }
     }


    //-----------------------------------------------------------------女性模块开始-------------------------------------------
    //女性抢单
    public function WomenGrabOrder()
    {
        //查询订单是否过期改订单状态
        $sj=get13TimeStamp();
        $data_sj['status']=0;
        $where_sj['end_time']  = array('lt', $sj);
        $Xro=M('XmRobOrde');
        $Xro->where( $where_sj)->save($data_sj); // 根据条件更新记录

        //清除过期菜单
        $Xro->where('status=0')->delete(); // 删除所有状态为0的用户数据

        //根据地区时间订单状态  女性是否是禁用时间
        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
        $t = intval($_POST['t']) > 0 ?$_POST['t'] : '';//时间
        $area = isset($_POST['area']) ? trim($_POST['area']) : '武汉';//用户id

        $where_u['id']=$user_id;
        $field='is_fwz,is_disable';
        $data_u = M('XmMember')->field($field)->where( $where_u)->find();
        if($data_u['is_fwz']==0){ returnApiError( '不是服务者');}
        if($data_u['is_disable']==1){ returnApiError( '被禁用');}


        //查询用订单
        $where_o['status']=1;
        $where_o['area']=$area;
        $where_o['user_id']=array("NEQ", $user_id);

        $field_o='id,user_id,order_id,type';
        $data_o=$Xro->field($field_o)->where($where_o)->order('id desc')->limit(20)->select();
     //   print_r( $data_o);exit;
     if($data_o){

    foreach($data_o as $value){
        if($value['type']=='1'){
//线上
        }else{
            //线下

            $where_os['id']=$value['order_id'];
            $field_os='appointment_time,time_limit,appointment_dd,remarks,is_sex,people_num,money,order_name,jisu_time,classify';

            $value['order']=M('XmOrder')->field($field_os)->where($where_os)->find();

            //分类图片
            if( $value['order']['classify']){
                $wherext['id']=$value['order']['classify'];
                $value['tupan']=M('XmTrystClassify')->where($wherext)->getField('prc');
            }


            $where_ou['id']= $value['user_id'];
            $field_ou='is_nm,nm,o_username,Head,birth,moblie';
            $value['user']=M('XmMember')->field($field_ou)->where($where_ou)->find();



            //抢订单状态
            $where_qds['order_id']=$value['order_id'];
            $where_qds['user_id']= $user_id;
            $field_os='order_id,is_qd_id,user_id';

            $qddata=M('XmGrabSingle')->field($field_os)->where( $where_qds)->find();
         //   $value['userstarts']=M('XmGrabSingle')->getLastSql();
            //状态 0抢 1已报名 2选中 3被抢
if($qddata){
    $value['userstart']='1';
    if($qddata['is_qd_id']== 1){
        $value['userstart']='2';
    }else{
        $field_bq='id';
        $where_bq['order_id']=$value['order_id'];
        $where_bq['is_qd_id']=1;
        $bqdata=M('XmGrabSingle')->field($field_bq)->where($where_bq)->find();
        if($bqdata){
            $value['userstart']='3';
        }
    }
}else{
    //可抢
    $value['userstart']='0';
}

        }
$ddata[]=$value;
    }

         returnApiSuccess('成功',$ddata);
}else{
    returnApiError( '无数据');
}

    }

    //女性点击抢单
    public function WomenGrabs(){
        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
        $order_id = isset($_POST['order_id']) ? trim($_POST['order_id']) : '';//订单id
        //查看订单状态，过期
        $ROrder=M('XmRobOrde');
        $sj=get13TimeStamp();
        $where_or['end_time']  = array('lt', $sj);
        $where_or['id']  =$order_id;
        $dataR=$ROrder->where($where_or)->find();
        if($dataR){ returnApiError( '订单已过期');}
        //抢过的不能再抢
        $where_qg['order_id']= $order_id;
        $where_qg['user_id']=  $user_id;
        $gsdata=M('XmGrabSingle');
        $dataR=  $gsdata->where($where_qg)->find();
        if($dataR){ returnApiError( '此订单已抢过');}
        //添加
        $where['order_id']= $order_id;
        $where['user_id']=  $user_id;
        $where['add_time']= get13TimeStamp();
        $where['start']= 1;

        $data=$gsdata->add($where);
if($data){
    returnApiSuccess('抢单成功',1);
}else{
    returnApiError( '抢单失败');
}
        //
    }
    //。。。。。。。-----------------------------------------------------------------女性线下模块开始-------------------------------------------
    


    //。。。。。。。-----------------------------------------------------------------女性线下模块结束-------------------------------------------
    //-----------------------------------------------------------------女性模块结束-------------------------------------------


}