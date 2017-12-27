<?php

namespace Home\Controller;
use Think\Upload;
use Vendor\Page;

class ApiController extends ComController
{
    public function _initialize()
    {
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Headers:Authorization');
     //   header("Access-Control-Allow-Methods: GET, POST, DELETE");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Headers: Content-Type, X-Requested-With, Cache-Control,Authorization");

    }
    public function index()
    {

        $this->display();
    }

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