<?php
/**
 * 隐私保护通话方法
 */
namespace Home\Controller;
use Aliyunphp\api_demo\Plsth;

trait Drive {
    public $carName = 'trait';
    public function driving() {
        echo "driving {$this->carName}\n";
    }
}

class XmcomprivacymobileController extends ComController
{

    //绑定测试
    public static function index()
    {


        $accessKeyId=C('accessKeyId');
        $accessKeySecret=C('accessKeySecret');
        $h=new Plsth($accessKeyId,$accessKeySecret);
        $NoA='15994221307';
        //  $NoB='15071278668';
        $NoB='17771879069';
        // $NoB='18611644130';
        $Stime = '2018-1-25 16:40:26';
        $orderid ='10';
        $axbResponse=  $h->bindAxb( $NoA, $NoB, $Stime, $orderid);
        print_r($axbResponse);
     //   $this->display();
     //   header("Location: localhost/index.php/Qwadmin/login/index.html");
    }

    //绑定手机号
    //$NoA='15994221307';
    // $NoB='15071278668';
    // $NoB='17771879069';
    // $NoB='18611644130';
    //$Stime = '2018-1-24 16:40:26';
    public static function bdshouji($NoA, $NoB, $Stime, $orderid)
    {
        $accessKeyId=C('accessKeyId');
        $accessKeySecret=C('accessKeySecret');
        $h=new Plsth($accessKeyId,$accessKeySecret);
        $axbResponse=  $h->bindAxb( $NoA, $NoB, $Stime);
        if($axbResponse->Code === "OK"){
            $axbSubsId = !empty($axbResponse->SecretBindDTO) ? $axbResponse->SecretBindDTO->SubsId : null;
            $axbSecretNo = !empty($axbResponse->SecretBindDTO) ? $axbResponse->SecretBindDTO->SecretNo : null;
            $data['order_id']=$orderid;
            $data['a_moblie']=$NoA;
            $data['b_moblie']=$NoB;
            $data['stime']=$Stime;
            $data['subsId']= $axbSubsId;
            $data['secretNo']= $axbSecretNo;
            $am = M("XmAppleM"); // 实例化User对象
            $amdata=$am->add($data);
            if( $amdata){
                return $amdata;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }


//解除绑定
    public static function jcshouji($orderid)
    {
        $mod = M("XmAppleM");
        $where['order_id'] = $orderid;
        $data = $mod->where($where)->select();
        if ($data) {
            $accessKeyId = C('accessKeyId');
            $accessKeySecret = C('accessKeySecret');
            $h = new Plsth($accessKeyId, $accessKeySecret);
            foreach ($data as $value) {
                $subsId = $value['subsid'];
                $secretNo = $value['secretno'];
                if (!empty($subsId) || !empty($subsId)) {
                    $axbResponse = $h->unbindSubscription($subsId, $secretNo);
                    if ($axbResponse->Code === "OK") {
                        $wheres['subsId'] = $subsId;
                        $wheres['secretNo'] = $secretNo;
                        $datasc = $mod->where($wheres)->delete();
                    }
                }
            }
        } else {
            return false;
        }
    }




}