<?php

/**
 * 支付代码碎片
 * @author 熊敏
 * @copyright 1.0
 */
namespace Home\controller;

trait TraitsPay
{
    function aaa(){

        $cc=C('appId');
        echo  $cc;
    }
    /**   app支付宝 **/

    /**
     * app支付宝生成订单
     * @param  int $body 对一笔交易的具体描述信息
     * @param  string $subject 商品的标题/交易标题/订单标题/订单关键字等。
     * @param  int $out_trade_no 商户网站唯一订单号
     * @param  int $order_amount 订单总金额，单位为元，精确到小数点后两位，取值范围[0.01,100000000]
     * @param  string $timeout_express 该笔订单允许的最晚付款时间，逾期将关闭交易。取值范围：1m～15d。m-分钟，h-小时，d-天，1c-当天（1c-当天的情况下，无论交易何时创建，都在0点关闭）。 该参数数值不接受小数点， 如 1.5h，可转换为 90m。注：若为空，则默认为15d。
     * @param  int $type 异步回调地址 1是升金卡会员的 2是线下订单支付的 3充值
     * @param  int $djj_id  代金券id线下专用
     * @return  boolean
     */
    function alipay($body,$subject,$out_trade_no,$order_amount,$timeout_express='1d',$type=1,$djj_id=0){

        if(!isset($body)){ return false;}
        if(!isset($subject)){ return false;}
        if(!isset($out_trade_no)){ return false;}
        if(!isset($order_amount)){ return false;}

        Vendor('Alipay.aop.AopClient');
        Vendor('Alipay.aop.request.AlipayTradeAppPayRequest');

        $aop = new \AopClient;
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->appId = C('appId');
        $aop->rsaPrivateKey = C('rsaPrivateKey');
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";
        $aop->alipayrsaPublicKey =  C('zfbkey');
//实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new \AlipayTradeAppPayRequest();
//SDK已经封装掉了公共参数，这里只需要传入业务参数

        $bizcontent = "{\"body\":\"$body\","
            . "\"subject\": \"$subject\","
            . "\"out_trade_no\": \"$out_trade_no\","
            . "\"timeout_express\": \"$timeout_express\","
            . "\"total_amount\": \"$order_amount\","
            . "\"product_code\":\"QUICK_MSECURITY_PAY\""
            . "}";

if($type==1){
    $lj =  C('setNotifyUrl');
}elseif($type==2){
    $lj =  C('setOrderNotifyUrl');
}elseif($type==3){
    $lj=  C('AlipayCzNotifyurl');
}


        $request->setNotifyUrl("$lj");

        $request->setBizContent($bizcontent);

//这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        return  $response;
    }








    /**
     * app支付宝服务端验证异步通知信息参数
     * @param  int $body 对一笔交易的具体描述信息
     * @return  boolean
     */
function  fh($POST){
    Vendor('Alipay.aop.AopClient');
$aop = new \AopClient;
$aop->alipayrsaPublicKey = C('alipayrsaPublicKey');
$flag = $aop->rsaCheckV1($POST, NULL, "RSA2");
        return $flag;
}

}