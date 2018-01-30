<?php
/**
 *
 * 版权所有：恰维网络<qwadmin.qiawei.com>
 * 作    者：寒川<hanchuan@qiawei.com>
 * 日    期：2016-02-16
 * 版    本：1.0.0
 * 功能说明：用户反馈。
 *
 **/

namespace Qwadmin\Controller;

class FacebookController extends ComController
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
                a.id as aid,a.user_id,a.is_yd,a.add_time as addtime,a.handl_time,a.start";
        $count = $user->field("{$prefix}xm_member.id")
            ->order($order)
            ->join("{$prefix}xm_feedback as a ON {$prefix}xm_member.id = a.user_id")
            ->where($where)
            ->count();
        $list = $user->field($field_sql)
            ->order($order)
            ->join("{$prefix}xm_feedback as a ON {$prefix}xm_member.id = a.user_id")
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
     * 反馈跳转
     */
    public function edit()
    {
        $id = isset($_GET['aid']) ? intval($_GET['aid']) : false;
        if ($id) {
            $user = M('xm_member');
            $prefix = C('DB_PREFIX');
            $field_sql="{$prefix}xm_member.moblie,{$prefix}xm_member.sex,{$prefix}xm_member.id,
                a.id as aid,a.user_id,a.is_yd,a.add_time as addtime,a.handl_time,a.start,a.content,a.tu1,a.tu2,a.tu3,a.tape";
            $member = $user->field($field_sql)->join("{$prefix}xm_feedback as a ON {$prefix}xm_member.id = a.user_id")->where("a.id=$id")->find();
        } else {
            $this->error('参数错误！');
        }
        $this->assign('member', $member);
        $this->display('form');
    }

    /**
     * 反馈状态修改
     */
    public function update()
    {
        $id = isset($_POST['id']) ? intval($_POST['id']) : false;
        $uid = isset($_POST['uid']) ? intval($_POST['uid']) : false;
        $data['content'] = isset($_POST['content']) ? $_POST['content'] : '';
        $data['start'] = isset($_POST['start']) ? intval($_POST['start']) : 0;
        if ($id) {
            $data['handl_time'] = time();
            addlog('反馈操作成功，会员UID：' . $uid);
            M('xm_feedback')->data($data)->where("id=$id")->save();
        }else {
            $this->error('参数错误！');
        }
        $this->success('操作成功！');
    }

}