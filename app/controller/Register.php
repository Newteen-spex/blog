<?php

namespace app\controller;

use app\BaseController;
use http\Header;
use think\facade\View;
use app\model\User;
use think\facade\Request;

class Register extends BaseController
{
    function index()
    {
        return View::fetch('register');
    }

    function insert()
    {
        User::create([
            'username'      =>      Request::post('username'),
            'passcode'      =>      Request::post('password'),
            'email'         =>      Request::post('email'),
            'student_id'    =>      Request::post('id_card'),
            'realname'      =>      Request::post('real_name')
        ]);
        echo "<script> alert('注册成功！确认后返回主页面'); </script>";
        echo "<meta http-equiv='Refresh' content='1;URL=http://localhost:8000/index/index'>";
    }
}