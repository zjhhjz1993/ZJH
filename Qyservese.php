<?php
namespace app\member\model;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/9/11
 * Time: 10:37
 */

use think\Model;

class Qyservese extends Model{
    // 设置当前模型对应的完整数据表名称
    protected $name = 'member_qyservese';
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * @param $field
     * @param $map
     * @param string $time_field
     * @param string $time_cycle
     * @param int $page_number
     * @return $this|\think\Paginator
     * 获取客户成交企业金额列表
     */
    static function getList($field,$map,$time_field = '',$time_cycle = '',$page_number = 10){
        $result = self::alias('a')->field($field)->where($map);

        if(isset($time_field) && isset($time_cycle) && $time_field != '' && $time_cycle != ''){
            $result = $result->whereTime($time_field,$time_cycle);
        }

        $result =  $result->leftJoin('dp_member_company c','a.cid = c.id')
            ->leftJoin('dp_member_authenmessenger b','c.parent_id = b.uid')
            ->order('a.create_time','DESC')
            ->paginate($page_number,false,['query'=>request()->param()]);
        return $result;
    }


        /**
     * @param string $ids
     * @param string $time
     * @return float|string
     * 成交企业服务金额
     */
    static function SigningServiceFee($time = '',$ids = ''){
        if(isset($ids) && $ids != ''){
            if($time != '') $result = self::alias('a')->where('a.delete_time',0)
                ->whereIn('c.signing_id',$ids)->whereTime('a.create_time',$time)
                ->leftJoin('dp_member_company c','a.cid = c.id')
                ->sum('a.payment');
            else $result = self::alias('a')->where('a.delete_time',0)->whereIn('c.signing_id',$ids)
                ->leftJoin('dp_member_company c','a.cid = c.id')
                ->sum('a.payment');
        }else{
            if($time != '') $result = self::alias('a')->where('a.delete_time',0)->whereTime('a.create_time',$time)
                ->leftJoin('dp_member_company c','a.cid = c.id')
                ->sum('a.payment');
            else $result = self::alias('a')->where('delete_time',0)
                ->leftJoin('dp_member_company c','a.cid = c.id')
                ->sum('a.payment');
        }
        return $result;
    }



}