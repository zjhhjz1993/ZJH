<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/9/10
 * Time: 16:08
 */
namespace app\member\model;

use think\Model;

class Followup extends Model{
    // 设置当前模型对应的完整数据表名称
    protected $name = 'member_followup';
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    static function getNotFollow($ids = ''){
        $result = self::alias('a')->field('a.id,a.types,a.update_time')
            ->where('a.delete_time',0)->where('a.status',0)->whereNotIn('s.cooperation_type','5');
        if(isset($ids) && $ids != ''){
            $result = $result->whereIn('a.uid',$ids);
        }
        $result = $result->join('dp_member_subsidies s','a.sub_id = s.id','left')
            ->order('a.update_time','DESC')->group('s.cid')->select();
        return $result;
    }

    static function getNotFollows($ids = ''){
        $result = self::alias('a')->field('a.id,a.types,a.update_time')
            ->where('a.delete_time',0)->where('a.status',0);
        if(isset($ids) && $ids != ''){
            $result = $result->whereIn('a.uid',$ids);
        }
        $result = $result->join('dp_member_company s','a.sub_id = s.id','left')
            ->order('a.update_time','DESC')->group('s.id')->select();
        return $result;
    }

    static function getNotFollowe($ids = ''){
        $result = self::alias('a')->field('a.id,a.types,a.update_time')
            ->where('a.delete_time',0)->where('a.status',0)->where('s.ambassador','<>',0);
        if(isset($ids) && $ids != ''){
            $result = $result->whereIn('a.uid',$ids);
        }
        $result = $result->join('dp_member_user s','a.sub_id = s.id','left')
            ->join('dp_member_authenmessenger b','a.sub_id = b.uid','left')
            ->order('a.update_time','DESC')->group('s.id')->select();
        return $result;
    }
}