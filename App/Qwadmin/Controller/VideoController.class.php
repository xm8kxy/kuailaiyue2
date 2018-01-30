<?php


namespace Qwadmin\Controller;

use Vendor\Tree;

class VideoController extends ComController
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
            $order = "a.add_time asc";
        } elseif (($order == 'desc')) {
            $order = "a.add_time desc";
        } else {
            $order = "a.id asc";
        }
        if ($keyword <> '') {

            if ($field == 'moblie') {
                $where = "{$prefix}xm_member.moblie LIKE '%$keyword%'";
            }
        }

        $user = M('xm_member');
        $pagesize = 10;#每页数量
        $offset = $pagesize * ($p - 1);//计算记录偏移量
        $field_sql="{$prefix}xm_member.moblie,{$prefix}xm_member.sex,
                a.id as aid,a.user_id,a.img1,a.img2,a.add_time as addtime,a.audit_time,a.is_audit";
        $count = $user->field("{$prefix}xm_member.id")
            ->order($order)
            ->join("{$prefix}xm_autonym as a ON {$prefix}xm_member.id = a.user_id")
            ->where($where)
            ->count();
        $list = $user->field($field_sql)
            ->order($order)
            ->join("{$prefix}xm_autonym as a ON {$prefix}xm_member.id = a.user_id")
            ->where($where)
            ->limit($offset . ',' . $pagesize)
            ->select();
        $page = new \Think\Page($count, $pagesize);
        $page = $page->show();
        $this->assign('list', $list);
        $this->assign('page', $page);
        $this->display();
    }

    /**
    实名认证跳转
     **/
    public function edit()
    {
        $id = isset($_GET['aid']) ? intval($_GET['aid']) : false;
        if ($id) {
            $user = M('xm_member');
            $prefix = C('DB_PREFIX');
            $field_sql="{$prefix}xm_member.moblie,{$prefix}xm_member.sex,{$prefix}xm_member.id,
                a.id as aid,a.user_id,{$prefix}xm_member.video,a.video2,a.video3,a.add_time as addtime,a.audit_time,a.is_audit,a.is_audit2,a.is_audit3";
            $member = $user->field($field_sql)->join("{$prefix}xm_autonym as a ON {$prefix}xm_member.id = a.user_id")->where("a.id=$id")->find();
        } else {
            $this->error('参数错误！');
        }
//        dump($member);exit;
        $this->assign('member', $member);
        $this->display('form');
    }

    /**
    实名认证状态修改
     **/
    public function update()
    {
        $id = isset($_POST['id']) ? intval($_POST['id']) : false;
        $uid = isset($_POST['uid']) ? intval($_POST['uid']) : false;
        $data['is_audit'] = isset($_POST['is_audit']) ? intval($_POST['is_audit']) : 0;
        if ($id) {
            M('xm_member')->data($data)->where("id=$uid")->save();

            $data['is_audit2'] = isset($_POST['is_audit2']) ? intval($_POST['is_audit2']) : 0;
            $data['is_audit3'] = isset($_POST['is_audit3']) ? intval($_POST['is_audit3']) : 0;
            $data['audit_time'] = time();
            addlog('实名认证成功，会员UID：' . $uid);
            M('xm_autonym')->data($data)->where("id=$id")->save();
        }else {
            $this->error('参数错误！');
        }
        $this->success('操作成功！');
    }

}