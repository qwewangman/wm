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


class ProCityModel extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = 'oa_province';

    //查询
    public function getList()
    {
        return $this->select();
    }

    //查询
    public function getOneById($id)
    {
        return $this->where("id",$id)->find();
    }


    //查询
    public function getCityList($pid)
    {
        return Db::table('oa_city')->where("province",$pid)->select();
    }
}