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

class CommentController extends ComController
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
            $order = "{$prefix}xm_comment.id asc";
        } elseif (($order == 'desc')) {
            $order = "{$prefix}xm_comment.id desc";
        } else {
            $order = "{$prefix}xm_comment.id asc";
        }
        if ($keyword <> '') {
            if ($field == 'moblie') {
                $where = "m.moblie LIKE '%$keyword%'";
            }
            if ($field == 'id') {
                $where = "{$prefix}xm_comment.id LIKE '%$keyword%'";
            }
        }

        $user = M('xm_comment');
        $pagesize = 10;#每页数量
        $offset = $pagesize * ($p - 1);//计算记录偏移量
        $count = $user->field("{$prefix}xm_comment.id")
            ->join("{$prefix}xm_member as m ON {$prefix}xm_comment.user_id = m.id")
            ->where($where)
            ->count();
        $list = $user->field("m.moblie,{$prefix}xm_comment.id,{$prefix}xm_comment.offline_type,{$prefix}xm_comment.offline_type,{$prefix}xm_comment.comment_type,{$prefix}xm_comment.content")
            ->order($order)
            ->join("{$prefix}xm_member as m ON {$prefix}xm_comment.user_id = m.id")
            ->where($where)
            ->limit($offset . ',' . $pagesize)
            ->select();
        $page = new \Think\Page($count, $pagesize);
        $page = $page->show();
        $this->assign('list', $list);
        $this->assign('page', $page);
        $this->display();
    }


    public function edit()
    {
        $id = isset($_GET['id']) ? intval($_GET['id']) : false;
        if ($id) {
            $user = M('xm_comment');
            $member = $user->field("*")->where("id=$id")->find();
        } else {
            $this->error('参数错误！');
        }
        $this->assign('member', $member);
        $this->display('form');
    }

    public function update($ajax = '')
    {
        $id = isset($_POST['id']) ? intval($_POST['id']) : false;
        $data['offline_type'] = isset($_POST['offline_type']) ? intval($_POST['offline_type']) : 0;
        $data['comment_type'] = isset($_POST['comment_type']) ? intval($_POST['comment_type']) : 0;
        $data['content'] = isset($_POST['content']) ? $_POST['content'] : '';


        if ($id) {
            addlog('编辑用户评论信息，评论ID：' . $id);
            M('xm_comment')->data($data)->where("id=$id")->save();
        } else {
            $this->error('参数错误！');
        }
        $this->success('操作成功！');
    }


}
