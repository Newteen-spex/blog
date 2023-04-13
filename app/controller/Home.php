<?php

namespace app\controller;

use app\BaseController;
use app\model\Star;
use think\facade\View;
use think\facade\Session;
use app\model\User;
use app\model\Info;
use app\model\Artical;
use think\facade\Request;
use think\response\Redirect;

class Home extends BaseController
{
    function index ($nav_alt = '欢迎回来, [用户名]', $logout = '', $userName = '[Username]',
                    $email = '[Email]', $idNumber = '[id_number]', $school = '[School]',
                    $major = '[Major]', $selfIntro = '[self_intro]')
    {
        $username = Session::get('username');
        if(!$username){
            return redirect('http://localhost:8000/login/index');
        }
        else{
            $query = User::Where('username',$username)->find();
            $email = $query->email;
            $idNumber = $query->student_id;

            $infoQuery = $query->info()->find();
            if($infoQuery){
                $school = $infoQuery->school;
                $major = $infoQuery->major;
                $selfIntro = $infoQuery->introduce;
            }

            $textList = $query->artical()->order('publish_time','desc')->select();
            $starList1 = $query->star()->order('star_time', 'desc')->select();
            $starList2 = array();
            foreach($starList1 as $key=>$obj)
            {
                $query = Artical::find($obj->text_id);
                Array_push($starList2, $query);
            }


            $nav_alt = '欢迎回来, '.$username;
            $logout = '退出登录';
            $userName = $username;
            $avatar = $username.'.jpg';
        }
        return View::fetch('home',[
            'avatar'        =>      $avatar,
            'nav_alt'       =>      $nav_alt,
            'logout'        =>      $logout,
            'userName'      =>      $userName,
            'email'         =>      $email,
            'idNumber'      =>      $idNumber,
            'school'        =>      $school,
            'major'         =>      $major,
            'selfIntro'     =>      $selfIntro,
            'textList'      =>      $textList,
            'starList'      =>      $starList2

        ]);
    }


    //取消收藏
    function unStar($textId)
    {
        $username = Session::get('username');
        $userId = User::where('username', $username)->find()->user_id;
        $query = Star::where('user_id', $userId)
                        ->where('text_id', $textId)->find();
        $query->delete();

        $queryText = Artical::find($textId);
        --$queryText->star_count;
        $queryText->save();

        return redirect('http://localhost:8000/home/index');
    }

    function editAvatar()
    {
        $username = Session::get('username');
        $file = request()->file('edit_avatar');
        try{
            validate(['image'=>'fileExt:jpg,png,gif'])->check(['image'=>$file]);
            $saveFile = \think\facade\Filesystem::disk('public')->putFileAs('images', $file, $username.'.jpg');
            echo "<script> alert('头像更改成功！'); </script>";
        }catch (\think\exception\ValidateException $e)
        {
            echo $e->getMessage().'，请选择以jpg,png,gif为后缀的图片文件';

        }
        echo "<meta http-equiv='Refresh' content='2;URL=http://localhost:8000/home/index'>";
    } 


    function others($authorName)
    {
        if($authorName == Session::get('username'))
        {
            return redirect('http://localhost:8000/home/index');
        }else{
            $query = User::where('username',$authorName)->find();
            $authorStudentId = $query->student_id;
            $authorRegisterTime = $query->create_time;
            $infoQuery = $query->info()->find();
            $authorIntroduce = $infoQuery->introduce;
            $articalList = $query->artical()->order('publish_time', 'desc')->select();


            return View::fetch('others', [
                'authorName'       =>       $authorName,
                'authorStudentId'  =>       $authorStudentId,
                'authorRegisterTime'    =>  $authorRegisterTime,
                'authorIntroduce'  =>       $authorIntroduce,
                'articalList'      =>       $articalList
            ]);
        }

    }

    function logout()
    {
        sleep(1);
        return redirect('http://localhost:8000/index/index')->with('username', null);
    }

    function updateInfo()
    {
        $username = Session::get('username');

        $email = Request::post('email');
        $idNumber = Request::post('id_number');
        $school = Request::post('school');
        $major = Request::post('major');
        $selfIntro = Request::post('self_intro');
        $query = User::where('username',$username)->find();
        $infoQuery = $query->info()->find();
        if(!$infoQuery)
        {
            Info::create([
                'user_id'       =>      $query->user_id,
                'school'        =>      $school,
                'introduce'     =>      $selfIntro,
                'major'         =>      $major
            ]);
        }else{
            $query->info()->update([
                'school'        =>      $school,
                'introduce'     =>      $selfIntro,
                'major'         =>      $major
            ]);
        }
        User::where('username',$username)->update([
                'email'         =>      $email,
                'student_id'    =>      $idNumber
        ]);
        return redirect('http://localhost:8000/home/index');
    }
}