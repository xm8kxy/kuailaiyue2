<?php
/**
 * 个人中心
 * @author 熊敏
 * @version 1.0
 */
namespace Home\Controller;
use Think\Upload;
use Vendor\Page;
use XmClass\Ucpassxm;
//require_once "./comm_function.php";
class ApipersonalController extends ApiComController
{
    private $options;//环信配置
    private $sms_accountsid;
    private $sms_token;
    private $sms_templateid;
    private $sms_appid;
    private $up_money;

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

        $this->up_money=2000;

        //验证
      parent::checkRequestAuth();
      parent::checkRequsetSign();
       //不用验证是方法
        $no_drs=array();

        $no_drs[]='PCenterInformation'; //个人信息
      //  $no_drs[]='PCenterHead'; //上传头像
    // parent::checkRequsetdr($no_drs);
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
    public function PCenterInformation()
    {

        $user_id = isset($_POST['userid']) ? trim($_POST['userid']) : '';//用户id

        if( $user_id  == ''){ returnApiError('用户id不能为空！');}

        $field = 'moblie,is_fwz,is_jkuser,jk_balance,is_nm,nm,o_username,balance,gxqm,birth,Head,is_audit,is_information,sex';
        $data= xm_user($user_id,$field);
        returnApiSuccess('请求成功',$data);
    }
    
    
    //个人中心
    public function PCenter()
    {
        header('Content-Type:text/event-stream');//通知浏览器开启事件推送功能

        for ($i=10; $i>2; $i--)
        {
            echo $i.'<br />';

            ob_flush(); //此句不能少
            flush();
            sleep(2);
        }
        ob_end_flush();
//        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
//        $str = isset($_POST['token']) ? trim($_POST['token']) : '';
//        $key =  $this->key_xm;
//        if ($user_id == '') { returnApiError( '用户id必须');}
//        if ($str == '') { returnApiError( 'token必须');}
//        if ($key == '') { returnApiError( 'key必须');}
//        $this->is_jwt($str,$key,1,$user_id);

    }


    //我的等级
    public function PCenterGrade()
    {
        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
        if($user_id  == ''){ returnApiError('用户id不能为空！');}
       $data=  xm_user($user_id,$field='integral');
        if ( 0 <= $data['integral'] && $data['integral']<200) {
            $datas['dj']=1;
        } elseif (200 <= $data['integral'] && $data['integral']<400) {
            $datas['dj']=2;
        } elseif (400 <= $data['integral'] && $data['integral']<800) {
            $datas['dj']=3;
        } elseif (800 <= $data['integral'] && $data['integral']<1600) {
            $datas['dj']=4;
        } elseif (1600 <= $data['integral'] && $data['integral']<3200) {
            $datas['dj']=5;
        } elseif (3200 <= $data['integral'] && $data['integral']<6400) {
            $datas['dj']=6;
        } elseif (6400 <= $data['integral'] && $data['integral']<12800) {
            $datas['dj']=7;
        } elseif (12800 <= $data['integral'] && $data['integral']<25600) {
            $datas['dj']=8;
        } elseif (25600 <= $data['integral'] && $data['integral']<51200) {
            $datas['dj']=9;
        }else{
            $datas['dj']=10;
        }
        returnApiSuccess('请求成功',$datas);
    }

//资金流水
//类型0是订单 1是礼品3充值4提现5退款
    public function PCenterWater()
    {
        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
        $type=isset($_POST['type']) ? trim($_POST['type']) : '0';//类型
        $p = intval($_POST['p']) > 0 ?$_POST['p'] : 1;//分页

        if($user_id  == ''){ returnApiError('用户id不能为空！');}

        $where['user_id']= $user_id;

        switch($type)
        {
            case 1:
                $where['money']= array('gt',0);
            break;
            case 2:
            $where['money']= array('lt',0);
            break;
            case 3:
                $where['type']= 4;
                break;
            case 4:
                $where['type']= 3;
                break;
            case 5:
                $where['type']= 5;
                break;
            default:

        }
        $limit= page($p,4);
        $field='time_add,type,content,money';
        $data= xm_gf($table='XmMoneyFlow',$field,$where,$limit);
        returnApiSuccess('请求成功',$data);

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
                $where['status']=  array('gt','1');
                break;
            case 2:
                $where['status']=  array('in','2,3,4');
                break;
            case 3:
                $where['status']=  array('in','5');

                break;
            case 4:
                $where['status']= 6;
                break;

            case 5:
                $where['status']= 8;
                break;
            default:

        }

        $modle=M('XmOrder');
//        if($type){
//            $where['type']= $type;
//        }
        //自己接的单也要看到
        $jiewhere['_string']="FIND_IN_SET(".$user_id.",xz_user_id)" ;


        //自己的单
         $where['user_id']=  $user_id ;
        $field='id,user_id,appointment_time,time_limit,appointment_dd,money,status,xz_user_id,order_name,jisu_time,classify,is_renew,is_advance_notice';
        $dataa=$modle->field($field)->where($where)->order('id desc')->select();
        $datass=$modle->field($field)->where($jiewhere)->order('id desc')->select();
        $dataqian= array_merge($dataa,$datass);
        $data = my_sort($dataqian,'id',SORT_DESC,SORT_STRING);
        foreach($data as $k=>$value){
            //修改订单表示 is_gb_order=1 是视频  =2是发生改变的订单 =0 普通订单
            if($value['classify']==1){$value['is_gb_order']=1;}elseif($value['is_renew']==1){$value['is_gb_order']=2;}elseif($value['is_advance_notice']==1){$value['is_gb_order']=2;}else{$value['is_gb_order']=0;}
//订单状态 1待处理2待完成3已完成4已评价5取消
           if($value['status']==5){
               $value['status_id']=1;
           }elseif($value['status']==2||$value['status']==3||$value['status']==4){
               $value['status_id']=2;
           }elseif($value['status']==6){
               $value['status_id']=3;
           }elseif($value['status']==7){
               $value['status_id']=4;
           }elseif($value['status']==8){
               $value['status_id']=5;
           }



            $cx_user = array_filter(explode(',', $value['xz_user_id']));
            $userdata = xm_user($cx_user['0'], 'birth,Head,sex,o_username,id,birth,moblie');
            if($userdata < 0 ){
                $userdatas['birth']='';
                $userdatas['moblie']='';
                $userdatas['head']='';
                $userdatas['sex']='';
                $userdatas['o_username']='';
                $userdatas['id']='';
                $value['user']=$userdatas;
            }else{
                $value['user']=$userdata;
            }

            $datas[]=$value;
        }
        if($data){

            returnApiSuccess('请求成功', $datas);
        }else{
            $datas='';
            returnApiSuccess('无数据', $datas);
        }
    }



    //视频验证展示
    public function PVideoValidation()
    {
        $where['sid']=4;
        $field='tp_sp,pic,title';
        $data=M('Flash')->field( $field)->where($where)->find();
        $wheret['sid']=1;
        $datat=M('Flash')->field( $field)->where($wheret)->select();
        $data['tu']=$datat;
        if($data){
            returnApiSuccess('请求成功',$data);
        }else{
            returnApiError('请求失败');
        }
    }


    //更换头像和个性签名
    public function PCenterHead()
    {
        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
        $gxqm = isset($_POST['gxqm']) ? htmlentities(trim($_POST['gxqm'])) : '';//个性签名
        if($user_id  == ''){ returnApiError('用户id不能为空！');}
        if(empty($_FILES)){returnApiError( '必须选择上传文件');}
        if( $gxqm  == ''){ returnApiError('个性签名不能为空！');}
        $imgupdata=imgup();
        if(isset($imgupdata)){
                  //写入数据库
                  $img_val='/file/'.$imgupdata['file']['savepath'].$imgupdata['file']['savename'];
                  $data['gxqm']= $gxqm;
                  $data['Head']= $img_val;
                 $datas = xm_put_user($user_id,$data);
                 if($datas){
                        returnApiSuccess('上传成功','上传成功');
                    }
                    else{
                        returnApiError( '写入数据库失败');
                    }
      }
    }

    //上传验证视频
    public function PCentervideo()
    {
        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id

        if($user_id  == ''){ returnApiError('用户id不能为空！');}
        if(empty($_FILES)){returnApiError( '必须选择上传文件');}
        $videoupdata=videoup();
        if(isset($videoupdata)){
            //写入数据库
            $img_val='/file/'.$videoupdata['file']['savepath'].$videoupdata['file']['savename'];
            $data['video']= $img_val;
            $datas = xm_put_user($user_id,$data);
            if($datas){
                returnApiSuccess('上传成功','上传成功');
            }
            else{
                returnApiError( '写入数据库失败');
            }
        }


    }

//完善个人资料
    public function PCenterPutProfile()
    {
        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
        $o_username = isset($_POST['o_username']) ? htmlentities(trim($_POST['o_username'])) : '';//昵称
        $birth = isset($_POST['birth']) ? htmlentities(trim($_POST['birth'])) : '';//出生日
        $height = isset($_POST['height']) ? intval(trim($_POST['height'])) : '';//身高
        $weight = isset($_POST['weight']) ? intval(trim($_POST['weight'])) : '';//体重

        if($user_id == ''){ returnApiError('用户id不能为空！');}
        if($o_username  == ''){ returnApiError('昵称不能为空！');}
        if($birth  == ''){ returnApiError('年龄不能为空！');}
        if($height  == ''){ returnApiError('身高不能为空！');}
        if($weight  == ''){ returnApiError('体重不能为空！');}

        $data['o_username']=$o_username;
        $data['height']=$height;
        $data['birth']=$birth;
        $data['weight']=$weight;
       $datas=  xm_put_user($user_id,$data);
        if($datas){
            returnApiSuccess('更新成功',$datas);
        }else{
            returnApiError( '更新失败');
        }
}

    //普通会员成为金卡会员金额显示
    public function PCenterGetMoney()
    {
        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
        if($user_id == ''){ returnApiError('用户id不能为空！');}
        //先看自己状态
        $field='is_jkuser,balance';
        $userdata=  xm_user($user_id,$field);

        if($userdata['balance']<=$this->up_money){
            $zfje=ceil($this->up_money-$userdata['balance']);
            $userdata['zfje']=$zfje;
        }else{
            $userdata['zfje']=0;
        }

        returnApiSuccess('请求成功',$userdata);
    }

//普通会员成为金卡会员
    public function PCenterUpgrade()
    {
        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
        $zftype = isset($_POST['zftype']) ? trim($_POST['zftype']) : '';//支付方式1支付宝2微信
        if($user_id == ''){ returnApiError('用户id不能为空！');}
        //先看自己状态
        $field='is_jkuser,balance';
        $userdata=  xm_user($user_id,$field);
        if($userdata['is_jkuser']==1){returnApiError('用户状态不正确！');}
        //看自己余额里有多少
        //够就将所有钱转到金卡余额
        if($userdata['balance']>=$this->up_money){
            $datas['jk_balance']=$userdata['balance'];
            $datas['balance']=0;
            $datas['is_jkuser']=1;
          $sjdata=  xm_put_user($user_id,$datas);
            if($sjdata){
                returnApiSuccess('用余额升级成功',$sjdata);
            }else{
                returnApiError( '升级失败');
            }
        }else{
            //不够看差多少
          //  $zfje=ceil($this->up_money-$userdata['balance']);
            $zfje=0.01;
            //根据支付方式选择支付宝或者微信
            $otrade_no=build_order_no();
            if($zftype==1){
                //支付宝
     //生成支付订单
                $data['user_id']= $user_id;
                $data['cz_number']=$otrade_no;
                $data['money']=$zfje;

                $data['remarks']='充值成为金额用户';
                $data['status']='0';
                $data['type']='3';
                $data['add_time']=get13TimeStamp();
                $datas=M('XmCzOrder')->add($data);
        if(!$datas){ returnApiError( '订单生成失败');}
     //   支付
        $body="充值";
        $subject="升级为金卡用户";
        $out_trade_no=$data['cz_number'];
    //  $order_amount=0.01;
      $order_amount=$zfje;
        $zfsdk['aliyun']= TraitsPay::alipay($body,$subject,$out_trade_no,$order_amount,$timeout_express='1d');
        $zfsdk['weixin']='0';
        returnApiSuccess('支付宝支付sdk', $zfsdk);
            }elseif($zftype==2){
             //微信
            }else{
                returnApiError( '订单生成失败了');
            }
        }


}





    //---------------------------------------------------------------------个人中心结束--------------------------------------

    /**----------------------------------------个人资料 开始 钱晓松----------------------------------------*/

    /**
     * 修改个人资料
     * @author 钱晓松
     * @version 1.0
     * @param array $_POST 用户数据
     * @return bool
     **/

    public function OneUserData()
    {
        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
        $gxqm = isset($_POST['gxqm']) ? htmlentities(trim($_POST['gxqm'])) : '';//个性签名

        $o_username = isset($_POST['o_username']) ? htmlentities(trim($_POST['o_username'])) : '';//昵称
        $birth = isset($_POST['birth']) ? htmlentities(trim($_POST['birth'])) : '';//出生日
        $height = isset($_POST['height']) ? intval(trim($_POST['height'])) : '';//身高
        $weight = isset($_POST['weight']) ? intval(trim($_POST['weight'])) : '';//体重
        $tc_id = isset($_POST['tc_id']) ? htmlentities(trim($_POST['tc_id'])) : '';//职业爱好

        if($user_id == ''){ returnApiError('用户id不能为空！');}
        if($o_username != ''){$data['o_username']=$o_username;}
        if($birth != ''){$data['birth']=$birth;}
        if($height != ''){$data['height']=$height;}
        if($weight != ''){$data['weight']=$weight;}
        if($gxqm != ''){$data['gxqm']=$gxqm;}

        if($tc_id != ''){
            $where['tc_id']=$tc_id;
            $tab_count=M('xm_tab')->where($where)->count();//查询职业爱好类型是否存在
            if($tab_count){
                $data['tc_id']=$tc_id;
            }
        }
        if(!empty($_FILES)){
            $imgupdata=imgup();
            if(isset($imgupdata)){
                //写入数据库
                $img_val='/file/'.$imgupdata['file']['savepath'].$imgupdata['file']['savename'];
                $data['Head']= $img_val;
            }
        }
        $datas = xm_put_user($user_id,$data);
        if($datas){
            returnApiSuccess('修改成功',1);
        }
        else{
            returnApiError('编辑个人资料失败');
        }
    }


    /**
     * 获取个人资料
     *
     * @author 钱晓松
     * @version 1.0
     * @param int $_POST ['mobile'] 用户id
     * @return array
     **/
    public function GetUser()
    {
        $user_id = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';//用户id
        if( $user_id  == ''){ returnApiError('用户id不能为空！');}
        $field='o_username,birth,gxqm,Head,sex,tc_id,is_audit,height,weight,province,city,area';
        $data= get_user($user_id,$field);
        if($data){
            if($data['tc_id']){
                $data['tc_id']=get_hobby($data['tc_id'],$data['sex']);
            }else{
                $data['tc_id']=0;
            }
            $region=get_region($data['province'],$data['city'],$data['area']);
            if($region){
                $data['province'] = $region['province_name'];
                $data['city'] = $region['city_name'];
                $data['area'] = $region['area_name'];
            }else{
                $data['province'] = 0;
                $data['city'] = 0;
                $data['area'] = 0;

            }
            $data['birth']? $data['birth'] :$data['birth']=0;
            $data['gxqm'] ? $data['gxqm'] :$data['gxqm']=0;
            returnApiSuccess('请求成功',$data);
        }else{
            returnApiError('请求失败~');
        }

    }


    /**
     * 获取全部职业爱好
     *
     * @author 钱晓松
     * @version 1.0
     * @param int $_POST['sex'] 用户id
     * @return array
     **/
    public function GetHobbyAll()
    {
        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
        $sex = isset($_POST['sex']) ? trim($_POST['sex']) : '';//性别
        if( $user_id  == ''){ returnApiError('用户id不能为空！');}
        if( $sex  == ''){ returnApiError('用户性别不能为空！');}
        $hobby=get_hobby(0,$sex);
        if($hobby){
            returnApiSuccess('请求成功',$hobby);
        }else{
            returnApiError('请求失败~');
        }
    }

    /**
     * 身份认证
     *
     * @author 钱晓松
     * @version 1.0
     * @param int $_POST['user_id'] 用户id
     * @param array $front_file 身份证正面
     * @param array $contrary_file 身份证反面
     * @return bool
     **/
    public function GetIdentity()
    {
        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
        if($user_id == ''){ returnApiError('用户id不能为空！');}
        if(empty($_FILES)){
            returnApiError('请上传身份证');
        }else{
            $Tab=M('XmAutonym');
            $where['user_id']=$user_id;
            $autonym=$Tab->where($where)->find();
            //数据库没有正反身份证的时候才开始保存
            if($autonym['img1'] == ''&&$autonym['img2'] == ''){
                $imgupdata=imgup();//保存图片
                if(isset($imgupdata['front_file']['savename'])&&isset($imgupdata['contrary_file']['savename'])){
                    $data['img1']='/file/'.$imgupdata['front_file']['savepath'].$imgupdata['front_file']['savename'];//正面
                    $data['img2']='/file/'.$imgupdata['contrary_file']['savepath'].$imgupdata['contrary_file']['savename'];//反面
                }else{
                    returnApiError('请重新上传身份证~');
                }
            }
            //XmAutonym表没有这条数据的时候添加数据；有这条数据的时候判断身份证正反字段都为空的时候才更新身份证
            if($autonym){
                //更新身份证图片过滤
                if($autonym['img1'] == ''&&$autonym['img2'] == ''){
                    if($Tab->where($where)->save($data)){
                        returnApiSuccess('更新身份证成功~',1);
                    }
                }
            }else{
                $data['user_id'] = $user_id;
                $data['add_time'] = time();
                if($Tab->data($data)->add()){
                    returnApiSuccess('上传身份证成功~',1);
                }
            }
            returnApiError('上传身份证失败~');
        }

    }

    /**
     * 单个女性被评价数据
     *
     * @author 钱晓松
     * @version 1.0
     * @param int $_POST['user_id'] 用户id
     * @return array
     **/
    public function GetOneUserCommentList()
    {
        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
        $p = intval($_POST['p']) > 0 ?$_POST['p'] : 1;//分页
        if( $user_id  == ''){ returnApiError('用户id不能为空！');}
        $limit= page($p);
        $field='id,content,offline_type,comment_type';
        $where['user_id']= $user_id;
        $data= xm_gf('XmComment',$field,$where,$limit);

        if($data){
            $list_data['comment']=$data;
            $list_data['count']=get_count('XmComment',$where);
            returnApiSuccess('请求成功',$list_data);
        }else{
            returnApiError('请求失败~');
        }
    }

    /**
     * 单个用户代金券列表
     *
     * @author 钱晓松
     * @version 1.0
     * @param int $_POST['user_id'] 用户id
     * @return array
     **/
    public function GetOneUserCouponList()
    {
        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
        $p = intval($_POST['p']) > 0 ?$_POST['p'] : 1;//分页
        if( $user_id  == ''){ returnApiError('用户id不能为空！');}
        $limit= page($p);
        $field='id,coupon_num,title,coupon_money,create_time,expire_time,use_time,state';
        $where['user_id']= $user_id;
        $data= xm_gf('XmCoupon',$field,$where,$limit);
        if($data){
            $Tab=M('XmCoupon');
            foreach($data as $key=>$value){
                if($value['expire_time']<get13TimeStamp()){
                    echo '|'.$value['expire_time'].'|'.get13TimeStamp();
                    //状态2为过期
                    if($value['state']!= 2){
                        $whe['id']= $value['id'];
                        $save['state']= 2;
                        if(!$Tab->where($whe)->save($save)){
                            returnApiError('请求失败~');
                        }
                    }
                }
            }
            $list_data['coupon']=$data;
            $list_data['count']=get_count('XmCoupon',$where);
            returnApiSuccess('请求成功',$list_data);
        }else{
            returnApiError('请求失败~');
        }
    }

    /**
     * 订单详情
     *
     * @author 钱晓松
     * @version 1.0
     * @param int $_POST['order_id'] 订单id
     * @return array
     **/
    public function GetOneOrder()
    {
        $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';//用户id
        $order_id = isset($_POST['order_id']) ? trim($_POST['order_id']) : '';//订单id
        if( $user_id  == ''){ returnApiError('用户id不能为空！');}
        if( $order_id  == ''){ returnApiError('订单id不能为空！');}

        $prefix = C('DB_PREFIX');
        $order_name="{$prefix}xm_order";
        $field='m.id as userid,m.moblie,m.o_username,m.birth,m.Head,m.sex,t.title,t.prc,t.prc_da,'
            ."$order_name.appointment_dd,$order_name.order_number,$order_name.appointment_time,$order_name.jisu_time,$order_name.time_limit
            ,$order_name.status,$order_name.is_renew,$order_name.is_advance_notice,$order_name.money,$order_name.modify_money,$order_name.xz_user_id,$order_name.remarks";

        $Tab=M('XmOrder');
        $data = $Tab->field($field)
            ->join("{$prefix}xm_member as m ON m.id = {$prefix}xm_order.user_id")
            ->join("{$prefix}xm_tryst_classify as t ON {$prefix}xm_order.classify = t.id")
            ->where("{$prefix}xm_order.id=$order_id")->find();

        $hello = explode(',',$data['xz_user_id']);
        $xz_id=0;
        for($index=0;$index<count($hello);$index++)
        {
            if($hello[$index]==$user_id){
                $xz_id=1;
                break;
            }
        }
        if(($data && $data['userid']==$user_id)||($data &&$xz_id)){

            $data['status']=GetStatusName($data['status']);
            returnApiSuccess('请求成功',$data);
        }else{
            returnApiError('请求失败~');
        }
    }

    /**
     * xxxxxx
     *
     * @author 钱晓松
     * @version 1.0
     * @param int $_POST ['order_id'] 订单id
     * @return array
     **/
    public function GetOneOrders()
    {
        $h = new Easemob($this->options);
        $hx = $h->getToken();
        echo $hx;
        echo 1234569991123;
        exit;
//        git remote add origin https://github.com/xm8kxy/kuailaiyue2.git
    }
    /**----------------------------------------个人资料 结束 钱晓松----------------------------------------*/

}