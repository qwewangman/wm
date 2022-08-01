<?php

namespace app\index\controller;
use app\index\model\BorrowModel;
use app\index\model\BranchModel;
use app\index\model\CategoryModel;
use app\index\model\EmigrationModel;
use app\index\model\SensorModel;
use think;
use think\Db;
use think\Route;
use app\index\controller\Base;
use TCPDF;
use app\index\model\FilewithinModel;
use app\index\model\StudentsModel;
use app\index\model\DepartmentModel;
use app\index\model\FileModel;
use app\index\model\LogModel;
use app\index\model\MajorModel;
use app\index\model\FileJoinModel;
use app\index\model\ChangeModel;
use app\index\model\PostModel;
class File extends Base
{
    //编辑传感器
    public function saveFile()
    {
        $data       = input('param.');
        $name  = $data['sensor_name'];//传感器名称
        $ip      = $data['sensor_ip'];//传感器ip
        $green_house = $data['green_house_id']; //大棚ID
        $land = $data['land_id']; //地块ID
        $land = $data['is_delete'];//是否启用


        $f_obj   = new SensorModel();
        $s_info = $f_obj->getInfoBySid($name,$ip);

        if($s_info){  //如果存在  就是更新
           $sid = $s_info['id'];
            $r_info = $f_obj->updateDataBySid($data,$sid);
            $this->success("更新成功，可继续更新");
        }else{      //没有数据就是增加
            $add_sensor = $f_obj->createData($data);
            $this->success("编辑成功，可继续添加");
        }
    }
    //传感器列表
    public function getlist()
    {
        $f_obj   = new SensorModel();
        $s_info = $f_obj->getlist();
        $result = array("code"=>0,"msg"=>"123","data"=>$s_info);
        $result = json_encode($result);
        echo $result;
    }
 //传感器列表
    public function getHomeInfo()
    {
        $s_info=[];
        $f_obj   = new SensorModel();
        $s_info = $f_obj->getlist();
        foreach ($s_info as $value)
        {
            return $value;
        }
    }
    //温室度折线图
    public function getdataInfo()
    {
        $data = input('param.');
        $arr=[];
        $f_info=[];
        $list = [];
        $f_obj   = new EmigrationModel();
        $s_info = $f_obj->dataInfo();
        if(isset($data['beginDate'])&&isset($data['endDate'])){
            $time_1 = strtotime($data['beginDate']);
            $time_2 = strtotime($data['endDate']);
            foreach( $s_info AS $value ) {
                if(strtotime($value['createTime']) >= $time_1 && strtotime($value['createTime']) <= $time_2) {
                    $f_info[] = $value;
                }
            }
        }else{
            $f_info = $s_info;
        }

        foreach($f_info as $k=>$v){
            $arr[$v['createTime']] = $v;

        }
        foreach($arr as $key=>$value){
            $list['date'][] = $key;
            $list['temperature'] [] = $value['temperature'];
            $list['humidity'] [] = $value['humidity'];
         }
        return $list;
    }


    //大棚列表
    public function getcateinfo()
    {
        $f_obj   = new BranchModel();
        $s_info = $f_obj->getlist();
        $result = array("code"=>0,"msg"=>"123","data"=>$s_info);
        $result = json_encode($result);
        echo $result;
    }

    //园区
    public function getHouseInfo()
    {
        $f_obj   = new FileJoinModel();
        $s_info = $f_obj->getList();
        $result = array("code"=>0,"msg"=>"123","data"=>$s_info);
        $result = json_encode($result);
        echo $result;
    }

    //地块列表
    public function getcodeinfo()
    {
        $f_obj   = new CategoryModel();
        $s_info = $f_obj->getlist();
        $result = array("code"=>0,"msg"=>"123","data"=>$s_info);
        $result = json_encode($result);
        echo $result;
    }


//传感器设置
    public function setPage()
    {

        return view('/doc/doclist');
    }


    //立卷页面
    public function addFile()
    {
        /*$d = new DepartmentModel();
        $dep = $d->getDepartList();
        $this->assign('departlist',$dep);
        return view('addfile');*/
    }

    //编辑页面
    public function editFile($id)
    {

        $data = input('param.');
        $type = "";
        if(isset($data['type']))
            $type   = $data['type'];
        $d      = new DepartmentModel();
        $dep    = $d->getDepartList();

        $stu_obj    = new FileModel();
        $stu_info   = $stu_obj->getInfoById($id);

        $m      = new MajorModel();
        $m_arr  = $m->getMajorList($stu_info['depart']);

        if($stu_info['file_derice'] != "")
        {
            $stu_info['file_derice']    = explode("-",$stu_info['file_derice']);
        }else{
            $stu_info['file_derice']    = array("","","");
        }

        $this->assign('type',$type);//学生基本信息

        $this->assign('m_arr',$m_arr);
        $this->assign('stu_info',$stu_info);
        $this->assign('departlist',$dep);
        return view('editfile');
    }

    public function setfile(){
        $d = new DepartmentModel();
        $dep = $d->getDepartList();//所有舞系
        $this->assign('departlist',$dep);
        return view('/setfile');
    }

    //获取单一条数据
    public function profile(){

        $data = input('param.');

        $f      = new FileModel();
        $wzd    = $f->getStudentOne($data['id']);

        switch ($wzd['status'])
        {
            case 0:
                $status = "在库";
                break;
            case 1:
                $status = "未接收";
                break;
            case 2:
                $status = "借出-外借";
                break;
            case 3:
                $status = "已迁出";
                break;
            case 4:
                $status = "已退档";
                break;
            case 5:
                $status = "转研究生";
                break;
            case 6:
                $status = "在档-毕业暂存";
                break;
            case 7:
                $status = "在档-村官暂存";
                break;
            case 8:
                $status = "借出-室内查阅";
                break;
            case 9:
                $status = "借出-在线查阅";
                break;
        }
        //polity 政治面貌
        switch ($wzd['polity'])
        {
            case 0:
                $polity = "中共党员";
                break;
            case 1:
                $polity = "中共预备党员";
                break;
            case 2:
                $polity = "共青团员";
                break;
            case 3:
                $polity = "民革党员";
                break;
            case 4:
                $polity = "民盟盟员";
                break;
            case 5:
                $polity = "民建会员";
                break;
            case 6:
                $polity = "民进会员";
                break;
            case 7:
                $polity = "农工党党员";
                break;
           case 8:
                $polity = "致公党党员";
                break;

                case 9:
                $polity = "九三学社社员";
                break;
            case 10:
                $polity = "台盟盟员";
                break;
            case 11:
                $polity = "无党派人士";
                break;
                case 0:
                $polity = "中共党员";
                break;
            case 12:
                $polity = "群众";
                break;

        }

        switch ($wzd['learn_style']) {
            case 0:
                $learn_style = "全日制";
                break;
        }
        switch ($wzd['preschool']) {
            case 0:
                $preschool = "未接收";
                break;
            case 1:
                $preschool = "已接收";
                break;
        }
        $wzd['status']  = $status;
        $wzd['polity']  = $polity;
        $wzd['learn_style']  = $learn_style;
        $wzd['preschool']=$preschool;
        $this->assign('info',$wzd);//学生基本信息



        return view('profile');
    }

    //删除
    public function delFile()
    {
        $data   = input('param.');

        $id     = $data['id'];

        $file   = new FileModel();

        $file->deleteData($id);

        $result = array("code"=>200);
        echo json_encode($result);
    }




    #传感器列表展示
    public function fileList()
    {

        $data   = input('param.');

        $type   = session("type");

        $f = new FilewithinModel();
        $allInfo = $f->getListAll();




        $f      = new FileModel();
        $wzd    = $f->getStudentsList($data,$type);

        $count  = $wzd['count'];

        $result = $wzd['content'];


        foreach($result as &$v)
        {

            switch ($v['status'])
            {
                case 0:
                    $status = "在库";
                    break;
                case 1:
                    $status = "未接收";
                    break;
                case 2:
                    $status = "借出";
                    break;
                case 3:
                    $status = "已迁出";
                    break;
                case 4:
                    $status = "已退档";
                    break;
                case 5:
                    $status = "转研究生";
                    break;
                case 6:
                    $status = "在档-毕业暂存";
                    break;
                case 7:
                    $status = "在档-村官暂存";
                    break;
                default:
                    $status = "在卷";
            }
            switch ($v['preschool']) {
                case 0:
                    $preschool = "未接收";
                    break;
                case 1:
                    $preschool = "已接收";
                    break;
            }
            $v['preschool']=$preschool;
            $v['status']    = $status;
            $v['dname']     = $v['dname']."-".$v['mname'];
        }
        $result = array("code"=>0,"msg"=>"","count"=>$count,"data"=>$result,"page"=>$data['page']);
        echo json_encode($result);
    }



    public function fileAllList()
    {

        $data   = input('param.');

        $type   = session("type");

        if(isset($data['grade']))
        {
            $f      = new FileModel();
            $wzd    = $f->getAllStudentsList($data,$type);

            $count  = $wzd['count'];

            $result = $wzd['content'];
        }else{
            $count      = 0;
            $result     = array();
        }


        $result = array("code"=>0,"msg"=>"","count"=>$count,"data"=>$result);
        echo json_encode($result);
    }






    //保存添加
    public function updateFile($id)
    {
        $data       = input('param.');

        $f_obj      = new FileModel();
        if(isset($data['ftype']))
        {
            $photo  = $data['photo'];
            $save_data  = array();
            $save_data['photo'] = $photo;


            $stu_id   = $f_obj->updateData($save_data,$id);
            $result = array("code"=>200);
            echo json_encode($result);
            die();
        }
        $sc_number  = $data['sc_number'];
        $grade      = $data['grade'];
        $data['file_derice']    = "";
        if($data['weizhi1'] != "")
        {
            $data['file_derice']           = $data['weizhi1']."-".$data['weizhi2']."-".$data['weizhi3'];
        }
        unset($data['weizhi1'],$data['weizhi2'],$data['weizhi3']);

        $file_derice      = $data['file_derice'];
        if($sc_number <= 0)
        {
            /*
            $re_arr = array();
            $re_arr['code'] = 100;
            $re_arr['msg']  = '学号错误';
            $this->output($re_arr);
            */
            $this->error("学号错误");
        }
        if($grade <= 0)
        {
            /*
            $re_arr = array();
            $re_arr['code'] = 100;
            $re_arr['msg']  = '年级错误';
            $this->output($re_arr);
            */
            $this->error("年级错误");
        }
        //判断学号是否存在
        $stu_info   = $f_obj->getInfoByNum($sc_number,$id);
        if(!empty($stu_info))
        {
            /*
            $re_arr = array();
            $re_arr['code'] = 101;
            $re_arr['msg']  = '学号已存在';
            $this->output($re_arr);
            */
            $this->error("学号已存在");
        }
        //判断位置是否被占用
        if($file_derice != "")
        {
            $stu_info   = $f_obj->getInfoByDerice($file_derice,$id);
            if(!empty($stu_info))
            {
                /*
                $re_arr = array();
                $re_arr['code'] = 101;
                $re_arr['msg']  = '存放位置是否被占用';
                $this->output($re_arr);
                */
                $this->error("存放位置是否被占用");
            }
        }

        $stu_id   = $f_obj->updateData($data,$id);

        //写变动日志
        $admin_arr  = session('admin');
        $log_obj    = new LogModel();
        $save_arr   = array();
        $save_data  = array();
        $save_data['stu_id']        = $stu_id;
        $save_data['act_id']        = $admin_arr['id'];
        $save_data['created_at']    = date("Y-m-d H:i:s");
        $save_data['name']          = "修改学生信息";
        $save_data['after']         = $data['name']."信息已入库";

        $save_arr[] = $save_data;

        $log_obj->createAllData($save_arr);
        $this->success("编辑成功，可继续添加",url("/file/getone")."?id=".$id);
    }


    public function isHavNum()
    {
        $data   = input('param.');
        $type   = $data['type'];
        $num    = $data['num'];

        $result = array("code"=>0);
        $f_obj      = new FileModel();
        if($type == 1)
        {
            $ishav  = $f_obj->getInfoByNum($num);
            if(!empty($ishav))
            {
                $result = array("code"=>200);
            }
        }elseif ($type == 2){
            $ishav  = $f_obj->getInfoByDerice($num);
            if(!empty($ishav))
            {
                $result = array("code"=>200);
            }
        }
        echo json_encode($result);
    }

    //集体交接
    public  function join()
    {
        return view('join');
    }
 //集体交接
    public  function bigPage()
    {
        return view('bigPage');
    }


    //集体交接
    public  function receive()
    {
        $fj_obj     = new FileJoinModel();
        $result     = $fj_obj->getCount();
        $this->assign('num',$result);
        return view('receive');
    }

    public function getReceiveList()
    {
        $data   = input('param.');
        $st     = 2;
        if(isset($data['st']))
            $st     = $data['st'];

        $fj_obj     = new FileJoinModel();
        $result     = $fj_obj->getList($st);
        $result = array("code"=>0,"msg"=>"","data"=>$result);
        echo json_encode($result);
    }


    //确认接收
    public function setReceive()
    {
        $data   = input('param.');

        $ids        = $data['id'];
        $stu_ids    = $data['stu_ids'];
        $fj_obj     = new FileJoinModel();

        $save_data  = array();
        $save_data['status']    = 3;
        $fj_obj->setReceive($save_data,$ids);

        $file_obj   = new FileModel();
        $save_data  = array();
        $save_data['status']    = 0;
        $save_data['preschool']    = 1;
        $file_obj->setStatus($save_data,$stu_ids);

        $result = array("code"=>200,"msg"=>"","data"=>"");
        echo json_encode($result);
    }

    //获取案卷变动
    public function change()
    {

        $data   = input('param.');
        $stu_id     = $data['stu_id'];
        $this->assign('stu_id',$stu_id);
        return view('change');
    }
    //个人迁出信息
    public function moveFile()
    {
        $data   = input('param.');
        $model     = new EmigrationModel();
        $info    = $model->getOne($data['stu_id']);
        $this->assign('info',$info);
        return view('movefile');
    }

    //获取案卷变动
    public function getChange()
    {
        $data      = input('param.');
        $stu_id    = $data['stu_id'];

        $f         = new LogModel();
        $result    = $f->getList($stu_id);

        foreach($result as $k=>&$v)
        {
            $v['num']    = $k+1;
        }
        $result = array("code"=>0,"msg"=>"","data"=>$result);
        echo json_encode($result);
    }



    //获取案卷变动
    public function getFile()
    {
        $data      = input('param.');

        $stu_id    = $data['stu_id'];

        $f         = new FilewithinModel();
        $result    = $f->getList($stu_id);
        foreach($result as $k=>&$v)
        {
            $v['num']   = $k+1;
            $enclo      = explode(",",$v['enclo']);

            $img_src    = $enclo[0];

            $img        = '<img src="'.$img_src.'"  class="showimg" >';
            switch ($v['status'])
            {
                case 0:
                    $status = "在库";
                    break;
                case 1:
                    $status = "未接收";
                    break;
                case 2:
                    $status = "退件";
                    break;
                case 3:
                    $status = "关闭";
                    break;
                case 4:
                    $status = "借出";
                    break;
            }
            $v['status']        = $status;

            $v['showimg']       = $img;
            $v['within_time']    = $v['nian']."-".$v['yue']."-".$v['ri'];
        }
        $result = array("code"=>0,"msg"=>"","data"=>$result);

        echo json_encode($result);
    }


    //获取案卷变动
    public function ccwz()
    {

        return view('ccwz');
    }

    //真正的案卷变动
     public function fileChange(){
         return view('filechange');
     }
    //获取案卷变动
    public function setCcwz()
    {

        $data       = input('param.');
        $grade      = $data['grade'];
        $depart     = $data['depart'];
        $weizhi1    = $data['weizhi1'];
        $weizhi2    = $data['weizhi2'];
        $weizhi3    = $data['weizhi3'];

        $obj        = new FileModel();
        $file_arr   = $obj->getStudentByDepart($grade,$depart);

        foreach ($file_arr AS $value)
        {
            $save_arr   = array();
            $weizhi3=sprintf("%03d",$weizhi3);
            $save_arr['file_derice']    = $weizhi1."-".$weizhi2."-".$weizhi3;
            $obj->updateData($save_arr,$value['id']);
            $weizhi3++;


        }
        $this->success("设置成功",url("/file/ccwz"));
    }
    //批量上次
    public function upload()
    {

        $d = new DepartmentModel();
        $dep = $d->getDepartList();
        $this->assign('departlist',$dep);
        return view('upload');
    }
    //立卷
    public function fill()
    {

        $d = new DepartmentModel();
        $dep = $d->getDepartList();
        $this->assign('departlist',$dep);
        return view('fill');
    }
    //立卷导入
    public function uploadExccel()
    {
        //获取表单上传文件
        $file   = request()->file('file');
        $info   = $file->validate(['size'=>100000,'ext'=>'xlsx,xls,csv'])->move(ROOT_PATH . 'public' . DS . 'excel');

        if($info){
            //获取文件名
            $exclePath = $info->getSaveName();
            $file_name =  'public' . DS . 'excel' . DS . $exclePath;

            $result = array("code"=>200,"msg"=>"上传成功","data"=>array("src"=>$file_name,'info'=>$info->getInfo()));
            echo json_encode($result);
        }else{
            // 上传失败获取错误信息
            echo $file->getError();
        }
    }

    //立卷导入//批量立卷
    public function doUpload()
    {

        $data       = input('param.');
        $grade      = $data['grade'];
        $depart     = $data['depart'];
        $major      = $data['major'];
        $excel      = $data['excel'];

        $type       = session('type');
        if($type <= 0)
        {
            $type = 1;
        }

        vendor("PHPExcel.PHPExcel");

        $file_name      = ROOT_PATH . $excel;
        $objReader      = \PHPExcel_IOFactory::createReader('Excel2007');
        $obj_PHPExcel   = $objReader->load($file_name, $encode = 'utf-8');

        //转换为数组格式
        $excel_array    = $obj_PHPExcel->getsheet(0)->toArray();


        //删除第一个数组(标题);
        array_shift($excel_array);

        $admin_arr  = session('admin');

        foreach($excel_array as $k=>$v)
        {
            $f_obj      = new FileModel();
            //获取最后一条数据
            $num    = 0;
            $sinfo  = $f_obj->getInfoByGrade($grade);
            if(!empty($sinfo))
            {
                $num    = $sinfo['serial_number'];
            }
            //生产入档号
            $filing_number  = $this->createStuNumber($grade,$num,1);
            //生产卷号
            $file_number    = $this->createStuNumber($grade,$num,-1);

            $data       = array();
            $stype      = 1;
            $ctype      = 1;
            $country    = $v[6];
            switch ($v[6])
            {
                case "中国香港":
                case "中国澳门":
                case "中国台湾":
                    $stype = 2;
                    $ctype = 2;
                    $country = "中国";
                    break;
                case "泰国":
                case "俄罗斯":
                case "越南":
                case "美国":
                case "马来西亚":
                    $stype = 3;
                    $ctype = 2;
                    break;
                default:
                    $country = "中国";
                    break;
            }
            $brithday   = "";
            if($ctype == 1)
            {
                $brithday   = substr($v[4],6,4)."-".substr($v[4],10,2)."-".substr($v[4],12,2);
            }
            $data['grade']          = $grade;
            $data['name']           = $v[1];
            $data['sex']            = (int)$v[2]==2?"女":"男";
            $data['sc_number']      = str_replace(".00","",$v[3]);
            $data['card']           = str_replace(".00","",$v[4]);
            $data['nation']         = $v[5];
            $data['country']        = $country;
            $data['native']         = $v[6];
            $data['education']      = $v[8];
            $data['phone']          = (int)$v[9];
            $data['address']        = $v[10];
            $data['postcode']       = $v[11];
            $data['card_type']      = $ctype;
            $data['student_type']   = $stype;
            $data['depart']         = $depart;
            $data['major']          = $major;
            $data['type']           = $type;
            $data['brithday']       = $brithday;
            $data['filing_number']  = $filing_number;
            $data['file_number']    = $file_number;
            $data['serial_number']  = $num+1;
            $data['addtime']        = date("Y-m-d");

            if($data['sc_number'] != "")
            {
                $judge=$f_obj->getInfoByNum($data['sc_number']);

                if(!empty($judge))
                {

                    $this->error("发生错误，已经立卷了");
                }
            }

            $stu_id     = $f_obj->createData($data);


            $log_obj    = new LogModel();
            $save_data  = array();
            $save_data['stu_id']        = $stu_id;
            $save_data['act_id']        = $admin_arr['id'];
            $save_data['created_at']    = date("Y-m-d H:i:s");
            $save_data['name']          = "立卷";
            $save_data['after']         = "批量立卷，形成卷号：".$file_number;
            $log_obj->createData($save_data);
        }

        $this->success("上传成功，可继续导入",url("/file/upload"));

    }

    //批量导入邮寄地址
    public function addAll(){
      return view('addcode');
    }
    //导入邮寄地址的excel表
    public function addAllData(){
        $data       = input('param.');
        $excel      = $data['excel'];
        $type       = session('type');

        vendor("PHPExcel.PHPExcel");
        $file_name      = ROOT_PATH . $excel;
        $objReader      = \PHPExcel_IOFactory::createReader('Excel2007');
        $obj_PHPExcel   = $objReader->load($file_name, $encode = 'utf-8');
        //转换为数组格式
        $excel_array    = $obj_PHPExcel->getsheet(0)->toArray();
        //删除第一个数组(标题);
        array_shift($excel_array);
        foreach($excel_array as $k=>$v)
        {

            $arr[$k]['name']        = $v[0];
            $arr[$k]['province']         = $v[1];
            $arr[$k]['city']      = $v[2];
            $arr[$k]['address']        = $v[3];
            $arr[$k]['code']        = $v[4];
            $arr[$k]['contact']       = $v[5];
            $arr[$k]['status']      = 1;
            $arr[$k]['created_at'] = date("Y-m-d H:i:s");
            $arr[$k]['updated_at'] = date("Y-m-d H:i:s");

        }
        $m = new PostModel();
        $m->createDataAll($arr);
        $this->success("添加成功",url("/file/addcode"));
    }

    //案卷变动
    public function changeFile()
    {
        $data       = input('param.');
        $id         = $data['id'];

        $obj        = new FileModel();
        $stu_info   = $obj->getInfoById($id);
       // print($stu_info);die;
        $this->assign('id',$id);
        $this->assign('stu_info',$stu_info);


        $d          = new DepartmentModel();
        $dep        = $d->getDepartList();
        $this->assign('departlist',$dep);

        $alldep     = $d->getAllDepartList();
        $this->assign('alldepart',$alldep);

        $m          = new MajorModel();
        $m_arr      = $m->getMajorList($stu_info['depart']);

        $this->assign('m_arr',$m_arr);

        $doc_obj    = new FilewithinModel();
        $doc_arr    = $doc_obj->getListBySid($id);

        $d_list     = array();

        foreach ($doc_arr AS $val)
        {

            $category_id    = $val['category_id'];
            if(isset($d_list[$category_id]))
            {
                $d_list[$category_id]['doc'][]  = $val;
            }else{
                $tmp_arr = array();
                $tmp_arr['name']    = $val['ocname'];
                $tmp_arr['doc'][]   = $val;
                $d_list[$category_id]   = $tmp_arr;
            }
        }

        $this->assign('d_list',$d_list);


       return view('changefile');
    }
    //改派
    public function changesSend(){
        $data       = input('param.');
        $student_id=$data['stu_id'];
        $obj=new StudentsModel();
        return view('changesSend');

    }

    //处理案卷变动
    public function doChangeFile($id)
    {
        $data       = input('param.');
        $ctype      = $data['ctype'];

        $obj        = new FileModel();
        $stu_info   = $obj->getInfoById($id);

        $admin_arr  = session('admin');
        $msg        = "变更成功";
        switch ($ctype){
            case "baodao":
                $type       = $data['type'];
                $str        = "正常报道，接收档案";
                if($type ==1)
                {
                    $save_arr   = array();
                    $save_arr['status']     = 1;
                    $save_arr['xj_status']  = 1;
                    $save_arr['preschool']  = 1;
                    $obj->updateData($save_arr,$id);
                }else{
                    $save_arr['preschool']  = 0;
                    if($type ==2)
                    {
                        $str        = "异常报道，档案不合格";
                    }else{
                        $str        = "异常报道，档案未携带";
                    }
                }


                $log_obj    = new LogModel();
                $save_data  = array();
                $save_data['stu_id']        = $id;
                $save_data['act_id']        = $admin_arr['id'];
                $save_data['created_at']    = date("Y-m-d H:i:s");
                $save_data['name']          = "报道";
                $save_data['before']        = "";
                $save_data['after']         = $str;
                $save_data['content']       = $data['note'];
                $log_obj->createData($save_data);

                if($type ==1) {
                    $filing_number = $stu_info['filing_number'];
                    $log_obj = new LogModel();
                    $save_data = array();
                    $save_data['stu_id'] = $id;
                    $save_data['act_id'] = $admin_arr['id'];
                    $save_data['created_at'] = date("Y-m-d H:i:s");
                    $save_data['name'] = "收档";
                    $save_data['after'] = "报道收档，收档号为：" . $filing_number;
                    $save_data['content'] = $data['note'];
                    $log_obj->createData($save_data);
                }

                break;

            case "baoliu":
                $type       = $data['type'];

                $save_arr   = array();
                $save_arr['xj_status']  = 2;
                $obj->updateData($save_arr,$id);


                $log_obj    = new LogModel();
                $save_data  = array();
                $save_data['stu_id']        = $id;
                $save_data['act_id']        = $admin_arr['id'];
                $save_data['created_at']    = date("Y-m-d H:i:s");
                $save_data['name']          = "保留学籍";
                $save_data['before']        = "";
                $save_data['after']         = $type;
                $save_data['content']       = $data['note'];
                $log_obj->createData($save_data);

                break;

            case "shengxue":
                $save_arr   = array();
                $save_arr['type']  = 2;
                $save_arr['status']  = 3;
                $obj->updateData($save_arr,$id);
                $log_obj    = new LogModel();
                $save_data  = array();
                $save_data['stu_id']        = $id;
                $save_data['act_id']        = $admin_arr['id'];
                $save_data['created_at']    = date("Y-m-d H:i:s");
                $save_data['name']          = "升学";
                $save_data['before']        = "";
                //$save_data['after']         = $type;
                $save_data['content']       = $data['note'];
                $log_obj->createData($save_data);

                break;

            case "xiuxue":
                $type       = $data['type'];

                $save_arr   = array();
                $save_arr['xj_status']  = 3;
                $obj->updateData($save_arr,$id);


                $log_obj    = new LogModel();
                $save_data  = array();
                $save_data['stu_id']        = $id;
                $save_data['act_id']        = $admin_arr['id'];
                $save_data['created_at']    = date("Y-m-d H:i:s");
                $save_data['name']          = "休学";
                $save_data['before']        = "";
                $save_data['after']         = $type;
                $save_data['content']       = $data['note'];
                $log_obj->createData($save_data);

                break;


            case "xiuxue":

                $save_arr   = array();
                $save_arr['xj_status']  = 1;
                $obj->updateData($save_arr,$id);


                $log_obj    = new LogModel();
                $save_data  = array();
                $save_data['stu_id']        = $id;
                $save_data['act_id']        = $admin_arr['id'];
                $save_data['created_at']    = date("Y-m-d H:i:s");
                $save_data['name']          = "恢复学籍";
                $save_data['before']        = "保留学籍或休学";
                $save_data['after']         = "学籍正常在籍";
                $save_data['content']       = $data['note'];
                $log_obj->createData($save_data);

                break;

            case "zancun":

                $type       = $data['type'];

                switch ($type)
                {
                    case 1:
                        $tp = 6;
                        $tcon   = "毕业暂存";
                        break;
                    case 2:
                        $tp = 7;
                        $tcon   = "村官暂存";
                        break;
                    case 3:
                        $tp = 10;
                        $tcon   = "其他暂存";
                        break;
                }
                $save_arr   = array();
                $save_arr['status']     = $tp;
                $save_arr['xj_status']  = 4;
                $obj->updateData($save_arr,$id);


                $log_obj    = new LogModel();
                $save_data  = array();
                $save_data['stu_id']        = $id;
                $save_data['act_id']        = $admin_arr['id'];
                $save_data['created_at']    = date("Y-m-d H:i:s");
                $save_data['name']          = "档案暂存";
                $save_data['before']        = "正常在档";
                $save_data['after']         = $tcon;
                $save_data['content']       = $data['note'];
                $log_obj->createData($save_data);

                break;
            case "jiangji":
                $grade  = $data['grade'];
                $save_arr   = array();
                $save_arr['grade']  = $grade;
                $obj->updateData($save_arr,$id);

                $log_obj    = new LogModel();
                $save_data  = array();
                $save_data['stu_id']        = $id;
                $save_data['act_id']        = $admin_arr['id'];
                $save_data['created_at']    = date("Y-m-d H:i:s");
                $save_data['name']          = "降级";
                $save_data['before']        = "原年级：".$stu_info['grade']."级";
                $save_data['after']         = "变更后年级：".$grade."级";
                $save_data['content']       = $data['note'];
                $log_obj->createData($save_data);
                break;

            case "yuanxi":
                $depart     = $data['depart'];
                $major      = $data['major'];
                $data['file_derice']    = "";
                if($data['weizhi1'] != "")
                {
                    $data['file_derice']           = $data['weizhi1']."-".$data['weizhi2']."-".$data['weizhi3'];
                    $file_derice      = $data['file_derice'];
                }
                unset($data['weizhi1'],$data['weizhi2'],$data['weizhi3']);
                $f_obj      = new FileModel();
                //判断位置是否被占用
                if($file_derice != "")
                {
                    $stu_info   = $f_obj->getInfoByDerice($file_derice);
                    if(!empty($stu_info))
                    {
                        /*
                        $re_arr = array();
                        $re_arr['code'] = 101;
                        $re_arr['msg']  = '存放位置是否被占用';
                        $this->output($re_arr);
                        */
                        $this->error("存放位置是否被占用");
                    }
                }

                $save_arr   = array();
                $save_arr['depart']     = $depart;
                $save_arr['major']      = $major;
                $save_arr['file_derice']      = $file_derice;
                $obj->updateData($save_arr,$id);

                $d          = new DepartmentModel();
                $d_info     = $d->getDepId($stu_info['depart']);
                $nd_info    = $d->getDepId($depart);

                $m          = new MajorModel();
                $m_info     = $m->getDepId($stu_info['major']);
                $nm_info    = $m->getDepId($major);

                $log_obj    = new LogModel();
                $save_data  = array();
                $save_data['stu_id']        = $id;
                $save_data['act_id']        = $admin_arr['id'];
                $save_data['created_at']    = date("Y-m-d H:i:s");
                $save_data['name']          = "院系变更";
                $save_data['before']        = "原院系专业：".$d_info."-".$m_info;
                $save_data['after']         = "变更后院系：".$nd_info."-".$nm_info;
                $save_data['content']       = $data['note'];
                $log_obj->createData($save_data);

                break;
            case "jieyue":
                $type     = $data['type'];
                $st         = 2;
                $scode      = "";
                switch ($type)
                {
                    case 1:
                        $st         = 2;
                        $scode      = "普通借阅";
                        break;
                    case 2:
                        $st         = 8;
                        $scode      = "室内查阅";
                        break;
                    case 3:
                        $st         = 9;
                        $scode      = "在线查阅";
                        break;
                }
                $save_arr   = array();
                $save_arr['status'] = $st;
                $obj->updateData($save_arr,$id);
                $doc_obj    = new FilewithinModel();
                $filetype   = $data['filetype'];

                if($filetype == 1)
                {
                    $data['docs']   = 0;
                    $save_d     = array();
                    $save_d['status'] = 4;
                    $doc_obj->updateDataBySid($save_d,$id);
                }else{
                    $doc    = empty($data['doc'])?0:$data['doc'];
                    $docs   = implode(",",$doc);
                    $data['docs']   = $docs;

                    if(empty($data['doc']))
                    {
                        $save_d     = array();
                        $save_d['status'] = 4;
                        $doc_obj->updateDataBySid($save_d,$id);
                    }else{
                        $save_d     = array();
                        $save_d['status'] = 4;
                        $doc_obj->updateDataByIds($save_d,$data['doc']);
                    }
                }

                $daterang   = $data['daterang'];
                $data['start_time'] =  $daterang;
                $data['end_time']   = $data['return'];
                $data['student_id'] = $id;
                unset($data['ctype'],$data['daterang'],$data['filetype'],$data['doc'],$data['return']);

                $nian   = date("Y");
                $data['nian']       = $nian;
                $b_obj  = new BorrowModel();
                //获取最后一条数据
                $num    = 0;
                $sinfo  = $b_obj->getInfoByGrade($nian);

                if(!empty($sinfo))
                {
                    $num    = $sinfo['serial_number'];
                }
                //生产借阅号
                $number  = $this->createStuNumber($nian,$num,5);
                $data['number']         = $number;
                $data['serial_number']  = $num+1;
                $b_obj->createData($data);//有错
                $log_obj    = new LogModel();
                $save_data  = array();
                $save_data['stu_id']        = $id;
                $save_data['act_id']        = $admin_arr['id'];
                $save_data['created_at']    = date("Y-m-d H:i:s");
                $save_data['name']          = "案卷借阅";
                $save_data['after']         = $scode.",形成借阅号：".$number;
                $save_data['content']       = $data['content'];
                $log_obj->createData($save_data);
                $msg    = "借阅成功";
                break;
            case "qiandang":

                $change   = new ChangeModel();
                $change->graduationMove($data);

                $save_arr   = array();
                $save_arr['status']     = 4;
                $save_arr['xj_status']  = 4;
                $obj->updateData($save_arr,$id);

                $nian   = date("Y");
                $data['nian']       = $nian;

                $qobj   = new EmigrationModel();

                $num    = 0;
                $sinfo  = $qobj->getInfoByGrade($nian);
                if(!empty($sinfo))
                {
                    $num    = $sinfo['serial_number'];
                }
                //生产号
                $number  = $this->createStuNumber($nian,$num,3);

                $data['number']         = $number;
                $data['serial_number']  = $num+1;

                unset($data['ctype']);
                unset($data['batch']);
                $qobj->createData($data);

                $log_obj    = new LogModel();
                $save_data  = array();
                $save_data['stu_id']        = $id;
                $save_data['act_id']        = $admin_arr['id'];
                $save_data['created_at']    = date("Y-m-d H:i:s");
                $save_data['name']          = "迁档";
                $save_data['after']         = "毕业迁档,形成迁档号：".$number;
                $save_data['content']       = $data['note'];
                $log_obj->createData($save_data);
                $msg    = "成功";
                break;
        }

        $this->success($msg,url("/file/changefile")."?id=".$id);
    }

    //接收档案
    public function receiveFile()
    {
        $data       = input('param.');
        $id         = $data['id'];
        $f_obj      = new FileModel();
        $stu_info   = $f_obj->getInfoById($id);

        if($stu_info['file_derice'] != "")
        {
            $stu_info['file_derice']    = explode("-",$stu_info['file_derice']);
        }else{
            $stu_info['file_derice']    = array("","","");
        }

        $this->assign('stu_info',$stu_info);
        return view('filereceive');
    }

    //处理接收档案
    public function doReceiveFile()
    {
        $data       = input('param.');
        $id         = $data['id'];
        $rtype          = $data['rtype'];
        $filing_number  = $data['filing_number'];
        $data['file_derice']    = "";
        if($data['weizhi1'] != "")
        {
            $data['file_derice']           = $data['weizhi1']."-".$data['weizhi2']."-".$data['weizhi3'];
        }
        unset($data['weizhi1'],$data['weizhi2'],$data['weizhi3'],$data['id'],$data['rtype']);

        $file_derice      = $data['file_derice'];

        $f_obj  = new FileModel();
        //判断位置是否被占用
        if($file_derice != "")
        {
            $stu_info   = $f_obj->getInfoByDerice($file_derice,$id);
            if(!empty($stu_info))
            {
                $this->error("存放位置是否被占用");
            }
        }

        $admin_arr  = session('admin');

        $f_obj->updateData($data,$id);

        $log_obj    = new LogModel();
        $save_data  = array();
        $save_data['stu_id']        = $id;
        $save_data['act_id']        = $admin_arr['id'];
        $save_data['created_at']    = date("Y-m-d H:i:s");
        $save_data['name']          = "收档";
        $save_data['after']         = $rtype."，收档号为：".$filing_number;
        $save_data['content']       = $data['note'];
        $log_obj->createData($save_data);


        $this->success("成功",url("/file/receivefile")."?id=".$id);

    }
    //搜索学生档案
    public function searchSnum(){
        $data       = input('param.');

        $s          = new StudentsModel();
        $success    = $s->getSearchStudent($data);

        echo json_encode($success);
    } 
    //提交学生档案 状态为已确认 同时刷新table表
    public function addFileCheck(){
        $data       = input('param.');
        $s          = new StudentsModel();
        $f          = new FileJoinModel();
        $success    = $s->getSearchStudent($data);//学生信息
        // $status     = 0;
        $f_mes      = $f->getFileStudentId($success['id']);//提交表
        $admin      = session('admin');

        if($f_mes){
            if($f_mes['status'] == 2){
                $data = array(
                    'code'          =>  2,
                    'status'        =>  '该学生您已提交',//状态提示
                    'admin_name'    =>  $admin['name'],
                    'date'          =>  $f_mes['join_date'],
                    'student_name'  =>  $success['name'],
                    'sc_number'     =>  $success['sc_number'],
                );
            }elseif($f_mes['status'] == 1){
                $data = array(
                    'code'          =>  1,
                    'status'        =>  '该学生您已确认档案',//状态提示
                    'admin_name'    =>  $admin['name'],
                    'date'          =>  $f_mes['join_date'],
                    'student_name'  =>  $success['name'],
                    'sc_number'     =>  $success['sc_number'],
                );
            }else{
                $data  = array(
                    'code'  =>  0,
                    'id'    => $f_mes['id'],
                );
            }
            return $data;
        }else{
            $data = array(
                'student_id'    =>  $success['id'],
                'admin_id'      =>  $admin['id'],
                'status'        =>  1,
                'join_date'     =>  date('Y-m-d H:i:s',time()),
            );
            $mes    = $f->AddStudentJoin($data);
            $array  = array(
                'code'  =>  0,
                'id'    => $mes,
            );
            return $array;
        }
    }
    //附中 学生列表
    public function affiliatedFileList(){
        $data       = input('param.');
        $f          = new FileJoinModel();
        $wzd    = $f->getStudentsList($data);
        

        $result = $wzd['content'];
        
        $result = array("code"=>0,"msg"=>"","data"=>$result);
        echo json_encode($result);
    }

    //附中 删除数据
    public function deleteJoin(){
        $data       = input('param.');
        $sc_muinber=$data['sc_number'];
        $h=new StudentsModel();
        $data=$h->selectStudnetId($sc_muinber);
        $f          = new FileJoinModel();
        $wzd    = $f->delStudentOne($data);

        return $wzd;
    }
    //附中 修改状态
    public function affiliatedDyFile()
    {
        $data       = input('param.');
        $ids        = $data['str_tmp'];

        $files      = New FileJoinModel();

        $save_arr   = array();
        $save_arr['status'] = 2;

        $files->updateJoinByIds($save_arr,$ids);

        $result = array("code"=>0,"msg"=>"");
        echo json_encode($result);
    }


    //附中 打开pdf
    public function downloadPdf()
    {

        $data       = input('param.');
        $stu_ids    = $data['stu_ids'];
        $s          = new StudentsModel();
        $stu_arr    = $s->getFileByIds($stu_ids);

        
        $lj_time        = date('Y年m月d日');

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Nicola Asuni');
$pdf->SetTitle('案卷信息');
$pdf->SetSubject('TCPDF Tutorial');
$pdf->SetKeywords('TCPDF, PDF, example, test, guide');
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
$pdf->setFooterData(array(0,64,0), array(0,64,128));
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(10, -10, 10);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setPrintHeader(false);

if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }


$pdf->setFontSubsetting(true);
$pdf->SetFont('stsongstdlight','', 20);
$pdf->AddPage();
$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));
$html = '<!doctype html>
    <html lang="en">
    <head>
    <meta charset="UTF-8" />
    <title>档案列表</title>
    </head>
    <body>
    <div class="content">
    <p align="center" style="color: #0a6ebd;font-size: 24px" ><b>档案列表</b></p>
     <div style="color:#6a6a6a;letter-spacing:4px">';
$html .= '<table border rules=none cellspacing=0 align=center>
          <tr>
            <th>姓名：</th>
            <th>学号：</th>
            <th>性别：</th>
            <th>时间：</th>
          </tr>';
        foreach ($stu_arr as $k => $v) {
            
                $html .= '<tr>';
                $html .= '<td>';
                $html .=     $v['name'];
                $html .= ' </td>';
                $html .= ' <td>';
                $html .=    $v['sc_number'];
                $html .= '</td>';
                $html .= '<td>';
                $html .=    $v['sex'];
                $html .= '</td>';
                $html .= '<td>';
                $html .=   date('Y年m月d日');
                $html .= '</td>';
                $html .= '</tr>';
           

            // $html .= '<p style=font-size: 20px><b>';
            // $html .=    $k + 1;
            // $html .=  '</p>';
            // $html .= '<p style=" font-size: 14px">姓名：<span style="color:#0a6ebd;">';
            // $html .=     $v['name'];
            // $html .= '</span></p>';
            // $html .= '<p style=" font-size: 14px">学号：<span style="color:#0a6ebd;">';
            // $html .=     $v['sc_number'];
            // $html .= '</span></p>';
            // $html .= '<p style=" font-size: 14px">性别：<span style="color:red;">';
            // $html .=    $v['sex'];
            // $html .= '</span></p>';
            // $html .= '<p style=" font-size: 14px">时间：<span style="color:red;">';
            // $html .=    date('Y年m月d日');
            // $html .= '</span></p>';
        }
$html .= '</table>';
$html .= '</div>
    </body>
    </html>';
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
$pdf->Output('example_001.pdf', 'D');

        print_r($pdf);die;

    }
    //-------------------------------------
    //打印封面
    public function printCatalogue()
    {

        $data       = input('param.');

        $stu_id     = $data['stu_id'];
        $files      = New FileModel();
        $stu_info   = $files->getStudentOne($stu_id);

        $file_name      = $stu_info['name'];
        $file_number    = $stu_info['file_number'];
        $sc_number      = $stu_info['sc_number'];
        $file_address   = $stu_info['file_derice'];
        $addtime        = date("Y年m月d日",strtotime($stu_info['addtime']));

        $file_num       = $stu_info['file_num'];
        $yx             = $stu_info['dname'];
        $zy             = $stu_info['mname'];
        $lj_time        = date('Y年m月d日');


        //实例化
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        // 设置文档信息
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Nicola Asuni');
        $pdf->SetTitle('案卷信息');
        $pdf->SetSubject('TCPDF Tutorial');
        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');



        // 设置页眉和页脚字体
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // 设置默认等宽字体
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // 设置间距
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // 设置分页
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);


        // ---------------------------------------------------------

        // set default font subsetting mode
        $pdf->setFontSubsetting(true);

        //设置字体
        $pdf->SetFont('dejavusans', '', 14, '', true);
        $pdf->SetFont('stsongstdlight', '', 10);

        // Add a page
        // This method has several options, check the source code documentation for more information.
        $pdf->AddPage();

        // set text shadow effect
        $pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));

        $n = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        // 内容
        $f_obj      = new FilewithinModel();
        $file_arr   = $f_obj->getList($stu_id);
        $file_arr = json_decode($file_arr,TRUE);
        $tmp_arr=array();
        foreach ($file_arr as $k=>$v){
            $tmp_arr[$k][$v['category_id']]['ocname'] = $v['ocname'];
            $tmp_arr[$k][$v['category_id']]['num'] = $v['num'];
            $tmp_arr[$k][$v['category_id']]['child_arr'][] = $v;
        }
       //$pdf->Image('./logo.png');//
        $html = <<<EOD
   
<h1 style="text-align: center;line-height:150px">北京舞蹈学院学生档案</h1>
<div style="text-indent:130px;">
<p></p> 
<p></p>
<p></p>
<p></p>
<h2>卷 $n 名 $n $file_name</h2>
<h2>卷 $n 号 $n $file_number</h2>
<h2>学 $n 号 $n $sc_number</h2>
<h2>存放位置 $n $file_address</h2>
<h2>立卷时间 $n $addtime</h2>
<h2>总 &nbsp;件 &nbsp;数 $n $file_num</h2>
</div>
<p></p>
<p></p>
<p></p>
<p></p>
<p></p>
<p></p>
<p></p>
<p></p>
<p></p>
<p> <span>$lj_time $n$n</span>  <span>案卷管理 $n$n </span> 学生档案  </p>
<p>__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.</p>
<p></p>
<p>卷号:$file_number $n 存放位置:$file_address $n 学号:$sc_number $n 卷名:$file_name  $n  院系:$yx $n  专业:$zy</p>

<p></p>
EOD;
        // Print text using writeHTMLCell()
        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
        $dtime = date('Y-m-d');
        $pdf->Output($dtime.'.pdf', 'I');
        print_r($pdf);die;
    }
    //打印目录
    public function printFill()
    {
        $data       = input('param.');
        $stu_id     = $data['stu_id'];
        $files      = New FileModel();
        $stu_info   = $files->getStudentOne($stu_id);
        $file_name      = $stu_info['name'];
        $file_number    = $stu_info['file_number'];
        $sc_number      = $stu_info['sc_number'];
        $file_address   = $stu_info['file_derice'];
        $addtime        = date("Y年m月d日",strtotime($stu_info['addtime']));
        $file_num       = $stu_info['file_num'];
        $yx             = $stu_info['dname'];
        $zy             = $stu_info['mname'];
        $student_type=$stu_info['type'];
        $lj_time        = date('Y年m月d日');

        //实例化
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        // 设置文档信息
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Nicola Asuni');
        $pdf->SetTitle('案卷信息');
        $pdf->SetSubject('TCPDF Tutorial');
        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');

        // 设置页眉和页脚字体
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // 设置默认等宽字体
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);


        // 设置间距
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // 设置分页
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        //$pdf->AutoPageBreak(TRUE, TCPDF);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        $pdf->setFontSubsetting(true);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        //设置字体
        $pdf->SetFont('dejavusans', '', 14, '', true);
        $pdf->SetFont('stsongstdlight', '', 10);

        $pdf->AddPage();

        // set text shadow effect
        $pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));

        $n = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        // 内容
        $f_obj      = new FilewithinModel();
        $file_arr   = $f_obj->getList($stu_id)->toArray();

        $tmp_arr   = array();
        foreach ($file_arr AS $value)
        {
            $category_id    = $value['category_id'];
            $tmp_arr[$category_id][]   = $value;
        }
        switch ($student_type)
        {
            case 1:
                $degree = "本科";
                break;
            case 2:
                $degree = "研究生";
                break;

        }

        $html = <<<EOD
<h3 style="text-align: center;">北京舞蹈学院学生档案卷内目录</h3>
<p><span style="text-align: center;">
($degree)</span></p>
<p><span style="text-align: left;">卷名:$file_name  　&nbsp;&nbsp;&nbsp;&nbsp;　卷号:$file_number 　&nbsp;&nbsp;&nbsp;&nbsp; 院系:$yx 　 &nbsp;&nbsp;&nbsp;&nbsp;　专业:$zy</span></p>
<p></p>
EOD;
        $text = <<<EOD
<p><span style="text-align:right;font-size:10px;">北京舞蹈学院学生工作部学生档案中心</span>&nbsp;&nbsp;&nbsp;&nbsp;<span style="font-size:10px;">联系电话：010-68937319</span></p>
<p><span style="text-align:right;font-size:10px;">地址:北京市海淀区万寿寺路1号</span>&nbsp;&nbsp;&nbsp;<span style="font-size:10px;">邮编：100081</span></p>
<p></p>
EOD;

        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
        $pdf->MultiCell(15, 10, "序号", $border=1, $align='C',$fill=false, $ln=0, $x='', $y='',  $reseth=true, $stretch=0,$ishtml=false, $autopadding=true, $maxh=0, $valign='M', $fitcell=true);
        $pdf->MultiCell(80, 10, "文件名称", $border=1, $align='C',$fill=false, $ln=0, $x='', $y='',  $reseth=true, $stretch=0,$ishtml=false, $autopadding=true, $maxh=0, $valign='M', $fitcell=true);
        $pdf->MultiCell(45, 10, "形成日期", $border=1, $align='C',$fill=false, $ln=0, $x='', $y='',  $reseth=true, $stretch=0,$ishtml=false, $autopadding=true, $maxh=0, $valign='M', $fitcell=true);
        $pdf->MultiCell(20, 10, "件数", $border=1, $align='C',$fill=false, $ln=0, $x='', $y='',  $reseth=true, $stretch=0,$ishtml=false, $autopadding=true, $maxh=0, $valign='M', $fitcell=true);
        $pdf->MultiCell(20, 10, "页数", $border=1, $align='C',$fill=false, $ln=1, $x='', $y='',  $reseth=true, $stretch=0,$ishtml=false, $autopadding=true, $maxh=0, $valign='M', $fitcell=true);

        $number     = 0;
        $page_num   = 0;
        $height     = 10;
        $datanum    = 0;
        $total_page = ceil((count($file_arr)+count($tmp_arr))/16);
        $total_page = $total_page==0?1:$total_page;
        foreach ($tmp_arr As $k => $v) {
                $number++;
                $num = $this->numeral($number);
                if(($page_num+1) % 15 ==0)
                {
                    $datanum++;
                    $pdf->ln(20);
                    $pdf->writeHTMLCell(0, 0, '', '', $text, 0, 1, 0, true, '', true);
                    $kk = <<<EOD
<p><span style="text-align: right;">第</span><span>$datanum</span><span>页</span> <span>共</span><span>$total_page</span><span>页</span></p>
EOD;
                    $pdf->writeHTMLCell(0, 0, '', '', $kk, 0, 1, 0, true, '', true);
                    $pdf->AddPage();
                    $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
                    $pdf->MultiCell(15, 10, "序号", $border=1, $align='C',$fill=false, $ln=0, $x='', $y='',  $reseth=true, $stretch=0,$ishtml=false, $autopadding=true, $maxh=0, $valign='M', $fitcell=true);
                    $pdf->MultiCell(80, 10, "文件名称", $border=1, $align='C',$fill=false, $ln=0, $x='', $y='',  $reseth=true, $stretch=0,$ishtml=false, $autopadding=true, $maxh=0, $valign='M', $fitcell=true);
                    $pdf->MultiCell(45, 10, "形成日期", $border=1, $align='C',$fill=false, $ln=0, $x='', $y='',  $reseth=true, $stretch=0,$ishtml=false, $autopadding=true, $maxh=0, $valign='M', $fitcell=true);
                    $pdf->MultiCell(20, 10, "件数", $border=1, $align='C',$fill=false, $ln=0, $x='', $y='',  $reseth=true, $stretch=0,$ishtml=false, $autopadding=true, $maxh=0, $valign='M', $fitcell=true);
                    $pdf->MultiCell(20, 10, "页数", $border=1, $align='C',$fill=false, $ln=1, $x='', $y='',  $reseth=true, $stretch=0,$ishtml=false, $autopadding=true, $maxh=0, $valign='M', $fitcell=true);
                }
                $page_num++;
                $pdf->MultiCell(15, $height, $num, $border = 1, $align = 'C', $fill = true, $ln = 0, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
                $pdf->MultiCell(80, $height, $v[0]['ocname'], $border = 1, $align = 'C', $fill = true, $ln = 0, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
                $pdf->MultiCell(15, $height, '年', $border = 1, $align = 'C', $fill = false, $ln = 0, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
                $pdf->MultiCell(15, $height, '月', $border = 1, $align = 'C', $fill = false, $ln = 0, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
                $pdf->MultiCell(15, $height, '日', $border = 1, $align = 'C', $fill = false, $ln = 0, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
                $pdf->MultiCell(20, $height, '', $border = 1, $align = 'C', $fill = false, $ln = 0, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
                $pdf->MultiCell(20, $height, '', $border = 1, $align = 'C', $fill = false, $ln = 1, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);


                foreach ($v AS $kk => $vv) {
                    $pdf->MultiCell(15, $height, $kk + 1, $border = 1, $align = 'C', $fill = true, $ln = 0, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
                    $pdf->MultiCell(80, $height, $vv['name_sub'] . $vv['name'], $border = 1, $align = 'C', $fill = true, $ln = 0, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
                    $pdf->MultiCell(15, $height, $vv['nian'], $border = 1, $align = 'C', $fill = false, $ln = 0, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
                    $pdf->MultiCell(15, $height, $vv['yue'], $border = 1, $align = 'C', $fill = false, $ln = 0, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
                    $pdf->MultiCell(15, $height, $vv['ri'], $border = 1, $align = 'C', $fill = false, $ln = 0, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
                    $pdf->MultiCell(20, $height, $vv['item_num'], $border = 1, $align = 'C', $fill = false, $ln = 0, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
                    $pdf->MultiCell(20, $height, $vv['page_num'], $border = 1, $align = 'C', $fill = false, $ln = 1, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
                    $page_num++;
                    if($page_num % 15 ==0)
                    {
                        $datanum++;
                        $pdf->ln(20);
                        $pdf->writeHTMLCell(0, 0, '', '', $text, 0, 1, 0, true, '', true);
                        $kk = <<<EOD
            <p><span style="text-align: right;">第</span><span>$datanum</span>
            <span>页</span> <span>共</span><span>$total_page</span><span>页</span></p>
EOD;
                        $pdf->writeHTMLCell(0, 0, '', '', $kk, 0, 1, 0, true, '', true);
                        $pdf->AddPage();
                        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
                        $pdf->MultiCell(15, 10, "序号", $border=1, $align='C',$fill=false, $ln=0, $x='', $y='',  $reseth=true, $stretch=0,$ishtml=false, $autopadding=true, $maxh=0, $valign='M', $fitcell=true);
                        $pdf->MultiCell(80, 10, "文件名称", $border=1, $align='C',$fill=false, $ln=0, $x='', $y='',  $reseth=true, $stretch=0,$ishtml=false, $autopadding=true, $maxh=0, $valign='M', $fitcell=true);
                        $pdf->MultiCell(45, 10, "形成日期", $border=1, $align='C',$fill=false, $ln=0, $x='', $y='',  $reseth=true, $stretch=0,$ishtml=false, $autopadding=true, $maxh=0, $valign='M', $fitcell=true);
                        $pdf->MultiCell(20, 10, "件数", $border=1, $align='C',$fill=false, $ln=0, $x='', $y='',  $reseth=true, $stretch=0,$ishtml=false, $autopadding=true, $maxh=0, $valign='M', $fitcell=true);
                        $pdf->MultiCell(20, 10, "页数", $border=1, $align='C',$fill=false, $ln=1, $x='', $y='',  $reseth=true, $stretch=0,$ishtml=false, $autopadding=true, $maxh=0, $valign='M', $fitcell=true);
                    }
                }
            }
        $datanum++;
        $pdf->ln(20);
        $pdf->writeHTMLCell(0, 0, '', '', $text, 0, 1, 0, true, '', true);
        $kk = <<<EOD
<p><span style="text-align: right;">第</span><span>$datanum</span><span>页</span> <span>共</span><span>$total_page</span><span>页</span></p>
EOD;
        $pdf->writeHTMLCell(0, 0, '', '', $kk, 0, 1, 0, true, '', true);
        $dtime = date('Y-m-d');
        $pdf->Output($dtime.'.pdf', 'I');
        print_r($pdf);die;
    }

    //批量打印目录
    public function printAllFill()
    {
        $data = input('param.');
        $stu_id = $data['stu_id'];//所有的id
        $arr=explode(",", $stu_id);
        $count=count($arr);
        $stuarr=[];
        for($i=0;$i<$count;$i++){
            $stu=$this->getinfo($arr[$i]);
            array_push($stuarr,$stu);
        }
        $this->catalogue($stuarr);
    }
    public function getinfo($id){
        $stu_id=$id;
        $files      = New FileModel();
        $stu_info   = $files->getStudentOne($stu_id)->toArray();
        return $stu_info;
    }

    public function catalogue($stuinfo){
        $len=count($stuinfo);
            //实例化
           $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            // 设置文档信息
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Nicola Asuni');
            $pdf->SetTitle('案卷信息');
            $pdf->SetSubject('TCPDF Tutorial');
            $pdf->SetKeywords('TCPDF, PDF, example, test, guide');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        // 设置页眉和页脚字体
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
            // 设置默认等宽字体
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            // 设置间距
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            /*$pdf->SetAutoPageBreak(TRUE, 25);
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);*/
            // 设置分页
//        $pdf->Ln(6,true);
//           $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            // set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            // set default font subsetting mode
            $pdf->setFontSubsetting(true);
            //设置字体
            $pdf->SetFont('dejavusans', '', 14, '', true);
            $pdf->SetFont('stsongstdlight', '', 10);
            // $pdf->SetFont(‘stsongstdlight’, ”, 20);
            // set text shadow effect
            $pdf->setTextShadow(array('enabled' => true, 'depth_w' => 0.2, 'depth_h' => 0.2, 'color' => array(196, 196, 196), 'opacity' => 1, 'blend_mode' => 'Normal'));

        for($i=0;$i<$len;$i++){
            //换页
            $pdf->AddPage();
            $stu_id=$stuinfo[$i]['id'];
            $file_name = $stuinfo[$i]['name'];
            $student_type=$stuinfo[$i]['type'];
            $file_number = $stuinfo[$i]['file_number'];
            $sc_number = $stuinfo[$i]['sc_number'];
            $file_address = $stuinfo[$i]['file_derice'];
            $addtime = date("Y年m月d日", strtotime($stuinfo[$i]['addtime']));

            $file_num = $stuinfo[$i]['file_num'];
            $yx = $stuinfo[$i]['dname'];
            $zy = $stuinfo[$i]['mname'];
            $lj_time = date('Y年m月d日');

            $f_obj = new FilewithinModel();
            $file_arr = $f_obj->getList($stu_id)->toArray();
            $tmp_arr   = array();
            foreach ($file_arr AS $value)
            {
                $category_id    = $value['category_id'];
                $tmp_arr[$category_id][]   = $value;
            }

            switch ($student_type)
            {
                case 1:
                    $degree = "本科";
                    break;
                case 2:
                    $degree = "研究生";
                    break;

            }

            $html = <<<EOD
          
<h3 style="text-align: center;">北京舞蹈学院学生档案卷内目录</h3>
<p><span style="text-align: center;">($degree)</span></p>
<p><span style="text-align: left;">卷名:$file_name  　&nbsp;&nbsp;&nbsp;&nbsp;　卷号:$file_number 　&nbsp;&nbsp;&nbsp;&nbsp; 院系:$yx 　 &nbsp;&nbsp;&nbsp;&nbsp;　专业:$zy</span></p><p></p>

EOD;

            $text = <<<EOD
<p><span style="text-align:right;font-size:10px;">北京舞蹈学院学生工作部学生档案中心</span>&nbsp;&nbsp;&nbsp;&nbsp;<span style="font-size:10px;">联系电话：010-68937319</span></p>
<p><span style="text-align:right;font-size:10px;">地址:北京市海淀区万寿寺路1号</span>&nbsp;&nbsp;&nbsp;<span style="font-size:10px;">邮编：100081</span></p>
<p></p>
EOD;

            // Print text using writeHTMLCell()
            $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
            $pdf->MultiCell(15, 10, "序号", $border = 1, $align = 'C', $fill = false, $ln = 0, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
            $pdf->MultiCell(80, 10, "文件名称", $border = 1, $align = 'C', $fill = false, $ln = 0, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
            $pdf->MultiCell(45, 10, "形成日期", $border = 1, $align = 'C', $fill = false, $ln = 0, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
            $pdf->MultiCell(20, 10, "件数", $border = 1, $align = 'C', $fill = false, $ln = 0, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
            $pdf->MultiCell(20, 10, "页数", $border = 1, $align = 'C', $fill = false, $ln = 1, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);


                $number     = 0;
                $page_num   = 0;
                $height     = 10;
            $datanum    = 0;
            $total_page = ceil((count($file_arr)+count($tmp_arr))/16);
            $total_page = $total_page==0?1:$total_page;
                foreach ($tmp_arr As $k => $v) {
                    $number++;
                    $num = $this->numeral($number);
                    if(($page_num+1) % 15 ==0)
                    {
                        $datanum++;
                        $pdf->ln(20);
                        $pdf->writeHTMLCell(0, 0, '', '', $text, 0, 1, 0, true, '', true);
                        $kk = <<<EOD
<p><span style="text-align: right;">第</span><span>$datanum</span><span>页</span> <span>共</span><span>$total_page</span><span>页</span></p>
EOD;
                        $pdf->writeHTMLCell(0, 0, '', '', $kk, 0, 1, 0, true, '', true);
                        $pdf->AddPage();
                        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
                        // Print text using writeHTMLCell()
                        //$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
                        $pdf->MultiCell(15, 10, "序号", $border = 1, $align = 'C', $fill = false, $ln = 0, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
                        $pdf->MultiCell(80, 10, "文件名称", $border = 1, $align = 'C', $fill = false, $ln = 0, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
                        $pdf->MultiCell(45, 10, "形成日期", $border = 1, $align = 'C', $fill = false, $ln = 0, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
                        $pdf->MultiCell(20, 10, "件数", $border = 1, $align = 'C', $fill = false, $ln = 0, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
                        $pdf->MultiCell(20, 10, "页数", $border = 1, $align = 'C', $fill = false, $ln = 1, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);

                    }
                    $page_num++;
                    $pdf->MultiCell(15, $height, $num, $border = 1, $align = 'C', $fill = true, $ln = 0, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
                    $pdf->MultiCell(80, $height, $v[0]['ocname'], $border = 1, $align = 'C', $fill = true, $ln = 0, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
                    $pdf->MultiCell(15, $height, '年', $border = 1, $align = 'C', $fill = false, $ln = 0, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
                    $pdf->MultiCell(15, $height, '月', $border = 1, $align = 'C', $fill = false, $ln = 0, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
                    $pdf->MultiCell(15, $height, '日', $border = 1, $align = 'C', $fill = false, $ln = 0, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
                    $pdf->MultiCell(20, $height, '', $border = 1, $align = 'C', $fill = false, $ln = 0, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
                    $pdf->MultiCell(20, $height, '', $border = 1, $align = 'C', $fill = false, $ln = 1, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);

                    foreach ($v AS $kk => $vv) {
                        $pdf->MultiCell(15, $height, $kk + 1, $border = 1, $align = 'C', $fill = true, $ln = 0, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
                        $pdf->MultiCell(80, $height, $vv['name_sub'] . $vv['name'], $border = 1, $align = 'C', $fill = true, $ln = 0, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
                        $pdf->MultiCell(15, $height, $vv['nian'], $border = 1, $align = 'C', $fill = false, $ln = 0, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
                        $pdf->MultiCell(15, $height, $vv['yue'], $border = 1, $align = 'C', $fill = false, $ln = 0, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
                        $pdf->MultiCell(15, $height, $vv['ri'], $border = 1, $align = 'C', $fill = false, $ln = 0, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
                        $pdf->MultiCell(20, $height, $vv['item_num'], $border = 1, $align = 'C', $fill = false, $ln = 0, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
                        $pdf->MultiCell(20, $height, $vv['page_num'], $border = 1, $align = 'C', $fill = false, $ln = 1, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
                        $pdf->SetLineStyle(array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => '0', 'color' => array(0, 0, 0)));

                        $page_num++;
                        if($page_num % 15 ==0)
                        {
                            $datanum++;
                            $pdf->ln(20);
                            $pdf->writeHTMLCell(0, 0, '', '', $text, 0, 1, 0, true, '', true);
                            $kk = <<<EOD
<p><span style="text-align: right;">第</span><span>$datanum</span><span>页</span> <span>共</span><span>$total_page</span><span>页</span></p>
EOD;
                            $pdf->writeHTMLCell(0, 0, '', '', $kk, 0, 1, 0, true, '', true);
                            $pdf->AddPage();
                            $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
                            // Print text using writeHTMLCell()
                           // $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
                            $pdf->MultiCell(15, 10, "序号", $border = 1, $align = 'C', $fill = false, $ln = 0, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
                            $pdf->MultiCell(80, 10, "文件名称", $border = 1, $align = 'C', $fill = false, $ln = 0, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
                            $pdf->MultiCell(45, 10, "形成日期", $border = 1, $align = 'C', $fill = false, $ln = 0, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
                            $pdf->MultiCell(20, 10, "件数", $border = 1, $align = 'C', $fill = false, $ln = 0, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
                            $pdf->MultiCell(20, 10, "页数", $border = 1, $align = 'C', $fill = false, $ln = 1, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'M', $fitcell = true);
                        }
                    }

                }
            $datanum++;
            $pdf->ln(20);
            $pdf->writeHTMLCell(0, 0, '', '', $text, 0, 1, 0, true, '', true);
            $kk = <<<EOD
<p><span style="text-align: right;">第</span><span>$datanum</span><span>页</span> <span>共</span><span>$total_page</span><span>页</span></p>
EOD;
            $pdf->writeHTMLCell(0, 0, '', '', $kk, 0, 1, 0, true, '', true);

            $dtime = date('Y-m-d');
        }
        $pdf->Output($dtime.'.pdf', 'I');
        print_r($pdf);
           die;
    }
    //pdf 页面--封面部分
      public function printAll(){
          $data = input('param.');
          $stu_id = $data['stu_id'];
          $files = New FileModel();
          $stu_info = $files->getStudentAll($stu_id);
          $stu_info = json_decode($stu_info, true);
          $count=count($stu_info);


          //实例化
          $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
          // 设置文档信息
          $pdf->SetCreator(PDF_CREATOR);
          $pdf->SetAuthor('Nicola Asuni');
          $pdf->SetTitle('案卷信息');
          $pdf->SetSubject('TCPDF Tutorial');
          $pdf->SetKeywords('TCPDF, PDF, example, test, guide');


          // 设置页眉和页脚字体
          $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
          $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
          $pdf->setPrintHeader(false);
          $pdf->setPrintFooter(false);

          // 设置默认等宽字体
          $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

          // 设置间距
          $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
          $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
          $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

          // 设置分页
          $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

          // set image scale factor
          $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);


          // ---------------------------------------------------------

          // set default font subsetting mode
          $pdf->setFontSubsetting(true);

          //设置字体
          $pdf->SetFont('dejavusans', '', 14, '', true);
          $pdf->SetFont('stsongstdlight', '', 10);

          // Add a page
          // This method has several options, check the source code documentation for more information.
          $pdf->AddPage();

          // set text shadow effect
          $pdf->setTextShadow(array('enabled' => true, 'depth_w' => 0.2, 'depth_h' => 0.2, 'color' => array(196, 196, 196), 'opacity' => 1, 'blend_mode' => 'Normal'));


          $html = "";
          for ($i=0;$i<$count;$i++)
          {
              $file_name      = $stu_info[$i]['name'];
              $file_number    = $stu_info[$i]['file_number'];
              $sc_number      = $stu_info[$i]['sc_number'];
              $file_address   = $stu_info[$i]['file_derice'];
              $addtime        = date("Y年m月d日",strtotime($stu_info[$i]['addtime']));

              $file_num       = $stu_info[$i]['file_num'];
              $yx             = $stu_info[$i]['dname'];
              $zy             = $stu_info[$i]['mname'];

              $html .= $this->getPdfHtml($file_name,$file_number,$sc_number,$file_address,$addtime,$file_num,$yx,$zy);
          }

          $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);



          $dtime = date('Y-m-d');
          $pdf->Output($dtime . '.pdf', 'I');
          print_r($pdf);
          die;
      }
    //获取html pdf
    private function getPdfHtml($file_name,$file_number,$sc_number,$file_address,$addtime,$file_num,$yx,$zy)
    {
        $lj_time        = date('Y年m月d日');
        $n = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        // 内容
        $html = <<<EOD
<h1 style="text-align: center;line-height:100px">北京舞蹈学院学生档案</h1>
<div style="text-indent:130px;">
<p></p>
<p></p>
<p></p>
<p></p>
<h2>卷 $n 名 $n $file_name</h2>
<h2>卷 $n 号 $n $file_number</h2>
<h2>学 $n 号 $n $sc_number</h2>
<h2>存放位置 $n $file_address</h2>
<h2>立卷时间 $n $addtime</h2>
<h2>总 &nbsp;件 &nbsp;数 $n $file_num</h2>
</div>
<p></p>
<p></p>
<p></p>
<p></p>
<p></p>
<p></p>
<p></p>
<p></p>
<p></p>
<p> <span>$lj_time $n$n</span>  <span>案卷管理 $n </span> 学生档案 </p>
<p>__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.__.</p>
<p></p>
<p>院系:$yx $n 专业:$zy $n 卷号:$file_number $n 卷名:$file_name  $n$n 学号:$sc_number $n 存放位置:$file_address</p>
EOD;
        return $html;
    }
    public function array_unset_tt($arr,$key){  //二维数组根据某字段去重
        $res = array();
        foreach ($arr as $value) {
            if(isset($res[$value[$key]])){
                unset($value[$key]);
            }
            else{
                $res[$value[$key]] = $value;
            }
        }
        return $res;
    }

    //把阿拉伯数字转化为中文数字
    function numeral($num){
        $china=array('','一','二','三','四','五','六','七','八','九','十一','十二','十三','十四','十五','十六','十七','十八','十九','二十','二十一');
        return $china[$num];
    }


}

