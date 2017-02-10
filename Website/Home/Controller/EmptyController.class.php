<?php
/**
 * Created by PhpStorm.
 * User: dingxiaowang
 * Date: 2016/5/21
 * Time: 9:51
 */

namespace Home\Controller;


use Think\Controller;

class EmptyController extends Controller
{
    public function _empty(){
        return $this->error("亲，您访问的页面不存在！");
    }
}