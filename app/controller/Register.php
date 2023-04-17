<?php

namespace app\controller;

use app\BaseController;
use http\Header;
use think\facade\View;
use app\model\User;
use app\model\Identity;
use think\facade\Request;

class Register extends BaseController
{
    function index()
    {
        return View::fetch('register');
    }

    function insert()
    {
        $username = Request::post('username');
        $passcode = Request::post('password');
        $confirmPasscode = Request::post('confirm_password');
        $email = Request::post('email');
        $studentId = Request::post('id_card');
        $realName = Request::post('real_name');

        //确认密码的一致
        if($passcode != $confirmPasscode){
            echo "<script> alert('两次密码输入不一致！'); </script>";
            echo "<meta http-equiv='Refresh' content='1;URL=http://localhost:8000/register/index'>";
            return;
        }

        //保证用户名唯一
        $userNameQuery = User::where('username', $username)->find();
        if($userNameQuery){
            echo "<script> alert('用户名已被注册！'); </script>";
            echo "<meta http-equiv='Refresh' content='1;URL=http://localhost:8000/register/index'>";
            return;
        }

        //保证学号唯一
        $IdQuery = User::where('student_id', $studentId)->find();
        if($IdQuery){
            echo "<script> alert('学号已被注册！'); </script>";
            echo "<meta http-equiv='Refresh' content='1;URL=http://localhost:8000/register/index'>";
            return;
        }

        //最后一步，学籍验证,从只读表（Identity）中查验
        $queryIdentity = Identity::where('student_id', $studentId)->find();
        //如果不存在该学号（学籍无记录）
        if(!$queryIdentity){
            echo "<script> alert('注册学号不存在！'); </script>";
            echo "<meta http-equiv='Refresh' content='1;URL=http://localhost:8000/register/index'>";
            return;
        }
        //若学号存在，看是否存在与学号相匹配的学生姓名
        if($queryIdentity->name != $realName){
            echo "<script> alert('姓名与学号不匹配，请填写真实姓名！'); </script>";
            echo "<meta http-equiv='Refresh' content='1;URL=http://localhost:8000/register/index'>";
            return;
        }

        if(!$userNameQuery && !$IdQuery){
            User::create([
                'username'      =>      $username,
                'passcode'      =>      $passcode,
                'email'         =>      $email,
                'student_id'    =>      $studentId,
                'realname'      =>      $realName
            ]);
            echo "<script> alert('注册成功！确认后返回主页面'); </script>";
            echo "<meta http-equiv='Refresh' content='1;URL=http://localhost:8000/index/index'>";
        }
    }
}