<?php

namespace app\controller;

use app\BaseController;
use think\facade\View;
use app\model\User;
use think\facade\Request;
use think\facade\Session;

class Login extends BaseController
{
    function index ()
    {
        return View::fetch('login');
    }

    function verify()
    {
        $username = Request::post('username');
        $password = Request::post('password');
        $query = User::where('username',$username)->find();
        if(!$query)
        {
            echo "<script> alert('用户名不存在！'); </script>";
            echo "<meta http-equiv='Refresh' content='1;URL=http://localhost:8000/login/index'>";
        }else if($query->passcode != $password)
        {
            echo "<script> alert('密码错误！'); </script>";
            echo "<meta http-equiv='Refresh' content='1;URL=http://localhost:8000/login/index'>";
        }else {
            Session::set('username',$username);
            echo "<script> alert('登录成功！'); </script>";
            echo "<meta http-equiv='Refresh' content='1;URL=http://localhost:8000/index/index'>";
        }
    }
}