<?php
namespace app\member\model;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/9/6
 * Time: 17:10
 */

use think\Model;

class Authenmessenger extends Model{
    // 设置当前模型对应的完整数据表名称
    protected $name = 'member_authenmessenger';
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * @param $map array(条件)
     * @param $field string(需要的字段)
     * @return mixed
     * 通过查询条件获取单独数据
     */
    static function getValues($map,$field){
        return self::where($map)->value($field);
    }

    /**
     * @param $field
     * @param $map
     * @param string $time_field
     * @param string $time_cycle
     * @param int $page_number
     * @return $this|\think\Paginator
     *
     */
    static function getList($field,$map,$time_field = '',$time_cycle = '',$page_number = 10){
        $result = self::alias('a')->field($field)->where($map);

        if(isset($time_field) && isset($time_cycle) && $time_field != '' && $time_cycle != ''){
            $result = $result->whereTime($time_field,$time_cycle);
        }

        $result =  $result->leftJoin('dp_member_user b','b.id = a.uid')
            ->leftJoin('dp_member_authenmessenger s','s.uid = b.sig_id')
            ->leftJoin('dp_member_authenmessenger p','p.uid = b.parent_id')
            ->group('b.id')->order('a.create_time','DESC')
            ->paginate($page_number,false,['query'=>request()->param()]);
        return $result;
    }

    /**
     * @param $time
     * @return float|string
     * 店铺数量
     */
    static function ShopNum($time = '',$ids = ''){
        if(isset($ids) && $ids != ''){
            if($time != '')return self::alias('a')
                ->whereTime('a.create_time',$time)->whereIn('b.sig_id',$ids)
                ->where('b.ambassador','>',0)
                ->where('a.status',2)
                ->join('dp_member_user b','b.id = a.uid','left')
                ->count('a.id');
            else return self::alias('a')->whereIn('b.sig_id',$ids)
                ->where('b.ambassador','>',0)
                ->where('a.status',2)
                ->join('dp_member_user b','b.id = a.uid','left')
                ->count('a.id');
        }else{
            if($time != '')return self::alias('a')->whereTime('a.create_time',$time)
                ->where('b.ambassador','>',0)
                ->where('a.status',2)
                ->join('dp_member_user b','b.id = a.uid','left')
                ->count('a.id');
            else return self::alias('a')
                ->where('b.ambassador','>',0)
                ->where('a.status',2)
                ->join('dp_member_user b','b.id = a.uid','left')
                ->count('a.id');
        }
    }

    /**
     * @param string $ids
     * @param string $time
     * @return float|string
     * 过期店铺汇总数据
     */
    static function getExpireCount($ids = '',$time = ''){
        if(isset($ids) && $ids != ''){
            if($time != '') $result = self::alias('a')
                ->whereTime('a.end_time',$time)->whereIn('b.sig_id',$ids)
                ->join('dp_member_user b','b.id = a.uid','left')
                ->count('a.id');
            else $result = self::alias('a')->where('a.end_time','<=',time())
                ->whereIn('b.sig_id',$ids)
                ->join('dp_member_user b','b.id = a.uid','left')
                ->count('a.id');
        }else{
            if($time != '') $result = self::alias('a')
                ->whereTime('a.end_time',$time)
                ->join('dp_member_user b','b.id = a.uid','left')
                ->count('a.id');
            else $result = self::alias('a')->where('a.end_time','<=',time())
                ->join('dp_member_user b','b.id = a.uid','left')
                ->count('a.id');
        }
        return $result;
    }

    /**
     * @param $type
     * @param string $ids
     * @return \think\db\Query
     * 店铺等级汇总数据
     */
    static function getMemberCount($type,$ids = ''){
        if(isset($ids) && $ids != '') $result = self::alias('a')->where('a.status',2)
            ->whereIn('b.ambassador',$type)->whereIn('b.sig_id',$ids)
            ->join('dp_member_user b','b.id = a.uid','left')->count('a.id');
        else $result = self::alias('a')->where('a.status',2)->whereIn('b.ambassador',$type)
            ->join('dp_member_user b','b.id = a.uid','left')->count('a.id');
        return $result;
    }

    /**
     * @param $label
     * @param string $ids
     * @return float|string
     * 客户类别汇总数据
     */
    static function getClassCount($label,$ids = ''){
        if(isset($ids) && $ids != '') $result = self::alias('a')->whereIn('a.channel_label',$label)->whereIn('b.sig_id',$ids)
            ->join('dp_member_user b','b.id = a.uid','left')->count('a.id');
        else $result = self::alias('a')->whereIn('a.channel_label',$label)
            ->join('dp_member_user b','b.id = a.uid','left')->count('a.id');
        return $result;
    }


    /**
     * @param string $start_time
     * @param string $end_time
     * @return float|string
     * 时间区间取数量
     */
    static function getBetweenCount($start_time = '',$end_time = ''){
        return self::whereBetweenTime('create_time',$start_time,$end_time)->where('status',2)->count('id');
    }

    /**
     * @param $ids
     * @param string $time
     * @param int $status 默认认证成功
     * @return float|string
     * 下级店铺数量（分支的总下级）
     */
    static function SubordinateShopNum($ids,$time = '',$status = 2){
        if($time != '') $result = self::whereIn('uid',$ids)->whereTime('create_time',$time)->where('status',$status)->count();
        else $result = self::whereIn('uid',$ids)->where('status',$status)->count();
        return $result;
    }

    /**
     * @param $ids
     * @param string $time
     * @param int $status 默认认证成功
     * @return float|string
     * 下级店铺支付数量（分支的总下级）
     */
    static function SubordinatePayShopNum($ids,$time = '',$status = 2){
        if($time != '') $result = self::whereIn('uid',$ids)->whereTime('create_time',$time)->where('is_pay','>',0)->where('status',$status)->count();
        else $result = self::whereIn('uid',$ids)->where('is_pay','>',0)->where('status',$status)->count();
        return $result;
    }
}