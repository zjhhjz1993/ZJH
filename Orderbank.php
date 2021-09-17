<?php
// +----------------------------------------------------------------------
// | 博派PHP框架 [ JlmPHP ]
// +----------------------------------------------------------------------
// | 版权所有 2016~2019 博派 [ http://www.zrthink.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://JlmPHP.com
// +----------------------------------------------------------------------

namespace app\member\model;

use think\Model;
use app\member\model\User as MemberUser;
/**
 * 日志模型
 * @package app\admin\model
 */
class Orderbank extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $name = 'order_bank';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;





    static function getList($map){
        $data_list=[];
        $data_list = Orderbank::where($map)->order('id desc')->paginate();
        $integral_type = config('config.integral_type');

        // foreach ($data_list as &$val){
        //     $val['type']=$integral_type[$val['type']];
        //     if($val['status']==1){
        //         $val['num']='+'.$val['num'];
        //     }else{
        //         $val['num']='-'.$val['num'];
        //     }
        // }
        return $data_list;

    }


    /**
     * 积分
     * @param int $status 1 收入积分   2 支出积分
     * @param int $memebr_id 用户id
     * @param int $type 类型id 情可看config文件里的integral_type
     * @param int $num 积分数
     * @author 博派
     * @return array|bool
     */
    static function integralLog($status=1,$memebr_id,$type,$num){
        $res=['code'=>0,'msg'=>'','data'=>''];
        if(!$memebr_id){$res['msg'] = '缺少memebr_id';return $res;};
        if(!$type){$res['msg'] = '缺少type,详情可看config.integral_type';return $res;};
        if(!$num){$res['msg'] = '缺少积分num';return $res;};
        $integral       = MemberUser::where(['id'=>$memebr_id])->find();
        $integral       = $integral['integral'];
        $integral_type  = config('config.integral_type');

        $integralNum    = $status==1?($integral+$num):($integral>=$num?$integral-$num:0);
        MemberUser::update(['integral'=>$integralNum], ['id'=>$memebr_id]);
        $data=[
            'mid'=>$memebr_id,
            'type'=>$type,
            'type_name'=>$integral_type[$type],
            'status'=>$status==1?$status:2,
            'num'=>$status==1?($num):($integral>=$num?$num:$integral),
            'addtime'=>time(),
            'order_sn'=>random_num()
        ];
        Integral::insert($data);
        $res['code']  = 1;
        $res['msg']   = '添加成功';
        $res['data']  = $data;
        return $res;
    }

    /**
     * @param string $start_time
     * @param string $end_time
     * @param string $ids
     * @param string $where
     * @return $this|float
     * 成交伙伴合作金额
     */
    static function getDealPartnerFee($start_time = '',$end_time = '',$ids = '',$where = ''){
        $result = self::alias('a')->where('a.pay_status',1)->where('b.ambassador','<>',0);

        if(isset($start_time) && isset($end_time) && $start_time != '' && $end_time != ''){
            $result = $result->whereBetween('a.pay_time',$start_time.','.$end_time);
        }
        if(isset($ids) && $ids != ''){
            $result = $result->whereIn('b.sig_id',$ids);
        }
        if(isset($where) && $where != ''){
            $result = $result->where(function($query) use($where){
//                $query->whereOr($where);
                $query->whereOr('a.totalmoney','in','399,5980,59800')
                    ->whereOr('a.pay_type','xxhy');
            });
        }

        $result = $result->join('dp_member_user b','a.uid = b.id','left')
            ->join('dp_member_authenmessenger c','b.id = c.uid','left')
            ->sum('a.totalmoney');
        return $result;
    }

    /**
     * @param $field
     * @param $map
     * @param string $time_field
     * @param string $time_cycle
     * @param int $page_number
     * @return $this
     */
    static function getLists($field,$map,$time_field = '',$time_cycle = '',$page_number = 10){
        $result = self::alias('a')->field($field)->where($map);

        if(isset($time_field) && isset($time_cycle) && $time_field != '' && $time_cycle != ''){
            $result = $result->whereTime($time_field,$time_cycle);
        }
        $where_or['a.totalmoney'] = array('in','399,5980,59800');
        $where_or['a.pay_type'] = 'xxhy';
        $result =  $result->where(function($query) use($where_or){
            $query->whereOr('a.totalmoney','in','399,5980,59800')
                ->whereOr('a.pay_type','xxhy');
        });

        $result =  $result->leftJoin('dp_member_user b','b.id = a.uid')
            ->leftJoin('dp_member_authenmessenger c','c.uid = b.id')
            ->leftJoin('dp_member_authenmessenger s','s.uid = b.sig_id')
            ->leftJoin('dp_member_authenmessenger p','p.uid = b.parent_id')
            ->group('a.id')->order('a.pay_time','DESC')
            ->paginate($page_number,false,['query'=>request()->param()]);
        return $result;
    }








}
