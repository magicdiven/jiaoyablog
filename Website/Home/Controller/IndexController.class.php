<?php

namespace Home\Controller;

use Think\Controller;

class IndexController extends Controller
{
    public function _initialize()
    {
        // utf-8编码
        header('Content-Type: text/html; charset=utf-8');
    }

    public function index()
    {
        $_SESSION['current_account']['id'];

        $this->display();
    }
}