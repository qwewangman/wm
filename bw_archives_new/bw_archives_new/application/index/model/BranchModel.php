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


class BranchModel extends Model
{
    // 大棚
    protected $table = 'oa_green_house';


    //大棚
    public function getBranch($j_bumen)
    {
        return $this->where("green_house_name",$j_bumen)->find();
    }

    //新建
    public function createData($data)
    {
        $this->save($data);
        return $this->getLastInsID();
    }
 //获取列表
    public function getlist()
    {
        $list = $this->select();
        return $list;
    }











}