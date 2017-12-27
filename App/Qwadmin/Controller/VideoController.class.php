<?php


namespace Qwadmin\Controller;

use Vendor\Tree;

class VideoController extends ComController
{

    public function lists($sid = 0, $p = 1)
    {

        $p = intval($p) > 0 ? $p : 1;

        $article = M('video');
        $pagesize = 20;#每页数量
        $offset = $pagesize * ($p - 1);//计算记录偏移量
        $prefix = C('DB_PREFIX');
        $statue = isset($_GET['statue']) ? $_GET['statue'] : '';
        $keyword = isset($_GET['keyword']) ? htmlentities($_GET['keyword']) : '';
        $order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
        $where = '1 = 1 ';

        /*qxs 2017.8.2 文章列表显示添加条件 sta*/
      //  $where .="and {$prefix}article.is_del = 0 ";
        /*qxs 2017.8.2 文章列表显示添加条件 end*/

        /*qxs 2017.8.4 文章列表根据权限显示 sta*/
        session('uid')>1 ? $where .="and {$prefix}video.user_id=".session('uid')." " :'';
        /*qxs 2017.8.4 文章列表根据权限显示 end*/
    if($statue=='3') {
        $where .= "and {$prefix}video.statue = 0 ";
    } else if ($statue) {
            $sids_array = category_get_sons($statue);
            $sids = implode(',',$sids_array);
           $where .= "and {$prefix}video.statue in ($sids) ";
        }

        if ($keyword) {
            $where .= "and {$prefix}video.title like '%{$keyword}%' ";
        }
        //默认按照时间降序
        $orderby = "t desc";
        if ($order == "asc") {

            $orderby = "t asc";
        }
        //获取栏目分类
//        $category = M('video')->field('id,pid,name')->order('o asc')->select();
//        $tree = new Tree($category);
//        $str = "<option value=\$id \$selected>\$spacer\$name</option>"; //生成的形式
//        $category = $tree->get_tree(0, $str, $sid);
//        $this->assign('category', $category);//导航


        $count = $article->where($where)->count();
        $list = $article->field("{$prefix}video.*")->where($where)->order($orderby)->limit($offset . ',' . $pagesize)->select();

        $page = new \Think\Page($count, $pagesize);
        $page = $page->show();

//        $list="";
        $this->assign('list', $list);
        $this->assign('page', $page);
        $this->display();

    }



    public function index($sid = 0, $p = 1)
    {

        $this->assign('uid', session('uid'));
        $this->display();
    }



    public function edit($aid)
    {


        $aid = intval($aid);
        $article = M('Video')->where('aid=' . $aid)->find();
        if ($article) {


            $this->assign('article', $article);
        } else {
            $this->error('参数错误！');
        }
        $this->display('form');
    }

    public function update($aid = 0)
    {

        $aid = intval($aid);

        $data['user_id'] = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

        $data['content'] = isset($_POST['content']) ? $_POST['content'] : false;
        $data['statue'] = isset($_POST['statue']) ? intval($_POST['statue']) : 0;

        $data['t'] = time();

        $data['urls'] = I('post.urls', '', 'strip_tags');
        $data['url'] = I('post.url', '', 'strip_tags');

        $data['title'] = I('post.title', '', 'strip_tags');
        $data['fabulous'] = isset($_POST['fabulous']) ? intval($_POST['fabulous']) : 0;
        $data['comment_number'] = isset($_POST['comment_number']) ? intval($_POST['comment_number']) : 0;
        $data['browse_count'] = isset($_POST['browse_count']) ? intval($_POST['browse_count']) : 0;
        $data['fabulous'] = isset($_POST['fabulous']) ? intval($_POST['fabulous']) : 0;


        if (!$data['content'] or !$data['urls']  or !$data['url'] or !$data['title']) {
            $this->error('警告！必填项目。');
        }
        if ($aid) {
            M('Video')->data($data)->where('aid=' . $aid)->save();
            addlog('编辑视频，AID：' . $aid);
            $this->success('恭喜！视频编辑成功！');
        }
    }


    public function del()
    {
        $aids = isset($_REQUEST['aids']) ? $_REQUEST['aids'] : false;

        if ($aids) {

                $map = 'aid=' . $aids;


            $xmpat=C("xmpat");
        $dates = M('Video')->where($map)->find();
       // print_r($dates['url']);exit;
            $file =   $xmpat.$dates['url'];
            if (!unlink($file))
            {
                $this->error('删除失败！');
              //  echo ("Error deleting $file");
            }
            else
            {

                if (M('Video')->where($map)->delete()) {
                    addlog('删除视频，AID：' . $aids);


                    $this->success('恭喜，视频删除成功！');
                } else {
                    $this->error('参数错误！');
                }

            }

        } else {
            $this->error('参数错误！');
        }

    }
}