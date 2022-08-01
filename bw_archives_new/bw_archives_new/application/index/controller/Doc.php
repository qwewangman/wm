<?php

namespace app\index\controller;
use app\index\model\FileModel;
use think;
use think\Db;
use think\Route;
use app\index\controller\Base;
use TCPDF;
use app\index\model\FilewithinModel;

use app\index\model\DepartmentModel;
use app\index\model\CategoryModel;
use app\index\model\LogModel;


class Doc extends Base
{
    //添加页面
    public function addDoc()
    {

        $data       = input('param.');

        $id         = $data['id'];

        $c      = new CategoryModel();
        $c_arr  = $c->getList();
        $this->assign('c_arr',$c_arr);
        $this->assign('id',$id);

        return view('adddoc');
    }

    //添加页面
    public function addOneDoc()
    {

        $c      = new CategoryModel();
        $c_arr  = $c->getList();
        $this->assign('c_arr',$c_arr);
        return view('addonedoc');
    }
    //上传
    public function upload()
    {
        return view('upload');
    }
    //立卷导入
    public function save()
    {

         $data           = input('param.');
        $tname          = $data['name'];

        $imgs           = isset($data['imgs'])?$data['imgs']:"";


        $student_id     = $data['student_id'];

        $sobj           = new FileModel();
        $s_info         = $sobj->getInfoById($student_id);

        if($imgs != "")
        {
            $data['enclo']          = implode(",",$imgs);
        }

        unset($data['imgs'],$data['file']);

        $obj    = new FilewithinModel();

        //获取最后一条数据
        $num    = 0;
        $sinfo  = $obj->getInfoBySid($student_id);
        if(!empty($sinfo))
        {
            $num    = $sinfo['serial_number'];
        }
        //生产件号
        $number  = $this->createFileNumber($s_info['file_number'],$num);

        $data['number']           = $number;
        $data['serial_number']    = $num+1;

        $obj->createData($data);


        //存储文件数量
        $save_arr   = array();
        $save_arr['file_num']   = $s_info['file_num']+$data['item_num'];
        $sobj->updateData($save_arr,$student_id);
        //写变动日志
        $admin_arr  = session('admin');
        $log_obj    = new LogModel();
        $save_data  = array();
        $save_data['stu_id']        = $student_id;
        $save_data['act_id']        = $admin_arr['id'];
        $save_data['created_at']    = date("Y-m-d H:i:s");
        $save_data['name']          = "添加文件";
        $save_data['after']         = "《".$tname."》文件已添加，件号：".$number;
        $log_obj->createData($save_data);

        $this->success("上传成功，可继续添加",url("/doc/addonedoc"));

    }

    //单文件录入
    private function saveOne($data,$file_number)
    {
        $tname          = $data['name'];
        $student_id     = $data['student_id'];

        $obj            = new FilewithinModel();
        //获取最后一条数据
        $num    = 0;
        $sinfo  = $obj->getInfoBySid($student_id);
        if(!empty($sinfo))
        {
            $num    = $sinfo['serial_number'];
        }
        //生产件号
        $number  = $this->createFileNumber($file_number,$num);
        $data['number']           = $number;
        $data['serial_number']    = $num+1;
        $obj->createData($data);


        //写变动日志
        $admin_arr  = session('admin');
        $log_obj    = new LogModel();
        $save_data  = array();
        $save_data['stu_id']        = $student_id;
        $save_data['act_id']        = $admin_arr['id'];
        $save_data['created_at']    = date("Y-m-d H:i:s");
        $save_data['name']          = "添加文件";
        $save_data['after']         = "《".$tname."》文件已添加，件号：".$number;
        $log_obj->createData($save_data);

        return true;
    }
    //文件上传 ---单文件
    public function saveMuch()
    {

        $data           = input('param.');
        $student_id     = $data['student_id'];//学生id//array_column()
        unset($data['student_id']);

        //案卷信息
        $sobj           = new FileModel();
        $s_info         = $sobj->getInfoById($student_id);

        /*if(empty($s_info))
        {
            error("案卷为空");
        }*/
        $file_number    = $s_info['file_number'];

        $save_arr       = array();
        foreach ($data as $key => $value ) {
            foreach ($value as $kk => $vv) {
                $save_arr[$kk][$key] = $vv;
            }
        }
        foreach($save_arr as $k=>$v){
            $v['student_id']    = $student_id;
            $this->saveOne($v,$file_number);
        }
        //存储文件数量
        $save_arr   = array();
        $save_arr['file_num']   = $s_info['file_num']+count($save_arr);
        $sobj->updateData($save_arr,$student_id);

        $this->success("上传成功，可继续添加",url("/doc/addonedoc"));

    }

    //编辑页面
    public function editDoc($id)
    {

        $data = input('param.');

        $stu_obj    = new FilewithinModel();
        $stu_info   = $stu_obj->getInfoByid($id);


        $c      = new CategoryModel();
        $c_arr  = $c->getList();

        $c_arr2    = $c->getList($stu_info['category_id']);
        $this->assign('c_arr',$c_arr);
        $this->assign('c_arr2',$c_arr2);
        $this->assign('stu_info',$stu_info);
        $this->assign('id',$id);

        return view('editdoc');
    }

    //编辑页面
    public function updateDoc($id)
    {

        $data           = input('param.');
        $imgs           = isset($data['imgs'])?$data['imgs']:"";
        $student_id     = $data['student_id'];

        $sobj           = new FileModel();
        $s_info         = $sobj->getInfoById($student_id);

        if(!empty($imgs))
        {
            $data['enclo']          = implode(",",$imgs);
        }

        unset($data['imgs'],$data['file']);

        $obj    = new FilewithinModel();

        $old_info   = $obj->getInfoByid($id);
        $obj->updateData($data,$id);


        //存储文件数量
        $save_arr   = array();
        $save_arr['file_num']   = $s_info['file_num']+($data['item_num']-$old_info['item_num']);
        $sobj->updateData($save_arr,$student_id);


        $this->success("修改成功，可继续添加",url("/doc/editdoc/".$id));
    }
    //添加页面
    public function addAllDoc()
    {

        $d = new DepartmentModel();
        $dep = $d->getDepartList();//所有舞系
        $this->assign('departlist',$dep);


        $c  = new CategoryModel();
        $c_arr  = $c->getList();
        $this->assign('c_arr',$c_arr);

        return view('addalldoc');
    }


    //立卷导入
    public function saveAll()
    {
        $data           = input('param.');
        $category_id    = $data['category_id'];
        $name           = $data['name'];
        $nian           = $data['nian'];
        $yue            = $data['yue'];
        $ri             = $data['ri'];
        $stu_ids        = $data['stu_ids'];

        $stu_arr        = explode(",",$stu_ids);

        $sobj           = new FileModel();

        foreach ($stu_arr AS $value)
        {
            $stu_id     = $value;
            $s_info     = $sobj->getInfoById($stu_id);

            //获取最后一条数据
            $obj        = new FilewithinModel();
            $num        = 0;
            $sinfo      = $obj->getInfoBySid($stu_id);
            if(!empty($sinfo))
            {
                $num    = $sinfo['serial_number'];
            }
            //生产件号
            $number  = $this->createFileNumber($s_info['file_number'],$num);

            $save_data  = array();
            $save_data['student_id']        = $stu_id;
            $save_data['category_id']       = $category_id;
            $save_data['name']              = $name;
            $save_data['nian']              = $nian;
            $save_data['yue']               = $yue;
            $save_data['ri']                = $ri;
            $save_data['item_num']          = $data['item_num'];
            $save_data['page_num']          = $data['page_num'];
            $save_data['name_sub']          = $data['name_sub'];
            $save_data['content']           = $data['content'];
            $save_data['number']            = $number;
            $save_data['serial_number']     = $num+1;

            $obj->createData($save_data);


            //存储文件数量
            $save_arr   = array();
            $save_arr['file_num']   = $s_info['file_num']+$data['item_num'];
            $sobj->updateData($save_arr,$stu_id);

            //写变动日志
            $admin_arr  = session('admin');
            $log_obj    = new LogModel();
            $save_data  = array();
            $save_data['stu_id']        = $stu_id;
            $save_data['act_id']        = $admin_arr['id'];
            $save_data['created_at']    = date("Y-m-d H:i:s");
            $save_data['name']          = "添加文件";
            $save_data['after']         = "《".$name."》文件已添加，件号：".$number;
            $log_obj->createData($save_data);
        }

        $this->success("上传成功，可继续添加",url("/doc/addalldoc"));

    }
    //获取列表
    public function getDoc()
    {

        $this->assign('departlist',1);
        //print_r($c_arr);die;
        $this->assign('c_arr',2);
        return view('doclist');

    }
    //删除文件deleteDoc
    public function deleteDoc(){
        $data           = input('param.');
        $id=$data['id'];

        $c  = new FilewithinModel();
        $doc  = $c->deleteDoc($id);
        return $doc;


    }
    //获取列表
    public function getOneDoc()
    {

        $data   = input('param.');
        $id     = $data['id'];

        $f      = new FilewithinModel();
        $doc_info   = $f->getOne($id);
        $doc_info['imgs']   = explode(",",$doc_info['enclo']);
        $doc_info['enum']   = count($doc_info['imgs']);
        $doc_info['jiou']   = $doc_info['enum']%2==0?2:1;

        $fin    = new CategoryModel();
        $cate   = $fin->getInfoById($doc_info['category_id']);
        $cat_info   = $fin->getInfoById($cate['branch_id']);
        $doc_info['ocname'] = $cat_info['name'];

        $this->assign('doc_info',$doc_info);
        return view('onedoc');

    }

    //获取单一条数据
    public function getDocList()
    {
        $data   = input('param.');
        $type   = session("type");

        $f      = new FilewithinModel();
        $wzd    = $f->getAllList($data,$type);
        $count  = $wzd['count'];

        $result = $wzd['content'];

        foreach($result as $k=>&$v)
        {

            $v['num']   = $k+1;
            $enclo      = explode(",",$v['enclo']);

            $img_src    = $enclo[0];

            $img        = '<img src="'.$img_src.'"  class="showimg" >';


            switch ($v['status'])
            {
                case 0:
                    $status = "在卷";
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
            $v['showimg']       = $img;
            $v['status']        = $status;


            $v['within_time']    = $v['nian']."-".$v['yue']."-".$v['ri'];
        }
        $result = array("code"=>0,"msg"=>"","count"=>$count,"data"=>$result,"page"=>$data['page']);
        echo json_encode($result);
    }
}