<?php

namespace Home\Controller;
use Think\Upload;
use Vendor\Page;
require(C('Library')."/Firebase/JWT/JWT.php");
use Firebase\JWT\JWT;

use XmClass\RndChinaName;
use XmClass\Easemob;

class ApiController extends ComController
{
    private $token_xm; //校验参数
    private $key_xm; //jwt参数
    private $man_xx_time; //线下发单限制时间
    private $options;//环信配置

//       private $options['client_id']='YXA6j5xyMORUEee2UYE4debtsQ';
//       private $options['client_secret']='YXA6C0vhpohfnQMBfQAzfK-XNiWu_Lc';
//       private $options['org_name']='1134171215115606';
//       private $options['app_name']='appointment';

    public function _initialize()
    {
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Headers:Authorization');
     //   header("Access-Control-Allow-Methods: GET, POST, DELETE");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Headers: Content-Type, X-Requested-With, Cache-Control,Authorization");
        $this->token_xm='kly2018';
        $this->key_xm='klyjwt';
        $this->man_xx_time='600';//10分钟

        $this->options['client_id']='YXA6j5xyMORUEee2UYE4debtsQ';
        $this->options['client_secret']='YXA6C0vhpohfnQMBfQAzfK-XNiWu_Lc';
        $this->options['org_name']='1134171215115606';
        $this->options['app_name']='appointment';
        //验证
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


    public function index()
    {



        $h=new Easemob( $this->options);
       // print_r($h->createUser("xm4","123456"));exit;
//        var_dump($h->createUser("xm","123456"));
   //     $this->display();
        $from='xm';
        $target_type="users";
        //$target_type="chatgroups";
        $target=array("18672995588","lisi","wangwu");
        //$target=array("122633509780062768");
        $aaa='{
        "flag": "Success",
        "msg": "登入成功",
        "data": {
            "id": "6",
            "sex": "1",
            "moblie": "15994221308",
            "is_fwz": "0",
            "is_nm": "0",
            "nm": "",
            "o_username": "帅哥",
            "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6IjYiLCJzZXgiOiIxIiwibW9ibGllIjoiMTU5OTQyMjEzMDgiLCJpc19md3oiOiIwIiwiaXNfbm0iOiIwIiwibm0iOiIiLCJvX3VzZXJuYW1lIjoiXHU1ZTA1XHU1NGU1In0.eXuyWDJu2VRumIZlOsd_zA1-5qETI3ZbtOLwrgkuZxA"
        }
    }';
        $content="$aaa";
        $ext['a']="a";
        $ext['b']="b";
        var_dump($h->sendText($from,$target_type,$target,$content,$ext));
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


//-------------------------------------------------------------注册部分开始--------------------------------------------
    //注册
    public function register()
    {

        $moblie = isset($_POST['moblie']) ? trim($_POST['moblie']): '';//手机号
        $password = isset($_POST['password']) ? password(trim($_POST['password'])) : '';


        //验证
        if ($moblie == '') { returnApiError( '手机号必须！');}
        if ($password == '') {returnApiError( '密码必须！');}

        $model = M("XmMember");
        $user = $model->field('id')->where(array('moblie' => $moblie))->find();
        if ($user) {
            returnApiError( '手机号已注册');
        }else{
            //可以注册逻辑
            $data['moblie']= $moblie;
            $data['password']= $password;
            $data['code']= make_coupon_card();//邀请码
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
                returnApiSuccess('添加成功',$tjcg);
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
        $birth= intval($_POST['birth']) > 0 ?intval(trim($_POST['birth'])) : '0';//出生时间搓559238400
        $user_id = intval($_POST['user_id']) > 0 ?intval($_POST['user_id'])  : '';//用户id

        //验证
        if ($username == '') { returnApiError( '姓名必须！');}
        if ($birth == 0) {returnApiError( '出生时间必须！');}
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

        $moblie = isset($_POST['moblie']) ? trim($_POST['moblie']) : '';
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
            exit(0);
        } else {
            returnApiError('手机号或密码有错误');
        }
}


    //首页
    public function syindex()
    {
//        //验证必须登入
//        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
//        $str = isset($_POST['token']) ? trim($_POST['token']) : '';
//        $key =  $this->key_xm;
//        if ($user_id == '') { returnApiError( '用户id必须');}
//        if ($str == '') { returnApiError( 'token必须');}
//        if ($key == '') { returnApiError( 'key必须');}
//        $this->is_jwt($str,$key,1,$user_id);

        $table="Flash";
        $field='id,pic,tp_sp';
        $where['type']=3;
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

//  //视频验证
//    public function PVideoValidation()
//    {
//        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
//        $str = isset($_POST['token']) ? trim($_POST['token']) : '';
//        $key =  $this->key_xm;
//        if ($user_id == '') { returnApiError( '用户id必须');}
//        if ($str == '') { returnApiError( 'token必须');}
//        if ($key == '') { returnApiError( 'key必须');}
//        $this->is_jwt($str,$key,1,$user_id);
//    }
//
//
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
//        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
//        $str = isset($_POST['token']) ? trim($_POST['token']) : '';
//        $key =  $this->key_xm;
//        if ($user_id == '') { returnApiError( '用户id必须');}
//        if ($str == '') { returnApiError( 'token必须');}
//        if ($key == '') { returnApiError( 'key必须');}
//        $this->is_jwt($str,$key,1,$user_id);

        $table="XmTrystClassify";
        $field='*';
        $data=xm_gf($table,$field);
//数据添加图片宽高

        foreach( $data as $key=>$value){
           if($value['id']==1){
               $value[w] = 320;
               $value[h] = 360;
           }else{
               $value[w] = 320;
               $value[h] = 200;
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
 //支付前审核
    public function ManPaymentExamine()
    {
        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
        $order_id = isset($_POST['order_id']) ? trim($_POST['order_id']) : '';//订单id
        $f_money= intval($_POST['money']) > 0 ?intval(trim($_POST['money'])) : '0';//金额
        $zffs= intval($_POST['zffs']) > 0 ?intval(trim($_POST['zffs'])) : '1';//支付方式1是余额2是微信3是支付宝
        if($f_money==0){ returnApiError( '金额必须');}
        $Oerder = M("XmOrder"); // 实例化User对象
        $where['order_id']= $order_id;
        $field='money,status';
        $data = $Oerder->field($field)->where($where)->find();

if($data['status']=='0'){
//未支付
    $where_u['id']=  $user_id ;
    $field_u='balance,jk_balance,is_jkuser';
    $user_data = M('XmMember')->field($field_u)->where($where_u)->find();
    if($data['money'] != $f_money ){  returnApiError( '订单价格不正确');}
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
//        if($data['money'] != $f_money ){  returnApiError( '订单价格不正确');}
        //改变订单状态,
        $model = M();
//        $m=D('XmOrder');
//        $m2=D('XmMember');
        $model->startTrans();
        $where_dd['id']= $order_id;
        $data_dd['status'] = '1';
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
        if( $order_id==''){ returnApiError( '订单id必须');}
        //订单状态
        $order_start=xm_order_start($order_id, $user_id);
        if( $order_start=='-1'){returnApiError( '无订单或者不是你的订单');}
        if( $order_start>5){returnApiError( '订单状态不正确');}


        $where_o_c['id']= $order_id;
        $field_o_c='money,xz_pe_num,xz_user_id';
        $cx_data=M('XmOrder')->field($field_o_c)->where($where_o_c)->find();
        //查询用户人数
        $cx_r_s=$cx_data['xz_pe_num'];
        //查询订单钱
        $cx_r_money=$cx_data['money'];
        //每个用户可以分到的钱
        $money_f=floor($cx_r_money/$cx_r_s);


        //查询用户
        //查询用户等级
        //查询用户比例
        $cx_user=array_filter(explode(',',$cx_data['xz_user_id']));
        foreach( $cx_user as $value ){
            $userdata=xm_user($value,'women_grade,id,balance');
            $userdata['bfb'] =  xm_women_draw($userdata['women_grade']);
            $datas[]=$userdata;
        }

        $Model = M();
        $Model->startTrans();
      //改变订单状态
        $where_o['id']= $order_id;
        $data_o['status']=6;
        $data_o['time_completion']=time();
      $result=M('XmOrder')->where($where_o)->save($data_o);

        //分钱

       foreach( $datas as $key=>$value){
              $where_f_u['id']= $value['id'];
              $data_f_u['balance']=$value['balance']+$money_f;
           //   $mz='result'.$key;
              $result1 =  $Model->table('qw_xm_member')->where($where_f_u)->save($data_f_u);
            if($result1){
                $results = true;
            }else{
                $results = false;
            }
       }

      if($result && $results){

          $Model->commit(); // 成功则提交事务
          //日志
          foreach( $datas as $key=>$value){

              $tcmoney=$value['balance']+$money_f;

              $where_cx['id']=$order_id;
              $order_num =M('XmOrder')->where($where_cx)->getField('order_number');


                  $ml='订单'.$order_num.'获得提成'. $money_f.' ,账号可用余额为'. $tcmoney;

                  moneylog( $ml, $value['id'],6,$money_f,$tcmoney,'余额',$order_id);

          }

          returnApiSuccess('订单已完成',1);

      }else{
          $Model->rollback(); // 否则将事务回滚
          returnApiError( '改变订单完成失败');
      }
    }



//。。。。。。。-----------------------------------------------------------------男性线下模块结束-------------------------------------------
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
    //。。。。。。。-----------------------------------------------------------------女性线下模块开始-------------------------------------------
    


    //。。。。。。。-----------------------------------------------------------------女性线下模块结束-------------------------------------------
    //-----------------------------------------------------------------女性模块结束-------------------------------------------



//jwt测试
//    public function one(){
//
//        $key =  $this->key_xm;
//        $token = array(
//            'uid' => 1050,
//            'username' => 'baby',
//        );
//
//        $jwt = JWT::encode($token, $key);
//        echo $jwt;
//    }
//    public function two(){
//        $key = "xmkly";
//        $key =  $this->key_xm;
//        $str = isset($_POST['str']) ? $_POST['str'] : '';
//        if($str == ''){
//            exit('empty');
//        }
//        $decoded = JWT::decode( $str, $key, array('HS256'));
//        if(!is_object($decoded)){
//            echo "error";
//        }else{
//            $arr = json_decode(json_encode($decoded), true);
//         //   dump($arr);
//            $uid = $arr['uid']; //既然能拿到uid，那么就说明是有权限的用户，并且他的uid是1050。剩下的，只要有uid，该干什么就干什么好了。
//            echo  $uid;
//        }
//    }


//新闻媒体行业列表页
    function xwmtlb(){
        verifys($_POST['verify']);
        $p = intval($_POST['p']) > 0 ?$_POST['p'] : 1;

        $pid=22;
        $tjmsdata = articledata('aid,title,description,thumbnail,t',$pid,page($p));
        $zs= zongshu($pid);
if($tjmsdata){
    $data['nr']= $tjmsdata;
    $data['zs']= $zs;
    returnApiSuccess('1',  $data);
}else{
    returnApiError( '无数据');
}


    }
//新闻媒体公司列表页
    function xwmtgslb(){
        verifys($_POST['verify']);
        $p = intval($_POST['p']) > 0 ?$_POST['p'] : 1;

        $pid=23;
        $tjmsdata = articledata('aid,title,description,thumbnail,t',$pid,page($p));
        $zs= zongshu($pid);
        if($tjmsdata){
            $data['nr']= $tjmsdata;
            $data['zs']= $zs;
            returnApiSuccess('1',  $data);
        }else{
            returnApiError( '无数据');
        }


    }
//房车销售列表页
    function fcxslb(){
        verifys($_POST['verify']);
        $p = intval($_POST['p']) > 0 ?$_POST['p'] : 1;

        $pp= isset($_POST['pp']) ? trim($_POST['pp']) : '';//品牌
        $maxpr= isset($_POST['maxpr']) ? intval(trim($_POST['maxpr'])) : '200';//最大价格
        $mixpr= isset($_POST['mixpr']) ? intval(trim($_POST['mixpr'])) : '10';//最小价格
        $leibei=intval($_POST['leibei']) > 0 ?$_POST['leibei'] : 0;


        $pid=34;
        $fsl=12;//每页显示数量
        if(!empty($pp)){
            $where['pingpai']=$pp;
        }
        if(!empty($maxpr)&&!empty($mixpr)){
            $where['jiage']=array('between',array($mixpr,$maxpr));
        }
        if(!empty( $leibei)){
            $where['leixing']= $leibei;
        }
        $tjmsdata = articlewhere('aid,title,description,thumbnail,t,jiage,danwei',$pid,page($p,$fsl),$where);
        //  echo M("article")->getLastSql();exit;
        $zs= zongshu($pid,$fsl);
        if($tjmsdata){
            $data['nr']= $tjmsdata;
            $data['zs']= $zs;
            returnApiSuccess('1',  $data);
        }else{
            returnApiError( '无数据');
        }


    }


//二手房车列表页
    function esfclb(){
        verifys($_POST['verify']);
        $p = intval($_POST['p']) > 0 ?$_POST['p'] : 1;

        $pp= isset($_POST['pp']) ? trim($_POST['pp']) : '';//品牌
        $maxpr= isset($_POST['maxpr']) ? intval(trim($_POST['maxpr'])) : '200';//最大价格
        $mixpr= isset($_POST['mixpr']) ? intval(trim($_POST['mixpr'])) : '10';//最小价格
        $leibei=intval($_POST['leibei']) > 0 ?$_POST['leibei'] : 0;


        $pid=16;
        $fsl=12;//每页显示数量
        if(!empty($pp)){
            $where['pingpai']=$pp;
        }
        if(!empty($maxpr)&&!empty($mixpr)){
            $where['jiage']=array('between',array($mixpr,$maxpr));
        }
        if(!empty( $leibei)){
            $where['leixing']= $leibei;
        }
        $tjmsdata = articlewhere('aid,title,description,thumbnail,t,jiage,danwei',$pid,page($p,$fsl),$where);
        //  echo M("article")->getLastSql();exit;
        $zs= zongshu($pid,$fsl);
        if($tjmsdata){
            $data['nr']= $tjmsdata;
            $data['zs']= $zs;
            returnApiSuccess('1',  $data);
        }else{
            returnApiError( '无数据');
        }


    }





//房车租凭列表页
    function fczplb(){
        verifys($_POST['verify']);
        $p = intval($_POST['p']) > 0 ?$_POST['p'] : 1;

        $pp= isset($_POST['pp']) ? trim($_POST['pp']) : '';//品牌
        $maxpr= isset($_POST['maxpr']) ? intval(trim($_POST['maxpr'])) : '200';//最大价格
        $mixpr= isset($_POST['mixpr']) ? intval(trim($_POST['mixpr'])) : '10';//最小价格
        $leibei=intval($_POST['leibei']) > 0 ?$_POST['leibei'] : 0;


        $pid=7;
        $fsl=12;//每页显示数量
        if(!empty($pp)){
            $where['pingpai']=$pp;
        }
        if(!empty($maxpr)&&!empty($mixpr)){
            $where['jiage']=array('between',array($mixpr,$maxpr));
        }
        if(!empty( $leibei)){
            $where['leixing']= $leibei;
        }
        $tjmsdata = articlewhere('aid,title,description,thumbnail,t,jiage,danwei',$pid,page($p,$fsl),$where);
        //  echo M("article")->getLastSql();exit;
        $zs= zongshu($pid,$fsl);
        if($tjmsdata){
            $data['nr']= $tjmsdata;
            $data['zs']= $zs;
            returnApiSuccess('1',  $data);
        }else{
            returnApiError( '无数据');
        }


    }

//户外装备列表页
    function hwzblb(){
        verifys($_POST['verify']);
        $p = intval($_POST['p']) > 0 ?$_POST['p'] : 1;

        $pp= isset($_POST['pp']) ? trim($_POST['pp']) : '';//品牌
        $maxpr= isset($_POST['maxpr']) ? intval(trim($_POST['maxpr'])) : '200';//最大价格
        $mixpr= isset($_POST['mixpr']) ? intval(trim($_POST['mixpr'])) : '10';//最小价格
        $leibei=intval($_POST['leibei']) > 0 ?$_POST['leibei'] : 0;


        $pid=5;
        $fsl=12;//每页显示数量
        if(!empty($pp)){
            $where['pingpai']=$pp;
        }
        if(!empty($maxpr)&&!empty($mixpr)){
            $where['jiage']=array('between',array($mixpr,$maxpr));
        }
        if(!empty( $leibei)){
            $where['leixing']= $leibei;
        }
        $tjmsdata = articlewhere('aid,title,description,thumbnail,t,jiage,danwei',$pid,page($p,$fsl),$where);
        //  echo M("article")->getLastSql();exit;
        $zs= zongshu($pid,$fsl);
        if($tjmsdata){
            $data['nr']= $tjmsdata;
            $data['zs']= $zs;
            returnApiSuccess('1',  $data);
        }else{
            returnApiError( '无数据');
        }


    }

//营房设计选项
    function  yfsjxx(){
        verifys($_POST['verify']);
        $v='ydsjcs';
        $cs=zdybl($v);
        $cs=explode("、",trim($cs,'、'));
        $data['cs']=$cs;
        $v='jdsjjg';
        $jg=zdybl($v);
        $jg=explode("、",trim($jg,'、'));
        $data['jg']=$jg;
        returnApiSuccess('1',  $data);
    }


    //营房设计列表页
    function yfsjlb(){
        verifys($_POST['verify']);
        $p = intval($_POST['p']) > 0 ?$_POST['p'] : 1;

        $pp= isset($_POST['pp']) ? trim($_POST['pp']) : '';//城市
        $maxpr= isset($_POST['maxpr']) ? intval(trim($_POST['maxpr'])) : '200';//最大价格
        $mixpr= isset($_POST['mixpr']) ? intval(trim($_POST['mixpr'])) : '10';//最小价格
        $leibei=intval($_POST['leibei']) > 0 ?$_POST['leibei'] : 0;


        $pid=35;
        $fsl=12;//每页显示数量
        if(!empty($pp)){
            $where['city']=$pp;
        }
        if(!empty($maxpr)&&!empty($mixpr)){
            $where['jiage']=array('between',array($mixpr,$maxpr));
        }
        if(!empty( $leibei)){
            $where['type']= $leibei;
        }
        $tjmsdata = articlewhere('aid,title,description,thumbnail,t,jiage,danwei',$pid,page($p,$fsl),$where);
        //  echo M("article")->getLastSql();exit;
        $zs= zongshu($pid,$fsl);
        if($tjmsdata){
            $data['nr']= $tjmsdata;
            $data['zs']= $zs;
            returnApiSuccess('1',  $data);
        }else{
            returnApiError( '无数据');
        }


    }

    //常见问题
    function cjwtlb(){
        verifys($_POST['verify']);
        $p = intval($_POST['p']) > 0 ?$_POST['p'] : 1;

        $where['sid']=array('in',array('29','30','31'));
        $field='aid,title,t,sid';
        $order="t desc";

         $tjmsdata = M('article')->field($field)->where($where)->limit(page($p))->order($order)->select();

        //  echo M("article")->getLastSql();exit;
        $zs= M('article')->field($field)->where($where)->count();
        $zs=ceil($zs/10);
        if($tjmsdata){
            foreach( $tjmsdata as $v){
                $wherec['id']=$v['sid'];
                $lmz =M('category')->field('name')->where($wherec)->find();

                $v['leimz']=$lmz['name'];
$datas[]=$v;
            };

            $data['nr']= $datas;
            $data['zs']= $zs;
            returnApiSuccess('1',  $data);
        }else{
            returnApiError( '无数据');
        }
    }


//首页
    function home_page(){
        verifys($_POST['verify']);

//        //公告图
//        $tjmsdata = M('flash')->field("id,title,url,pic")->limit(4)->select();
//        $data['ad0']=$tjmsdata;

//关于我们
$data['xywm1']=categorydye('content','26');


//房车销售
        $tjmsdata = articlewhere('aid,title,description,thumbnail,t,jiage,danwei',34,6);
        $data['fcxs2']=$tjmsdata;
//二手车房
        $tjmsdata = articlewhere('aid,title,description,thumbnail,t,jiage,danwei',16,6);
        $data['escf2']=$tjmsdata;
//房车租凭
        $tjmsdata = articlewhere('aid,title,description,thumbnail,t,jiage,danwei',7,6);
        $data['fwzp2']=$tjmsdata;
 //户外装备
        $tjmsdata = articlewhere('aid,title,description,thumbnail,t,jiage,danwei',5,6);
        $data['hwzb2']=$tjmsdata;
//营地设计
        $tjmsdata = articlewhere('aid,title,description,thumbnail,t,jiage,danwei',35,6);
        $data['ydsj2']=$tjmsdata;

//专家顾问
        $tjmsdata = articlewhere('aid,title,description,thumbnail,t',27,2);
        $data['zjgw3']=$tjmsdata;

        //常见问题
        //房车销售
        $tjmsdata = articlewhere('aid,title,description,thumbnail,t',29,6);
        $data['fcxs3']=$tjmsdata;
        //房车改装售后
        $tjmsdata = articlewhere('aid,title,description,thumbnail,t',30,6);
        $data['fcgzsh3']=$tjmsdata;
        //营房设计
        $tjmsdata = articlewhere('aid,title,description,thumbnail,t',31,6);
        $data['yfsj3']=$tjmsdata;

       //活动体验
        //用自驾心得
        $tjmsdata = articlewhere('aid,title,description,thumbnail,t',20,1);
        $data['hdty4']=$tjmsdata;
        //视频中心
        $tjmsdata = articlewhere('aid,title,description,thumbnail,t',37,8);
        $data['spzx4']=$tjmsdata;
        //新闻媒体
        //用行业动态
        $tjmsdata = articlewhere('aid,title,description,thumbnail,t',22,8);
        $data['hydt4']=$tjmsdata;

        //活动公告
        $data['hdgg5']=articlewhere('aid,title,description,thumbnail,t',18,1);
        //历史活动
        $tjmsdata = articlewhere('aid,title,description,thumbnail,t',19,6);
        $data['lshd5']=$tjmsdata;

        //会员风采
        $tjmsdata = articlewhere('aid,title,thumbnail,t',32,8);
        $data['hyfc6']=$tjmsdata;
        //合作品牌
        $tjmsdata = articlewhere('aid,title,thumbnail,t',11,8);
        $data['hzpp6']=$tjmsdata;
        //友情链接
        $tjmsdata = M('links')->limit(8)->select();
        $data['yqlj6']=$tjmsdata;
        returnApiSuccess('1',  $data);
}




    //空操作
    public function _empty($name){
        returnApiError( '无方法');
    }



    /*
    //一些前台DEMO
    //单页
    public function single($aid){

        $aid = intval($aid);
        $article = M('article')->where('aid='.$aid)->find();
        $this->assign('article',$article);
        $this->assign('nav',$aid);
        $this -> display();
    }
    //文章
    public function article($aid){

        $aid = intval($aid);
        $article = M('article')->where('aid='.$aid)->find();
        $sort = M('asort')->field('name,id')->where("id='{$article['sid']}'")->find();
        $this->assign('article',$article);
        $this->assign('sort',$sort);
        $this -> display();
    }

    //列表
    public function articlelist($sid='',$p=1){
        $sid = intval($sid);
        $p = intval($p)>=1?$p:1;
        $sort = M('asort')->field('name,id')->where("id='$sid'")->find();
        if(!$sort) {
            $this -> error('参数错误！');
        }
        $sorts = M('asort')->field('id')->where("id='$sid' or pid='$sid'")->select();
        $sids = array();
        foreach($sorts as $k=>$v){
            $sids[] = $v['id'];
        }
        $sids = implode(',',$sids);

        $m = M('article');
        $pagesize = 2;#每页数量
        $offset = $pagesize*($p-1);//计算记录偏移量
        $count = $m->where("sid in($sids)")->count();
        $list  = $m->field('aid,title,description,thumbnail,t')->where("sid in($sids)")->order("aid desc")->limit($offset.','.$pagesize)->select();
        //echo $m->getlastsql();
        $params = array(
            'total_rows'=>$count, #(必须)
            'method'    =>'html', #(必须)
            'parameter' =>"/list-{$sid}-?.html",  #(必须)
            'now_page'  =>$p,  #(必须)
            'list_rows' =>$pagesize, #(可选) 默认为15
        );
        $page = new Page($params);
        $this->assign('list',$list);
        $this->assign('page',$page->show(1));
        $this->assign('sort',$sort);
        $this->assign('p',$p);
        $this->assign('n',$count);

        $this -> display();
    }
    */
    //联系我们-添加留言
    /**
     * verify varchar notnull  非法验证
     * name varchar notnull  姓名
     * phone int notnull  联系电话
     * email varchar notnull  邮箱
     * content varchar notnull  内容
     */
    public function AddContact()
    {
        verifys($_POST['verify']);
        $user['name'] = isset($_POST['name']) ? trim($_POST['name']) : '';//姓名
        $user['phone'] = isset($_POST['phone']) ? trim($_POST['phone']) : '';//联系电话
        $user['email'] = isset($_POST['email']) ? trim($_POST['email']) : '';//邮箱
        $user['content'] = isset($_POST['content']) ? trim($_POST['content']) : '';//内容
        $user['t'] = time();//添加时间

        if ($user['name'] == '') {
            returnApiError( '姓名不能为空！');
        }
        if ($user['phone'] == '') {
            returnApiError( '联系电话不能为空！');
        }
        if ($user['email'] == '') {
            returnApiError( '邮箱不能为空！');
        }
        if ($user['content'] == '') {
            returnApiError( '内容不能为空！');
        }

        if (M('contact')->data($user)->add()) {
            returnApiSuccess('1','添加联系成功');
        }else{
            returnApiError('无数据');
        }

    }

//联系我们--获取联系方式
    public function GetContact()
    {
        verifys($_POST['verify']);
        $vars = M('setting')->where("k in ('wxname','wxmark','address','phone','email')")->select();
        $data=array();
        $flash=M("flash")->field('pic')->where('sid=12')->find();
        $data['code']=$flash['pic'];
        if(is_array($vars) && count($vars)>0) {
            foreach($vars as $key=>$value){
                if($value['k']=='wxname'){
                    $data['wxname']=$value['v'];
                }
                if($value['k']=='wxmark'){
                    $data['wxmark']=$value['v'];
                }
                if($value['k']=='address'){
                    $data['address']=$value['v'];
                }
                if($value['k']=='phone'){
                    $data['phone']=$value['v'];
                }
                if($value['k']=='email'){
                    $data['email']=$value['v'];
                }
            }
            returnApiSuccess('1', $data);
        }else{
            returnApiError('无数据');
        }

    }


    /**
     * 发展历程
     * verify varchar notnull  非法验证
     * p    int  post 页数
     * size  int post 每页数量
     */
    public function GetCourse()
    {
        verifys($_POST['verify']);
        $coures = M('article')->field("title,danwei")->where("sid=12")->limit(page($_POST['p'],$_POST['size']))->select();
        $data=array();
        if(is_array($coures) && count($coures)>0){
            $data['coures']=$coures;
            $data['count']=zongshu(12,$_POST['size']);
            returnApiSuccess('1',$data);
        }else{
            returnApiError('无数据');
        }
    }

    /**
     * 公司简介
     * verify varchar notnull  非法验证
     */
    public function GetKnow()
    {
        verifys($_POST['verify']);
        $know=categorydye('name,content,tu',10);
        if(is_array($know) && count($know)>0){
            returnApiSuccess('1',$know);
        }else{
            returnApiError('无数据');
        }
    }


    /**
     * 合作品牌
     * verify varchar notnull  非法验证
     */
    public function GetBrand()
    {
        verifys($_POST['verify']);
        $coures = M('article')->field("aid,title,thumbnail")->where("sid=11")->limit(12)->select();
        $data=array();
        if(is_array($coures) && count($coures)>0){
            $data['coures']=$coures;
            $data['count']=zongshu(33,$_POST['size']);
            returnApiSuccess('1',$data);
        }else{
            returnApiError('无数据');
        }

    }

    /**
     * 荣誉证书
     * verify varchar notnull  非法验证
     */
    public function GetCertificate()
    {
        verifys($_POST['verify']);
        $coures = M('article')->field("aid,title,thumbnail,content")->where("sid=13")->limit(page($_POST['p'],6))->select();
        $data=array();
        if(is_array($coures) && count($coures)>0){
            $data['coures']=$coures;
            $data['count']=zongshu(13,6);
            returnApiSuccess('1',$data);
        }else{
            returnApiError('无数据');
        }
    }

    /**
     * 视频中心
     * verify varchar notnull  非法验证
     * p int 页数
     * size int 每页数量
     */
    public function GetVideo()
    {
        verifys($_POST['verify']);
        $coures = M('article')->field("aid,title,thumbnail,sp")->where("sid=37")->limit(page($_POST['p'],$_POST['size']))->select();
        $data=array();
        if(is_array($coures) && count($coures)>0){
            $data['coures']=$coures;
            $data['count']=zongshu(37,$_POST['size']);
            returnApiSuccess('1',$data);
        }else{
            returnApiError('无数据');
        }
    }




    /**
     * 详情页面
     * verify varchar notnull  非法验证
     * aid int id
     */
    public function Particulars()
    {
        verifys($_POST['verify']);
        $aid = isset($_POST['aid']) ? trim($_POST['aid']) : 0;
        $article=M("article")->field('title,content,t')->where("aid=$aid")->find();

        $gy=M("flash")->field('pic')->where("sid=9")->find();
        $ge=M("flash")->field('pic')->where("sid=10")->find();

        if(is_array($article) && count($article)>0){
            $article['gy']=$gy['pic'];
            $article['ge']=$ge['pic'];

            $coures = M('article')->field("aid,title,thumbnail,sp")->where("sid=37")->limit(4)->order("t desc")->select();
            $data=array();
            if(is_array($coures) && count($coures)>0){
                foreach($coures as $key=>$value){
                    $data[]=$value;
                }
            }else{
                $data[]='';
            }
            $article['sp']=$data;

            $zj = M('article')->field("aid,title,thumbnail")->where("sid=20")->limit(4)->order("t desc")->select();
            $hd=array();
            if(is_array($zj) && count($zj)>0){
                foreach($zj as $k=>$v){
                    $hd[]=$v;
                }
            }else{
                $hd[]='';
            }
            $article['hd']=$hd;

            returnApiSuccess('1',$article);
        }else{
            returnApiError('无数据');
        }
    }



    /**
     * 招聘启事
     * verify varchar notnull  非法验证
     */
    public function GetInvite()
    {
        verifys($_POST['verify']);
        $coures = M('article')->field("aid,title,thumbnail,jiage,address,content,t")->where("sid=24")->select();
        if(is_array($coures) && count($coures)>0){
            returnApiSuccess('1',$coures);
        }else{
            returnApiError('无数据');
        }
    }

    /**
     * 弹窗
     * verify varchar notnull  非法验证
     * aid int id
     */
    public function FindPopup()
    {
        verifys($_POST['verify']);
        $aid=$p = intval($_POST['aid']) > 0 ?$_POST['aid'] : 0;
        $where['sid']=32;
        $where['aid']=$aid;
        $field='title,content';
        $article=M("article")->field($field)->where($where)->find();
        if(is_array($article) && count($article)>0){
            returnApiSuccess('1',$article);
        }else{
            returnApiError('无数据');
        }
    }

    /**
     * 视频详情
     * verify varchar notnull  非法验证
     * aid int id
     */
    public function FindVideo()
    {
        verifys($_POST['verify']);
        $aid=$p = intval($_POST['aid']) > 0 ?$_POST['aid'] : 0;
        $where['sid']=37;
        $where['aid']=$aid;
        $field="aid,title,thumbnail,sp";
        $article=M("article")->field($field)->where($where)->find();
        if(is_array($article) && count($article)>0){
            returnApiSuccess('1',$article);
        }else{
            returnApiError('无数据');
        }
    }

    /**
     * 活动公告
     * verify varchar notnull  非法验证
     * p int 页数
     * size int 每页数量
     */
    public function Notice()
    {
        verifys($_POST['verify']);
        $sid=intval($_POST['sid']) > 0 ?$_POST['sid'] : 0;
        $where['sid']=$sid;
        $field="aid,title,thumbnail,description,t";

        $coures = M('article')->field($field)->where($where)->limit(page($_POST['p'],$_POST['size']))->select();
        $arr=array();
        $data=array();
        if(is_array($coures) && count($coures)>0){
            $data['coures']=$coures;
            $data['count']=zongshu($sid,$_POST['size']);
            $arr['notice']=$data;

            $ersc = M('article')->field("aid,title,thumbnail")->where("sid=16")->limit(4)->order("t desc")->select();
            if(is_array($ersc) && count($ersc)>0){
                $arr['ersc']=$ersc;
            }else{
                $arr['ersc']='';
            }
            returnApiSuccess('1',$arr);
        }else{
            returnApiError('无数据');
        }
    }
}