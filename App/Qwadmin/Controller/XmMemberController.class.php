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

class XmMemberController extends ComController
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
            $order = "{$prefix}xm_member.add_time asc";
        } elseif (($order == 'desc')) {
            $order = "{$prefix}xm_member.add_time desc";
        } else {
            $order = "{$prefix}xm_member.id desc";
        }
        if ($keyword <> '') {
            if ($field == 'o_username') {
                $where = "{$prefix}xm_member.o_username LIKE '%$keyword%'";
            }
            if ($field == 'moblie') {
                $where = "{$prefix}xm_member.moblie LIKE '%$keyword%'";
            }
        }

        $user = M('xm_member');
        $pagesize = 10;#每页数量
        $offset = $pagesize * ($p - 1);//计算记录偏移量
        $count = $user->field("{$prefix}xm_member.id")
            ->order($order)
            ->where($where)
            ->count();
        $list = $user->field("*")
            ->order($order)
            ->where($where)
            ->limit($offset . ',' . $pagesize)
            ->select();
        $page = new \Think\Page($count, $pagesize);
        $page = $page->show();
        $this->assign('list', $list);
        $this->assign('page', $page);
        $this->display();
    }

    public function del()
    {
        $uids = isset($_REQUEST['uids']) ? $_REQUEST['uids'] : false;
        //uid为1的禁止删除
        if ($uids == 1 or !$uids) {
            $this->error('参数错误！');
        }
        if (is_array($uids)) {
            foreach ($uids as $k => $v) {
                if ($v == 1) {//uid为1的禁止删除
                    unset($uids[$k]);
                }
                $uids[$k] = intval($v);
            }
            if (!$uids) {
                $this->error('参数错误！');
                $uids = implode(',', $uids);
            }
        }

        $map['id'] = array('in', $uids);
        if (M('xm_member')->where($map)->delete()) {
            addlog('删除会员UID：' . $uids);
            $this->success('恭喜，用户删除成功！');
        } else {
            $this->error('参数错误！');
        }
    }

    public function edit()
    {
        /*qxs 区域三级联动代码 sta*/
        if (I('post.pro_id')) {
            $parent_id['parent_id'] = I('post.pro_id');
            $region = M('qxs_region')->where($parent_id)->select();
            $opt = '<option value="0">请选择</option>';
            foreach($region as $key=>$val){
                $opt .= "<option value='{$val['region_id']}'>{$val['region_name']}</option>";
            }
            echo json_encode($opt);exit;
        }else {
            $parent_id['parent_id'] = 1;
            $region = M('qxs_region')->where($parent_id)->select();
            $this->assign('region',$region);
        }
        /*qxs 区域三级联动代码 sta*/

        $id = isset($_GET['id']) ? intval($_GET['id']) : false;
        if ($id) {
            $user = M('xm_member');
            $member = $user->field("*")->where("id=$id")->find();
            $count=0;
            //根据性别判断使用哪个字段
            if($member['sex']){
                $xm_order = M('xm_order')->field("xz_user_id")->select();
                foreach($xm_order as $key=>$val){
                    $hello = explode(',',$val['xz_user_id']);
                    for($index=0;$index<count($hello);$index++)
                    {
                        if($hello[$index]==$id){
                            $count++;
                        }
                    }
                }
            }else{
                $where ='user_id='.$id;
                $count = M('xm_order')->field("id")->where($where)->count();
                $count>10?$count=10:'';
            }
        } else {
            $this->error('参数错误！');
        }
        $member['women_grade']=$count<11?$count:0;//根据订单数量判断提成等级
        /*qxs 新用户页面编辑页面显示区域 sta*/
        if($member['province']){
            $province_name=M('qxs_region')->where("region_id=".$member['province'])->find();
            $city_name=$member['city']?M('qxs_region')->where("region_id=".$member['city'])->find():'';
            $district_name=$member['area']?M('qxs_region')->where("region_id=".$member['area'])->find():'';
            $member['province_name']=$province_name['region_name'];
            $member['city_name']=$city_name['region_name'];
            $member['district_name']=$district_name['region_name'];

            $member['province']?$city = M('qxs_region')->where("parent_id=".$member['province'])->select():'';
            $this->assign('city',$city);
            $member['city']?$district = M('qxs_region')->where("parent_id=".$member['city'])->select():'';
            $this->assign('district',$district);
        }
        /*qxs 新用户页面编辑页面显示区域 end*/
        $this->assign('member', $member);
        $this->display('form');
    }

    public function update($ajax = '')
    {
        $id = isset($_POST['id']) ? intval($_POST['id']) : false;
        $data['o_username'] = isset($_POST['o_username']) ? trim($_POST['o_username']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : false;
        if ($password) {
            $data['password'] = password($password);
        }
        $head = I('post.Head', '', 'strip_tags');
        $data['sex'] = isset($_POST['sex']) ? intval($_POST['sex']) : 0;
        $data['Head'] = $head ? $head : '';
        $data['birth'] = isset($_POST['birth']) ? strtotime($_POST['birth']) : 0;
        $data['moblie'] = isset($_POST['moblie']) ? trim($_POST['moblie']) : '';
        $data['gxqm'] = isset($_POST['gxqm']) ? trim($_POST['gxqm']) : '';
        $data['height'] = isset($_POST['height']) ? trim($_POST['height']) : '';
        $data['weight'] = isset($_POST['weight']) ? trim($_POST['weight']) : '';
        $data['code'] = isset($_POST['code']) ? trim($_POST['code']) : '';
        $data['video'] = I('post.sp', '', 'strip_tags');
        $data['is_disable'] = isset($_POST['is_disable']) ? intval($_POST['is_disable']) : 0;
        $data['is_audit'] = isset($_POST['is_audit']) ? intval($_POST['is_audit']) : 0;

        /*qxs 2017.8.3 添加用户页面区域功能 sta*/
        $data['province'] = isset($_POST['pro']) ? trim($_POST['pro']) : '';//省
        $data['city'] = isset($_POST['city']) ? trim($_POST['city']) : '';//市
        $data['area'] = isset($_POST['area']) ? trim($_POST['area']) : '';//区
        /*qxs 2017.8.3 添加用户页面区域功能 end*/
        if (!$id) {
            if (!$password) {
                $this->error('用户密码不能为空！');
            }
            $data['add_time'] = time();

            $id = M('xm_member')->data($data)->add();
            addlog('新增会员，会员UID：' . $id);
        } else {
            $data['modify_time'] = time();
            addlog('编辑会员信息，会员UID：' . $id);
            M('xm_member')->data($data)->where("id=$id")->save();
        }
        $this->success('操作成功！');
    }


    public function add()
    {
        $this->display('form');
    }


    /**
     * 实名认证列表
     *
     */
    public function autonym()
    {
        $p = isset($_GET['p']) ? intval($_GET['p']) : '1';
        $field = isset($_GET['field']) ? $_GET['field'] : '';
        $keyword = isset($_GET['keyword']) ? htmlentities($_GET['keyword']) : '';
        $order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
        $where = '';

        $prefix = C('DB_PREFIX');
        if ($order == 'asc') {
            $order = "a.audit_time asc";
        } elseif (($order == 'desc')) {
            $order = "a.audit_time desc";
        } else {
            $order = "a.id desc";
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
        $count = $user->field($field_sql)
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
        $this->display('autonym_index');
    }
    /**
        实名认证跳转
     **/
    public function autonym_edit()
    {
        $id = isset($_GET['aid']) ? intval($_GET['aid']) : false;
        if ($id) {
            $user = M('xm_member');
            $prefix = C('DB_PREFIX');
            $field_sql="{$prefix}xm_member.moblie,{$prefix}xm_member.sex,{$prefix}xm_member.id,
                a.id as aid,a.user_id,a.img1,a.img2,a.add_time as addtime,a.audit_time,a.is_audit";
            $member = $user->field($field_sql)->join("{$prefix}xm_autonym as a ON {$prefix}xm_member.id = a.user_id")->where("a.id=$id")->find();
        } else {
            $this->error('参数错误！');
        }
        $this->assign('member', $member);
        $this->display('autonym_form');
    }

    /**
        实名认证状态修改
     **/
    public function autonym_update($ajax = '')
    {
        $id = isset($_POST['id']) ? intval($_POST['id']) : false;
        $uid = isset($_POST['uid']) ? intval($_POST['uid']) : false;
        $data['is_audit'] = isset($_POST['is_audit']) ? intval($_POST['is_audit']) : 0;
        if ($id) {
            $data['audit_time'] = time();
            addlog('实名认证成功，会员UID：' . $uid);
            M('xm_autonym')->data($data)->where("id=$id")->save();
        }else {
            $this->error('参数错误！');
        }
        $this->success('操作成功！');
    }



}
