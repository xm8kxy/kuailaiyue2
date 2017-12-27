<?php


namespace Home\Controller;

use Think\Controller;

class ComController extends Controller
{

    public function _initialize()
    {
        C(setting());
        /*
        $links = M('links')->limit(10)->order('o ASC')->select();
        $this->assign('links',$links);
        */
    }
}