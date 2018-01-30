<?php
namespace Aliyunphp\api_demo;
ini_set("display_errors", "on");

require_once dirname(__DIR__) . '/api_sdk/vendor/autoload.php';

use Aliyun\Core\Config;
use Aliyun\Core\Exception\ClientException;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Api\Dypls\Request\V20170525\BindAxbRequest;
use Aliyun\Api\Dypls\Request\V20170525\BindAxnRequest;
use Aliyun\Api\Dypls\Request\V20170525\UnbindSubscriptionRequest;
use Aliyun\Api\Dypls\Request\V20170525\UpdateSubscriptionRequest;
use Aliyun\Api\Dypls\Request\V20170525\QueryRecordFileDownloadUrlRequest;
use Aliyun\Api\Dypls\Request\V20170525\QuerySubscriptionDetailRequest;

 //加载区域结点配置
Config::load();

/**
 * Created on 17/6/7.
 * 号码隐私保护API产品的DEMO程序,工程中包含了一个PlsDemo类，直接通过
 * 执行此文件即可体验号码隐私保护产品API功能(只需要将AK替换成开通了云通信-号码隐私保护产品功能的AK即可)
 * 备注:Demo工程编码采用UTF-8
 */
class Plsth
{
    static $acsClient = null;

    public function __construct($accessKeyId,$accessKeySecret)
    {
      $this->getAcsClient($accessKeyId,$accessKeySecret);
}

    public static function getAcsClient($KeyId, $KeySecret) {
        //产品名称:云通信流量服务API产品,开发者无需替换
        $product = "Dyplsapi";

        //产品域名,开发者无需替换
        $domain = "dyplsapi.aliyuncs.com";
        $accessKeyId =  $KeyId; // AccessKeyId
        $accessKeySecret =  $KeySecret; // AccessKeySecret

        // 暂时不支持多Region
        $region = "cn-hangzhou";

        // 服务结点
        $endPointName = "cn-hangzhou";


        if(static::$acsClient == null) {

            //初始化acsClient,暂不支持region化
            $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);

            // 增加服务结点
            DefaultProfile::addEndpoint($endPointName, $region, $product, $domain);

            // 初始化AcsClient用于发起请求
            static::$acsClient = new DefaultAcsClient($profile);
        }
        return static::$acsClient;
    }

    /**
    //     * AXB绑定接口
    //     *
    //     * @return stdClass
    //     * @throws ClientException
    //     */
    public function bindAxb($NoA,$NoB,$Stime) {

        //组装请求对象-具体描述见控制台-文档部分内容
        $request = new BindAxbRequest();

        //必填:号池Key
        $request->setPoolKey("FC100000025240454");

        //必填:AXB关系中的A号码
        $request->setPhoneNoA($NoA);

        //必填:AXB关系中的B号码
        $request->setPhoneNoB($NoB);

        //可选:指定X号码进行绑定
//        $request->setPhoneNoX("17080032581");

        //可选:期望分配X号码归属的地市(省去地市后缀后的城市名称)
        $request->setExpectCity("武汉");

        //必填:绑定关系对应的失效时间-不能早于当前系统时间
        $request->setExpiration($Stime);

        //可选:是否需要录制音频-默认是false
        $request->setIsRecordingEnabled(false);

        //可选:外部业务自定义ID属性
      //  $request->setOutId($orderid);

        //hint 此处可能会抛出异常，注意catch
        $response = static::getAcsClient()->getAcsResponse($request);

        return $response;
    }


    /**
    //     * 解绑接口
    //     *
    //     * @return stdClass
    //     * @throws ClientException
    //     */
    public static function unbindSubscription($subsId, $secretNo) {

        //组装请求对象
        $request = new UnbindSubscriptionRequest();

        //必填:号池Key
        $request->setPoolKey("FC100000025240454");

        //必填:对应的产品类型
        $request->setProductType("AXB_170");

        //必填-分配的X小号-对应到绑定接口中返回的secretNo;
        $request->setSecretNo($secretNo);

        //必填-绑定关系对应的ID-对应到绑定接口中返回的subsId;
        $request->setSubsId($subsId);

        //hint 此处可能会抛出异常，注意catch
        $response = static::getAcsClient()->getAcsResponse($request);

        return $response;
    }

}
//
//    function aaa($aaa)
//    {
//        echo $aaa;
//    }
//    static $acsClient = null;
//
//
//
//
//    /**
//     * 取得AcsClient
//     *
//     * @return DefaultAcsClient
//     */
//    public static function getAcsClient() {
//        //产品名称:云通信流量服务API产品,开发者无需替换
//        $product = "Dyplsapi";
//
//        //产品域名,开发者无需替换
//        $domain = "dyplsapi.aliyuncs.com";
//        $accessKeyId = "LTAIRLaC2K6j0h5u"; // AccessKeyId
//        $accessKeySecret = "iiIxUbzqsQUFuwqN1nLbsBFDsBoqdN"; // AccessKeySecret
////        $accessKeyId =   self::$KeyId; // AccessKeyId
////        $accessKeySecret =   self::$KeySecret; // AccessKeySecret
//
//        // 暂时不支持多Region
//        $region = "cn-hangzhou";
//
//        // 服务结点
//        $endPointName = "cn-hangzhou";
//
//
//        if(static::$acsClient == null) {
//
//            //初始化acsClient,暂不支持region化
//            $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);
//
//            // 增加服务结点
//            DefaultProfile::addEndpoint($endPointName, $region, $product, $domain);
//
//            // 初始化AcsClient用于发起请求
//            static::$acsClient = new DefaultAcsClient($profile);
//        }
//        return static::$acsClient;
//    }
//
//
//    /**
//     * AXB绑定接口
//     *
//     * @return stdClass
//     * @throws ClientException
//     */
//    public function bindAxb($ccc) {
//
//    echo $ccc;exit;
//        //组装请求对象-具体描述见控制台-文档部分内容
//        $request = new BindAxbRequest();
//
//        //必填:号池Key
//        $request->setPoolKey("FC100000025240454");
//
//        //必填:AXB关系中的A号码
//        $request->setPhoneNoA('15994221307');
//
//        //必填:AXB关系中的B号码
//        $request->setPhoneNoB($NoB);
//
//        //可选:指定X号码进行绑定
////        $request->setPhoneNoX("17080032581");
//
//        //可选:期望分配X号码归属的地市(省去地市后缀后的城市名称)
//        $request->setExpectCity("武汉");
//
//        //必填:绑定关系对应的失效时间-不能早于当前系统时间
//        $request->setExpiration($Stime);
//
//        //可选:是否需要录制音频-默认是false
//        $request->setIsRecordingEnabled(false);
//
//        //可选:外部业务自定义ID属性
//        $request->setOutId($orderid);
//
//        //hint 此处可能会抛出异常，注意catch
//        $response = static::getAcsClient()->getAcsResponse($request);
//
//        return $response;
//    }
//
//    public static function bindAxn() {
//
//        //组装请求对象-具体描述见控制台-文档部分内容
//        $request = new BindAxnRequest();
//
//        //必填:号池Key
//        $request->setPoolKey("FC123456");
//
//        //必填:AXB关系中的A号码
//        $request->setPhoneNoA("15010101010");
//
//        //可选:AXN中A拨打X的时候转接到的默认的B号码,如果不需要则不设置
//        $request->setPhoneNoB("15020202020");
//
//        //可选:指定X号码进行选号
//        $request->setPhoneNoX("17000000000");
//
//        //可选:期望分配X号码归属的地市(省去地市后缀后的城市名称)
//        $request->setExpectCity("北京");
//
//        //必填:95中间号,NO_170代表选择使用170号码资源
//        $request->setNoType("NO_95");
//
//        //必填:绑定关系对应的失效时间-不能早于当前系统时间
//        $request->setExpiration("2017-09-08 17:00:00");
//
//        //可选:是否需要录制音频-默认是false
//        $request->setIsRecordingEnabled(false);
//
//        //可选:外部业务自定义ID属性
//        $request->setOutId("yourOutId");
//
//        //hint 此处可能会抛出异常，注意catch
//        $response = static::getAcsClient()->getAcsResponse($request);
//
//        return $response;
//    }
//    /**
//     * 解绑接口
//     *
//     * @return stdClass
//     * @throws ClientException
//     */
//    public static function unbindSubscription($subsId, $secretNo) {
//
//        //组装请求对象
//        $request = new UnbindSubscriptionRequest();
//
//        //必填:号池Key
//        $request->setPoolKey("FC123456");
//
//        //必填:对应的产品类型
//        $request->setProductType("AXB_170");
//
//        //必填-分配的X小号-对应到绑定接口中返回的secretNo;
//        $request->setSecretNo($secretNo);
//
//        //必填-绑定关系对应的ID-对应到绑定接口中返回的subsId;
//        $request->setSubsId($subsId);
//
//        //hint 此处可能会抛出异常，注意catch
//        $response = static::getAcsClient()->getAcsResponse($request);
//
//        return $response;
//    }
//
//    /**
//     * 更新绑定关系
//     *
//     * @return stdClass
//     * @throws ClientException
//     */
//    public static function updateSubscription() {
//
//        //组装请求对象
//        $request = new UpdateSubscriptionRequest();
//
//        //必填:号池Key
//        $request->setPoolKey("FC123456");
//
//        //必填: 您所选择的产品类型,目前支持AXB_170、AXN_170、AXN_95三种产品类型
//        $request->setProductType("AXB_170");
//
//        //必填: 创建绑定关系API接口所返回的订购关系ID
//        $request->setSubsId("123456");
//
//        //必填: 创建绑定关系API接口所返回的X号码
//        $request->setPhoneNoX("170000000");
//
//
//        // todo 以下操作三选一, 目前支持三种类型: updateNoA(修改A号码)、updateNoB(修改B号码)、updateExpire(更新绑定关系有效期)
//
//        // -------------------------------------------------------------------
//
//        // 1. 修改A号码示例：
//        // 必填: 操作类型
//        $request->setOperateType("updateNoA");
//
//        // OperateType为updateNoA时必选: 需要修改的A号码
//        $request->setPhoneNoA("150000000");
//
//        // -------------------------------------------------------------------
//
//        // 2. 修改B号码示例：
//        // 必填: 操作类型
//        // $request->setOperateType("updateNoB");
//
//        // OperateType为updateNoB时必选: 需要修改的B号码
//        // $request->setPhoneNoB("150000000");
//
//        // -------------------------------------------------------------------
//
//        // 3. 更新绑定关系有效期示例：
//        // 必填: 操作类型
//        // $request->setOperateType("updateExpire");
//
//        // OperateType为updateExpire时必选: 需要修改的绑定关系有效期
//        // $request->setExpiration("2017-09-05 12:00:00");
//
//        // -------------------------------------------------------------------
//
//        // 此处可能会抛出异常，注意catch
//        $response = static::getAcsClient()->getAcsResponse($request);
//
//        return $response;
//    }
//
//
//    /**
//     * 查询绑定关系详情
//     *
//     * @return stdClass
//     * @throws ClientException
//     */
//    public static function querySubscriptionDetail() {
//
//        //组装请求对象
//        $request = new QuerySubscriptionDetailRequest();
//
//        //必填:号池Key
//        $request->setPoolKey("FC123456");
//
//        //必填: 产品类型,目前一共支持三款产品AXB_170,AXN_170,AXN_95
//        $request->setProductType("AXB_170");
//
//        //必填: 绑定关系ID
//        $request->setSubsId("123456");
//
//        //必填: 绑定关系对应的X号码
//        $request->setPhoneNoX("170000000");
//
//        //hint 此处可能会抛出异常，注意catch
//        $response = static::getAcsClient()->getAcsResponse($request);
//
//        return $response;
//    }
//}
//
//// 调用示例：
//set_time_limit(0);
//header("Content-Type: text/plain; charset=utf-8");
//
//$axbResponse = Plsth::bindAxb();
//echo "AXB绑定(bindAxb)接口返回的结果:\n";
//echo "Code={$axbResponse->Code}\n";
//echo "Message={$axbResponse->Message}\n";
//echo "RequestId={$axbResponse->RequestId}\n";
//$axbSubsId = !empty($axbResponse->SecretBindDTO) ? $axbResponse->SecretBindDTO->SubsId : null;
//$axbSecretNo = !empty($axbResponse->SecretBindDTO) ? $axbResponse->SecretBindDTO->SecretNo : null;
//echo "subsId={$axbSubsId}\n";
//echo "secretNo={$axbSecretNo}\n";
//
//sleep(3);
////
////if ($axbResponse->Code === "OK") {
////    $unbind = PlsDemo::unbindSubscription($axbSubsId, $axbSecretNo);
////    echo "解绑(unbindSubscription)接口返回的结果\n";
////    echo "Code={$axbResponse->Code}\n";
////    echo "Message={$axbResponse->Message}\n";
////    echo "RequestId={$axbResponse->RequestId}\n";
////
////    sleep(3);
////}
////
//
////
////if($axnResponse->Code === "OK") {
////    $unbind = PlsDemo::unbindSubscription($axnSubsId, $axnSecretNo);
////    echo "解绑(unbindSubscription)接口返回的结果:\n";
////    echo "Code={$axnResponse->Code}\n";
////    echo "Message={$axnResponse->Message}\n";
////    echo "RequestId={$axnResponse->RequestId}\n";
////}
////
////sleep(3);
////
////$response = PlsDemo::updateSubscription();
////echo "更新绑定关系(UpdateSubscription)接口返回的结果:\n";
////print_r($response);
////
////
////sleep(3);
////
////sleep(3);
////
////$response = PlsDemo::querySubscriptionDetail();
////echo "查询绑定关系详情(QuerySubscriptionDetail)接口返回的结果:\n";
//print_r($response);
