<?php

namespace Home\Controller;
use Think\Upload;
use Vendor\Page;
use Firebase\JWT\JWT;
use XmClass\RndChinaName;
use XmClass\Easemob;
use XmClass\Ucpassxm;

class ApiController extends ComController
{
    private $token_xm; //校验参数
    private $key_xm; //jwt参数
    private $man_xx_time; //线下发单限制时间
    private $options;//环信配置

    private $sms_accountsid;
    private $sms_token;
    private $sms_templateid;
    private $sms_appid;

    public function _initialize()
    {
        //跨域
    //    $this->checkRequestCors();

        $this->token_xm=C('token_xm');
        $this->key_xm=C('key_xm');
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
    //    $this->checkRequestAuth();
        $t = intval($_POST['t']) > 0 ?$_POST['t'] : '';//时间
        $xycs= isset($_POST['verify']) ? trim($_POST['verify']) : '';//mb5(时间+校验参数)
        $xycs_bd=  $this->token_xm;
        $verify=md5($t.$xycs_bd);
        if ( $t == '') {returnApiError( '时间必须！');}
        if ($xycs == '') {returnApiError( '校验码必须！');}
    //    if ($verify!=$xycs){returnApiError( '非法数据');}

       //不用验证是方法
        $no_dr=array();
        $no_dr[]='is_jwt';//jwt验证
        $no_dr[]='register';//注册
        $no_dr[]='ziyezs';//选择性别和职业展示页
        $no_dr[]='ziyeqr'; //确认选择性别和职业
        $no_dr[]='xmandage'; //完善资料
        $no_dr[]='login'; //登入
        $no_dr[]='index'; //
        $no_dr[]='ManPostPaymentAudit'; //支付后调用验证
        $no_dr[]='getSMSCode'; //发送短信

        if(!in_array(ACTION_NAME, $no_dr)){
            $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
            $str = isset($_POST['token']) ? trim($_POST['token']) : '';
            $key =  $this->key_xm;
            if ($user_id == '') { returnApiError( '用户id必须');}
            if ($str == '') { returnApiError( 'token必须');}
            if ($key == '') { returnApiError( 'key必须');}
            $this->is_jwt($str,$key,1,$user_id);
        }

    }

//    public function checkRequestCors()
//    {
//        //跨域
//        header("Access-Control-Allow-Origin: *");
//        header('Access-Control-Allow-Headers:Authorization');
//        //   header("Access-Control-Allow-Methods: GET, POST, DELETE");
//        header("Access-Control-Allow-Methods: POST");
//        header("Access-Control-Allow-Credentials: true");
//        header("Access-Control-Allow-Headers: Content-Type, X-Requested-With, Cache-Control,Authorization");
//}
//    public function checkRequestAuth() {
//        $headers =  getallheaders();
//        $app_type=explode(',',c('app_type'));
//        if(!in_array($headers['Apptype'], $app_type)) {
//           returnApiError( 'app_type不合法');
//        }
//
//    }

    public function index()
    {

        $h=new Easemob( $this->options);
$data = $h-> isOnline(18644444444);
         print_r($data['data'] );
       // print_r($h->createUser("xm4","123456"));exit;
//        var_dump($h->createUser("xm","123456"));
   //     $this->display();
//        $from='admin';
//        $target_type="users";
//        //$target_type="chatgroups";
//        $target=array("18627909358","lisi","wangwu");
//        //$target=array("122633509780062768");
//        $aaa='{
//	"flag": "Success",
//	"msg": "查询成功",
//	"data": [{
//		"order_id": "1",
//		"user_id": "7",
//		"userdata": {
//			"o_username": "别b",
//			"height": "000",
//			"weight": "0",
//			"sex": "1",
//			"video": "",
//			"head": ""
//		}
//	}, {
//		"order_id": "1",
//		"user_id": "7",
//		"userdata": {
//			"o_username": "别别别b",
//			"height": "000",
//			"weight": "0",
//			"sex": "1",
//			"video": "",
//			"head": ""
//		}
//	}]
//}';
//        $content="$aaa";
//        $ext['a']="a";
//        $ext['b']="b";
//        var_dump($h->sendText($from,$target_type,$target,$content,$ext));
    }

    /*验证jwt
     *$str  jwt密码
     * $key  jwt参数
     *$is_yz  0是返回数据 1是验证是不是唯一登入
     * */
    private function is_jwt($str='', $key='',$is_yz='0',$user_id=''){
        if($str == ''){
            returnApiError( 'tokan必须！');
        }
        $decoded = JWT::decode( $str, $key, array('HS256'));
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





//-------------------------------------------------------------注册部分开始--------------------------------------------
    //注册
    public function register()
    {
        $moblie = isset($_POST['mobile']) ? trim($_POST['mobile']): '';//手机号
        $password = isset($_POST['password']) ? password(trim($_POST['password'])) : '';

        $phone_vf = isset($_POST['phone_vf']) ? trim($_POST['phone_vf']) : '';//手机验证码
        if(empty($phone_vf)){
            returnApiError( '手机验证码不能为空');
        }



        //验证
        if ($moblie == '') { returnApiError( '手机号必须！');}
        if ($password == '') {returnApiError( '密码必须！');}

        $smscode = D('XmCord');
        $smscodeObj = $smscode->where("mobile='$moblie'")->find();

        if($smscodeObj){
            if($smscodeObj['cord']!= $phone_vf){
                returnApiError( '验证码错误');
            }
        }else{
            returnApiError( '无验证码');
        }

        $model = M("XmMember");
        $user = $model->field('id')->where(array('moblie' => $moblie))->find();
        if ($user) {
            returnApiError( '手机号已被注册');
        }else{
            //可以注册逻辑
            $data['moblie']= $moblie;
            $data['password']= $password;
            $data['code']= make_coupon_card();//邀请码
            $data['kx_password']= trim($_POST['password']);
            //环信注册
            $h=new Easemob( $this->options);
            $hx=$h->createUser($moblie,trim($_POST['password']));
            if (isset($hx['entities'][0]["uuid"])) {
                $data['hx_uuid'] = $hx['entities'][0]["uuid"];
            } else {
                returnApiError('环信注册失败请换用户名重新注册');
            }

            $tjcg=$model->add($data);
            if($tjcg){
                returnApiSuccess('注册成功',$tjcg);
            }else{
                returnApiError( '添加失败');
           }
        }
    }

    //选择性别和职业展示页
    public function ziyezs()
    {
        $sex= intval($_POST['sex']) > 0 ?$_POST['sex'] : '';//性别
//赋值
        $table="XmTab";
        $field='id,o_username';
        $where['type']=0;
        $where['sex']=$sex;
        $limit=10;
        $data=xm_gf($table,$field,$where,$limit);
        if($data){
            returnApiSuccess('请求成功',$data);
        }else{
            returnApiError( '请求失败');
        }

    }

    //确认选择性别和职业
    public function ziyeqr()
    {
        $sex= intval($_POST['sex']) > 0 ?$_POST['sex'] : '0';//性别
        $xzid=isset($_POST['xzid']) ? trim($_POST['xzid']) : '';//选择id
        $user_id = intval($_POST['user_id']) > 0 ?$_POST['user_id'] : '';//用户id

        //  验证
        if ( $user_id == '') { returnApiError( '用户id必须！');}

        //逻辑

        $name_obj = new RndChinaName();
        $name = $name_obj->getName(2);
        $where['id'] =  $user_id;
        $data['sex']= $sex;
        $data['tc_id']= $xzid;
        $data['nm']=  $name;
        $User = M('XmMember')->where($where)->save($data);
        if ($User) {
            returnApiSuccess('请求成功', $User);
        } else {
            returnApiError('添加失败');
        }
    }

   //完善资料
    public function xmandage()
    {
        $username=isset($_POST['username']) ? trim($_POST['username']) : '';//姓名
        $birth=isset($_POST['birth'])?trim($_POST['birth']): '';//出生时间搓559238400
        $user_id = intval($_POST['user_id']) > 0 ?intval($_POST['user_id'])  : '';//用户id

        //验证
        if ($username == '') { returnApiError( '姓名必须！');}
        if ($birth == '') {returnApiError( '出生时间必须！');}
        if ( $user_id == '') { returnApiError( '用户id必须！');}

        //逻辑
        $where['id'] =  $user_id;
        $data['o_username']= $username;
        $data['birth']=  $birth;
        $data['is_news']= 1;
        $User = M('XmMember')->where($where)->save($data);
        if ($User) {
            returnApiSuccess('请求成功', $User);
        } else {
            returnApiError('添加失败');
        }
}

//-------------------------------------------------------------注册部分完成--------------------------------------------

//  登入
    public function login()
    {

        $moblie = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
        $password = isset($_POST['password']) ? password(trim($_POST['password'])) : '';
  //      $remember = isset($_POST['remember']) ? $_POST['remember'] : 0;短信

        if ($moblie == '') {
            returnApiError('手机不能为空！');
        } elseif ($password == '') {
            returnApiError('密码必须！');
        }

        $model = M("XmMember");
        $user = $model->field('id')->where(array('moblie' => $moblie, 'password' => $password))->find();

        if ($user) {
            //这个时候判断环信的有没有登入
            $h=new Easemob( $this->options);
            $data_user = $h-> isOnline($moblie);
          if($data_user['data'][$moblie]=='offline'){
              returnApiError('环信必须登入！');
          }

            //将session_id存到数据库

            $where['id'] = $user['id'];
            $data['sessionId']=  session_id();
            M('XmMember')->where($where)->save($data);


            $users = $model->field('id,sex,moblie,is_fwz,is_nm,nm,o_username,sessionId,birth')->where(array('moblie' => $moblie, 'password' => $password))->find();

            $key =$this->key_xm;
            $token =$users;
            $jwt = JWT::encode($token, $key);
            $data=$users;

            $data['token'] =  $jwt;
            returnApiSuccess('登入成功', $data);

        } else {
            returnApiError('手机号或密码有错误');
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

    //-----------------------------------------------------------------男性模块开始-------------------------------------------
    //约会主题
    public function ManDateTheme()
    {
        //验证必须登入
        $table="XmTrystClassify";
        $field='*';
        $data=xm_gf($table,$field);
//数据添加图片宽高

        foreach( $data as $key=>$value){
           if($value['id']==1){
               $value[w] = 480;
               $value[h] = 540;
           }else{
               $value[w] = 480;
               $value[h] = 300;
           }
            $datas[]= $value;
        }



        if($data){
            returnApiSuccess('请求成功',$datas);
        }else{
            returnApiError('请求失败');
        }
    }

//。。。。。。。-----------------------------------------------------------------男性线下模块开始-------------------------------------------
//发约订单生成
    public function ManTrystCreate()
    {
        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
        $classify= intval($_POST['classify']) > 0 ?intval(trim($_POST['classify'])) : '0';//分类id
        $createtime= intval($_POST['createtime']) > 0 ?intval(trim($_POST['createtime'])) : '0';//约会时间
        $time_limit= intval($_POST['time_limit']) > 0 ?intval(trim($_POST['time_limit'])) : '0';//约会时限
        $appointment_dd=isset($_POST['appointment_dd']) ? trim($_POST['appointment_dd']) : '';//约会地点
        $is_sex= intval($_POST['is_sex']) > 0 ?'1' : '0';//要求性别
        $people_num= intval($_POST['people_num']) > 0 ?intval(trim($_POST['people_num'])) : '1';//要求人数
        $is_drink= intval($_POST['is_drink']) > 0 ?'1' : '0';//是否喝酒
        $is_anonymous= intval($_POST['is_anonymous']) > 0 ?intval(trim($_POST['is_anonymous'])) : '0';//是否匿名
        $remarks=isset($_POST['remarks']) ? trim($_POST['remarks']) : ''; //备注
        $money= intval($_POST['money']) > 0 ?intval(trim($_POST['money'])) : '0';//金额
        $daz_business_id= intval($_POST['daz_business_id']) > 0 ?intval(trim($_POST['daz_business_id'])) : '0';//大众点评id
        $target_area = isset($_POST['target_area']) ? trim($_POST['target_area']) : '';//地点

        //过滤
        if($user_id==""){ returnApiError( '用户id必须');}
        if($createtime==0){ returnApiError( '约会时间必须');}
        if($time_limit==0){ returnApiError( '约会时限必须');}
        if($appointment_dd==""){ returnApiError( '约会地点必须');}
        if($money==0){ returnApiError( '金额必须');}
        //逻辑
         $data['order_number']= build_order_no(); //订单编号
         $data['user_id']= $user_id;//发起者用户id
         $data['classify']= $classify;//分类id
         $data['order_name']= xm_fl_name($classify);//分类名字

         $data['appointment_time']=  $createtime;//约会时间
         $data['time_limit']= $time_limit;//约会时限
         $data['appointment_dd']= $appointment_dd;//约会地点
         $data['is_sex']=  $is_sex;//要求性别
         $data['people_num']=  $people_num;//要求人数
         $data['is_drink']=  $is_drink;//是否喝酒
         $data['is_anonymous']=  $is_anonymous;//是否匿名
         $data['remarks']=  $remarks;//备注
         $data['money']=  $money;//金额
         $data['status']=  0;
         $data['daz_business_id']= $daz_business_id;
         $data['target_area']=  $target_area;
         $data['time_add']=  time();
         $model=M('XmOrder');
         $tjcg=$model->add($data);
        if($tjcg){
            returnApiSuccess('添加成功',$tjcg);
        }else{
            returnApiError( '添加失败');
        }
}

    //支付选择
    public function ManPaymentChoice()
    {
        $table="XmPayment";
        $field='id,title,option';
        $where['is_enable']=1;
        $data=xm_gf($table,$field,$where);
        foreach( $data as $value){
            $value['option']=explode(',',$value['option']);
            $datas[]= $value;
        }
        if($data){
            returnApiSuccess('查询成功',$datas);
        }else{
            returnApiError( '查询失败');
        }

}
    //用户支付计算金额
    public function ManPaymoney()
    {
        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
        $order_id = isset($_POST['order_id']) ? trim($_POST['order_id']) : '';//订单id

        $order_start=xm_order_start($order_id, $user_id);
        if( $order_start=='-1'){
            returnApiError( '无订单或者不是你的订单');
        }else{
            if($order_start!=0&&$order_start!=4){
                returnApiError( '订单状态不正确');
            }
        }
        $where_o['id']= $order_id;
        $zf_money=M('XmOrder')->where($where_o)->getField('money');

        //查看用户是否有代金券
        $djj=xm_ky_djj($user_id);
if($djj){
    //使用代金券后价格money
$dj_money=  $djj['coupon_money'];

    if($zf_money>$dj_money){
        $zf_q=$zf_money-$dj_money;
        $zf_moneys['zf_money']=$zf_q;
        $zf_moneys['coupon_num']=$djj['coupon_num'];
        $zf_moneys['coupon_id']=$djj['id'];
        $zf_moneys['order_money']= $zf_money;
        returnApiSuccess('查询成功', $zf_moneys);
    }else{
        $zf_moneys['zf_money']=0;
        $zf_moneys['coupon_num']=$djj['coupon_num'];
        $zf_moneys['coupon_id']=$djj['id'];
        $zf_moneys['order_money']= $zf_money;
        returnApiSuccess('查询成功', $zf_moneys);
    }

}else{
    //如果没有返回订单原价
    $zf_moneys['zf_money']=$zf_money;
    $zf_moneys['coupon_num']=$djj['coupon_num'];
    $zf_moneys['coupon_id']=$djj['id'];
    $zf_moneys['order_money']= $zf_money;
    returnApiSuccess('查询成功', $zf_moneys);

}




    }

    

 //支付前审核
    public function ManPaymentExamine()
    {
        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
        $order_id = isset($_POST['order_id']) ? trim($_POST['order_id']) : '';//订单id
        $f_money= intval($_POST['money']) > 0 ?intval(trim($_POST['money'])) : '0';//金额
        $zffs= intval($_POST['zffs']) > 0 ?intval(trim($_POST['zffs'])) : '1';//支付方式1是余额2是微信3是支付宝

        if($f_money==0){ returnApiError( '金额必须');}
        $Oerder = M("XmOrder"); //实例化User对象
        $where['order_id']= $order_id;
        $field='money,status';
        $data = $Oerder->field($field)->where($where)->find();

if($data['status']=='0'){
//未支付
    $where_u['id']=  $user_id ;
    $field_u='balance,jk_balance,is_jkuser';
    $user_data = M('XmMember')->field($field_u)->where($where_u)->find();
  //  if($data['money'] != $f_money ){  returnApiError( '订单价格不正确');}
    if($zffs==1){
        //余额
        if($user_data['is_jkuser']==1){
            //是金卡
            if($user_data['jk_balance']>= $f_money){
                returnApiSuccess('金卡余额可以支付','1');
            }else{
                returnApiError( '金卡余额不足');
            }
   }else{
            if($user_data['balance']>= $f_money){
                returnApiSuccess('余额可以支付','1');
            }else{
                returnApiError( '余额不足');
            }
        }
    }else{
        //其他支付方式
        returnApiSuccess('其他支付方式可以支付','1');
    }

}else{
//已支付
    returnApiError( '订单状态不正确');
}

    }



    //其它支付支付后返回改状态
    public function ManPostPaymentAudit()
    {
       //查订单状态如果支付的不然走
        //查价格看订单价格是不是正确的
        //改变订单状态,扣个人钱
    }

    //余额线下支付支付后返回改状态
    public function ManYePostPaymentAudit()
    {

       $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
       $order_id = isset($_POST['order_id']) ? trim($_POST['order_id']) : '';//订单id
        $f_money= intval($_POST['money']) > 0 ?intval(trim($_POST['money'])) : '0';//金额
        $coupon_id = isset($_POST['coupon_id']) ? trim($_POST['coupon_id']) : '';//优惠卷id

//
//        if($f_money==0){ returnApiError( '金额必须');}
//        if($order_id==''){ returnApiError( '订单id必须');}

        $where_u['id']=  $user_id ;
        $field_u='balance,jk_balance,is_jkuser';
        $user_data = M('XmMember')->field($field_u)->where($where_u)->find();

        if($user_data['is_jkuser']==1){
            //是金卡
            if($user_data['jk_balance']< $f_money){returnApiError( '金卡余额不足');}
        }else{
            if($user_data['balance']< $f_money){returnApiError( '余额不足');}
        }

        $Oerder = M("XmOrder"); // 实例化User对象
        $where['order_id']= $order_id;
        $field='money,status,target_area';
        $data = $Oerder->field($field)->where($where)->find();
//        if($data['status']>0){returnApiError( '订单已支付');}
        //如果有代金券，支付钱加上代金券
        if($coupon_id){
           $where_s_d['id']=$coupon_id;
            $field_s_d="coupon_money,state";
            $data_s_d=M('XmCoupon')->field($field_s_d)->where($where_s_d)->find();
            if( $data_s_d['state']>0){
                returnApiError( '代金券状态有问题');
            }else{
             //   if($data['money'] != $f_money+ $data_s_d['coupon_money'] ){  returnApiError( '订单价格不正确');}
            }
        }else{
            //        if($data['money'] != $f_money ){  returnApiError( '订单价格不正确');}
        }

        //改变订单状态,
        $model = M();
//        $m=D('XmOrder');
//        $m2=D('XmMember');
        $model->startTrans();
        $where_dd['id']= $order_id;
        $data_dd['status'] = '1';
        if( $data_s_d['state']<1){
            $data_dd['is_user_coupon']=1;
            $data_dd['coupon_id']=$coupon_id;
            //代金券用完要改状态2.0加
        }
        $result=$model->table('qw_xm_order')->where($where_dd)->save($data_dd);

        //扣个人钱
        $where_us['id']= $user_id;
        if($user_data['is_jkuser']==1){
            //是金卡
           $user_sx = $user_data['jk_balance'] -  $f_money;
            if($user_sx>0){
                $where_us['id']=  $user_id ;
                $data_us['jk_balance'] = $user_sx;
                $result2=$model->table('qw_xm_member')->where( $where_us)->save($data_us);

            }else{
                $result2= false;
            }

        }else{
            $user_sx = $user_data['balance'] -  $f_money;
            if($user_sx>0){
                $where_us['id']=  $user_id ;
                $data_us['balance'] = $user_sx;
                $result2=$model->table('qw_xm_member')->where( $where_us)->save($data_us);

            }else{
                $result2= false;
            }
        }

if($result && $result2){
    $model->commit();//成功则提交
    $zjdata['is_zf']=1;
    $where_cx['id']=$order_id;
    $order_num =M('XmOrder')->where($where_cx)->getField('order_number');
    $ml='订单'.$order_num.' 支付成功余额支付'. $f_money.' ,剩余余额为'.$user_sx;
    $mlfm='-'.$f_money;
    moneylog( $ml, $user_id,0,$mlfm,$user_sx,'余额',$order_id);
   //可抢订单

    $data_ro['order_id'] = $order_id;
    $data_ro['create_time'] = time();
    $data_ro['end_time'] = time() + $this->man_xx_time;
    $data_ro['status'] =  1;
    $data_ro['area'] = $data['target_area'];
    $data_ro['user_id'] =  $user_id;
    $Rorde = M("XmRobOrde")->add($data_ro); // 实例化User对象
     if($Rorde){
         $zjdata['is_xsdd']=1;
     }else{
         $zjdata['is_xsdd']=0;
     }
    returnApiSuccess('支付成功',$zjdata);
}else{
    $model->rollback();//不成功，则回滚
    returnApiError( '支付失败');
}
    }




//男性选择模块
    public function ManChoice()
    {

        $order_id = isset($_POST['order_id']) ? trim($_POST['order_id']) : '';//订单id
        $Muser=M("XmMember");
        $Mcdata=M('XmGrabSingle');
        $where['order_id']=  $order_id;
        $where['start'] = 1;
        $field='order_id,user_id';
        $data=$Mcdata->field( $field)->where($where)->select();
if( $data){
    foreach( $data as $value){
       $where_u['id']=$value['user_id'];
       $field_u='o_username,height,weight,sex,video,Head';
        $value['userdata']= $Muser->field($field_u)->where($where_u)->find();
    $userdatas[]=$value;
    }
    returnApiSuccess('查询成功',$userdatas);
}
    }

    //男性选择确认
    public function ManChoiceqr()
    {
        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
        $order_id = isset($_POST['order_id']) ? trim($_POST['order_id']) : '';//订单id
        $xz_id=isset($_POST['xz_id']) ? trim($_POST['xz_id']) : '';//已选择id
        if( $xz_id==''){ returnApiError( '已选择id必须');}
        if( $order_id==''){ returnApiError( '订单id必须');}
        $where_xm['id']= $order_id;

        //如果取消的就不能选择
        $order_start=xm_order_start($order_id, $user_id);
        if( $order_start=='-1'){
            returnApiError( '无订单或者不是你的订单');
        }else{
            if($order_start==8){
                returnApiError( '订单状态不正确');
            }
        }

        $fenxzid=array_filter(explode(',',$xz_id));
        $xz_count=count($fenxzid);
        foreach($fenxzid as $value){
            $where['user_id']=$value;
            $where['order_id']= $order_id;
            $data['is_qd_id']=1;
            M('XmGrabSingle')->where($where)->save($data);
        }
      $data_o['xz_user_id']=$xz_id;
      $data_o['xz_pe_num']= $xz_count;
        $data_o['status']= 2;
      $where_o['id'] =   $order_id ;

      $data_s=M('XmOrder')->where($where_o)->save($data_o);

        if($data_s){
            returnApiSuccess('选择成功',$data_s);
        }else{
            returnApiError( '选择失败');
        }
    }


//男性无选择退钱
    public function ManNoChoice()
    {
        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
        $order_id = isset($_POST['order_id']) ? trim($_POST['order_id']) : '';//订单id
        if( $order_id==''){ returnApiError( '订单id必须');}
        $where_xm['id']= $order_id;
        $firld_xm='user_id,status,money';
        $gf_id=M('XmOrder')->field($firld_xm)->where($where_xm)->find();
        if($gf_id['user_id']!= $user_id){returnApiError( '不是你的订单');}
        if( $gf_id['status']!=2){returnApiError( '订单状态有异常');}
        $order_money=$gf_id['money'];

        $Model = M(); // 实例化一个空对象
        $Model->startTrans(); // 开启事务
        //改可选订单状态
        $data_t_os['status']=8;
        $result =$Model->table('qw_xm_order')->where($where_xm)->save($data_t_os);

        //改用户金额
        $where_c['id'] =  $user_id;
        $field_c = 'is_jkuser,balance,jk_balance';
            $userm=M('XmMember')->field($field_c)->where($where_c)->find();
        $where_t_o['id']=$user_id;
if( $userm['is_jkuser']==1){
    //金卡用户
    $data_t_o['jk_balance']=$userm['jk_balance']+ $order_money;

    $result1 =$Model->table('qw_xm_member')->where($where_t_o)->save($data_t_o);

}else{
    $data_t_o['balance']=$userm['balance']+ $order_money;
    $result1 =$Model->table('qw_xm_member')->where($where_t_o)->save($data_t_o);

}
        //修改可抢订单
        $where_r_o['id']= $order_id;
        $data_r_o['status']= 0;

        $result2 =$Model->table('qw_xm_rob_orde')->where($where_r_o)->save($data_r_o);

        //删除女性选择
        $where_x_o['order_id']= $order_id;
        $data_x_o['start']=0;
        $result3 =$Model->table('qw_xm_grab_single')->where($where_x_o)->save($data_x_o);

        if ($result && $result1 && $result2 && $result3) {
            $Model->commit(); // 成功则提交事务
            $where_cx['id']=$order_id;
            $order_num =M('XmOrder')->where($where_cx)->getField('order_number');

            if( $userm['is_jkuser']==1){
                //金卡用户
                $ml='订单'.$order_num.' 无选择，订单退款'. $order_money.' ,账号可用余额为'. $data_t_o['jk_balance'];
                $mlfm=$data_t_o['jk_balance'];
                moneylog( $ml, $user_id,5,$order_money,$mlfm,'余额',$order_id);
            }else{
                $ml='订单'.$order_num.' 无选择，订单退款'. $order_money.' ,账号可用余额为'. $data_t_o['balance'];
                $mlfm=$data_t_o['balance'];
                moneylog( $ml, $user_id,5,$order_money,$mlfm,'余额',$order_id);
            }

            returnApiSuccess('选择成功',1);

        } else {

            $Model->rollback(); // 否则将事务回滚
            returnApiError( '退款失败');
        }

    }

    //约会详情
    public function ManDetailsDate()
    {
        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
        $order_id = isset($_POST['order_id']) ? trim($_POST['order_id']) : '';//订单id
        if( $order_id==''){ returnApiError( '订单id必须');}
        //订单状态
        $order_start=xm_order_start($order_id, $user_id);
        if( $order_start=='-1'){returnApiError( '无订单或者不是你的订单');}

        $Model=M('XmOrder');
        $where['id']= $order_id ;
        $field='order_name,appointment_time,time_limit,appointment_dd,is_sex,is_drink,is_anonymous';
        $data['order']=$Model->field($field)->where($where)->find();
        $data['order']['start']=$order_start;


        $ufield='nm,o_username,Head';
        $xuer=xm_user($user_id,$ufield);
        if($xuer==-1){
            $data['user']='';
        }else{
            $data['user']= $xuer;
        }
        returnApiSuccess('订单详情', $data);
    }

 //点击确认完成订单女性分钱
    public function ManCompleteOrder()
    {
        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
        $order_id = isset($_POST['order_id']) ? trim($_POST['order_id']) : '';//订单id
        if ($order_id == '') {
            returnApiError('订单id必须');
        }
        //订单状态
        $order_start = xm_order_start($order_id, $user_id);
        if ($order_start == '-1') {
            returnApiError('无订单或者不是你的订单');
        }
        if ($order_start > 5) {
            returnApiError('订单状态不正确');
        }

        $where_o_c['id'] = $order_id;
        $field_o_c = 'money,xz_pe_num,xz_user_id';
        $cx_data = M('XmOrder')->field($field_o_c)->where($where_o_c)->find();
        //查询用户人数
        $cx_r_s = $cx_data['xz_pe_num'];
        //查询订单钱
        $cx_r_money = $cx_data['money'];
        //每个用户可以分到的钱
        $money_f = floor($cx_r_money / $cx_r_s);

        //查询用户
        //查询用户等级
        //查询用户比例
        $cx_user = array_filter(explode(',', $cx_data['xz_user_id']));
        foreach ($cx_user as $value) {
            $userdata = xm_user($value, 'women_grade,id,balance');
            $userdata['bfb'] = xm_women_draw($userdata['women_grade']);
            $datas[] = $userdata;
        }
        $Model = M();
        $Model->startTrans();
        //改变订单状态
        $where_o['id'] = $order_id;
        $data_o['status'] = 6;
        $data_o['time_completion'] = time();
        $result = M('XmOrder')->where($where_o)->save($data_o);
        //分钱
        foreach ($datas as $key => $value) {
            $where_f_u['id'] = $value['id'];
            $data_f_u['balance'] = $value['balance'] + $money_f;
            //   $mz='result'.$key;
            $result1 = $Model->table('qw_xm_member')->where($where_f_u)->save($data_f_u);
            if ($result1) {
                $results = true;
            } else {
                $results = false;
            }
        }
        if ($result && $results) {

            $Model->commit(); // 成功则提交事务
            //日志
            foreach ($datas as $key => $value) {
                $tcmoney = $value['balance'] + $money_f;
                $where_cx['id'] = $order_id;
                $order_num = M('XmOrder')->where($where_cx)->getField('order_number');
                $ml = '订单' . $order_num . '获得提成' . $money_f . ' ,账号可用余额为' . $tcmoney;
                moneylog($ml, $value['id'], 6, $money_f, $tcmoney, '余额', $order_id);
            }
            returnApiSuccess('订单已完成', 1);
        } else {
            $Model->rollback(); // 否则将事务回滚
            returnApiError('改变订单完成失败');
        }
    }

//评价页面
//    public function ManAssess()
//    {
//        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
//        $order_id = isset($_POST['order_id']) ? trim($_POST['order_id']) : '';//订单id
//        if ($order_id == '') {
//            returnApiError('订单id必须');
//        }
//        //订单状态
//        $order_start = xm_order_start($order_id, $user_id);
//        if ($order_start == '-1') {
//            returnApiError('无订单或者不是你的订单');
//        }
//        if ($order_start >6) {
//            returnApiError('订单状态不正确');
//        }
//
//        $where['sex']=1;
//        $where['type']=array('gt',1);
//        $field='o_username,type';
//       $data=M('XmTab')->field($field)->where($where)->select();
//
//        if( $data){
//            returnApiSuccess('请求成功', $data);
//        }else{
//            returnApiError('请求失败');
//        }
//
//    }

//确认评价
    public function ManAssessqw()
    {

        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
        $order_id = isset($_POST['order_id']) ? trim($_POST['order_id']) : '';//订单id
        $contents=isset($_POST['content']) ? trim($_POST['content']) : '';//评价内容
        $o_type=isset($_POST['o_type']) ? intval(trim($_POST['o_type'])) : '1';//线上线下
        $c_type=isset($_POST['c_type']) ? intval(trim($_POST['c_type'])) : '0';//好差评类型 0是中评1是好评2是差评',

        if ($order_id == '') {
            returnApiError('订单id必须');
        }
        //订单状态
        $order_start = xm_order_start($order_id, $user_id);
        if ($order_start == '-1') {
            returnApiError('无订单或者不是你的订单');
        }
        if ($order_start  !=  6  ) {
            returnApiError('订单状态不正确');
        }

        //
        $where['id']= $order_id;
        $data['offline_type']=$o_type;
        $data['comment_type']= $c_type;
      //轮询
       $rx = M('XmOrder')->where($where)->getField('xz_user_id');
        $rx_user = array_filter(explode(',',$rx));
        foreach ($rx_user as $value) {
            $data['user_id']=$value;
            $cx_user = array_filter(explode(',', $contents));
            foreach ($cx_user as $value) {
                $data['content']= $value;
                M('XmComment')->add($data);
            }
        }
        $cg = xm_order_g_start($order_id,7);
        if($cg){
            returnApiSuccess('请求成功', $cg);
        }else{
            returnApiError('请求失败');
        }

}



//。。。。。。。-----------------------------------------------------------------男性线下模块结束-------------------------------------------

//。。。。。。。-----------------------------------------------------------------男性线上模块开始-------------------------------------------
//男性查询余额
    public function ManSelectMoney()
    {
        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
        $usermoney =  xm_is_jk_money($user_id);//用户可用钱
        if($usermoney<10){  returnApiError('余额不足请充值');}
}

//正常线上扣费
    public function ManOnlineMomey()
    {
        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
        $order_id = isset($_POST['order_id']) ? trim($_POST['order_id']) : '';//订单id
        $time_add = isset($_POST['time_add']) ? trim($_POST['time_add']) : '';//发起时间
        $time_completion = isset($_POST['time_completion']) ? trim($_POST['time_completion']) : '';//结束时间
        $guanduan_id = isset($_POST['guanduan_id']) ? trim($_POST['guanduan_id']) : '';//挂断人id

        if ($order_id == '') {returnApiError('订单id必须');}
        if ($user_id == '') {returnApiError('用户必须');}
        if ($time_add == '') {returnApiError('发起时间必须');}
        if ($time_completion == '') {returnApiError('结束时间必须');}

        //订单状态
        $order_start = xm_order_start($order_id, $user_id);
        if ($order_start == '-1') {
            returnApiError('无订单或者不是你的订单');
        }
//是不是新用户
      $userdata=  xm_user( $user_id,'is_news,is_jkuser');
        if( $userdata['is_news']){
            $kouqian =  xm_xsxuer_kou_money( $time_add , $time_completion);
        }else{
            $kouqian =  xm_xs_kou_money( $time_add , $time_completion);
        }
        $yumoney= xm_is_jk_money($user_id);
        if($yumoney< $kouqian){
          returnApiError('余额不足');
    //异常订单2.0添加
        }

//男方扣钱 女方得钱  改订单状态

$Model = M();
$Model->startTrans();
 $where_m_k['id']= $user_id;
if($userdata['is_jkuser']){
    $data_m_k['jk_balance']=$yumoney - $kouqian;
    $result1 =$Model->table('qw_xm_member')->where($where_m_k)->save($data_m_k);
}else{
    $data_m_k['balance']=$yumoney - $kouqian;
    $result1 =$Model->table('qw_xm_member')->where($where_m_k)->save($data_m_k);
}
  // 订单女性id
        $where_w_o['id']= $order_id;
        $women_id =M('XmOrder')->where($where_w_o)->getField('xz_user_id');
        $where_w_d['id']= $women_id;
        $women_money =M('XmMember')->where($where_w_d)->getField('balance');
        $women_w_d['balance']=$women_money + $kouqian;
        $result2 =$Model->table('qw_xm_member')->where($where_w_d)->save($women_w_d);

    //order
        $where_o_d['id']=$order_id;
        $data_o['status']='6';
        $data_o['guanduan_id']=$guanduan_id;
        $data_o['time_completion']=$time_completion;
        $result3 =$Model->table('qw_xm_order')->where($where_o_d)->save($data_o);


    }


//。。。。。。。-----------------------------------------------------------------男性线上模块结束-------------------------------------------



    //-----------------------------------------------------------------男性模块结束-------------------------------------------

    //-----------------------------------------------------------------女性模块开始-------------------------------------------
    //女性抢单
    public function WomenGrabOrder()
    {
        //查询订单是否过期改订单状态
        $sj=time();
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
        $field_o='id,user_id';
        $data_o=$Xro->field($field_o)->where($where_o)->limit(20)->select();
     if($data_o){

    foreach($data_o as $value){
        if($value['type']){
//线上
        }else{
            //线下
            $where_os['id']=$value['id'];
            $field_os='appointment_time,time_limit,appointment_dd,remarks,is_sex,people_num,money';
            $value['order']=M('XmOrder')->field($field_os)->where($where_o)->find();
            $where_ou['id']=$value['user_id'];
            $field_ou='is_nm,nm,o_username,Head';
            $value['user']=M('XmMember')->field($field_ou)->where($where_u)->find();
            //抢订单状态
            $where_qds['id']=$value['id'];
            $field_os='order_id,is_qd_id';
            $qddata=M('XmGrabSingle')->field($field_os)->where($where_o)->find();
           if( $qddata['user_id']== $user_id){
               $value['userstart']='已报名';
               if($qddata['user_id']== 0){
                   $value['userstart']='已报名';
               }elseif($qddata['user_id']== 1){
                   $value['userstart']='已被选中';
               }else{
                   $value['userstart']='订单被抢';
               }
           }else{
               $value['userstart']='抢';
           }

        }
$ddata[]=$value;
    }

         returnApiSuccess('支付成功',$ddata);
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
        $sj=time();
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
        $where['order_id']=  $order_id;
        $where['user_id']=  $user_id;
        $where['add_time']= time();
        $where['start']=  1;

        $data=$gsdata->add($where);
if($data){
    returnApiSuccess('抢单成功',1);
}else{
    returnApiError( '抢单失败');
}
        //
    }
//    //。。。。。。。-----------------------------------------------------------------女性线下模块开始-------------------------------------------
//


    //。。。。。。。-----------------------------------------------------------------女性线下模块结束-------------------------------------------
    //-----------------------------------------------------------------女性模块结束-------------------------------------------

}