<?php
/**
 * Created by PhpStorm.
 * User: 25352
 * Date: 2018/5/14
 * Time: 13:41
 */

namespace app\index\model;

use think\Model;
use think\Db;


class FileModel extends Model
{
    protected $table = 'oa_students';


    //未在档案卷状态   0 未收取   1 在档   2  借出   3  迁出  4 退档  5 转研究生 6 毕业暂存  7 村官暂存
    public function getStudentsList($data,$type=1)
    {
        if($type <=0)
            $type = 1;
        $num    = $data['limit'];
        $page   = $data['page']<=0?1:$data['page'];

        $p      = ($page-1)*$num;
        $lmt    = $p.",".$num;


        $where  = "type = ".$type ;
        if(isset($data['sc_number']) && $data['sc_number'] !="")
        {
            $where .= "  and sc_number = '".$data['sc_number']."'";
            $wherec     = $where;
        }else{

            if (isset($data['file_number']) && $data['file_number'] != "") {
                $where .= "  and file_number = '" . $data['file_number'] . "'";
                $wherec = $where;
            } else {
                if(isset($data['card']) && $data['card'] !="")
                {
                    $where .= "  and card = '".$data['card']."'";
                    $wherec     = $where;
                }else{
                    if(isset($data['phone']) && $data['phone'] !="")
                    {
                        $where .= "  and phone = '".$data['phone']."'";
                        $wherec     = $where;
                    }else {
                        if (isset($data['grade']) && $data['grade'] > 0)
                            $where .= "  and grade = '" . $data['grade'] . "'";

                        if (isset($data['depart']) && $data['depart'] > 0)
                            $where .= "  and depart = '" . $data['depart'] . "'";

                        if (isset($data['major']) && $data['major'] > 0)
                            $where .= "  and major = '" . $data['major'] . "'";

                        $wherec = $where;

                        if (isset($data['status']) && $data['status'] >= 0) {
                            $wherec .= "  and status = " . $data['status'];
                            $where .= "  and s.status = " . $data['status'];
                        }
                        if (isset($data['name']) && $data['name'] != "") {
                            $wherec .= "  and name like '%" . $data['name'] . "%'";
                            $where .= "  and s.name like '%" . $data['name'] . "%'";
                        }

                    }
                }
            }
        }

        $list = $this->alias('s')->where($where)
            ->join(['oa_depart'=>'d'],'s.depart = d.id','LEFT')
            ->join(['oa_major'=>'m'],'s.major = m.id','LEFT')
            ->field("s.id,s.preschool,s.name,file_number,file_derice,sc_number,file_num,country,nation,sex,d.name AS dname,m.name AS mname,grade,class,card,phone,addtime,s.status,s.xj_status")
            ->order('s.id','desc')
            ->limit($lmt)
            ->select();

        $arr = array();
        $arr['count'] = $this->where($wherec)->count();
        $arr['content'] = $list;
        return $arr;

    }

    //未在档案卷状态   0 未收取   1 在档   2  借出   3  迁出  4 退档  5 转研究生 6 毕业暂存  7 村官暂存
    public function getAllStudentsList($data,$type=1)
    {
        if($type <=0)
            $type = 1;


        $where  = "type = ".$type ;


        if (isset($data['grade']) && $data['grade'] > 0)
            $where .= "  and grade = '" . $data['grade'] . "'";

        if (isset($data['depart']) && $data['depart'] > 0)
            $where .= "  and depart = '" . $data['depart'] . "'";

        if (isset($data['major']) && $data['major'] > 0)
            $where .= "  and major = '" . $data['major'] . "'";

        $wherec = $where;




        $list = $this->alias('s')->where($where)
            ->join(['oa_depart'=>'d'],'s.depart = d.id','LEFT')
            ->join(['oa_major'=>'m'],'s.major = m.id','LEFT')
            ->field("s.id,s.name,file_number,file_derice,sc_number,file_num,country,nation,sex,d.name AS dname,m.name AS mname,grade,class,card,phone,addtime,s.status,s.xj_status")
            ->order('s.id','desc')
            ->select();

        $arr = array();
        $arr['count'] = $this->where($wherec)->count();
        $arr['content'] = $list;
        return $arr;

    }
    //根据id获取所有关联数据单一数据   0 未收取   1 在档   2  借出   3  迁出  4 退档  5 转研究生 6 毕业暂存  7 村官暂存
    public function getStudentOne($id)
    {

        $info = $this->alias('s')->where("s.id=".$id)
            ->join(['oa_depart'=>'d'],'s.depart = d.id','LEFT')
            ->join(['oa_major'=>'m'],'s.major = m.id','LEFT')
            ->field("s.*,d.name AS dname,m.name AS mname")
            ->find();
        return $info;

    }
    public function getStudentAll($id)
    {

        $info = $this->alias('s')->where("s.id",'in',"$id")
            ->join(['oa_depart'=>'d'],'s.depart = d.id','LEFT')
            ->join(['oa_major'=>'m'],'s.major = m.id','LEFT')
            ->field("s.*,d.name AS dname,m.name AS mname")
            ->select();

        /*$info = $this->alias('s')->where("s.id=".$id)
            ->join(['oa_depart'=>'d'],'s.depart = d.id','LEFT')
            ->join(['oa_major'=>'m'],'s.major = m.id','LEFT')
            ->field("s.*,d.name AS dname,m.name AS mname")
            ->find();*/
        return $info;

    }
    //
    public function getStudentByDepart($grade,$depart)
    {
        $info   = $this->where("grade",$grade)->whereIn("depart",$depart)->order("depart")->order("id")->select();
        return $info;

    }

    //根据学号获取数据
    public function getInfoByNum($sc_number,$id=0)
    {
        if($id == 0)
        {
            return $this->where("sc_number",$sc_number)->find();
        }else{
            return $this->where("sc_number",$sc_number)->where("id","<>",$id)->find();
        }

    }

    //根据存放位置获取数据
    public function getInfoByDerice($file_derice,$id=0)
    {
        if($id == 0)
        {
            return $this->where("file_derice",$file_derice)->where("status","<>",3)->find();
        }else{
            return $this->where("file_derice",$file_derice)->where("id","<>",$id)->where("status","<>",3)->find();
        }
    }

    //根据ID获取数据
    public function getInfoById($id)
    {
        return $this->where("id",$id)->find();
    }

    //根据ID获取数据
    public function getCountByYId($grade,$yid)
    {
        return $this->where("depart",$yid)->where("grade",$grade)->count();
    }
    //根据年级获取最后一条数据
    public function getInfoByGrade($grade)
    {
        $type   = session("type");
        if($type <= 0)
            $type =1;
        return $this->where("grade",$grade)->where("type",$type)->order("id","DESC")->find();
    }

    public function createAllData($data)
    {
        return $this->saveAll($data);
    }
    //新建
    public function createData($data)
    {
        $data['created_at'] = date("Y-m-d H:i:s");
        $data['updated_at'] = date("Y-m-d H:i:s");



        $this->save($data);
        return $this->getLastInsID();
    }
    //新建
    public function updateData($data,$id)
    {
        $data['updated_at'] = date("Y-m-d H:i:s");
        return $this->update($data,"id=".$id);
    }

    //更改状态
    public function setStatus($data,$ids)
    {
        $data['updated_at'] = date("Y-m-d H:i:s");
        return $this->where("id","IN",$ids)->update($data);
    }

    //更改状态
    public function deleteData($id)
    {
        return $this->where("id","=",$id)->delete();
    }


}