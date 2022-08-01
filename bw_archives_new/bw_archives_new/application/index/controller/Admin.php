<?php
/**
 * Created by PhpStorm.
 * User: 25352
 * Date: 2018/5/14
 * Time: 10:05
 */
namespace app\index\controller;
use app\index\model\BorrowModel;
use app\index\model\BranchModel;
use app\index\model\FileJoinModel;
use app\index\model\FileModel;
use app\index\model\FilewithinModel;
use app\index\model\PostModel;
use Symfony\Component\VarDumper\Cloner\Data;
use think;
use think\Db;
use think\Request;
use think\Route;
use app\index\controller\Base;
use TCPDF;

use app\index\model\StudentsModel;
use app\index\model\ChangeModel;
use app\index\model\DepartmentModel;
use app\index\model\MajorModel;
use app\index\model\CategoryModel;
use app\index\model\UserModel;
use app\index\model\ProCityModel;

use OSS\OssClient;
use OSS\Core\OssException;

class Admin extends Base
{


    //后台登录页
    public function admin_index()
    {

        return view('/index/admin_index');
    }

    //设置档案类型
    public function setType()
    {
        $data = input('param.');
        session("type",$data['type']);

        $re_arr     = array();
        $re_arr['code'] = 200;
        $re_arr['msg'] = "设置成功";
        echo json_encode($re_arr);
    }

    //获取专业
    public function getZy()
    {
        $data  = input('param.');
        $m = new MajorModel();
        $maj = $m->getMajorList($data['id']);
        return $maj;
    }


    //获取专业
    public function getYx()
    {

        $data   = input('param.');

        $year   = $data['year'];
        $obj    = new FileModel();
        $d      = new DepartmentModel();
        $dep    = $d->getDepartList();
        foreach ($dep AS &$value)
        {
            $num    = $obj->getCountByYId($year,$value['id']);
            $value['name']  = $value['name']."(".$num."人)";
        }
        return $dep;
    }
    //修改数据
    public function updateDate(){
        $data=input('param.');
        $id=$data['id'];
        unset($data['id']);

        $m = new PostModel();
        $m->updateDateAll($data,$id);
        $this->success("修改成功",url("/system/addpost"));
    }


    //管理员添加
    public function savePost()
    {
        $data   = input('param.');

        $pid    = $data['province'];

        $m = new ProCityModel();
        $p_info     = $m->getOneById($pid);
        $data['province'] = $p_info['province'];

        $m = new PostModel();
        $m->createData($data);
        $this->success("添加成功",url("/system/addpost"));
    }

    //获取专业
    public function getCategoryList()
    {
        $data  = input('param.');
        $m = new CategoryModel();
        $maj = $m->getList($data['id']);
        return $maj;
    }

    //获取市区
    public function getCity()
    {
        $data  = input('param.');
        $m = new ProCityModel();
        $maj = $m->getCityList($data['id']);
        return $maj;
    }
    //院系试图
    public function setDepart()
    {
        return view('depart');
    }  //
    //园区
    public function setHouse()
    {
        return view('house');
    }

    //获取院系
    public function getDepart()
    {
        $m = new DepartmentModel();
        $maj = $m->getAllDepartList();

        foreach ($maj as &$v)
        {
            if($v['status'] == 1)
            {
                $v['status']    = '<a style="color: #2ca02c" lay-event="del">可用，点击禁用</a>';
            }else{
                $v['status']    = '<a style="color: #c67605"  lay-event="nodel">已禁用，点击启动</a>';"";
            }
        }
        $result = array("code"=>0,"msg"=>"","data"=>$maj);
        echo json_encode($result);

    }
    //设置档案类型
    public function updateDepart()
    {
        $data   = input('param.');
        $type   = $data['type'];
        $id   = $data['id'];

        $save_arr   = array();
        switch ($type)
        {
            case 1:
                $save_arr['name']   = $data['value'];
                break;
            case 2:
                $save_arr['status']   = 0;

                break;
            case 3:
                $save_arr['status']   = 1;

                break;
            case 4:
                $save_arr['name']           = $data['value'];
                $save_arr['status']         = 1;

                break;
        }
        $m = new DepartmentModel();
        if($type != 4)
        {
            $m->updateData($save_arr,$id);
        }else{
            $m->createData($save_arr);
        }

        $re_arr     = array();
        $re_arr['code'] = 200;
        $re_arr['msg'] = "设置成功";
        echo json_encode($re_arr);
    }

    //院系专业试图
    public function setMajor()
    {
        $data   = input('param.');
        $this->assign('id',$data['id']);

        return view('major');
    }
    //获取院系专业
    public function getMajor()
    {

        $data   = input('param.');
        $m      = new MajorModel();
        $maj    = $m->getAllList($data['id']);

        foreach ($maj as &$v)
        {
            if($v['status'] == 1)
            {
                $v['status']    = '<a style="color: #2ca02c" lay-event="del">可用，点击禁用</a>';
            }else{
                $v['status']    = '<a style="color: #c67605"  lay-event="nodel">已禁用，点击启动</a>';"";
            }
        }
        $result = array("code"=>0,"msg"=>"","data"=>$maj);
        echo json_encode($result);

    }
    //设置档案类型
    public function updateMajor()
    {
        $data   = input('param.');
        $type   = $data['type'];
        $id     = $data['id'];

        $save_arr   = array();
        switch ($type)
        {
            case 1:
                $save_arr['name']   = $data['value'];
                break;
            case 2:
                $save_arr['status']   = 0;

                break;
            case 3:
                $save_arr['status']   = 1;

                break;
            case 4:
                $did    = $data['did'];
                $save_arr['name']           = $data['value'];
                $save_arr['status']         = 1;
                $save_arr['department_id']  = $did;

                break;
        }
        $m = new MajorModel();
        if($type != 4)
        {
            $m->updateData($save_arr,$id);
        }else{
            $m->createData($save_arr);
        }

        $re_arr     = array();
        $re_arr['code'] = 200;
        $re_arr['msg'] = "设置成功";
        echo json_encode($re_arr);
    }
    //获取编码规则
    public function getCodeRule()
    {

        return view('coderule');
    }
    //院系试图
    public function category()
    {
        $data   = input('param.');

        if(!isset($data['id']))
            $data['id'] = 0;

        $this->assign('id',$data['id']);
        return view('category');
    }
    //获取院系
    public function getCategory()
    {

        $data   = input('param.');
        $id     = $data['id'];

        $m = new CategoryModel();
        $maj = $m->getAllList($id);

        foreach ($maj as &$v)
        {
            if($v['status'] == 1)
            {
                $v['status']    = '<a style="color: #2ca02c" lay-event="del">可用，点击禁用</a>';
            }else{
                $v['status']    = '<a style="color: #c67605"  lay-event="nodel">已禁用，点击启动</a>';"";
            }
        }
        $result = array("code"=>0,"msg"=>"","data"=>$maj);
        echo json_encode($result);

    }
    //设置档案类型
    public function updateCategory()
    {
        $data   = input('param.');
        $type   = $data['type'];

        $id     = $data['id'];

        $save_arr   = array();
        switch ($type)
        {
            case 1:
                $save_arr['name']   = $data['value'];
                break;
            case 2:
                $save_arr['status']   = 0;

                break;
            case 3:
                $save_arr['status']   = 1;

                break;
            case 4:

                $fid     = $data['fid'];
                $save_arr['name']           = $data['value'];
                $save_arr['status']         = 1;
                $save_arr['branch_id']      = $fid;

                break;
        }
        $m = new CategoryModel();

        if($type != 4)
        {
            $m->updateData($save_arr,$id);
        }else{
            $m->createData($save_arr);
        }


        $re_arr     = array();
        $re_arr['code'] = 200;
        $re_arr['msg'] = "设置成功";
        echo json_encode($re_arr);
    }


    //管理员添加
    public function addUser()
    {
        return view('adduser');
    }
    //管理员添加
    public function addpart()
    {
        return view('addpart');
    }//管理员添加
    public function addcode()
    {
        return view('addcode');

    }
    public function addhouse()
    {
        return view('addhouse');

    }
    public function addcate()
    {
        return view('addcate');
    }

    //管理员添加
    public function saveUser()
    {
        $data   = input('param.');

        if(!isset($data['status']))
        {
            $data['status'] = 0;
        }
        if(isset($data['permissions']))
        {
            $data['permissions']    = implode(",",$data['permissions']);
        }
        if(isset($data['password']))
        {
            $data['password']   = md5($data['password']);
        }
        $m = new UserModel();
        $m->createData($data);
        $this->success("添加成功",url("/system/adduser"));
    }
    //传感器添加
    public function savepart()
    {
        $data   = input('param.');

        if(!isset($data['is_delete']))
        {
            $data['is_delete'] = '关闭';
        }else{
            $data['is_delete'] = '正常';
        }

        $m = new BorrowModel();
        $f=  $m->getInfoByGrade($data['sensor_name'],$data['sensor_ip']);
        if(empty($f)){
            $m->createData($data);
        }
        $this->success("添加成功",url("/system/addpart"));
    }

    //大棚的添加
   public function savecate()
    {
        $data   = input('param.');

        if(!isset($data['status']))
        {
            $data['status'] = '关闭';
        }else{
            $data['status'] = '正常';
        }

        $m = new BranchModel();
        $f=  $m->getBranch($data['green_house_name']);
        if(empty($f)){
            $m->createData($data);
        }
        $this->success("添加成功",url("/system/addcate"));
    }

    //园区的添加
   public function savehouse()
    {
        $data   = input('param.');

        if(!isset($data['status']))
        {
            $data['status'] = '关闭';
        }else{
            $data['status'] = '正常';
        }
        $m = new FileJoinModel();
        $f  = $m->addStudentJoin($data);
       /* $m = new BranchModel();
        $f=  $m->getBranch($data['green_house_name']);
        if(empty($f)){
            $m->createData($data);
        }*/
        $this->success("添加成功",url("/system/addhouse"));
    }

    //地块的添加
    public function savecode()
    {
        $data   = input('param.');

        if(!isset($data['status']))
        {
            $data['status'] = '关闭';
        }else{
            $data['status'] = '正常';
        }

        $m = new CategoryModel();
        $f=  $m->getInfoByGrade($data['land_name'],$data['crop']);
        if(empty($f)){
            $m->createData($data);
        }
        $this->success("添加成功",url("/system/addcode"));
    }



    //删除文件deleteDoc
    public function deleteAddress(){
        $data           = input('param.');
        $id=$data['id'];
        $c  = new PostModel();
        $doc  = $c->deleteDoc($id);
        return $doc;


    }
    //邮编地址修改
    public function updatePost(){
        $data=input('param.');
        $id=$data['id'];

        $pro_obj    = new ProCityModel();
        $pro_arr    = $pro_obj->getList();
        $f   = new PostModel();
        $arr    = $f->getOne($id);

        $this->assign('arr',$arr);

        $this->assign('pro_arr',$pro_arr);
        return view('updatepost');
    }

    //管理员列表
    public function userList()
    {
        return view('user');
    }
    //获取
    public function getUser()
    {
        $m = new UserModel();
        $maj = $m->getAllList();

        foreach ($maj as &$v)
        {
            if($v['status'] == 1)
            {
                $v['status']    = '<a style="color: #2ca02c" lay-event="del">可用，点击禁用</a>';
            }else{
                $v['status']    = '<a style="color: #c67605"  lay-event="nodel">已禁用，点击启动</a>';"";
            }
        }
        $result = array("code"=>0,"msg"=>"","data"=>$maj);
        echo json_encode($result);
    }
    //设置
    public function updateUser()
    {
        $data   = input('param.');
        $type   = $data['type'];
        $id     = $data['id'];

        $save_arr   = array();
        switch ($type)
        {
            case 1:
                $save_arr['password']   = md5($data['value']);
                break;
            case 2:
                $save_arr['status']   = 0;

                break;
            case 3:
                $save_arr['status']   = 1;

                break;
            case 4:
                $save_arr['user_name']   = $data['value'];
                break;
            case 5:
                $save_arr['login_name']   = $data['value'];
                break;
        }
        $m = new UserModel();
        $m->updateData($save_arr,$id);

        $re_arr     = array();
        $re_arr['code'] = 200;
        $re_arr['msg'] = "设置成功";
        echo json_encode($re_arr);
    }

    //获取邮寄数据
    public function post()
    {
        return view("post");
    }

    //获取
    public function getPost()
    {
        $data   = input('param.');
        $m = new PostModel();
        $maj = $m->getAllList($data);
       // print_r($maj);die;
        $count      = $maj['count'];
        $maj_arr    = $maj['content'];
        foreach ($maj_arr as &$v)
        {
            if($v['status'] == 1)
            {
                $v['status']    = '<a style="color: #2ca02c" lay-event="del">可用，点击禁用</a>';
            }else{
                $v['status']    = '<a style="color: #c67605"  lay-event="nodel">已禁用，点击启动</a>';"";
            }
        }
        $result = array("code"=>0,"msg"=>"","count"=>$count,"data"=>$maj_arr,"page"=>$data['page']);
        echo json_encode($result);

    }

    //添加
    public function addPost()
    {
        $pro_obj    = new ProCityModel();
        $pro_arr    = $pro_obj->getList();

        $this->assign('pro_arr',$pro_arr);
        return view('addpost');
    }



    /**
     * 实例化阿里云OSS
     * @return object 实例化得到的对象
     * @return 此步作为共用对象，可提供给多个模块统一调用
     */
    public function new_oss(){
        //获取配置项，并赋值给对象$config
        $config=config('aliyun_oss');
        //实例化OSS
        $oss=new \OSS\OssClient($config['AccessKeyId'],$config['AccessKeySecret'],$config['Endpoint']);
        return $oss;
    }

    //上传文件
    public function uploadFile()
    {
        $file   = request()->file('file');
        $config = config('aliyun_oss');
        //try 要执行的代码,如果代码执行过程中某一条语句发生异常,则程序直接跳转到CATCH块中,由$e收集错误信息和显示
        try{
            $info   = $file->validate(['size'=>10000000,'ext'=>'gif,jpg,jpeg,bmp,png'])->move(ROOT_PATH . 'public' . DS . 'uploadimg');
            //获取文件名
            $path       = $info->getSaveName();
            $file_name  =  ROOT_PATH.'public' . DS . 'uploadimg' . DS . $path;

            $ext_arr    = explode(".",$path);
            $count      = count($ext_arr);
            $ext        = $ext_arr[$count-1];
            //没忘吧，new_oss()是我们上一步所写的自定义函数
            $ossClient = $this->new_oss();

            //uploadFile的上传方法
            $b = $ossClient->uploadFile($config['BucketName'], date('Ymd')."/".time().".".$ext, $file_name);

            $url    = $b['info']['url'];
            $result = array("code"=>200,"msg"=>"上传成功","data"=>array("src"=>$url));
            echo json_encode($result);
        } catch(OssException $e) {
            //如果出错这里返回报错信息

            echo $file->getError();
        }
    }
}