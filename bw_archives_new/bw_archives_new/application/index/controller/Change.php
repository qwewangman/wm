<?php

namespace app\index\controller;
use app\index\model\BorrowModel;
use app\index\model\EmigrationModel;
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

class Change extends Base
{

    //获取列表
    public function getOut()
    {

        $d = new DepartmentModel();
        $dep = $d->getDepartList();//所有舞系

        //$batch=$d->getBatchList();//所有的批次
       // print($batch);die;
       // $batch  = 1;
        //$this->assign('batch',$batch);

        $this->assign('departlist',$dep);
        return view('outlist');

    }

    //获取单一条数据
    public function getOutList()
    {
        $data   = input('param.');
        $type   = session("type");
        $f      = new EmigrationModel();
        $wzd    = $f->getList($data,$type);
        foreach($wzd['content'] as $k=>$v){
            $dep     = new DepartmentModel();
            $department  = $dep->getDepId($v['depart']);
            $wzd['content'][$k]['depart']=$department;
            $major=new MajorModel();
            $career  = $major->getDepId($v['major']);
            $wzd['content'][$k]['major']=$career;
        }


        //echo  $wzd;die;
        $count  = $wzd['count'];

        $result = $wzd['content'];

        $result = array("code"=>0,"msg"=>"","count"=>$count,"data"=>$result,"page"=>$data['page']);
        echo json_encode($result);
    }


    //获取借阅列表
    public function getJie()
    {
        return view('jielist');

    }

    //传感器历史数据
    public function getJieList()
    {
        $data   = input('param.');
        $f = new EmigrationModel();
        $wzd    = $f->getList($data);


        foreach($wzd['content'] as $k=>$v){

            $v['createTime']  = date("Y-m-d H:i" ,strtotime($v['createTime']) );
        }

        $result = array("code"=>0,"msg"=>"","count"=>$wzd['count'],"data"=>$wzd['content'],"page"=>$data['page']);
        echo json_encode($result);


        //$result = array("code"=>0,"msg"=>"","count"=>1,"data"=>$wzd,"page"=>1);
        //echo json_encode($result);
        /*$data   = input('param.');
        $type   = session("type");
        $f      = new BorrowModel();
        $wzd    = $f->getList($data,$type);

        foreach($wzd['content'] as $k=>$v){
            $dep     = new DepartmentModel();
            $department  = $dep->getDepId($v['depart']);
            $wzd['content'][$k]['depart']=$department;
            $major=new MajorModel();
            $career  = $major->getDepId($v['major']);
            $wzd['content'][$k]['major']=$career;
        }

        $count  = $wzd['count'];

        $result = $wzd['content'];

        $doc_obj    = new FilewithinModel();
        foreach($result as &$v)
        {
            switch ($v['type'])
            {
                case 1:
                    $v['type']  = "普通借阅  ";
                    break;
                case 2:
                    $v['type']  = "室内查阅";
                    break;
                case 3:
                    $v['type']  = "在线查阅";
                    break;
            }
            $v['file_type']    = $v['docs']==0?"全卷借阅":"分文件借阅";
            $v['statuscon']    = $v['status']==1?"已归还":"借阅中";

            if($v['docs'] > 0)
            {
                $doc_arr    = explode(",",$v['docs']);
                $doc_list   = $doc_obj->getInfoByIds($doc_arr);
                $tmp_arr    = array();
                foreach ($doc_list AS $vv)
                {
                    $tmp_arr[]  = $vv['name'];
                }
                $v['docstr']  = implode("<br>",$tmp_arr);
            }else{
                $v['docstr']  = "全卷借阅";
            }
        }
        $result = array("code"=>0,"msg"=>"","count"=>$count,"data"=>$result,"page"=>$data['page']);
        echo json_encode($result);*/
    }
    //借阅管理信息展示
    public function borrowManage(){
        $data   = input('param.');
        $f      = new BorrowModel();
        $wzd    = $f->getOne($data['stu_id']);

        $result = $wzd;
        $doc_obj    = new FilewithinModel();
        foreach($result as &$v)
        {
            switch ($v['type'])
            {
                case 1:
                    $v['type']  = "普通借阅  ";
                    break;
                case 2:
                    $v['type']  = "室内查阅";
                    break;
                case 3:
                    $v['type']  = "在线查阅";
                    break;
            }
            $v['file_type']    = $v['docs']==0?"全卷借阅":"分文件借阅";
            $v['statuscon']    = $v['status']==1?"已归还":"借阅中";


            if($v['docs'] > 0)
            {
                $doc_arr    = explode(",",$v['docs']);
                $doc_list   = $doc_obj->getInfoByIds($doc_arr);

                $tmp_arr    = array();
                foreach ($doc_list AS $vv)
                {
                    $tmp_arr[]  = $vv['name'];
                }
                $v['docstr']  = implode("<br>",$tmp_arr);
            }else{
                $v['docstr']  = "全卷借阅";
            }
        }
        // echo $result;die;
        //$result=json_decode($result,true);

        $this->assign('data',$result);
        return view('borrow');

    }

    //借阅管理信息展示
    public function borrowContent(){
        $data   = input('param.');
        $f      = new BorrowModel();
        $wzd    = $f->getOne($data['stu_id']);
        $result = $wzd;
        $doc_obj    = new FilewithinModel();
        $re_arr     = array();
        foreach($result as &$v)
        {

            switch ($v['type'])
            {
                case 1:
                    $v['type']  = "普通借阅  ";
                    break;
                case 2:
                    $v['type']  = "室内查阅";
                    break;
                case 3:
                    $v['type']  = "在线查阅";
                    break;
            }
            $v['statuscon']    = $v['status']==1?"已归还":"借阅中";
            $value['statuscon'] = $v['statuscon'];

            //查询具体的是哪份文件
            $content=array();
            if($v['docs'] > 0)
            {
                $doc_arr    = explode(",",$v['docs']);
                $doc_list   = $doc_obj->getInfoByIds($doc_arr);//查询到了具体的文件

                foreach ($doc_list as &$value)
                {

                    $value['start_time'] = $v['start_time'];
                    $value['end_time'] = $v['end_time'];
                    $value['type'] = $v['type'];
                    $value['phone'] = $v['phone'];
                   // $value['name'] = $v['name'];
                    $value['phone'] = $v['phone'];
                    $value['document'] = $v['document'];
                    $value['dep_name'] = $v['dep_name'];
                    $value['content'] = $v['content'];
                    $value['type'] = $v['type'];
                    $value['id'] = $v['id'];
                    $re_arr[]=$value;
                }

            }else{
                
                switch ($v['type'])
                {
                    case 1:
                        $v['type']  = "普通借阅  ";
                        break;
                    case 2:
                        $v['type']  = "室内查阅";
                        break;
                    case 3:
                        $v['type']  = "在线查阅";
                        break;
                }
                $v['name']  = '全卷借阅('.$v['file_number'].')';
                $re_arr[]   = $v;

            }
        }

        return  array("code"=>0,"msg"=>"","data"=>$re_arr);

    }

    //借阅详情
    public function borrowDetail(){
        $data   = input('param.');
        $stu_id     = $data['stu_id'];
        $this->assign('stu_id',$stu_id);
        return view('change');
    }

    //获取借阅列表
    public function setJieYue()
    {
        $data   = input('param.');
        $id         = $data['id'];
        $sid        = $data['sid'];
        $true_time  = $data['true_time'];
        $number     = $data['number'];

        $s=new StudentsModel();
        $s->updateStatus($sid);

        $save_arr = array();
        $save_arr['true_time']  = $true_time;
        $save_arr['status']     = 1;
        $f      = new BorrowModel();
        $f->updateData($save_arr,$id);

        $type   = session("type");
        $h      = new FilewithinModel();
        $h->getOneUpdate($data,$type);

        //写变动日志
        $admin_arr  = session('admin');
        $log_obj    = new LogModel();
        $save_data  = array();
        $save_data['stu_id']        = $sid;
        $save_data['act_id']        = $admin_arr['id'];
        $save_data['created_at']    = date("Y-m-d H:i:s");
        $save_data['name']          = "借阅归还";
        $save_data['before']        = "借阅号：".$number;
        $save_data['after']         = "案卷归还，归还时间：".$true_time;

        $log_obj->createData($save_data);

        $this->success("成功");

    }
}