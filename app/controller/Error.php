<?php

namespace app\controller;

use app\BaseController;

class Error extends BaseController
{
    public function index()
    {
        return '当前控制器不存在！';
    }
}