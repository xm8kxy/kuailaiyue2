<?php


namespace Qwadmin\Controller;

class ContactController extends ComController
{

    //友情联系我们
    public function index()
    {

        $list = M('contact')->order('t asc')->select();
        $this->assign('list', $list);
        $this->display();
    }

    //新增联系我们
    public function add()
    {

        $this->display('form');
    }

    //新增或修改联系我们
    public function edit($id = null)
    {

        $id = intval($id);
        $link = M('contact')->where('id=' . $id)->find();
        $this->assign('link', $link);
        $this->display('form');
    }

    //删除联系我们
    public function del()
    {

        $ids = isset($_REQUEST['ids']) ? $_REQUEST['ids'] : false;
        if ($ids) {
            if (is_array($ids)) {
                $ids = implode(',', $ids);
                $map['id'] = array('in', $ids);
            } else {
                $map = 'id=' . $ids;
            }
            if (M('contact')->where($map)->delete()) {
                addlog('删除联系我们，ID：' . $ids);
                $this->success('恭喜，删除成功！');
            } else {
                $this->error('参数错误！');
            }
        } else {
            $this->error('参数错误！');
        }
    }

    //保存联系我们
    public function update($id = 0)
    {
        $id = intval($id);
        $data['name'] = I('post.name', '', 'strip_tags');
        if (!$data['name']) {
            $this->error('请填写名称！');
        }
        $data['phone'] = I('post.phone', '', 'strip_tags');
        $data['content'] = I('post.content', '', 'strip_tags');
        $data['t'] = time();


        if ($id) {
            M('contact')->data($data)->where('id=' . $id)->save();
            addlog('修改联系我们，ID：' . $id);
        } else {
            M('contact')->data($data)->add();
            addlog('新增联系我们');
        }

        $this->success('恭喜，操作成功！', U('index'));
    }
}