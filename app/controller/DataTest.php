<?php

namespace app\controller;

use app\model\User;
use think\facade\Db;

class DataTest
{
    public function index()
    {
        $user = Db::connect('mysql')->table('user')->select();
        //$user是一个object类型
        return $user;
    }

    public function getLiuyan()
    {
        $liuyan = User::select();
        return $liuyan;
    }
}