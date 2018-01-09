<?php

namespace Home\Controller;
use Think\Upload;
use Vendor\Page;
require(C('Library')."/Firebase/JWT/JWT.php");
use Firebase\JWT\JWT;

use XmClass\RndChinaName;

class ApiController extends ComController
{
    private $token_xm; //校验参数
    private $key_xm; //jwt参数
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
        echo build_order_no();exit;
   //     $this->display();
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
        $User = M("XmOrder"); // 实例化User对象
        $where['order_id']= $order_id;
        $data = $User->where($where)->find();
       print_r($data);
        //订单和要好支付的状态
        //支付金额
        //用户余额
    }

//。。。。。。。-----------------------------------------------------------------男性线下模块结束-------------------------------------------
    //-----------------------------------------------------------------男性模块结束-------------------------------------------






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