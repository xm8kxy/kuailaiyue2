<?php
/**
 *
 * 版权所有：恰维网络<qwadmin.qiawei.com>
 * 作    者：寒川<hanchuan@qiawei.com>
 * 日    期：2016-01-21
 * 版    本：1.0.0
 * 功能说明：焦点图。
 *
 **/

namespace Qwadmin\Controller;

class FlashController extends ComController
{

    //flash焦点图
    public function index($p=1)
    {
        $p = intval(10) > 0 ?$p : 1;
        $pagesize = 20;#每页数量
        $offset = $pagesize * ($p - 1);//计算记录偏移量
        $cc=$offset . ',' . $pagesize;

        $list = M('flash')->order('o asc')->limit($cc)->select();


        $count = M('flash')->where("1=1")->count();

        $page = new \Think\Page($count, $pagesize);
        $page = $page->show();
        $this->assign('list', $list);
        $this->assign('page', $page);

        $this->display();
    }

    //新增焦点图
    public function add()
    {
//        $category = M('category')->field('id,pid,name')->order('o asc')->select();
//        $tree = new Tree($category);
//        $str = "<option value=\$id \$selected>\$spacer\$name</option>"; //生成的形式
//        $category = $tree->get_tree(0, $str, 0);
//        $this->assign('category', $category);//导航

        $this->display('form');
    }

    //修改焦点图
    public function edit($id = null)
    {

        $id = intval($id);
        $flash = M('flash')->where('id=' . $id)->find();
        $this->assign('flash', $flash);
        $this->display('form');
    }

    //删除焦点图
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
            if (M('flash')->where($map)->delete()) {
                addlog('删除焦点图，ID：' . $ids);
                $this->success('恭喜，删除成功！');
            } else {
                $this->error('参数错误！');
            }
        } else {
            $this->error('参数错误！');
        }
    }

    //保存焦点图
    public function update($id = 0)
    {
        $id = intval($id);
        $data['title'] = I('post.title', '', 'strip_tags');
        if (!$data['title']) {
            $this->error('请填写标题！');
        }
        $data['url'] = I('post.url', '', 'strip_tags');
        $data['o'] = I('post.o', '', 'strip_tags');
        $data['pic'] = I('post.pic', '', 'strip_tags');
        $data['sid'] = I('post.sid', '', 'strip_tags');
        if ($data['pic'] == '') {
            $this->error('请上传图片！');
        }
        if ($id) {
            M('flash')->data($data)->where('id=' . $id)->save();
            addlog('修改焦点图，ID：' . $id);
        } else {
            M('flash')->data($data)->add();
            addlog('新增焦点图');
        }

        $this->success('恭喜，操作成功！', U('index'));
    }
}