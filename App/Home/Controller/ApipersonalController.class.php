<?php
namespace Home\Controller;
use Think\Upload;
use Vendor\Page;
use XmClass\Ucpassxm;

class ApipersonalController extends ApiComController
{

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

    //---------------------------------------------------------------------个人中心开始--------------------------------------
    //个人中心
    public function PCenter()
    {
//        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
//        $str = isset($_POST['token']) ? trim($_POST['token']) : '';
//        $key =  $this->key_xm;
//        if ($user_id == '') { returnApiError( '用户id必须');}
//        if ($str == '') { returnApiError( 'token必须');}
//        if ($key == '') { returnApiError( 'key必须');}
//        $this->is_jwt($str,$key,1,$user_id);

    }

    //个人中心订单列表页
    public function PCenterOrder()
    {
        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
        $type=isset($_POST['type']) ? trim($_POST['type']) : '';//分类不填是所有
        if ( $type == '') {
            returnApiError('分类必须要！');}
        switch ($type)
        {
            case 1:

                break;
            case 2:
                $where['type']=  array('in','3,4');
                break;
            case 3:
                $where['type']=  array('in','2,5');

                break;
            case 3:
                $where['type']= 6;
                break;
            case 4:
                $where['type']= 7;
                break;
            case 5:
                $where['type']= 8;
                break;
            default:

        }

        $modle=M('XmOrder');
//        if($type){
//            $where['type']= $type;
//        }
        $where['user_id']=  $user_id ;
        $field='user_id,appointment_time,time_limit,appointment_dd,money,status,xz_user_id,order_name';
        $data=$modle->field($field)->where($where)->select();
        foreach($data as $k=>$value){
            $cx_user = array_filter(explode(',', $value['xz_user_id']));
            $userdata = xm_user($cx_user['0'], 'birth,Head,sex,o_username,id');
            $value['user']=$userdata;
            $datas[]=$value;
        }
        if($data){

            returnApiSuccess('请求成功', $datas);
        }else{
            returnApiError('请求失败');
        }
    }





    //视频验证展示
    public function PVideoValidation()
    {
        $where['sid']=4;
        $field='tp_sp,pic,title';
        $data=M('Flash')->field( $field)->where($where)->find();
        if($data){
            returnApiSuccess('请求成功',$data);
        }else{
            returnApiError('请求失败');
        }

    }


//    //添加约伴
//    function  addgo(){
//
//        $uid = isset($_POST['uid']) ? trim($_POST['uid']) : '';//用户id
//        $title = isset($_POST['title']) ? htmlentities($_POST['title']) : '';
//        $content = isset($_POST['content']) ? htmlentities($_POST['content']) : '';
//        $destination = isset($_POST['destination']) ? htmlentities($_POST['destination']) : '';
//        $requirement = isset($_POST['requirement']) ? htmlentities($_POST['requirement']) : '';
//        $cost = isset($_POST['cost']) ? htmlentities($_POST['cost']) : '';
//        $people_number = isset($_POST['people_number']) ? trim($_POST['people_number']) : '1';
//        $go_off = isset($_POST['go_off']) ? trim($_POST['go_off']) : '';//
//        $back_time = isset($_POST['back_time']) ? trim($_POST['back_time']) : '';//
//        $is_pf = isset($_POST['is_pf']) ? trim($_POST['is_pf']) : '';//
//        $cfd=isset($_POST['cfd']) ? htmlentities($_POST['cfd']) : '';
////标签
//        $bq = isset($_POST['bq']) ? trim($_POST['bq']) : '';//
//        //   echo $bq;exit;
//        if($uid){
//            verifys($_POST['verify'],$uid);
//
//            //文件上传地址提交给他，并且上传完成之后返回一个信息，让其写入数据库
////dump($_FILES);exit;
//            if(empty($_FILES)){
//                returnApiError( '必须选择上传文件');
//
//            }else{
//                $a=$this->up();
//                $xdata['cfd']=$cfd;
//                $xdata['title']=$title;
//                $xdata['user_id']=$uid;
//                $xdata['content']=$content;
//                $xdata['destination']=$destination;
//                $xdata['requirement']=$requirement;
//                $xdata['cost']=$cost;
//                $xdata['people_number']=$people_number;
//                $xdata['go_off']=$go_off;
//                $xdata['back_time']=$back_time;
//                $xdata['is_pf']=$is_pf;
//                $xdata['t']=time();
//
//                if( $bq){
//                    $bqs = array_filter(explode('|',$bq));
//
//                }
//
//                if(isset($a)){
//                    //写入数据库的自定义c方法
//                    if($this->c($a,$xdata,$bqs)){
//                        returnApiSuccess('1','上传成功');
//
//                    }
//                    else{
//                        returnApiError( '写入数据库失败');
//
//                    }
//                }else{
//                    returnApiError( '上传文件异常，请与系统管理员联系');
//
//                }
//            }
//
//        }else{
//            returnApiError( '请登入');
//        }
//
//    }
//
////接收文件
//    private function c($data,$xdata=null,$pdata=null){
//        $prefix = C('DB_PREFIX');
//        foreach($data as $k=>$file){
//            $img_val='/file/'.$file['savepath'].$file['savename'];
//            if($k<7){
//                $ks=$k+1;
//                //$datas["img$ks"] ='/file/'.$file['savepath'].$file['savename'];
//                $datas["img$ks"] =$img_val;
//            }
//        }
//        $datas = array_merge($datas,$xdata);
//        $file=M('go');
//        if($file->add($datas)){
//            if($pdata){
//
//                foreach($pdata as $k=>$value){
//                    $pd['sid']=1;
//                    $pd['table_name']= "{$prefix}member";
//                    $pd['bid']=$xdata['user_id'];
//                    $pd['tab_id']=$value;
//                    $pd['t']=time();
//                    M('tab_nexus')->add($pd);
//                }
//            }
//
//            return true;
//        }else{
//            return false;
//        }
//
//    }
//    function up(){
//        $upload = new \Think\Upload();// 实例化上传类
//        $upload->maxSize   =     3145728 ;// 设置附件上传大小
//        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
//        $upload->rootPath  =      './file/'; // 设置附件上传根目录
//        $upload->savePath  =      ''; // 设置附件上传（子）目录
//// 上传文件
//        $info   =   $upload->upload();
//        if(!$info) {// 上传错误提示错误信息
//            $this->error($upload->getError());
//        }else{// 上传成功 获取上传文件信息
//            return $info;
//
//        }
//
//    }

    //---------------------------------------------------------------------个人中心结束--------------------------------------


}