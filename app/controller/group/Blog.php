<?php
//多级控制器示例
namespace app\controller\group;
use app\BaseController;

class Blog extends BaseController
{
    public function index()
    {
        return 'blog';
    }
}