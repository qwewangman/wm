<?php
/**
 * Created by PhpStorm.
 * User: 25352
 * Date: 2018/5/15
 * Time: 10:51
 */
namespace app\index\model;

use think\Model;
use think\Db;


class CategoryModel extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = 'oa_land';

    //根据年级获取最后一条数据
    public function getInfoByGrade($sensor_name,$sensor_ip)
    {
        return $this->where("land_name",$sensor_name)->where('crop',$sensor_ip)->find();
    }


    //查询地块
    public function getList()
    {
        $list = $this->select();
        return $list;

    }

    //查询
    public function getAllList($cid=0)
    {
        if($cid >0)
        {
            return $this->where("branch_id",$cid)->order("id")->select();
        }else{
            return $this->where("branch_id",0)->order("id")->select();
        }

    }
    //根据ID获取数据
    public function getInfoById($id)
    {
        return $this->where("id",$id)->find();
    }
    //新建地块
    public function createData($data)
    {
        //$data['created_at'] = date("Y-m-d H:i:s");
        //$data['updated_at'] = date("Y-m-d H:i:s");
        $this->save($data);
        return $this->getLastInsID();
    }
    //修改
    public function updateData($data,$id)
    {
        $data['updated_at'] = date("Y-m-d H:i:s");
        return $this->update($data,"id=".$id);
    }
}
