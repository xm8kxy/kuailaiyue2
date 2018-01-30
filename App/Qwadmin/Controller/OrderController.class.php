<?php
/**
 *
 * 版权所有：恰维网络<qwadmin.qiawei.com>
 * 作    者：寒川<hanchuan@qiawei.com>
 * 日    期：2016-01-20
 * 版    本：1.0.0
 * 功能说明：用户控制器。
 *
 **/

namespace Qwadmin\Controller;

class OrderController extends ComController
{
    public function index()
    {
        
        $p = isset($_GET['p']) ? intval($_GET['p']) : '1';
        $field = isset($_GET['field']) ? $_GET['field'] : '';
        $keyword = isset($_GET['keyword']) ? htmlentities($_GET['keyword']) : '';
        $order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
        $where = '';

        $prefix = C('DB_PREFIX');
        if ($order == 'asc') {
            $order = "time_add asc";
        } elseif (($order == 'desc')) {
            $order = "{$prefix}xm_order.time_add desc";
        } else {
            $order = "{$prefix}xm_order.time_add asc";
        }
        if ($keyword <> '') {
            header('Content-Type:text/html; charset=utf-8');
            if ($field == 'order_number') {
                $where = "{$prefix}xm_order.order_number LIKE '%$keyword%'";
            }
            if ($field == 'moblie') {
                $where = "{$prefix}xm_member.moblie LIKE '%$keyword%'";
            }
            if ($field == 'classify'){
                $where_title['title']  = array('like', "%$keyword%");
                $classify_id =Get_Find_data('xm_tryst_classify',$where_title);
                $where = "{$prefix}xm_order.classify=".$classify_id['id'];
            }
            if ($field == 'status'){
                $status_id=Get_StatusId($keyword);
                if(isset($status_id)&&$status_id){
                    $where = "{$prefix}xm_order.status in($status_id)";
                }else{
                    $this->error('该订单状态不存在！');
                }
            }
        }
        $user = M('xm_order');
        $pagesize = 10;#每页数量
        $offset = $pagesize * ($p - 1);//计算记录偏移量
        $count = $user->field("{$prefix}xm_order.id as oid,order_number,{$prefix}xm_member.moblie,appointment_dd,time_add,classify,status")
            ->order($order)
            ->join("{$prefix}xm_member ON {$prefix}xm_member.id = {$prefix}xm_order.user_id")
            ->where($where)
            ->count();
        $list = $user->field("{$prefix}xm_order.id as oid,order_number,{$prefix}xm_member.moblie,appointment_dd,time_add,classify,status")
            ->order($order)
            ->join("{$prefix}xm_member ON {$prefix}xm_member.id = {$prefix}xm_order.user_id")
            ->where($where)
            ->limit($offset . ',' . $pagesize)
            ->select();
        //$user->getLastSql();
        //订单状态和订单类型汉化
        foreach ($list as $key => $value) {
            if(!is_null($value['classify'])){
                $list[$key]['classify']=Get_TypeName($value['classify']);
            }
            if(!is_null($value['status'])){
                $list[$key]['status']=Get_StatusName($value['status']);
            }
        }
        $page = new \Think\Page($count, $pagesize);
        $page = $page->show();
        $this->assign('list', $list);
        $this->assign('page', $page);
        $this->display();
    }

    public function details()
    {

        $oid = isset($_GET['oid']) ? intval($_GET['oid']) : false;

        if ($oid) {
            $prefix = C('DB_PREFIX');
            $user = M('xm_order');
            $member = $user->field("*")->join("{$prefix}xm_member ON {$prefix}xm_member.id = {$prefix}xm_order.user_id")->where("{$prefix}xm_order.id=$oid")->find();

            //查询分类id对应的分类名称
            $member['classify']=Get_TypeName($member['classify']);
            //选择人逗号区分隔的用户id汉化为|分隔的选择人用户名
            $xz_user_id=$member['xz_user_id'];
            if($xz_user_id){
                $arr_moblie=M('xm_member')->field("moblie")->where("id in($xz_user_id)")->select();
                $co=0;
                $var_moblie='';
                foreach ($arr_moblie as $key => $value) {
                    $co>0?$co=' || ':$co='';
                    $var_moblie .=$co.$value['moblie'];
                    $co++;
                }
            }

                $member['xz_user_id']=$var_moblie;
            //订单状态编号汉化
            $member['status']=Get_StatusName($member['status']);

        }else {
            $this->error('参数错误！');
        }
        $this->assign('member', $member);
        $this->display('form');
    }


    public function update($ajax = '')
    {
        $id = isset($_GET['oid']) ? intval($_GET['oid']) : false;
        if ($id) {
            $data['status'] = 8;
            addlog('取消订单，订单ID：' . $id);
            M('xm_order')->data($data)->where("id=$id")->save();
        } else {
            $this->error('参数错误！');
        }
        $this->success('操作成功！');
    }

}
