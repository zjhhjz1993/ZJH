<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/9/8
 * Time: 17:58
 */
namespace app\member\model;

use think\Model;

class Quity extends Model{
    // 设置当前模型对应的完整数据表名称
    protected $name = 'member_quity';
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * @param $uid
     * @param string $time
     * @param int $status 1收入 2支出
     * @param string $type
     * @return float
     * 个人财务收入支出
     */
     static function PersonalFinance($uid,$time = '',$status = 1,$type = ''){
        if(isset($type) && $type == 0){
            if($time != '') $result = self::where('uid',$uid)
                ->whereTime('addtime',$time)->where('status',$status)->where('type',$type)->sum('num');
            else $result = self::where('uid',$uid)->where('status',$status)->where('type',$type)->sum('num');
        }elseif(isset($type) && $type == 1){
            if($time != '') $result = self::where('uid',$uid)
                ->whereTime('addtime',$time)->where('status',$status)->where('type',$type)->sum('num');
            else $result = self::where('uid',$uid)->where('status',$status)->where('type',$type)->sum('num');
        }else{
            if($time != '') $result = self::where('uid',$uid)
                ->whereTime('addtime',$time)->where('status',$status)->sum('num');
            else $result = self::where('uid',$uid)->where('status',$status)->sum('num');
        }
        return $result;
    }

    /**
     * @param string $ids
     * @return float
     * 销售或总收益金额
     */
    static function getProfitAmount($ids = ''){
        $result = self::alias('a')->whereNotIn('a.type','5,10,13')->where('b.ambassador','<>',0);
        if(isset($ids) && $ids != ''){
            $result = $result->whereIn('b.sig_id',$ids);
        }
        $result = $result->join('dp_member_user b','b.id = a.uid','left')
            ->sum('a.num');
        return $result;
    }

    /**
     * @param string $id
     * @return float
     * 个人收益金额
     */
    static function getProfitAmounts($id){
        $result = self::where('uid',$id)->whereNotIn('type','5,10,13')->sum('num');
        return $result;
    }

    /**
     * @param string $time 时间条件
     * @param $map array(查询条件)
     * @return float
     * 根据查询条件计算金额
     */
    static function geiAllFinance($map,$time = ''){
        if($time != '') $result = self::whereTime('addtime',$time)->where($map)->sum('num');
        else $result = self::where($map)->sum('num');
        return $result;
    }

}