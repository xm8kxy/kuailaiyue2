<?php
/**
 * app支付宝验证
 *
 */
namespace Home\Controller;

require_once "./comm_function.php";
class ApiNotifyurlController extends ApiComController
{
    //---------------------------------------------------------------------支付宝验证开始--------------------------------------
    //申请成为金卡
    public function AlipayNotifyurl()
    {
        Vendor('Alipay.aop.AopClient');
        $aop = new \AopClient;
        //$public_path = "key/rsa_public_key.pem";//公钥路径
        $aop->alipayrsaPublicKey = C('zfbkey');
        //此处验签方式必须与下单时的签名方式一致
        $flag = $aop->rsaCheckV1($_POST, NULL, "RSA2");

        if($flag){

            //验签通过后再实现业务逻辑，比如修改订单表中的支付状态。
            /**
             *  ①验签通过后核实如下参数out_trade_no、total_amount、seller_id
             *  ②修改订单表
             **/
            if(C('seller_id')==$_POST['seller_id']){
                if($_POST['trade_status']!='TRADE_SUCCESS'){ echo 'success';exit;}
                $where['cz_number']=$_POST['out_trade_no'];
                $where['money']=$_POST['total_amount'];
                $where['status']=0;
                $data=M('XmCzOrder')->where($where)->getField('id');

                if($data){

                    //查询个人状态
                    $userid=M('XmCzOrder')->where($where)->getField('user_id');
                    if($userid){
                        $user_data= xm_user($userid,$field='jk_balance,balance,is_jkuser');
                        $datag['old_jk_money']= $user_data['jk_balance'];
                        $datag['old_money']=$user_data['balance'];
                    }

                    //②修改订单表
                    $whereg['id']=$data;
                    $datag['status']=1;
                    $datag['zffs']=1;
                    $datag['modify_time']=get13TimeStamp();
                    $datas=M('XmCzOrder')->where($whereg)->save($datag);
                    //改变用户状态
                    if($datas){
                        $datasuer['is_jkuser']=1;
                        $datasuer['jk_balance']=$datag['old_money']+ $where['money'];
                        $datasuer['balance']=0;
                        $datauser= xm_put_user($userid,$datasuer);
                        if($datauser){
//资金流水
                            $content='您充值'.$where['money'].'成功成为金卡用户,余额已经全部转为金卡余额';
                            czmoneylog($userid,$content,$where['money'],$datasuer['jk_balance'],1,$data);
                            echo 'success';exit;
                        }else{
                            exit;
                        }
                    }
                }

            }
        }
        //打印success，应答支付宝。必须保证本界面无错误。只打印了success，否则支付宝将重复请求回调地址。
        echo 'success';
    }

    //线下支付
    public function AlipayorderNotifyurl()
    {


        Vendor('Alipay.aop.AopClient');
        $aop = new \AopClient;
        //$public_path = "key/rsa_public_key.pem";//公钥路径
        $aop->alipayrsaPublicKey = C('zfbkey');
        //此处验签方式必须与下单时的签名方式一致
        $flag = $aop->rsaCheckV1($_POST, NULL, "RSA2");
        if($flag){
            //验签通过后再实现业务逻辑，比如修改订单表中的支付状态。
            /**
             *  ①验签通过后核实如下参数out_trade_no、total_amount、seller_id
             *  ②修改订单表
             **/
            if(C('seller_id')==$_POST['seller_id']){
                if($_POST['trade_status']!='TRADE_SUCCESS'){ echo 'success';exit;}
                $where['order_number']=$_POST['out_trade_no'];
                $where['status']=0;
                $data=M('XmOrder')->where($where)->getField('id');

                if($data){
                     $ojg = M('XmOrder')->where($where)->getField('money');
                     $ouser_id=M('XmOrder')->where($where)->getField('user_id');
                    //看有没有使用代金券
                    $subject = $_POST['subject'];
                //    $subject='线下订单支付';
                    $arrays= explode("@",trim($subject));
                    if($arrays['1']){
                        $coupon_num=$arrays['1'];
                        //有就,查状态，查金额
                          $cdata= get_djjs($coupon_num);
                        if($cdata){
                            $jq= $cdata['coupon_money']+$_POST['total_amount'];
                            if($ojg>$jq){
                                //异常订单
                                $adddata=add_yc_order(1,$_POST['out_trade_no'],$_POST['total_amount'],1,$_POST['gmt_payment'],$_POST['subject'],$coupon_num);
                                if($adddata){echo 'success';exit;}else{exit;}
                            }else{
                                //正常使用代金券订单，更新订单，代金券状态
                                $wheregx['id']=$data;

                                $datagx['status']=1;
                                $datagx['time_modify']=get13TimeStamp();
                                $datagx['user_payment']=$_POST['total_amount'];
                                $datagx['payment_method']="支付宝";
                                $datagx['is_user_coupon']=1;
                                $datagx['coupon_id']=$cdata['id'];
                              //  $datas=M('XmOrder')->where($wheregx)->save($datagx);
                                $Model = M(); // 实例化一个空对象
                                $Model->startTrans(); // 开启事务
                                $datas=$Model->table('qw_xm_order')->where($wheregx)->save($datagx);
                                $wherexc['id']=$cdata['id'];
                                $dataxc['state']=1;
                                $datas2=$Model->table('qw_xm_coupon')->where($wherexc)->save($dataxc);
                                if ($datas && $datas2) {
                                    $Model->commit(); // 成功则提交事务
                                    //资金流水
                                    $user_balance= xm_is_jk_money($ouser_id);
                                    moneylog($subject,$ouser_id,0,$_POST['total_amount'],$user_balance,'支付宝',$data);
                                    echo 'success';exit;
                                } else {
                                    $Model->rollback(); // 否则将事务回滚
                                    exit;
                                }
                            }
                        }else{
                            //同样是没查到可用代金券
                            //异常订单
                            $adddata=add_yc_order(1,$_POST['out_trade_no'],$_POST['total_amount'],1,$_POST['gmt_payment'],$_POST['subject'],$coupon_num);
                            if($adddata){echo 'success';exit;}else{exit;}
                        }
                    }else{
//没有就查订单金额
                    if($ojg!=$_POST['total_amount']){
                        //异常订单
                        $adddata=add_yc_order(1,$_POST['out_trade_no'],$_POST['total_amount'],1,$_POST['gmt_payment'],$_POST['subject']);
                        if($adddata){echo 'success';exit;}else{exit;}
                    }
                        //正常使用代金券订单，更新订单，代金券状态
                        $wheregx['id']=$data;

                        $datagx['status']=1;
                        $datagx['time_modify']=get13TimeStamp();
                        $datagx['user_payment']=$_POST['total_amount'];
                        $datagx['payment_method']="支付宝";
                        $datas=M('XmOrder')->where($wheregx)->save($datagx);
                       if($datas){
                            //资金流水
                            $user_balance= xm_is_jk_money($ouser_id);
                            moneylog($subject,$ouser_id,0,$_POST['total_amount'],$user_balance,'支付宝',$data);
                            echo 'success';exit;
                        }else{
                            exit;
                        }
                    }
                }
            }
        }
        //打印success，应答支付宝。必须保证本界面无错误。只打印了success，否则支付宝将重复请求回调地址。
        echo 'success';
    }


    //订单充值
    public function AlipayCzNotifyurl()
    {
        Vendor('Alipay.aop.AopClient');
        $aop = new \AopClient;
        //$public_path = "key/rsa_public_key.pem";//公钥路径
        $aop->alipayrsaPublicKey = C('zfbkey');
        //此处验签方式必须与下单时的签名方式一致
        $flag = $aop->rsaCheckV1($_POST, NULL, "RSA2");

        if($flag){

            //验签通过后再实现业务逻辑，比如修改订单表中的支付状态。
            /**
             *  ①验签通过后核实如下参数out_trade_no、total_amount、seller_id
             *  ②修改订单表
             **/
            if(C('seller_id')==$_POST['seller_id']){
                if($_POST['trade_status']!='TRADE_SUCCESS'){ echo 'success';exit;}
                $where['cz_number']=$_POST['out_trade_no'];
                $where['money']=$_POST['total_amount'];
                $where['status']=0;
                $data=M('XmCzOrder')->where($where)->getField('id');

                if($data){

                    //查询个人状态
                    $userid=M('XmCzOrder')->where($where)->getField('user_id');
                    if($userid){
                        $user_data= xm_user($userid,$field='jk_balance,balance,is_jkuser');
                        $datag['old_jk_money']= $user_data['jk_balance'];
                        $datag['old_money']=$user_data['balance'];
                    }

                    //②修改订单表
                    $whereg['id']=$data;
                    $datag['status']=1;
                    $datag['zffs']=1;
                    $datag['modify_time']=get13TimeStamp();
                    $datas=M('XmCzOrder')->where($whereg)->save($datag);
                    //改变用户状态
                    if($datas){
                        if($user_data['is_jkuser']){
                            $datasuer['jk_balance']=  $user_data['jk_balance']+ $where['money'];
                            $bdmoney=$datasuer['jk_balance'];
                        }else{
                            $datasuer['balance']=  $user_data['balance']+ $where['money'];
                            $bdmoney=$datasuer['balance'];
                        }
                        $datauser= xm_put_user($userid,$datasuer);
                        if($datauser){
//资金流水
                            $content='您充值'.$where['money'].',充值成功';
                            czmoneylog($userid,$content,$where['money'],$bdmoney,1,$data);
                            echo 'success';exit;
                        }else{
                            exit;
                        }
                    }
                }

            }
        }
        //打印success，应答支付宝。必须保证本界面无错误。只打印了success，否则支付宝将重复请求回调地址。
        echo 'success';
    }

    //---------------------------------------------------------------------支付宝验证结束--------------------------------------


}