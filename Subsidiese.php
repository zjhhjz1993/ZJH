<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/9/8
 * Time: 15:53
 */
namespace app\member\model;

use think\Model;

class Subsidiese extends Model{
    // 设置当前模型对应的完整数据表名称
    protected $name = 'member_subsidiese';
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * @param $field string(需要的字段)
     * @param $map array(条件合集)
     * @param string $time_field string(时间字段)
     * @param string $time_cycle string(时间周期)
     * @param int $page_number int(页面条数)
     * @return $this|\think\Paginator
     * 获取列表
     */
    static function getList($field,$map,$time_field = '',$time_cycle = '',$page_number = 10){
        $result = self::alias('a')->field($field)->where($map);

        if(isset($time_field) && isset($time_cycle) && $time_field != '' && $time_cycle != ''){
            $result = $result->whereTime($time_field,$time_cycle);
        }

        $result =  $result->leftJoin('dp_member_company b','a.cid = b.id')
            ->leftJoin('dp_member_subsidies s','a.cid = s.cid')
            ->leftJoin('dp_member_authenmessenger c','b.parent_id = c.uid')
            ->group('a.id')->order('a.grant_time','DESC')
            ->paginate($page_number,false,['query'=>request()->param()]);

        return $result;
    }

    /**
     * @param string $time
     * @param string $ids
     * @return float|\think\db\Query
     * 申报金额
     */
     static function DeclaredAmount($time = '',$ids = ''){
        if(isset($ids) && $ids != ''){
            if($time != '') return self::alias('a')->whereNotIn('b.cooperation_type','5')->where('a.submit_data',2)
                ->whereTime('a.create_time',$time)->whereIn('b.ced_id',$ids)->join('dp_member_subsidies b','a.cid = b.cid','left')->sum('advance');
            else return self::alias('a')->whereNotIn('b.cooperation_type','5')->where('a.submit_data',2)
                ->whereIn('ced_id',$ids)->join('dp_member_subsidies b','a.cid = b.cid','left')->sum('advance');
        }else{
            if($time != '') return self::whereTime('submit_time',$time)->sum('advance');
            else return self::sum('advance');
        }
    }

    /**
     * @param string $ids
     * @return $this|float
     * 实际申报金额
     */
    static function ActualDeclaredAmount($ids = ''){
        $result = self::alias('a')->where('a.grant_status','1')->where('b.cooperation_type','<>','5');
        if(isset($ids) && $ids != ''){
            $result = $result->whereIn('b.ced_id',$ids);
        }
        $result = $result->join('dp_member_subsidies b','a.cid = b.cid','left')
            ->sum('a.subsidies_amount');
        return $result;
    }

    /**
     * @param string $time
     * @param string $ids
     * @return $this|float|\think\db\Query
     * 服务费
     */
    static function ServiceCharge($time = '',$ids = ''){
        $result = self::alias('a')->where('a.grant_status',1);
        if(isset($time) && $time != ''){
            $result = $result->whereTime('a.grant_time',$time);
        }
        if(isset($ids) && $ids != ''){
            $result = $result->whereIn('b.ced_id',$ids);
        }
        $result = $result->join('dp_member_company b','a.cid = b.id','left')
            ->join('dp_member_authenmessenger c','b.parent_id = c.uid','left')
            ->sum('a.service_fee');
        return $result;
    }
}