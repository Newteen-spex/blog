<?php

namespace app\controller;

use app\BaseController;

class Test extends BaseController
{
    public function index()
    {
        return '124' . ',当前方法名：' .$this->request->action() . '当前路径：' . $this->app->getBasePath();
    }

    public function hello($name)
    {
        return 'hello,' . $name;
    }
}