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

class BorrowModel extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = 'oa_sensor';


    //根据年级获取最后一条数据
    public function getInfoByGrade($sensor_name,$sensor_ip)
    {
        return $this->where("sensor_name",$sensor_name)->where('sensor_ip',$sensor_ip)->find();
    }

    //新建
    public function createData($data)
    {
        $this->save($data);
        return $this->getLastInsID();
    }

    //新建
    public function getList($data,$type)
    {
        $num    = $data['limit'];
        $page   = $data['page']<=0?1:$data['page'];
        $p      = ($page-1)*$num;
        $lmt    = $p.",".$num;

        $where  = "os.type = ".$type ;
        //------------------------------
        if(isset($data['sc_number']) && $data['sc_number'] !="")
        {
            $where      .= "  and os.sc_number = '".$data['sc_number']."'";
            $wherec     = $where;
        }else{
            if (isset($data['file_number']) && $data['file_number'] != "") {
                $where .= "  and os.file_number = '" . $data['file_number'] . "'";
                $wherec = $where;
            } else {

                if (isset($data['name']) && $data['name'] != "") {
                    $where .= "  and os.name like '%" . $data['name'] . "%'";
                }
                $wherec = $where;
            }
        }

        //---------------------------------
        $list = $this->alias('s')->where($where)
            ->join(['oa_students'=>'os'],'s.student_id = os.id','LEFT')
            ->field("s.*,os.name AS dname,os.file_number,os.sc_number,os.grade,os.depart,os.major,os.file_derice,os.addtime")
            ->order('s.id','desc')
            ->group('os.file_number')
            ->limit($lmt)
            ->select();

        $arr = array();
        $arr['count'] = $this->alias('s')->where($wherec)
            ->join(['oa_students'=>'os'],'s.student_id = os.id','LEFT')
            ->count();
        //$arr['count'] =count($list)->where($wherec);
        $arr['content'] = $list;

        return $arr;
    }
    //借阅查询详情
    public function getOne($data)
    {
        $list = $this->alias('s')->where('s.student_id',$data)
            ->join(['oa_students'=>'d'],'s.student_id = d.id','INNER')
            ->join(['oa_department'=>'od'],'s.department_id = od.dep_number','INNER')
            ->field("s.*,s.name as document,d.name AS dname,d.file_number,d.sc_number,d.grade,d.depart,d.major,od.dep_name")
            ->order('s.id','desc')
            ->select();
        return $list;
    }

    //新建
    public function updateData($data,$id)
    {
        $data['updated_at'] = date("Y-m-d H:i:s");
        return $this->update($data,"id=".$id);
    }
}
