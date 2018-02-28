<?php
/**
 * 男性api接口
 * @author 熊敏
 * @version 1.0
 */
namespace Home\Controller;
use Home\Controller\XmcomprivacymobileController;

use Think\Upload;
use Vendor\Page;

use XmClass\RndChinaName;
use XmClass\Easemob;
use XmClass\Ucpassxm;
use Home\Controller\Traits;
use Home\Controller\Pay;
require_once "./comm_function.php";
class ApiController extends ApiComController
{
    private $man_xx_time; //线下发单限制时间
    private $options;//环信配置
    private $sms_accountsid;
    private $sms_token;
    private $sms_templateid;
    private $sms_appid;

    public function _initialize()
    {
        //跨域
        parent::checkRequestCors();
        $this->man_xx_time=C('man_xx_time')*1000;//10分钟
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
        $no_dr[]='is_jwt';//jwt验证
        $no_dr[]='register';//注册
        $no_dr[]='ziyezs';//选择性别和职业展示页
        $no_dr[]='ziyeqr'; //确认选择性别和职业
        $no_dr[]='xmandage'; //完善资料
        $no_dr[]='login'; //登入
        $no_dr[]='index'; //
        $no_dr[]='ManPostPaymentAudit'; //支付后调用验证
        $no_dr[]='getSMSCode'; //发送短信

    // parent::checkRequsetdr($no_dr);

    }



    public function index()
    {
        $user_id=14;
        $money=10.03;
        print_r(  xm_put_user_money($user_id,-$money));
        exit;
        returnApiError( '电话号码必须！');
        echo 1;exit;
        $body="";
        $subject=1;
        $out_trade_no=1;
        $order_amount=1;
        $aa= TraitsPay::alipay($body,$subject,$out_trade_no,$order_amount,$timeout_express='1d');
echo $aa;

//        $a=1.5;
//       echo  strtotime('15:00:00') +($a*3600);
//         $aa= Traits::xm_money_log();
//         echo $aa;
//        $subsId= "852410132" ;
//        $secretNo= "17080032906";
//        $orderid=10;
//        XmComPrivacyMobileController::jcshouji($orderid);
//
//        $NoA='15994221307';
//        //  $NoB='15071278668';
//        $NoB='17771879069';
//        // $NoB='18611644130';
//        $Stime = '2018-1-25 16:40:26';
//        $orderid ='10';
//        XmComPrivacyMobileController::bdshouji($NoA, $NoB, $Stime, $orderid);
//        $accessKeyId=C('accessKeyId');
//        $accessKeySecret=C('accessKeySecret');
 //      $h=new Plsth($accessKeyId,$accessKeySecret);
//        $NoA='15994221307';
//        //  $NoB='15071278668';
//        $NoB='17771879069';
//        // $NoB='18611644130';
//        $Stime = '2018-1-24 16:40:26';
//        $orderid ='10';
//        $axbResponse=  $h->bindAxb( $NoA, $NoB, $Stime, $orderid);
//        print_r($axbResponse);
     //   Pls::bindAxb();
//        $h=new Easemob( $this->options);
//$data = $h-> isOnline(18644444444);
//         print_r($data['data'] );
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
   //     var_dump($h->sendText($from,$target_type,$target,$content,$ext));
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
            $data['add_time']=time();
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

        $limit=12;
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

        if($sex){
            $toux='/Public/attached/image/women.png';
        }else{
            $toux='/Public/attached/image/man.png';
        }
        $name_obj = new RndChinaName();
        $name = $name_obj->getName(2);
        $where['id'] =  $user_id;
        $data['sex']= $sex;
        $data['tc_id']= $xzid;
        $data['nm']=  $name;
        $data['Head']=  $toux;
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
        $data['is_information']= 1;
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
//            $h=new Easemob( $this->options);
//             $data_user = $h->isOnline($moblie);
//          if($data_user['data'][$moblie]=='offline'){
//             returnApiError('环信必须登入！');
//          }

            //将session_id存到数据库

            $where['id'] = $user['id'];
            $data['sessionId']=  session_id();
            M('XmMember')->where($where)->save($data);
            $users = $model->field('id,sex,moblie,is_fwz,is_nm,nm,o_username,sessionId,birth,Head,is_jkuser')->where(array('moblie' => $moblie, 'password' => $password))->find();
            $token =$users;
            $jwt =  parent::yanjwt($token);//加密jwt
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
            $value['optime'] = xm_explod( $value['operation_time']);
            $value['optime_limit'] = xm_explod( $value['time_limit']);
            $value['opmoney'] = xm_explod( $value['money']);
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
        $createtime= intval($_POST['createtime']) > 0 ?trim($_POST['createtime']): '0';//约会时间

        $time_limit= isset($_POST['time_limit']) > 0 ?trim($_POST['time_limit']) : '0';//约会时限
        $appointment_dd=isset($_POST['appointment_dd']) ? trim($_POST['appointment_dd']) : '';//约会地点
        $is_sex= intval($_POST['is_sex']) > 0 ?'1' : '0';//要求性别
        $people_num= intval($_POST['people_num']) > 0 ?intval(trim($_POST['people_num'])) : '1';//要求人数
        $is_drink= intval($_POST['is_drink']) > 0 ?'1' : '0';//是否喝酒
        $is_anonymous= intval($_POST['is_anonymous']) > 0 ?intval(trim($_POST['is_anonymous'])) : '0';//是否匿名
        $remarks=isset($_POST['remarks']) ? trim($_POST['remarks']) : ''; //备注
        $money= intval($_POST['money']) > 0 ?intval(trim($_POST['money'])) : '0';//金额
    //    $daz_business_id= intval($_POST['daz_business_id']) > 0 ?intval(trim($_POST['daz_business_id'])) : '0';//大众点评id
        $target_area = isset($_POST['target_area']) ? trim($_POST['target_area']) : '';//地点

        //过滤
        if($user_id==""){ returnApiError( '用户id必须');}
        if($createtime==0){ returnApiError( '约会时间必须');}

        if($time_limit==0){ returnApiError( '约会时限必须');}
        if($appointment_dd==""){ returnApiError( '约会地点必须');}
        if($money==0){ returnApiError( '金额必须');}

        //检查余额是否
//       $ky_money= xm_is_jk_money($user_id);
//        if( $ky_money<$money){
//            returnApiError( '余额不足请充值');
//        }
        $jisu_time= $createtime+($time_limit*60*60*1000);//约会结束时间 时间搓为微秒
        //逻辑
         $data['order_number']= build_order_no(); //订单编号
         $data['user_id']= $user_id;//发起者用户id
         $data['classify']= $classify;//分类id
         $data['order_name']= xm_fl_name($classify);//分类名字

         $data['appointment_time']=  $createtime;//约会时间
         $data['jisu_time']=  $jisu_time;//约会时间
         $data['time_limit']= $time_limit;//约会时限
         $data['appointment_dd']= $appointment_dd;//约会地点
         $data['is_sex']=  $is_sex;//要求性别
         $data['people_num']=  $people_num;//要求人数
         $data['is_drink']=  $is_drink;//是否喝酒
         $data['is_anonymous']=  $is_anonymous;//是否匿名
         $data['remarks']=  $remarks;//备注
         $data['money']=  $money;//金额
         $data['status']=  0;
    //     $data['daz_business_id']= $daz_business_id;
         $data['target_area']=  $target_area;
         $data['time_add']=  get13TimeStamp();
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
        $coupon_id = isset($_POST['coupon_id']) ? trim($_POST['coupon_id']) : '';//代金券id

        $order_start=xm_order_start($order_id, $user_id);
        if( $order_start=='-1'){
            returnApiError( '无订单或者不是你的订单');
        }else{
            if($order_start!=0&&$order_start!=4){
                returnApiError( '订单状态不正确');
            }
        }

        //查看用户代金券是否合法
        if($coupon_id){
            $wheredj['id']=$coupon_id;
            $wheredj['state']=0;
            $wheredj['user_id']=$user_id;
            $field='id,coupon_money';
            $datadj=M('XmCoupon')->field($field)->where($wheredj)->find();
            if(!$datadj){ returnApiError( '代金券状态不正确');}
            $qiandata=Traits::get_order_jq( $user_id,$order_id,$coupon_id);
        }else{
            $qiandata=Traits::get_order_jq( $user_id,$order_id);
        }
        $qiandata['balance'] = xm_is_jk_money($user_id) ? xm_is_jk_money($user_id) : '0';
        returnApiSuccess('查询成功',  $qiandata);

    }

//


    //线下流程支付
    public function ManPostPaymentAudit()
    {
        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
        $order_id = isset($_POST['order_id']) ? trim($_POST['order_id']) : '';//订单id
        $coupon_id = isset($_POST['coupon_id']) ? trim($_POST['coupon_id']) : '0';//代金券id
      //  $f_money= isset($_POST['money']) ? trim($_POST['money']) : '';//金额
        $zftype = isset($_POST['zftype']) ? trim($_POST['zftype']) : '1';//支付方式1支付宝2微信
        if($user_id == ''){ returnApiError('用户id不能为空！');}
    //    if($f_money == ''){ returnApiError('金额不能为空！');}
        if($zftype == ''){ returnApiError('支付方式不能为空！');}


        $order_start=xm_order_start($order_id, $user_id);
        if( $order_start=='-1'){
            returnApiError( '无订单或者不是你的订单');
        }else{
            if($order_start!=0&&$order_start!=4){
                returnApiError( '订单状态不正确');
            }
        }
       //支付
        //查看用户代金券是否合法
        if($coupon_id){
            $wheredj['id']=$coupon_id;
            $wheredj['state']=0;
            $wheredj['user_id']=$user_id;
            $field='id,coupon_money,coupon_num';
            $datadj=M('XmCoupon')->field($field)->where($wheredj)->find();
            if(!$datadj){ returnApiError( '代金券状态不正确');}
            $qiandata=Traits::get_order_jq( $user_id,$order_id,$coupon_id);
        }else{
            $qiandata=Traits::get_order_jq( $user_id,$order_id);
        }
     // $zfje=0.01;

        $zfje= $qiandata['zf_money'];

        //根据支付方式选择支付宝或者微信
        $where['id']=$order_id;
        $where['user_id']=$user_id;
        $order_number=M('XmOrder')->where($where)->getField('order_number');

        $otrade_no= $order_number;
        if($zftype==1){
            //支付宝
            $body="支付";
            if($datadj){
                $subject="线下订单支付,使用代金券编号@".$datadj['coupon_num'];
            }else{
                $subject="线下订单支付";
            }

            $out_trade_no=$otrade_no;
            $order_amount=$zfje;
            $zfsdk['aliyun']= TraitsPay::alipay($body,$subject,$out_trade_no,$order_amount,'10m',2,$coupon_id);
            $zfsdk['weixin']='0';
            returnApiSuccess('支付宝支付sdk', $zfsdk);
        }elseif($zftype==2){
            //微信
        }else{
            returnApiError( '订单生成失败了');
        }

    }

    //线下支付支付后返回改状态
    public function ManYePostPaymentAudit()
    {
       $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
       $order_id = isset($_POST['order_id']) ? trim($_POST['order_id']) : '';//订单id
        $f_money= intval($_POST['money']) > 0 ?intval(trim($_POST['money'])) : '0';//金额
        $coupon_id = isset($_POST['coupon_id']) ? trim($_POST['coupon_id']) : '';//优惠卷id

        $zffs= intval($_POST['zffs']) > 0 ?intval(trim($_POST['zffs'])) : '1';//支付方式1是余额2是微信3是支付宝
//
        if($f_money==0){ returnApiError( '金额必须');}
        if($order_id==''){ returnApiError( '订单id必须');}
        if($zffs!=1) {returnApiError('支付方式不正确');}
        //支付前审核
        //如果取消的就不能选择
        $order_start=xm_order_start($order_id, $user_id);
        if( $order_start=='-1'){
            returnApiError( '无订单或者不是你的订单');
        }else{
            if($order_start!=0){
                returnApiError('订单状态不正确');
            }
        }

        $where_u['id']=  $user_id ;
        $field_u='balance,jk_balance,is_jkuser';
        $user_data = M('XmMember')->field($field_u)->where($where_u)->find();

            //余额支付
            if ($user_data['is_jkuser'] == 1) {
                //是金卡
                if ($user_data['jk_balance'] < $f_money) {
                    returnApiError('金卡余额不足');
                }
            } else {
                if ($user_data['balance'] < $f_money) {
                    returnApiError('余额不足');
                }
            }

        $Oerder = M("XmOrder"); // 实例化User对象
        $where['id']= $order_id;
        $field='money,status,target_area';
        $data = $Oerder->field($field)->where($where)->find();
        if($data['status']>0){
          returnApiError( '订单状态不正常');

        }

        //如果有代金券，支付钱加上代金券
        if($coupon_id){
           $where_s_d['id']=$coupon_id;
            $field_s_d="coupon_money,state";
            $data_s_d=M('XmCoupon')->field($field_s_d)->where($where_s_d)->find();
            if( $data_s_d['state']>0){
                returnApiError( '代金券状态有问题');
            }else{
              if($data['money'] != $f_money+ $data_s_d['coupon_money'] ){  returnApiError( '订单价格不正确');}
            }
        }else{
                  if($data['money'] != $f_money ){  returnApiError( '订单价格不正确');}
        }

        //改变订单状态,
        $model = M();
        $model->startTrans();
        $where_dd['id']= $order_id;
        $data_dd['status'] = '1';
        //余额支付
        $data_dd['payment_method']= '余额';
        $data_dd['user_payment']= $f_money;

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
        //可抢订单
        $data_ro['order_id'] = $order_id;
        $data_ro['create_time'] = get13TimeStamp();
        $data_ro['end_time'] = get13TimeStamp() + $this->man_xx_time;
        $data_ro['status'] =  1;
        $data_ro['area'] = $data['target_area'];
        $data_ro['user_id'] =  $user_id;
        $result3 = $model->table('qw_xm_rob_orde')->add($data_ro); // 实例化User对象
if($result && $result2 && $result3){
    $model->commit();//成功则提交
    $zjdata['is_zf']=1;
    $where_cx['id']=$order_id;
    $order_num =M('XmOrder')->where($where_cx)->getField('order_number');
    $ml='订单'.$order_num.' 支付成功余额支付'. $f_money.' ,剩余余额为'.$user_sx;
    $mlfm='-'.$f_money;
    moneylog( $ml, $user_id,0,$mlfm,$user_sx,'余额',$order_id);
   //可抢订单
//    $data_ro['order_id'] = $order_id;
//    $data_ro['create_time'] = time();
//    $data_ro['end_time'] = time() + $this->man_xx_time;
//    $data_ro['status'] =  1;
//    $data_ro['area'] = $data['target_area'];
//    $data_ro['user_id'] =  $user_id;
//    $Rorde = M("XmRobOrde")->add($data_ro); // 实例化User对象
    //使用的代金券改变状态

//     if($Rorde){
//         $zjdata['is_xsdd']=1;
//     }else{
//         $zjdata['is_xsdd']=0;
//     }
    $zjdata['is_xsdd']=1;
    returnApiSuccess('支付成功',$zjdata);
}else{
    $model->rollback();//不成功，则回滚
    returnApiError( '支付失败');
}
    }




//男性选择模块
    public function ManChoice()
    {
        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
        $order_id = isset($_POST['order_id']) ? trim($_POST['order_id']) : '';//订单id

        $order_start=xm_order_start($order_id, $user_id);
        if( $order_start=='-1'){
            returnApiError( '无订单或者不是你的订单');
        }else{
            if($order_start==0){
                returnApiError('订单状态不正确');
            }
        }


        $Muser=M("XmMember");
        $Mcdata=M('XmGrabSingle');
        $where['order_id']=  $order_id;
        $where['start'] = 1;
        $field='order_id,user_id';
        $data=$Mcdata->field( $field)->where($where)->select();
//        echo $Mcdata->getLastSql();
//     print_r( $data);
if($data){
    foreach( $data as $value){
       $where_u['id']=$value['user_id'];
       $field_u='o_username,height,weight,sex,video,Head,moblie';
        $value['userdata']= $Muser->field($field_u)->where($where_u)->find();
         $userdatas[]=$value;
    }
    returnApiSuccess('查询成功',$userdatas);
}else{
    $userdatas[]='';
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
            //绑定电话
            $where_N['id']= $user_id;
            $NoA=M('XmMember')->where($where_N)->getField('moblie');
            $orderid = $order_id;
            $where_O_id['id']= $order_id;
            $sj=M('XmOrder')->where($where_O_id)->getField('jisu_time');
            $Stime=date("Y-m-d H:i:s",$sj/1000);
            foreach($fenxzid as $value){
                $where_N_B['id']= $value;
                $NoB=M('XmMember')->where($where_N_B)->getField('moblie');
                XmComPrivacyMobileController::bdshouji($NoA, $NoB, $Stime, $orderid);
            }
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

    //续约

   //取消订单
    public function ManCancelOrder()
    {
        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
        $order_id = isset($_POST['order_id']) ? trim($_POST['order_id']) : '';//订单id
        if( $order_id==''){ returnApiError( '订单id必须');}
        //订单状态
        $order_start=xm_order_start($order_id, $user_id);
        if( $order_start=='-1'){returnApiError( '无订单或者不是你的订单');}
        if($order_start>2){returnApiError( '订单状态不正确');}
        //订单时间 如果时间超过约会开始时间 就不能取消
        $where['id']=$order_id;
        $where['user_id']=$user_id;
        //修改过的时间
        $isadvance=M('XmOrder')->where($where)->getField('is_advance_notice');
        if($isadvance){ $datatime=M('XmOrder')->where($where)->getField('modify_appointment_time');}else{
            $datatime=M('XmOrder')->where($where)->getField('appointment_time');
        }
        if($datatime<get13TimeStamp()){returnApiError( '订单已接近开始或者已开始，不能取消订单');}
        //退款
        $wheremember['id']=$user_id;
        $isQxOrder=M('XmMember')->where($wheremember)->getField('is_qx_order');
        if($isQxOrder){
            //后面取消扣10% //改变订单状态
           $tueiqian= Traits::put_order_tuei($user_id,$order_id,0.9);
        }else{
            //男生第1次取消扣5% //改变订单状态
            $tueiqian= Traits::put_order_tuei($user_id,$order_id,0.95);
            $datam['is_qx_order']=1;
            xm_put_user($user_id,$datam);
        }
        if($tueiqian){
            //将剩余钱给女性
            $moneygeiwomen=$tueiqian['kouqian'];
            Traits::put_order_womendeqiam($order_id,$moneygeiwomen);
            returnApiSuccess('取消订单成功',1);
        }else{
            returnApiError( '取消订单失败');
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
        $data_o['time_completion'] = get13TimeStamp();
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

            //解除电话订单绑定
            XmcomprivacymobileController::jcshouji($order_id);

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

    //生成订单
    public function ManOnlineOrder()
    {
        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
        $classify= 1;//分类id
        $target_area = isset($_POST['target_area']) ? trim($_POST['target_area']) : '';//地区
        $is_sex= intval($_POST['is_sex']) > 0 ?'1' : '0';//要求性别
        $is_anonymous= intval($_POST['is_anonymous']) > 0 ?intval(trim($_POST['is_anonymous'])) : '0';//是否匿名
        $money= intval($_POST['money']) > 0 ?intval(trim($_POST['money'])) : '10';//金额

        //过滤
        if($user_id==""){ returnApiError( '用户id必须');}
        if($target_area==""){ returnApiError( '地区必须');}
        //新用户送3分钟
        $userdata=  xm_user( $user_id,'is_news,is_jkuser');
        if($userdata['is_news']==0){
            $usermoney =  xm_is_jk_money($user_id);//用户可用钱
            if($usermoney<10){  returnApiError('余额不足请充值');}
        }

        //逻辑
        $data['order_number']= build_order_no(); //订单编号
        $data['user_id']= $user_id;//发起者用户id
        $data['classify']= $classify;//分类id
        $data['order_name']= xm_fl_name($classify);//分类名字
        $data['target_area']= $target_area;//分类名字
        $data['is_sex']=  $is_sex;//要求性别
        $data['is_anonymous']=  $is_anonymous;//是否匿名
        $data['money']=  $money;//金额
        $data['payment_method']=  '余额';//金额
        $data['user_payment']=  $money;//支付金额

        $data['status']=  1;

        $data['time_add']= get13TimeStamp();
        $data['appointment_time']= get13TimeStamp();
        $data['jisu_time']= get13TimeStamp()+(10*60*1000);

        $Model = M();
        $Model->startTrans();
        $result1 =$Model->table('qw_xm_order')->add($data);
        if($userdata['is_news']==0){
            //用户扣10钱
            $field="is_jkuser,jk_balance,balance";
            $xuj= xm_user($user_id, $field);
            $wherexuj['id']=$user_id;
            if( $xuj['is_jkuser']){
                //金卡用户
                $qian=$xuj['jk_balance']-$money;
                $datasj['jk_balance']= $qian;
                $result2=$Model->table('qw_xm_member')->where($wherexuj)->save($datasj);
            }else{
                $qian=$xuj['balance']-$money;
                $datasj['balance']=$qian;
                $result2=$Model->table('qw_xm_member')->where($wherexuj)->save($datasj);
            }
            if($result2){
                $manm='发起视频通话花费'.$money;
                moneylog( $manm, $user_id,0,-$money,$qian,'余额',$result1);
            }
        }else{
            $result2=1;
        }

        if ($result1 && $result2) {
            $Model->commit(); // 成功则提交事务
            returnApiSuccess('添加成功',$result1);
        } else {
            $Model->rollback(); // 否则将事务回滚
            returnApiError( '添加失败');
        }
}

//个人充值页面余额
    public function ManOnlineye()
    {
        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
        if($user_id == ''){ returnApiError('用户id不能为空！');}
        $qiandata['balance'] = xm_is_jk_money($user_id) ? xm_is_jk_money($user_id) : '0';
        returnApiSuccess('查询成功',  $qiandata);

    }
    
//线上充值
    public function ManOnlineRecharge()
    {
        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
        $zfje= isset($_POST['money']) ? trim($_POST['money']) : '';//金额
        $zftype = isset($_POST['zftype']) ? trim($_POST['zftype']) : '1';//支付方式1支付宝2微信

        if($user_id == ''){ returnApiError('用户id不能为空！');}
        if($zfje == ''){ returnApiError('金额不能为空！');}
        if($zftype == ''){ returnApiError('支付方式不能为空！');}

        //看自己余额里有多少

            //不够看差多少
            //  $zfje=ceil($this->up_money-$userdata['balance']);
            $zfje=0.02;
            //根据支付方式选择支付宝或者微信
            $otrade_no=build_order_no();
            if($zftype==1){
                //支付宝
                //生成支付订单
                $data['user_id']= $user_id;
                $data['cz_number']=$otrade_no;
                $data['money']=$zfje;

                $data['remarks']='充值金额';
                $data['status']='0';
                $data['type']='1';
                $data['add_time']=get13TimeStamp();
                $datas=M('XmCzOrder')->add($data);
                if(!$datas){ returnApiError( '订单生成失败');}
                //   支付
                $body="充值";
                $subject="充值";
                $out_trade_no=$data['cz_number'];
                //  $order_amount=0.01;
                $order_amount=$zfje;
                $zfsdk['aliyun']= TraitsPay::alipay($body,$subject,$out_trade_no,$order_amount,$timeout_express='1d',3);
                $zfsdk['weixin']='0';
                returnApiSuccess('支付宝充值sdk', $zfsdk);
            }elseif($zftype==2){
                //微信
            }else{
                returnApiError( '订单生成失败了');
            }
    }


    /**
     * 每次访问，检查用户钱，更改订单时间
     */
    public function ManOnlinePuttime(){
        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
        $order_id = isset($_POST['order_id']) ? trim($_POST['order_id']) : '';//订单id

        if ($order_id == '') {returnApiError('订单id必须');}
        if ($user_id == '') {returnApiError('用户必须');}

        //订单状态
        $order_start = xm_order_start($order_id, $user_id);
        if ($order_start == '-1') {
            returnApiError('无订单或者不是你的订单');
        }
        //改变订单时间
        if(xm_order_time($order_id)){
            $usermoney =  xm_is_jk_money($user_id);//用户可用钱
            if($usermoney==10){returnApiSuccess('余额不足请充值', 1);}
            if($usermoney<9){
                returnApiError('余额不足请充值');
            }else{
                returnApiSuccess('查询成功', 1);
            }

        }else{
            returnApiError('网络异常');
        };

    }


    /**
     * 根据订单时间扣费，改变订单状态，给女方钱
     */
//正常线上扣费(先服务在扣费)
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
        $hfmoney=$yumoney - $kouqian;
if($userdata['is_jkuser']){
    $data_m_k['jk_balance']= $hfmoney;
    $result1 =$Model->table('qw_xm_member')->where($where_m_k)->save($data_m_k);
}else{
    $data_m_k['balance']= $hfmoney;
    $result1 =$Model->table('qw_xm_member')->where($where_m_k)->save($data_m_k);
}
  // 订单女性id
        $where_w_o['id']= $order_id;
        $women_id =M('XmOrder')->where($where_w_o)->getField('xz_user_id');
        $where_w_d['id']= $women_id;
        $women_money =M('XmMember')->where($where_w_d)->getField('balance');
        $demoney=$women_money + $kouqian;
        $women_w_d['balance']= $demoney;
        $result2 =$Model->table('qw_xm_member')->where($where_w_d)->save($women_w_d);

    //order
        $where_o_d['id']=$order_id;
        $data_o['status']='6';
        $data_o['guanduan_id']=$guanduan_id;
        $data_o['time_completion']=$time_completion;
        $result3 =$Model->table('qw_xm_order')->where($where_o_d)->save($data_o);


        if ($result1 && $result2 && $result3) {
            $Model->commit(); // 成功则提交事务
            $where_jl['id']=$order_id;
            $order_num =M('XmOrder')->where($where_jl)->getField('order_number');

           //男生的
             $manm='视频通话'.$order_num."花费".$kouqian.'余额剩余'.$hfmoney;
            moneylog( $manm, $user_id,0,-$kouqian,$hfmoney,'余额',$order_id);
            //女生的
            $womenm='视频通话'.$order_num."得到".$kouqian.'余额剩余'.$demoney;
            moneylog( $manm, $women_id,0,$kouqian,$demoney,'余额',$order_id);

            returnApiSuccess('订单完成',1);

        } else {

            $Model->rollback(); // 否则将事务回滚
            returnApiError( '扣费失败');
        }
    }


//。。。。。。。-----------------------------------------------------------------男性线上模块结束-------------------------------------------

    //-----------------------------------------------------------------男性模块结束-------------------------------------------

}