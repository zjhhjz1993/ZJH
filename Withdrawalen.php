<?php
namespace app\member\model;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/9/11
 * Time: 15:43
 */

use think\Model;

class Withdrawalen extends Model{

    // 设置当前模型对应的完整数据表名称
    protected $name = 'member_withdrawalen';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * @param $field string(需要的字段)
     * @param $map array(条件合集)
     * @param string $time_field string(时间字段)
     * @param string $time_cycle string(时间周期)
     * @param int $page_number int(页面条数)
     * @return $this|\think\Paginator
     * 获取提现列表
     */
    static function getList($field,$map,$time_field = '',$time_cycle = '',$page_number = 10){
        $result = self::alias('a')->field($field)->where($map);

        if(isset($time_field) && isset($time_cycle) && $time_field != '' && $time_cycle != ''){
            $result = $result->whereTime($time_field,$time_cycle);
        }

        $result =  $result->leftJoin('dp_member_user b','a.uid = b.id')
            ->leftJoin('dp_member_authenmessenger c','b.id = c.uid')
            ->leftJoin('dp_member_authenmessenger s','s.uid = b.sig_id')
            ->leftJoin('dp_member_authenmessenger p','p.uid = b.parent_id')
            ->group('a.id')->order('a.create_time','DESC')
            ->paginate($page_number,false,['query'=>request()->param()]);
        return $result;
    }


    /**
     * 伙伴提现金额
     * @param string $status
     * @param string $ids
     * @param string $time
     * @return $this|float|\think\db\Query
     */
    static function getPartnerFee($status = '',$ids = '',$time = ''){
        $result = self::alias('a')->where('b.ambassador','<>',0);

        if(isset($ids) && $ids != ''){
            $result = $result->whereIn('b.sig_id',$ids);
        }
        if(isset($status) && $status != ''){
            $result = $result->where('a.remind_status',$status);
        }
        if(isset($time) && $time != ''){
            $result = $result->whereTime('a.create_time',$time);
        }

        $result = $result->join('dp_member_user b','b.id = a.uid','left')
            ->sum('a.amount');

        return $result;
    }


}