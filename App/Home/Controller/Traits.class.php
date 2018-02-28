<?php

/**
 * 代码碎片
 * @author 熊敏
 * @copyright 1.0
 */
namespace Home\controller;

trait Traits
{
    /**    关于订单 **/

    /**
     * 检查订单是否能续约
     * @param  int $order_id
     * @return  boolean
     */
    function get_is_renew($order_id){
        if(!isset($order_id)){ return false;}
        //查询订单状态
        $where['id']=$order_id;
        $xmorder = M('XmOrder');
        $data= $xmorder->where($where)->getField('status');
        if($data!=3 || $data!=4){
            return false;
        }
        //查询是否已经续约过
        $xydata= $xmorder->where($where)->getField('is_renew');
        if($xydata){ return false; }
        //查询当前时间是否符合
        $ktime= $xmorder->where($where)->getField('appointment_time');
        $jtime= $xmorder->where($where)->getField('jisu_time');
        $dtime = get13TimeStamp();
        if($dtime> $ktime && $dtime< $jtime){
            return true;
        }else{
            return false;
        }
    }


    /**
     * 检查订单是否能改时间
     * @param  int $order_id
     * @return  boolean
     */
    function get_is_gaitime($order_id){
        if(!isset($order_id)){ return false;}
        //查询订单状态
        $where['id']=$order_id;
        $xmorder = M('XmOrder');
        $data= $xmorder->where($where)->getField('status');
        if($data!=1 || $data!=2){
            return false;
        }
        //查询是否已经改过时间
        $xydata= $xmorder->where($where)->getField('is_advance_notice');
        if($xydata){ return false; }
        //查询当前时间是否符合
        $ktime= $xmorder->where($where)->getField('appointment_time');
        $dtime = get13TimeStamp();
        if($dtime<$ktime){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 检查订单是否能取消
     * @param  int $order_id
     * @return  boolean
     */
    function get_is_quxiao($order_id){
        if(!isset($order_id)){ return false;}
        //查询订单状态
        $where['id']=$order_id;
        $xmorder = M('XmOrder');
        $data= $xmorder->where($where)->getField('status');
        if($data!=1 || $data!=2){
            return false;
        }
        //查询是否已经改过时间
    }


    function add_xt_news(){

    }

    /**
     * 订单要支付金额（只计算钱）
     * @param int $user_id 用户id
     * @param int $order_id  订单id
     * @param int $djj_id    代金券id
     */
    function get_order_jq($user_id,$order_id,$djj_id){
        if(!isset($user_id)){ returnApiError( '用户id必须');}
        if(!isset($order_id)){ returnApiError( '订单id必须');}

        $where_o['id']= $order_id;
        $zf_money=M('XmOrder')->where($where_o)->getField('money');

            $where['id']=$djj_id;
        $field='id,coupon_money';
        $data=M('XmCoupon')->field($field)->where($where)->find();

        if($djj_id && $data){
            $dj_money=$data['coupon_money'];
            //有代金券
            //1代金券金额大于订单
            if($zf_money>$dj_money){

                $zf_q=$zf_money-$dj_money;

                $zf_moneys['zf_money']=$zf_q;
                $zf_moneys['dj_money']=$dj_money;
                $zf_moneys['coupon_id']=$data['id'];
                $zf_moneys['order_money']= $zf_money;
            }else{
                $zf_moneys['zf_money']=0.01;
                $zf_moneys['coupon_id']=$data['id'];
                $zf_moneys['dj_money']=$dj_money;
                $zf_moneys['order_money']= $zf_money;
            }
            //1代金券金额小于订单
        }else{
           //没有代金券
            $zf_moneys['zf_money']=$zf_money;
            $zf_moneys['coupon_id']='0';
            $zf_moneys['dj_money']='0';
            $zf_moneys['order_money']= $zf_money;
        }
        return $zf_moneys;
    }



    /**订单退钱
     * @param $user_id
     * @param $order_id
     * @param $percent 退款百分比  1是全部 0.8是80%
     * @return  boolean
     */
    public function put_order_tuei($user_id,$order_id,$percent=1)
    {
        if($user_id==''){ returnApiError( '用户id必须');}
        if($order_id==''){ returnApiError( '订单id必须');}
        if($order_id<0){returnApiError( '退款百分比有问题');}
        $where_xm['id']= $order_id;
        $firld_xm='user_id,status,money,user_payment';
        $gf_id=M('XmOrder')->field($firld_xm)->where($where_xm)->find();
        if($gf_id['user_id']!= $user_id){returnApiError( '不是你的订单');}
        if( $gf_id['status']<2 || $gf_id['status']>7){returnApiError( '订单状态有异常');}
        $order_money=$gf_id['user_payment']*$percent;
        $datakouqian=$gf_id['user_payment']-$order_money;//返回用的 不参与逻辑

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

        if ($result && $result1) {
            $Model->commit(); // 成功则提交事务
            $where_cx['id']=$order_id;
            $order_num =M('XmOrder')->where($where_cx)->getField('order_number');

            if( $userm['is_jkuser']==1){
                //金卡用户
                $ml='订单'.$order_num.' 订单退款'. $order_money.' ,账号可用余额为'. $data_t_o['jk_balance'];
                $mlfm=$data_t_o['jk_balance'];
                moneylog( $ml, $user_id,5,$order_money,$mlfm,'余额',$order_id);
            }else{
                $ml='订单'.$order_num.'订单退款'. $order_money.' ,账号可用余额为'. $data_t_o['balance'];
                $mlfm=$data_t_o['balance'];
                moneylog( $ml, $user_id,5,$order_money,$mlfm,'余额',$order_id);
            }
            $data['money']=$order_money;
            $data['kouqian']=  $datakouqian;

            return $data;

        } else {

            $Model->rollback(); // 否则将事务回滚
            return false;
        }

    }

    /**订单女性得钱
     * @param $order_id
     * @param $money
     * @param $type ；类型 1表示男方取消补偿
     * @return  真
     */
    public function put_order_womendeqiam($order_id,$money,$type=1){
        if($order_id==''){ returnApiError( '订单id必须');}
        if($money==''){ returnApiError( '可分的钱必须');}
        $where_o_c['id'] = $order_id;
        $field_o_c = 'money,xz_pe_num,xz_user_id';
        $cx_data = M('XmOrder')->field($field_o_c)->where($where_o_c)->find();
        //查询用户人数
        $cx_r_s = $cx_data['xz_pe_num'];
        //查询订单钱
        $cx_r_money = $money;
        //每个用户可以分到的钱
        $money_f = floor($cx_r_money / $cx_r_s);
        $datas = array_filter(explode(',', $cx_data['xz_user_id']));
        //分钱
        foreach ($datas as $key => $value) {

            $where_f_u['id'] = $value;

            $databalance=M('XmMember')->where($where_f_u)->getField('balance');

            $data_f_u['balance'] = $databalance + $money_f;
            //   $mz='result'.$key;
            $result1 = M('XmMember')->where($where_f_u)->save($data_f_u);
            if ($result1) {
                $where_cx['id'] = $order_id;
                $order_num = M('XmOrder')->where($where_cx)->getField('order_number');
                if($type==1){
                    $ml = '订单' . $order_num . '获得' . $money_f . ' ,账号可用余额为' . $data_f_u['balance'];
                }else{
                    $ml = '订单' . $order_num . '获得' . $money_f . ' ,账号可用余额为' . $data_f_u['balance'];
                }
                moneylog($ml, $value, 7, $money_f, $data_f_u['balance'], '余额', $order_id);
            }
        }

        return true;
    }



}