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


class MajorModel extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = 'oa_major';



    //查询院系专业
    public function getAllList($id)
    {
        return $this->where('department_id='.$id)->select();
    }
    //查询院系专业
    public function getMajorList($id)
    {
        return $this->where('department_id='.$id)->where("status",1)->select();
    }
    //获取院系id
    public function getDepId($id)
    {
        $dep = $this->where("id='".$id."'")->field('name')->find();
        return $dep['name'];
    }

    //新建
    public function createData($data)
    {
        $data['created_at'] = date("Y-m-d H:i:s");
        $data['updated_at'] = date("Y-m-d H:i:s");
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