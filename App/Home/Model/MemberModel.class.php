<?php

namespace Home\Model;
use Think\Model;
class MemberModel extends Model {
	
	  public function findRelease($where,$field){
    
        $result = $this->where($where)->field($field)->find();
        return $result;
    }
}