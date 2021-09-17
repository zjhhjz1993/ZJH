<?php
namespace app\member\model;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/9/6
 * Time: 17:10
 */

use think\Model;

class Company extends Model{
    // 设置当前模型对应的完整数据表名称
    protected $name = 'member_company';
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * @param $field string(需要的字段)
     * @param $map array(条件合集)
     * @param string $time_field string(时间字段)
     * @param string $time_cycle string(时间周期)
     * @param int $page_number int(页面条数)
     * @return $this|\think\Paginator
     * 获取企业列表 连表查询
     */
    static function getList($field,$map,$time_field = '',$time_cycle = '',$page_number = 10){
        $result = self::alias('a')->field($field)->where($map);

        if(isset($time_field) && isset($time_cycle) && $time_field != '' && $time_cycle != ''){
            $result = $result->whereTime($time_field,$time_cycle);
        }

        $result =  $result->leftJoin('dp_member_authenmessenger b','a.parent_id = b.uid')
            ->leftJoin('dp_member_subsidies s','a.id = s.cid')
            ->leftJoin('dp_member_user c','a.ced_id = c.id')
            ->group('a.id')->order('a.sign_up_time','DESC')
            ->paginate($page_number,false,['query'=>request()->param()]);
        return $result;
    }

    /**
     * @param $field
     * @param $map
     * @param $order
     * @param int $page_number
     * @return \think\Paginator
     * 获取企业列表 非连表
     */
    static function getLists($field,$map,$order,$page_number = 10){
        $result = self::field($field)->where($map)->order($order)
            ->paginate($page_number,false,['query'=>request()->param()]);
        return $result;
    }

    /**
     * @param $map
     * @param $value
     * @return float
     * 计算某个字段求和
     */
    static function getSumValue($map,$value){
        $result = self::where($map)->sum($value);
        return $result;
    }

    /**
     * @param string $field
     * @param string $time
     * @return \think\db\Query
     * 根据时间查数量
     */
    static function getCount($field = '',$time = ''){
        if($field != '' && $time != '') return self::whereTime($field,$time)
            ->where('sub_num','>',0)
            ->count('id');
        else return self::where('sub_num','>',0)->count('id');
    }

    /**
     * @param $map
     * @return float|string
     * 自设查询条件 查数量
     */
    static function getByCount($map){
        return self::where($map)->count('id');
    }

    /**
     * @param $type
     * @param string $ids
     * @param string $time
     * @return \think\db\Query
     * 合作企业汇总数据
     */
    static function getCompanyCount($type,$ids = '',$time = ''){
        if(isset($ids) && $ids != ''){
            if($time != '') $result = self::whereNotIn('cooperation_type',$type)->whereIn('ced_id',$ids)->whereTime('sign_up_time',$time)->count('id');
            else $result = self::whereNotIn('cooperation_type',$type)->whereIn('ced_id',$ids)->count('id');
        }else{
            if($time != '') $result = self::whereNotIn('cooperation_type',$type)->whereTime('sign_up_time',$time)->count('id');
            else $result = self::whereNotIn('cooperation_type',$type)->count('id');
        }
        return $result;
    }

    /**
     * @param string $ids
     * @param string $time
     * @return float|string
     * 过期会员汇总数据
     */
    static function getExpireCount($ids = '',$time = ''){
        if(isset($ids) && $ids != ''){
            if($time != '') $result = self::where('cooperation_type',4)->whereIn('ced_id',$ids)->whereTime('end_time',$time)->count('id');
            else $result = self::where('cooperation_type',4)->whereIn('ced_id',$ids)->count('id');
        }else{
            if($time != '') $result = self::where('cooperation_type',4)->whereTime('end_time',$time)->count('id');
            else $result = self::where('cooperation_type',4)->count('id');
        }
        return $result;
    }

    /**
     * @param $type
     * @param string $ids
     * @return \think\db\Query
     * 客户等级汇总数据
     */
    static function getMemberCount($type,$ids = ''){
        if(isset($ids) && $ids != '') $result = self::whereIn('cooperation_type',$type)->whereIn('ced_id',$ids)->count('id');
        else $result = self::whereIn('cooperation_type',$type)->count('id');
        return $result;
    }

    /**
     * @param $label
     * @param string $ids
     * @return float|string
     * 客户类别汇总数据
     */
    static function getClassCount($label,$ids = '',$str_time = '',$end_time = ''){
        if(isset($ids) && $ids != '') {
            $result = self::where('cooperation_type','<>',5)->where('customer_label',$label)->whereIn('ced_id',$ids);
            if(isset($str_time) && $str_time != '' && $end_time = ''){
                $result = $result->whereTime('create_time',$str_time);
            }
            if(isset($str_time) && isset($end_time) && $str_time != '' && $end_time != ''){
                $result = $result->whereBetweenTime('create_time',$str_time,$end_time);
            }
            $result =  $result->count('id');
        } else{
            $result = self::where('cooperation_type','<>',5)->where('customer_label',$label);
            if(isset($str_time) && $str_time != '' && $end_time = ''){
                $result = $result->whereTime('create_time',$str_time);
            }
            if(isset($str_time) && isset($end_time) && $str_time != '' && $end_time != ''){
                $result = $result->whereBetweenTime('create_time',$str_time,$end_time);
            }
            $result = $result->count('id');
        }
        return $result;
    }

    /**
     * @param string $start_time
     * @param string $end_time
     * @param string $type
     * @return float|string
     * 根据时间查数量
     */
    static function getBetweenCount($start_time = '',$end_time = '',$type = ''){
        if(isset($type) && $type == 1){
            $result = self::whereBetweenTime('sign_up_time',$start_time,$end_time)->where('cooperation_type','<>',5)->where('sub_num','>',0)->count('id');
        }elseif(isset($type) && $type == 2){
            $result = self::whereBetweenTime('sign_up_time',$start_time,$end_time)->whereNotIn('cooperation_type','0,5')->where('sub_num','>',0)->count('id');
        }elseif(isset($type) && $type == 3){
            $result = self::whereBetweenTime('sign_up_time',$start_time,$end_time)->whereNotIn('cooperation_type','0,5')->where('sub_num','>',0)->sum('members_fee');
        }else{
            $result = self::whereBetweenTime('create_time',$start_time,$end_time)->where('sub_num','>',0)->count('id');
        }
        return $result;
    }

    /**
     * @param $keyword
     * @param $type
     * @return float|string
     * 签约数量
     */
    public static function SignNum($keyword,$type){
        if($keyword != '') return self::whereTime('sign_up_time',$keyword)->whereNotIn('cooperation_type',$type)
            ->where('sub_num','>',0)
            ->count('id');
        else return self::whereNotIn('cooperation_type',$type)->where('sub_num','>',0)->count('id');
    }

    /**
     * @param $keyword
     * @param $type
     * @return float
     * 签约费
     */
    public static function MembersFees($keyword,$type){
        if($keyword != '') return self::whereTime('sign_up_time',$keyword)->whereNotIn('cooperation_type',$type)
            ->where('sub_num','>',0)
            ->sum('members_fee');
        else return self::whereNotIn('cooperation_type',$type)->where('sub_num','>',0)->sum('members_fee');
    }

    /**
     * @param string $field
     * @param string $time
     * @return \think\db\Query
     * 根据时间查数量（分支的总下级）
     */
    public static function getSubordinateCount($ids,$field = '',$time = ''){
        if($field != '' && $time != '') return self::whereTime($field,$time)
            ->whereIn('uid',$ids)
            ->where('sub_num','>',0)
            ->count('id');
        else return self::whereIn('uid',$ids)->where('sub_num','>',0)->count('id');
    }

    /**
     * @param $ids
     * @param string $keyword
     * @param $type
     * @return float|string
     * 签约数量（分支的总下级）
     */
    public static function SignSubordinateNum($ids,$keyword = '',$type){
        if($keyword != '') return self::whereTime('sign_up_time',$keyword)->whereIn('uid',$ids)->whereNotIn('cooperation_type',$type)
            ->where('sub_num','>',0)
            ->count('id');
        else return self::whereNotIn('cooperation_type',$type)->whereIn('uid',$ids)->where('sub_num','>',0)->count('id');
    }

    /**
     * @param string $ids array(id合集 不存在查总体)
     * @param $type
     * @return float|string
     * 非会员签约企业数量（分支的总下级）
     */
    public static function SubordinateSignNotVipNum($ids = '',$type){
        if($ids != '')return self::whereIn('uid',$ids)->where('cooperation_type',$type)->count('id');
        else return self::where('cooperation_type',$type)->count('id');
    }

    /**
     * @param $ids
     * @param $type
     * @return float|string
     * 会员签约企业数量（分支的总下级）
     */
    public static function SubordinateSignVipNum($ids = '',$type){
        if($ids != '') return self::whereIn('uid',$ids)->whereNotIn('cooperation_type',$type)->count('id');
        else return self::whereNotIn('cooperation_type',$type)->count('id');
    }


    /**
     * @param string $ids
     * @param string $time
     * @return float|string|\think\db\Query
     * 根据时间查销售跟进企业数量
     */
    static function getSigningCount($ids = '',$time = ''){
        if(isset($ids) && $ids != ''){
            if($time != '') $result = self::where('sub_num','>',0)
                ->whereIn('signing_id',$ids)->whereTime('create_time',$time)
                ->count('id');
            else $result = self::where('sub_num','>',0)->whereIn('signing_id',$ids)->count('id');
        }else{
            if($time != '') $result = self::where('sub_num','>',0)->whereTime('create_time',$time)->count('id');
            else $result = self::where('sub_num','>',0)->count('id');
        }
        return $result;
    }

    /**
     * @param string $ids
     * @param string $time
     * @return float|string
     * 过期销售跟进企业汇总数据
     */
    static function getSigningExpireCount($ids = '',$time = ''){
        if(isset($ids) && $ids != ''){
            if($time != '') $result = self::where('cooperation_type',4)->where('sub_num','>',0)
                ->whereIn('signing_id',$ids)->whereTime('end_time',$time)
                ->count('id');
            else $result = self::where('cooperation_type',4)->where('sub_num','>',0)
                ->whereIn('signing_id',$ids)
                ->count('id');
        }else{
            if($time != '') $result = self::where('cooperation_type',4)->where('sub_num','>',0)
                ->whereTime('end_time',$time)
                ->count('id');
            else $result = self::where('cooperation_type',4)->where('sub_num','>',0)->count('id');
        }
        return $result;
    }

    /**
     * @param $type
     * @param string $ids
     * @return \think\db\Query
     * 销售跟进企业客户等级汇总数据
     */
    static function getSigningMemberCount($type,$ids = ''){
        if(isset($ids) && $ids != '') $result = self::where('sub_num','>',0)
            ->whereIn('cooperation_type',$type)->whereIn('signing_id',$ids)
            ->count('id');
        else $result = self::where('sub_num','>',0)->whereIn('cooperation_type',$type)->count('id');
        return $result;
    }

    /**
     * @param $type
     * @param string $ids
     * @return \think\db\Query
     * 销售跟进企业客户等级汇总数据
     */
    static function getSigningNotMemberCount($type,$ids = ''){
        if(isset($ids) && $ids != '') $result = self::where('sub_num','>',0)
            ->whereNotIn('cooperation_type',$type)->whereIn('signing_id',$ids)
            ->count('id');
        else $result = self::where('sub_num','>',0)->whereIn('cooperation_type',$type)->count('id');
        return $result;
    }

    /**
     * @param $label
     * @param string $ids
     * @return float|string
     * 销售跟进企业客户类别汇总数据
     */
    static function getSigningClassCount($label,$ids = ''){
        if(isset($ids) && $ids != '') $result = self::where('sub_num','>',0)
            ->where('customer_labels',$label)->whereIn('signing_id',$ids)->count('id');
        else $result = self::where('sub_num','>',0)
            ->where('customer_labels',$label)->count('id');
        return $result;
    }

    /**
     * @param string $time
     * @param string $ids
     * @return \think\db\Query
     * 销售跟进成交会员费
     */
    static function SigningMemberFee($time = '',$ids = ''){
        if(isset($ids) && $ids != ''){
            if($time != '') return self::whereNotIn('cooperation_type','0,5')
                ->whereTime('sign_up_time',$time)->whereIn('signing_id',$ids)
                ->sum('members_fee');
            else return self::whereNotIn('cooperation_type','0,5')
                ->whereIn('signing_id',$ids)
                ->sum('members_fee');
        }else{
            if($time != '') return self::whereNotIn('cooperation_type','0,5')
                ->whereTime('sign_up_time',$time)
                ->sum('members_fee');
            else return self::whereNotIn('cooperation_type','0,5')->sum('members_fee');
        }
    }

    /**
     * @param string $ids
     * @return $this|\think\db\Query
     * 销售未跟进企业数量
     */
    static function getSigningNoFollowCount($ids = ''){
        $sub_id = Followup::where('delete_time',0)->where('status',0)->where('types',1)->column('sub_id');

        $result = self::whereNotIn('cooperation_type','4,5')->whereNotIn('id',$sub_id);
        if(isset($ids) && $ids != ''){
            $result = $result->whereIn('ced_id',$ids);
        }
        $result = $result->count('id');
        return $result;
    }



}