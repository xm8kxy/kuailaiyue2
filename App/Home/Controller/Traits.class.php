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

}