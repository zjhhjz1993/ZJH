<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/9/10
 * Time: 14:24
 */
namespace app\member\controller;

use app\member\model\Authenmessenger;
use app\member\model\Company;
use app\member\model\Followup;
use app\member\model\Orderbank;
use app\member\model\Quity;
use app\member\model\Qyservese;
use app\member\model\Subsidiese;
use app\member\model\User as UserModel;
use app\member\model\Withdrawalen;
use think\Db;

/**
 * 客户数据控制器
 * @package app\admin\controller
 */
class Customer extends Admin{
    /**
     * @return mixed
     * 客户数据
     */
    public function customerData() {
        //用户进入存入session的id
        $uid = session('member_user_auth.uid');
        //识别是否是子账号
        $relate = find_sub($uid);
        //是的话默认为主账号操作
        if($relate) $uid = $relate['master_uid'];
        //接收ced_id参数
        $ced_id = input('ced_id');
        $this->assign('ced_id',$ced_id);
        //存在参数 替换用户id
        if($ced_id != '') $uid = $ced_id;
        //赋值到模板
        $this->assign('uid',$uid);
        //设置查询条件
        $map   = $this->getMap();
        $map[] = ['id','=',$uid];
        //查出当前用户角色
        $role = UserModel::getValues($map,'role');
        $this->assign('role',$role);

        $ids = [];
        //客服总管查出手下的id合集
        if($role == 12){
            $map = []; $map[] = ['parent_id','=',$uid]; $map[] = ['role','=',10];
            $ids = UserModel::getUserIdArray($map,'id');
        }
        //一周之前的时间戳
        $weekTime = mktime(0,0,0,date('m'),date('d')-7,date('Y'))-1;
        //两周之前的时间戳
        $tweekTime = mktime(0,0,0,date('m'),date('d')-14,date('Y'))-1;
        //一个月之前的时间戳
        $mweekTime = mktime(0,0,0,date('m'),date('d')-30,date('Y'))-1;
        //五十天之前的时间戳
        $jweekTime = mktime(0,0,0,date('m'),date('d')-50,date('Y'))-1;
        //总后台看到的数据
        if($role == 1){
            //本周新增的合作企业
            $com_wnum = Company::getCompanyCount('4,5','','week');
            //本月新增的合作企业
            $com_mnum = Company::getCompanyCount('4,5','','month');
            //总的合作企业
            $com_znum = Company::getCompanyCount('5');

            // 本周到期客户
            $gqcus_wnum = Company::getExpireCount('','week');
            // 本月到期客户
            $gqcus_mnum = Company::getExpireCount('','month');
            // 总到期客户
            $gqcus_znum = Company::getExpireCount();

            // 非会员
            $fmeb_num = Company::getMemberCount(0);
            // 银卡会员
            $ykmeb_num = Company::getMemberCount('1,6,9,10');
            // 金卡会员
            $jkmeb_num = Company::getMemberCount('2,7');
            // 至尊会员
            $zzmeb_num = Company::getMemberCount('3,8');

            // C类客户
            $ccus_num = Company::getClassCount(2);
            // B类客户
            $bcus_num = Company::getClassCount(3);
            // A类客户
            $acus_num = Company::getClassCount(4);
            // KA类客户
            $kacus_num = Company::getClassCount(5);

            // 本周申报补贴金额
            $sub_wamount = Subsidiese::DeclaredAmount('week');
            // 本月申报补贴金额
            $sub_mamount = Subsidiese::DeclaredAmount('month');
            // 总申报补贴金额
            $sub_zamount = Subsidiese::ActualDeclaredAmount();

            // 本周服务费支付金额
            $sub_wfee = Subsidiese::ServiceCharge('week');
            // 本月服务费支付金额
            $sub_mfee = Subsidiese::ServiceCharge('month');
            // 总支付服务费金额
            $sub_zfee = Subsidiese::ServiceCharge();

            $idss = Followup::getNotFollow();
        }
        //客服总管看到的数据
        elseif($role == 12){
            //本周新增的合作企业
            $com_wnum = Company::getCompanyCount('4,5',$ids,'week');
            //本月新增的合作企业
            $com_mnum = Company::getCompanyCount('4,5',$ids,'month');
            //总的合作企业
            $com_znum = Company::getCompanyCount('5',$ids);

            // 本周到期客户
            $gqcus_wnum = Company::getExpireCount($ids,'week');
            // 本月到期客户
            $gqcus_mnum = Company::getExpireCount($ids,'month');
            // 总到期客户
            $gqcus_znum = Company::getExpireCount($ids);

            // 非会员
            $fmeb_num = Company::getMemberCount(0,$ids);
            // 银卡会员
            $ykmeb_num = Company::getMemberCount('1,6,9,10',$ids);
            // 金卡会员
            $jkmeb_num = Company::getMemberCount('2,7',$ids);
            // 至尊会员
            $zzmeb_num = Company::getMemberCount('3,8',$ids);

            // C类客户
            $ccus_num = Company::getClassCount(2,$ids);
            // B类客户
            $bcus_num = Company::getClassCount(3,$ids);
            // A类客户
            $acus_num = Company::getClassCount(4,$ids);
            // KA类客户
            $kacus_num = Company::getClassCount(5,$ids);

            // 本周申报补贴金额
            $sub_wamount = Subsidiese::DeclaredAmount('week',$ids);
            // 本月申报补贴金额
            $sub_mamount = Subsidiese::DeclaredAmount('month',$ids);
            // 总申报补贴金额
            $sub_zamount = Subsidiese::ActualDeclaredAmount($ids);

            // 本周服务费支付金额
            $sub_wfee = Subsidiese::ServiceCharge('week',$ids);
            // 本月服务费支付金额
            $sub_mfee = Subsidiese::ServiceCharge('month',$ids);
            // 总支付服务费金额
            $sub_zfee = Subsidiese::ServiceCharge('',$ids);

            $idss = Followup::getNotFollow($ids);
        }
        //客服看到的数据
        else{
            //本周新增的合作企业
            $com_wnum = Company::getCompanyCount('4,5',$uid,'week');
            //本月新增的合作企业
            $com_mnum = Company::getCompanyCount('4,5',$uid,'month');
            //总的合作企业
            $com_znum = Company::getCompanyCount('5',$uid);

            // 本周到期客户
            $gqcus_wnum = Company::getExpireCount($uid,'week');
            // 本月到期客户
            $gqcus_mnum = Company::getExpireCount($uid,'month');
            // 总到期客户
            $gqcus_znum = Company::getExpireCount($uid);

            // 非会员
            $fmeb_num = Company::getMemberCount(0,$uid);
            // 银卡会员
            $ykmeb_num = Company::getMemberCount('1,6,9,10',$uid);
            // 金卡会员
            $jkmeb_num = Company::getMemberCount('2,7',$uid);
            // 至尊会员
            $zzmeb_num = Company::getMemberCount('3,8',$uid);

            // C类客户
            $ccus_num = Company::getClassCount(2,$uid);
            // B类客户
            $bcus_num = Company::getClassCount(3,$uid);
            // A类客户
            $acus_num = Company::getClassCount(4,$uid);
            // KA类客户
            $kacus_num = Company::getClassCount(5,$uid);

            // 本周申报补贴金额
            $sub_wamount = Subsidiese::DeclaredAmount('week',$uid);
            // 本月申报补贴金额
            $sub_mamount = Subsidiese::DeclaredAmount('month',$uid);
            // 总申报补贴金额
            $sub_zamount = Subsidiese::ActualDeclaredAmount($uid);

            // 本周服务费支付金额
            $sub_wfee = Subsidiese::ServiceCharge('week',$uid);
            // 本月服务费支付金额
            $sub_mfee = Subsidiese::ServiceCharge('month',$uid);
            // 总支付服务费金额
            $sub_zfee = Subsidiese::ServiceCharge('',$uid);

            $idss = Followup::getNotFollow($uid);
        }
        //统计一周未跟进客户
        $wf_num = 0;
        foreach($idss as $w){
            if($w['update_time'] < $weekTime && $tweekTime < $w['update_time'] && $w['types'] == 0){
                $wf_num = $wf_num + 1;
            }
        }
        //统计两周未跟进客户
        $tf_num = 0;
        foreach($idss as $t){
            if($t['update_time'] < $tweekTime && $mweekTime < $t['update_time'] && $t['types'] == 0){
                $tf_num = $tf_num + 1;
            }
        }
        //统计一个月未跟进客户
        $mf_num = 0;
        foreach($idss as $m){
            if($m['update_time'] < $mweekTime && $jweekTime < $m['update_time'] && $m['types'] == 0){
                $mf_num = $mf_num + 1;
            }
        }
        //统计50天未跟进客户
        $jf_num = 0;
        foreach($idss as $j){
            if($j['update_time'] < $jweekTime && $j['types'] == 0){
                $jf_num = $jf_num + 1;
            }
        }

        //赋值数据到模板
        $this->assign('com_wnum',$com_wnum); $this->assign('com_mnum',$com_mnum); $this->assign('com_znum',$com_znum);
        //赋值数据到模板
        $this->assign('gqcus_wnum',$gqcus_wnum); $this->assign('gqcus_mnum',$gqcus_mnum); $this->assign('gqcus_znum',$gqcus_znum);
        //赋值数据到模板
        $this->assign('fmeb_num',$fmeb_num); $this->assign('ykmeb_num',$ykmeb_num); $this->assign('jkmeb_num',$jkmeb_num); $this->assign('zzmeb_num',$zzmeb_num);
        //赋值数据到模板
        $this->assign('ccus_num',$ccus_num); $this->assign('bcus_num',$bcus_num); $this->assign('acus_num',$acus_num); $this->assign('kacus_num',$kacus_num);
        //赋值数据到模板
        $this->assign('sub_wamount',$sub_wamount); $this->assign('sub_mamount',$sub_mamount); $this->assign('sub_zamount',$sub_zamount);
        //赋值数据到模板
        $this->assign('sub_wfee',$sub_wfee); $this->assign('sub_mfee',$sub_mfee); $this->assign('sub_zfee',$sub_zfee);
        //赋值数据到模板
        $this->assign('wfollowup',$wf_num); $this->assign('tfollowup',$tf_num); $this->assign('mfollowup',$mf_num); $this->assign('jfollowup',$jf_num);
        //总的客户数
        $zcus_num = $ccus_num + $bcus_num + $acus_num + $kacus_num;
        //计算客户占比 总数不为0时
        if($zcus_num != 0){
            // 单数/总数 * 100
            $cl_ratio = (float)$ccus_num / (float)$zcus_num * 100;
            //取两位小数
            $cl_ratio = sprintf('%.2f', $cl_ratio);
            $bl_ratio = (float)$bcus_num / (float)$zcus_num * 100;
            $bl_ratio = sprintf('%.2f', $bl_ratio);
            $al_ratio = (float)$acus_num / (float)$zcus_num * 100;
            $al_ratio = sprintf('%.2f', $al_ratio);
            $ka_ratio = (float)$kacus_num / (float)$zcus_num * 100;
            $ka_ratio = sprintf('%.2f', $ka_ratio);
        } else {
            //总数为0 占比全部默认为0
            $cl_ratio = 0;
            $bl_ratio = 0;
            $al_ratio = 0;
            $ka_ratio = 0;
        }
        //赋值数据到模板
        $this->assign('cl_ratio', $cl_ratio);
        $this->assign('bl_ratio', $bl_ratio);
        $this->assign('al_ratio', $al_ratio);
        $this->assign('ka_ratio', $ka_ratio);
        //计算服务费比例
        $circle_progress = 0;
        if($sub_zamount != 0) $circle_progress = sprintf('%.2f', ((float)$sub_zfee / (float)$sub_zamount));
        $this->assign('circle_progress', $circle_progress);

        $maps   = $this->getMap();
        $maps[] = ['role','=',10];
        $field = 'id,parent_id,username,nickname,create_time';
        if($role == 12){
            $maps[] = ['parent_id','=',$uid];
        }
        // 客服详情列表
        $list = UserModel::getUserList($maps,$field,10);
        $list1 = $list->toArray();
        foreach ($list1['data'] as $k => $v){
            // 客户总数
            $v['cus_znum'] = Company::getCompanyCount('5',$v['id']);
            // 在服务客户
            $v['ser_num'] = Company::getCompanyCount('4,5',$v['id']);
            // 过期客户
            $v['gqcus_znum'] = Company::getExpireCount($v['id']);
            // 过期比例
            $v['gq_progress'] = 0;
            if($v['cus_znum'] != 0) $v['gq_progress'] = sprintf('%.2f', (float)($v['gqcus_znum'] / $v['cus_znum'])) * 100;
            // C类客户
            $v['clcus_num'] = Company::getClassCount(2,$v['id']);
            // B类客户
            $v['blcus_num'] = Company::getClassCount(3,$v['id']);
            // A类客户
            $v['alcus_num'] = Company::getClassCount(4,$v['id']);
            // KA客户
            $v['kacus_num'] = Company::getClassCount(5,$v['id']);

            // 申报补贴金额
            $v['sub_zamount'] = Subsidiese::ActualDeclaredAmount($v['id']);
            // 支付服务费金额
            $v['sub_zfee'] = Subsidiese::ServiceCharge('',$v['id']);
            // 收取服务费比例
            $v['fee_progress'] = 0;
            if($v['sub_zamount'] != 0) $v['fee_progress'] = sprintf('%.2f', (float)($v['sub_zfee'] / $v['sub_zamount'])) * 100;
            $data = $v;
            $list->offsetSet($k,$data);
        }
        $data = array(
            'list' => $list,
            'page' => $list->render(),
            'count' => $list->total(),
        );
        $this->assign($data);
        $listtoarray = $list->toArray();
        $this->assign('listtoarray',$listtoarray);

        return $this->fetch();
    }

    /**
     * @return array
     * 服务漏斗筛选比例
     */
    public function getSerFunnel() {
        if($this->request->isAjax()) {
            //接收用户id
            $uid = $this->request->post('uid');
            //设置查询数据
            $map   = $this->getMap();
            $map[] = ['id','=',$uid];
            //获取用户角色
            $role = UserModel::getValues($map,'role');
            $this->assign('role',$role);
            //获取下级id合集
            $ids = [];
            if($role == 12){
                $map = []; $map[] = ['parent_id','=',$uid]; $map[] = ['role','=',10];
                $ids = UserModel::getUserIdArray($map,'id');
            }
            //时间戳参数
            $serveTime = $this->request->post('serveTime');
            // C类客户
            $ccus_num = Company::whereNotIn('cooperation_type','4,5')->where('customer_label',2);
            // B类客户
            $bcus_num = Company::whereNotIn('cooperation_type','4,5')->where('customer_label',3);
            // A类客户
            $acus_num = Company::whereNotIn('cooperation_type','4,5')->where('customer_label',4);
            // KA类客户
            $kacus_num = Company::whereNotIn('cooperation_type','4,5')->where('customer_label',5);
            if($role == 12){
                $ccus_num = $ccus_num->whereIn('ced_id',$ids);
                $bcus_num = $bcus_num->whereIn('ced_id',$ids);
                $acus_num = $acus_num->whereIn('ced_id',$ids);
                $kacus_num = $kacus_num->whereIn('ced_id',$ids);
            }
            if($role == 10){
                $ccus_num = $ccus_num->where('ced_id',$uid);
                $bcus_num = $bcus_num->where('ced_id',$uid);
                $acus_num = $acus_num->where('ced_id',$uid);
                $kacus_num = $kacus_num->where('ced_id',$uid);
            }
            $season = ceil(date('n') /3); //获取月份的季度
            $seasonStart = date('Y-m-01',mktime(0,0,0,($season - 1) *3 +1,1,date('Y')));
            $seasonStart = strtotime($seasonStart);
            $seasonEnd = date('Y-m-t',mktime(0,0,0,$season * 3,1,date('Y')));
            $seasonEnd = strtotime($seasonEnd);
            $lastSeasonStart = date('Y-m-01',mktime(0,0,0,($season - 2) * 3 +1,1,date('Y')));
            $lastSeasonStart = strtotime($lastSeasonStart);
            $lastSeasonEnd = date('Y-m-t',mktime(0,0,0,($season - 1) * 3,1,date('Y')));
            $lastSeasonEnd = strtotime($lastSeasonEnd);
            if($serveTime == 1){
                $ccus_num = $ccus_num->whereTime('create_time', 'week');
                $bcus_num = $bcus_num->whereTime('create_time', 'week');
                $acus_num = $acus_num->whereTime('create_time', 'week');
                $kacus_num = $kacus_num->whereTime('create_time', 'week');
            } elseif($serveTime == 2){
                $ccus_num = $ccus_num->whereTime('create_time', 'month');
                $bcus_num = $bcus_num->whereTime('create_time', 'month');
                $acus_num = $acus_num->whereTime('create_time', 'month');
                $kacus_num = $kacus_num->whereTime('create_time', 'month');
            } elseif($serveTime == 3){
                $ccus_num = $ccus_num->whereTime('create_time','between',[$seasonStart,$seasonEnd]);
                $bcus_num = $bcus_num->whereTime('create_time','between',[$seasonStart,$seasonEnd]);
                $acus_num = $acus_num->whereTime('create_time','between',[$seasonStart,$seasonEnd]);
                $kacus_num = $kacus_num->whereTime('create_time','between',[$seasonStart,$seasonEnd]);
            } elseif($serveTime == 4){
                $ccus_num = $ccus_num->whereTime('create_time', 'year');
                $bcus_num = $bcus_num->whereTime('create_time', 'year');
                $acus_num = $acus_num->whereTime('create_time', 'year');
                $kacus_num = $kacus_num->whereTime('create_time', 'year');
            } elseif($serveTime == 5){
                $ccus_num = $ccus_num->whereTime('create_time', 'yesterday');
                $bcus_num = $bcus_num->whereTime('create_time', 'yesterday');
                $acus_num = $acus_num->whereTime('create_time', 'yesterday');
                $kacus_num = $kacus_num->whereTime('create_time', 'yesterday');
            } elseif($serveTime == 6){
                $ccus_num = $ccus_num->whereTime('create_time', 'last week');
                $bcus_num = $bcus_num->whereTime('create_time', 'last week');
                $acus_num = $acus_num->whereTime('create_time', 'last week');
                $kacus_num = $kacus_num->whereTime('create_time', 'last week');
            } elseif($serveTime == 7){
                $ccus_num = $ccus_num->whereTime('create_time', 'last month');
                $bcus_num = $bcus_num->whereTime('create_time', 'last month');
                $acus_num = $acus_num->whereTime('create_time', 'last month');
                $kacus_num = $kacus_num->whereTime('create_time', 'last month');
            } elseif($serveTime == 8){
                $ccus_num = $ccus_num->whereTime('create_time','between',[$lastSeasonStart,$lastSeasonEnd]);
                $bcus_num = $bcus_num->whereTime('create_time','between',[$lastSeasonStart,$lastSeasonEnd]);
                $acus_num = $acus_num->whereTime('create_time','between',[$lastSeasonStart,$lastSeasonEnd]);
                $kacus_num = $kacus_num->whereTime('create_time','between',[$lastSeasonStart,$lastSeasonEnd]);
            } elseif($serveTime == 9){
                $ccus_num = $ccus_num->whereTime('create_time', 'last year');
                $bcus_num = $bcus_num->whereTime('create_time', 'last year');
                $acus_num = $acus_num->whereTime('create_time', 'last year');
                $kacus_num = $kacus_num->whereTime('create_time', 'last year');
            }
            $ccus_num = $ccus_num->count();
            $bcus_num = $bcus_num->count();
            $acus_num = $acus_num->count();
            $kacus_num = $kacus_num->count();

            $zcus_num = $ccus_num + $bcus_num + $acus_num + $kacus_num;
            if($zcus_num != 0){
                $cl_ratio = (float)$ccus_num / (float)$zcus_num * 100;
                $cl_ratio = sprintf('%.2f', $cl_ratio);
                $bl_ratio = (float)$bcus_num / (float)$zcus_num * 100;
                $bl_ratio = sprintf('%.2f', $bl_ratio);
                $al_ratio = (float)$acus_num / (float)$zcus_num * 100;
                $al_ratio = sprintf('%.2f', $al_ratio);
                $ka_ratio = (float)$kacus_num / (float)$zcus_num * 100;
                $ka_ratio = sprintf('%.2f', $ka_ratio);
            } else {
                $cl_ratio = 0;
                $bl_ratio = 0;
                $al_ratio = 0;
                $ka_ratio = 0;
            }

            $data = ['code'=>1,'cl_ratio'=>$cl_ratio,'bl_ratio'=>$bl_ratio,'al_ratio'=>$al_ratio,'ka_ratio'=>$ka_ratio,
                'ccus_num'=>$ccus_num,'bcus_num'=>$bcus_num,'acus_num'=>$acus_num,'kacus_num'=>$kacus_num,'zcus_num'=>$zcus_num];

            return $data;
        }
    }

    /**
     * @return mixed
     * 服务客户数据列表
     */
    public function serCusList() {
        $keyword = ['company'=>'','selectTime'=>'','type'=>'', 'cooperation_type'=>'', 'customer_label'=>'',
            'serveTime'=>''];
        //接收用户id
        $uid = input('uid');
        $this->assign('uid',$uid);
        //设置查询条件
        $map   = $this->getMap();
        $map[] = ['id','=',$uid];
        //查出当前用户角色
        $role = UserModel::getValues($map,'role');
        $this->assign('role',$role);
        //获取下级id合集
        $ids = [];
        if($role == 12){
            $map = []; $map[] = ['parent_id','=',$uid]; $map[] = ['role','=',10];
            $ids = UserModel::getUserIdArray($map,'id');
        }
        //接收参数合集
        $key  = input();
        $keyword = array_merge($keyword,$key);
        $this->assign('keyword',$keyword);
        //时间条件字段
        $time_field = ''; $time_cycle = '';
        //查询需要的字段
        $field = 'a.id,a.uid,a.company,a.cooperation_type,a.cooperation_time,a.customer_label,
            a.create_time,a.sign_up_time,b.name pname,s.cid,s.name,s.mobile,c.nickname cname';
        //查询条件合集
        $maps = $this->getMap();
        if($role == 12){
            $maps[] = ['a.ced_id','in',$ids];
        }
        if($role == 10){
            $maps[] = ['a.ced_id','=',$uid];
        }
        if($keyword['company']){
            $maps[] = ['a.company','like','%'.$keyword['company'].'%'];
        }
        if($keyword['selectTime']){
            if($keyword['selectTime'] == 2 || $keyword['selectTime'] == 3){
                $time_field = 'a.sign_up_time';
                if($keyword['selectTime'] == 2) $time_cycle = 'week';
                else $time_cycle = 'month';
            }elseif($keyword['selectTime'] == 4 || $keyword['selectTime'] == 5){
                $time_field = 'a.end_time';
                if($keyword['selectTime'] == 4) $time_cycle = 'week';
                else $time_cycle = 'month';
            }
        }
        if($keyword['type']){
            if($keyword['type'] == 1){
                $maps[] = ['a.cooperation_type','not in','4,5'];
            } elseif($keyword['type'] == 2){
                $maps[] = ['a.cooperation_type','<>','5'];
            } elseif($keyword['type'] == 3){
                $maps[] = ['a.cooperation_type','<>','4'];
            }
        }
        if($keyword['cooperation_type']){
            if($keyword['cooperation_type'] == 0) {
                $maps[] = ['a.cooperation_type','=','0'];
            } elseif($keyword['cooperation_type'] == 1){
                $maps[] = ['a.cooperation_type','in','1,6,9,10'];
            } elseif($keyword['cooperation_type'] == 2){
                $maps[] = ['a.cooperation_type','in','2,7'];
            } elseif($keyword['cooperation_type'] == 3){
                $maps[] = ['a.cooperation_type','in','3,8'];
            } elseif($keyword['cooperation_type'] == 4){
                $maps[] = ['a.cooperation_type','=',4];
            }
        }
        if($keyword['customer_label']){
            $maps[] = ['a.cooperation_type','<>','5'];
            $maps[] = ['a.customer_label','=',$keyword['customer_label']];
        }
        if($keyword['serveTime']){
            //获取月份的季度
            $season = ceil(date('n') /3);
            $seasonStart = date('Y-m-01',mktime(0,0,0,($season - 1) *3 +1,1,date('Y')));// 本季开始
            $seasonStart = strtotime($seasonStart);
            $seasonEnd = date('Y-m-t',mktime(0,0,0,$season * 3,1,date('Y')));// 本季结束
            $seasonEnd = strtotime($seasonEnd);
            $lastSeasonStart = date('Y-m-01',mktime(0,0,0,($season - 2) * 3 +1,1,date('Y')));// 上季开始
            $lastSeasonStart = strtotime($lastSeasonStart);
            $lastSeasonEnd = date('Y-m-t',mktime(0,0,0,($season - 1) * 3,1,date('Y')));// 上季结束
            $lastSeasonEnd = strtotime($lastSeasonEnd);
            if($keyword['serveTime'] == 1){
                $time_field = 'a.create_time';
                $time_cycle = 'week';
            } elseif($keyword['serveTime'] == 2){
                $time_field = 'a.create_time';
                $time_cycle = 'month';
            } elseif($keyword['serveTime'] == 3){
                $maps[] = ['a.create_time','between time',[$seasonStart,$seasonEnd]];
            } elseif($keyword['serveTime'] == 4){
                $time_field = 'a.create_time';
                $time_cycle = 'year';
            } elseif($keyword['serveTime'] == 5){
                $time_field = 'a.create_time';
                $time_cycle = 'yesterday';
            } elseif($keyword['serveTime'] == 6){
                $time_field = 'a.create_time';
                $time_cycle = 'last week';
            } elseif($keyword['serveTime'] == 7){
                $time_field = 'a.create_time';
                $time_cycle = 'last month';
            } elseif($keyword['serveTime'] == 8){
                $maps[] = ['a.create_time','between time',[$lastSeasonStart,$lastSeasonEnd]];
            } elseif($keyword['serveTime'] == 9){
                $time_field = 'a.create_time';
                $time_cycle = 'last year';
            }
        }
        //获取列表数据
        $list = Company::getList($field,$maps,$time_field,$time_cycle);

        $data = array(
            'list' => $list,
            'page' => $list->render(),
            'count' => $list->total(),
        );
        $this->assign($data);
        $listtoarray = $list->toArray();
        $this->assign('listtoarray',$listtoarray);

        return $this->fetch();
    }

    /**
     * @return mixed
     * 项目申报数据列表
     */
    public function serSubList() {
        $keyword = ['company'=>'','selectTime'=>''];
        //接收用户id
        $uid = input('uid');
        $this->assign('uid',$uid);
        //设置查询条件
        $map   = $this->getMap();
        $map[] = ['id','=',$uid];
        //查出当前用户角色
        $role = UserModel::getValues($map,'role');
        $this->assign('role',$role);
        //获取下级id合集
        $ids = [];
        if($role == 12){
            $map = []; $map[] = ['parent_id','=',$uid]; $map[] = ['role','=',10];
            $ids = UserModel::getUserIdArray($map,'id');
        }

        //接收参数合集
        $key  = input();
        $keyword = array_merge($keyword,$key);
        $this->assign('keyword',$keyword);
        //时间条件字段
        $time_field = ''; $time_cycle = '';
        $field = 'a.sub_id,a.uid,a.create_time,a.grant_time,a.advance,a.subsidies_amount,a.service_fee,a.service_fei,
                a.cid,b.company,b.cooperation_type,b.cooperation_time,s.name,s.mobile,c.name pname';
        //查询条件合集
        $maps = $this->getMap();
        if($role == 12){
            $maps[] = ['b.ced_id','in',$ids];
        }
        if($role == 10){
            $maps[] = ['b.ced_id','=',$uid];
        }
        if($keyword['company']){
            $maps[] = ['b.company','like','%'.$keyword['company'].'%'];
        }
        if($keyword['selectTime']){
            if($keyword['selectTime'] == 2 || $keyword['selectTime'] == 3){
                $maps[] = ['a.submit_data','=',2];
                $time_field = 'a.create_time';
                if($keyword['selectTime'] == 2) $time_cycle = 'week';
                else $time_cycle = 'month';
            } elseif($keyword['selectTime'] == 4){
                $maps[] = ['a.grant_status','=',1];
            } elseif($keyword['selectTime'] == 5 || $keyword['selectTime'] == 6){
                $maps[] = ['a.grant_status','=',1];
                $time_field = 'a.grant_time';
                if($keyword['selectTime'] == 5) $time_cycle = 'week';
                else $time_cycle = 'month';
            }
        }
        //获取列表数据
        $list = Subsidiese::getList($field,$maps,$time_field,$time_cycle);

        $data = array(
            'list' => $list,
            'page' => $list->render(),
            'count' => $list->total(),
        );
        $this->assign($data);
        $listtoarray = $list->toArray();
        $this->assign('listtoarray',$listtoarray);

        return $this->fetch();
    }

    /**
     * @return mixed
     * 未跟进客户数据列表
     */
    public function serFolList() {
        //接收用户id
        $uid = input('uid');
        $this->assign('uid',$uid);
        //设置查询条件
        $map   = $this->getMap();
        $map[] = ['id','=',$uid];
        //查出当前用户角色
        $role = UserModel::getValues($map,'role');
        $this->assign('role',$role);
        //获取下级id合集
        $ids = [];
        if($role == 12){
            $map = []; $map[] = ['parent_id','=',$uid]; $map[] = ['role','=',10];
            $ids = UserModel::getUserIdArray($map,'id');
        }

        // 未跟进客户
        $idss = Followup::alias('a')->field('a.id,a.types,a.update_time')
            ->where('a.delete_time',0)->where('a.status',0)
            ->whereNotIn('s.cooperation_type','5');
        if($role == 12){
            $idss = $idss->whereIn('a.uid',$ids);
        }
        if($role == 10){
            $idss = $idss->where('a.uid',$uid);
        }
        $company = input('company');
        $this->assign('company',$company);
        if(isset($company) && $company != ''){
            $idss = $idss->whereLike('s.company','%'.$company.'%');
        }
        $idss = $idss->join('dp_member_subsidies s','a.sub_id = s.id','left')
            ->order('a.update_time','DESC')->group('s.cid')->select();

        $weekTime = mktime(0,0,0,date('m'),date('d')-7,date('Y'))-1;
        $tweekTime = mktime(0,0,0,date('m'),date('d')-14,date('Y'))-1;
        $mweekTime = mktime(0,0,0,date('m'),date('d')-30,date('Y'))-1;
        $jweekTime = mktime(0,0,0,date('m'),date('d')-50,date('Y'))-1;

        $selectTime = input('selectTime');
        $this->assign('selectTime',$selectTime);

        $ids = [];
        if($selectTime == 1){
            foreach($idss as $v){
                if($v['update_time'] < $weekTime && $tweekTime < $v['update_time'] && $v['types'] == 0){
                    array_push($ids,$v['id']);
                }
            }
        } elseif($selectTime == 2){
            foreach($idss as $v){
                if($v['update_time'] < $tweekTime && $mweekTime < $v['update_time'] && $v['types'] == 0){
                    array_push($ids,$v['id']);
                }
            }
        } elseif($selectTime == 3){
            foreach($idss as $v){
                if($v['update_time'] < $mweekTime && $jweekTime < $v['update_time'] && $v['types'] == 0){
                    array_push($ids,$v['id']);
                }
            }
        } elseif($selectTime == 4){
            foreach($idss as $v){
                if($v['update_time'] < $jweekTime && $v['types'] == 0){
                    array_push($ids,$v['id']);
                }
            }
        }
        $ids = implode(',',$ids);

        $list = Followup::alias('a')
            ->field('a.sub_id,a.uid,a.content,a.update_time,
                b.name pname,s.company,s.cooperation_type,s.cooperation_time,s.cid,s.name,s.mobile')
            ->whereIn('a.id',$ids)
            ->join('dp_member_subsidies s','a.sub_id = s.id','left')
            ->join('dp_member_authenmessenger b','s.promoters_id = b.uid','left')
            ->order('a.update_time','DESC')
            ->group('s.cid')
            ->paginate(10,false,['query'=>request()->param()]);

        $data = array(
            'list' => $list,
            'page' => $list->render(),
            'count' => $list->total(),
        );
        $this->assign($data);
        $listtoarray = $list->toArray();
        $this->assign('listtoarray',$listtoarray);

        return $this->fetch();
    }



    // 销售客户数据
    public function saleCusData() {
        //用户进入存入session的id
        $uid = session('member_user_auth.uid');
        //识别是否是子账号
        $relate = find_sub($uid);
        //是的话默认为主账号操作
        if($relate) $uid = $relate['master_uid'];
        //接收ced_id参数
        $ced_id = input('ced_id');
        //存在参数 替换用户id
        if($ced_id != '') $uid = $ced_id;
        //赋值到模板
        $this->assign('uid',$uid);
        //设置查询条件
        $map   = $this->getMap();
        $map[] = ['id','=',$uid];
        //查出当前用户角色
        $role = UserModel::getValues($map,'role');
        $this->assign('role',$role);

        $ids = [];
        //销售管理查出手下的id合集
        if($role == 3){
            $map = []; $map[] = ['parent_id','=',$uid]; $map[] = ['role','=',11];
            $rids = UserModel::getUserArray($map,'id');
            $ids = array_column($rids,'id');
        }

        $weekTime = mktime(0,0,0,date('m'),date('d')-7,date('Y'))-1;
        $tweekTime = mktime(0,0,0,date('m'),date('d')-14,date('Y'))-1;
        $mweekTime = mktime(0,0,0,date('m'),date('d')-30,date('Y'))-1;
        $jweekTime = mktime(0,0,0,date('m'),date('d')-50,date('Y'))-1;

        //总后台看到的数据
        if($role == 1){
            //本周新增企业
            $com_wnum = Company::getSigningCount('','week');
            //本月新增企业
            $com_mnum = Company::getSigningCount('','month');
            //总的新增企业
            $com_znum = Company::getSigningCount();

            // 本周到期企业
            $gqcus_wnum = Company::getSigningExpireCount('','week');
            // 本月到期企业
            $gqcus_mnum = Company::getSigningExpireCount('','month');
            // 总到期企业
            $gqcus_znum = Company::getSigningExpireCount();

            // 未合作企业
            $wmeb_num = Company::getSigningMemberCount(5);
            // 非会员
            $fmeb_num = Company::getSigningMemberCount(0);
            // 银卡会员
            $ykmeb_num = Company::getSigningMemberCount('1,6,9,10');
            // 金卡会员
            $jkmeb_num = Company::getSigningMemberCount('2,7');
            // 至尊会员
            $zzmeb_num = Company::getSigningMemberCount('3,8');

            // D类客户
            $dcus_num = Company::getSigningClassCount(6);
            // C类客户
            $ccus_num = Company::getSigningClassCount(2);
            // B类客户
            $bcus_num = Company::getSigningClassCount(3);
            // A类客户
            $acus_num = Company::getSigningClassCount(4);
            // KA类客户
            $kacus_num = Company::getSigningClassCount(5);

            // 本周成交会员金额
            $hy_wmoney = Company::SigningMemberFee('week');
            // 本月成交会员金额
            $hy_mmoney = Company::SigningMemberFee('month');
            // 总成交会员金额
            $hy_zmoney = Company::SigningMemberFee();

            // 本周成交企业服务金额
            $qyser_wnum = Qyservese::SigningServiceFee('week');
            // 本月成交企业服务金额
            $qyser_mnum = Qyservese::SigningServiceFee('month');
            // 总支成交企业服务金额
            $qyser_znum = Qyservese::SigningServiceFee();

            $idss = Followup::getNotFollows();

        }
        //客服总管看到的数据
        elseif($role == 3){
            //本周新增企业
            $com_wnum = Company::getSigningCount($ids,'week');
            //本月新增企业
            $com_mnum = Company::getSigningCount($ids,'month');
            //总的新增企业
            $com_znum = Company::getSigningCount($ids);

            // 本周到期企业
            $gqcus_wnum = Company::getSigningExpireCount($ids,'week');
            // 本月到期企业
            $gqcus_mnum = Company::getSigningExpireCount($ids,'month');
            // 总到期企业
            $gqcus_znum = Company::getSigningExpireCount($ids);

            // 未合作企业
            $wmeb_num = Company::getSigningMemberCount(5,$ids);
            // 非会员
            $fmeb_num = Company::getSigningMemberCount(0,$ids);
            // 银卡会员
            $ykmeb_num = Company::getSigningMemberCount('1,6,9,10',$ids);
            // 金卡会员
            $jkmeb_num = Company::getSigningMemberCount('2,7',$ids);
            // 至尊会员
            $zzmeb_num = Company::getSigningMemberCount('3,8',$ids);

            // D类客户
            $dcus_num = Company::getSigningClassCount(6,$ids);
            // C类客户
            $ccus_num = Company::getSigningClassCount(2,$ids);
            // B类客户
            $bcus_num = Company::getSigningClassCount(3,$ids);
            // A类客户
            $acus_num = Company::getSigningClassCount(4,$ids);
            // KA类客户
            $kacus_num = Company::getSigningClassCount(5,$ids);

            // 本周成交会员金额
            $hy_wmoney = Company::SigningMemberFee('week',$ids);
            // 本月成交会员金额
            $hy_mmoney = Company::SigningMemberFee('month',$ids);
            // 总成交会员金额
            $hy_zmoney = Company::SigningMemberFee('',$ids);

            // 本周成交企业服务金额
            $qyser_wnum = Qyservese::SigningServiceFee('week',$ids);
            // 本月成交企业服务金额
            $qyser_mnum = Qyservese::SigningServiceFee('month',$ids);
            // 总支成交企业服务金额
            $qyser_znum = Qyservese::SigningServiceFee('',$ids);

            $idss = Followup::getNotFollows($ids);
        }
        //客服看到的数据
        else{
            //本周新增企业
            $com_wnum = Company::getSigningCount($uid,'week');
            //本月新增企业
            $com_mnum = Company::getSigningCount($uid,'month');
            //总的新增企业
            $com_znum = Company::getSigningCount($uid);

            // 本周到期企业
            $gqcus_wnum = Company::getSigningExpireCount($uid,'week');
            // 本月到期企业
            $gqcus_mnum = Company::getSigningExpireCount($uid,'month');
            // 总到期企业
            $gqcus_znum = Company::getSigningExpireCount($uid);

            // 未合作企业
            $wmeb_num = Company::getSigningMemberCount(5,$uid);
            // 非会员
            $fmeb_num = Company::getSigningMemberCount(0,$uid);
            // 银卡会员
            $ykmeb_num = Company::getSigningMemberCount('1,6,9,10',$uid);
            // 金卡会员
            $jkmeb_num = Company::getSigningMemberCount('2,7',$uid);
            // 至尊会员
            $zzmeb_num = Company::getSigningMemberCount('3,8',$uid);

            // D类客户
            $dcus_num = Company::getSigningClassCount(6,$uid);
            // C类客户
            $ccus_num = Company::getSigningClassCount(2,$uid);
            // B类客户
            $bcus_num = Company::getSigningClassCount(3,$uid);
            // A类客户
            $acus_num = Company::getSigningClassCount(4,$uid);
            // KA类客户
            $kacus_num = Company::getSigningClassCount(5,$uid);

            // 本周成交会员金额
            $hy_wmoney = Company::SigningMemberFee('week',$uid);
            // 本月成交会员金额
            $hy_mmoney = Company::SigningMemberFee('month',$uid);
            // 总成交会员金额
            $hy_zmoney = Company::SigningMemberFee('',$uid);

            // 本周成交企业服务金额
            $qyser_wnum = Qyservese::SigningServiceFee('week',$uid);
            // 本月成交企业服务金额
            $qyser_mnum = Qyservese::SigningServiceFee('month',$uid);
            // 总支成交企业服务金额
            $qyser_znum = Qyservese::SigningServiceFee('',$uid);

            $idss = Followup::getNotFollows($uid);
        }
        $wf_num = 0;
        foreach($idss as $w){
            if($w['update_time'] < $weekTime && $tweekTime < $w['update_time'] && $w['types'] == 1){
                $wf_num = $wf_num + 1;
            }
        }
        $tf_num = 0;
        foreach($idss as $t){
            if($t['update_time'] < $tweekTime && $mweekTime < $t['update_time'] && $t['types'] == 1){
                $tf_num = $tf_num + 1;
            }
        }
        $mf_num = 0;
        foreach($idss as $m){
            if($m['update_time'] < $mweekTime && $jweekTime < $m['update_time'] && $m['types'] == 1){
                $mf_num = $mf_num + 1;
            }
        }
        $jf_num = 0;
        foreach($idss as $j){
            if($j['update_time'] < $jweekTime && $j['types'] == 1){
                $jf_num = $jf_num + 1;
            }
        }

        //赋值数据到模板
        $this->assign('com_wnum', $com_wnum); $this->assign('com_mnum', $com_mnum); $this->assign('com_znum', $com_znum);
        //赋值数据到模板
        $this->assign('gqcus_wnum', $gqcus_wnum); $this->assign('gqcus_mnum', $gqcus_mnum); $this->assign('gqcus_znum', $gqcus_znum);
        //赋值数据到模板
        $this->assign('wmeb_num', $wmeb_num); $this->assign('fmeb_num', $fmeb_num); $this->assign('ykmeb_num', $ykmeb_num); $this->assign('jkmeb_num', $jkmeb_num); $this->assign('zzmeb_num', $zzmeb_num);
        //赋值数据到模板
        $this->assign('dcus_num', $dcus_num); $this->assign('ccus_num', $ccus_num); $this->assign('bcus_num', $bcus_num); $this->assign('acus_num', $acus_num); $this->assign('kacus_num', $kacus_num);
        //获取小数点后两位
        $hy_wmoney = sprintf('%.2f', $hy_wmoney); $hy_mmoney = sprintf('%.2f', $hy_mmoney); $hy_zmoney = sprintf('%.2f', $hy_zmoney);
        //获取小数点后两位
        $qyser_wnum = sprintf('%.2f', $qyser_wnum); $qyser_mnum = sprintf('%.2f', $qyser_mnum); $qyser_znum = sprintf('%.2f', $qyser_znum);
        //赋值数据到模板
        $this->assign('hy_wmoney', $hy_wmoney); $this->assign('hy_mmoney', $hy_mmoney); $this->assign('hy_zmoney', $hy_zmoney);
        //赋值数据到模板
        $this->assign('qyser_wnum', $qyser_wnum); $this->assign('qyser_mnum', $qyser_mnum); $this->assign('qyser_znum', $qyser_znum);
        //赋值数据到模板
        $this->assign('wfollowup', $wf_num); $this->assign('tfollowup', $tf_num); $this->assign('mfollowup', $mf_num); $this->assign('jfollowup', $jf_num);
        //总的企业数
        $zcus_num = $dcus_num + $ccus_num + $bcus_num + $acus_num + $kacus_num;
        if($zcus_num != 0){
            $dl_ratio = (float)$dcus_num / (float)$zcus_num * 100;
            $dl_ratio = sprintf('%.2f', $dl_ratio);
            $cl_ratio = (float)$ccus_num / (float)$zcus_num * 100;
            $cl_ratio = sprintf('%.2f', $cl_ratio);
            $bl_ratio = (float)$bcus_num / (float)$zcus_num * 100;
            $bl_ratio = sprintf('%.2f', $bl_ratio);
            $al_ratio = (float)$acus_num / (float)$zcus_num * 100;
            $al_ratio = sprintf('%.2f', $al_ratio);
            $ka_ratio = (float)$kacus_num / (float)$zcus_num * 100;
            $ka_ratio = sprintf('%.2f', $ka_ratio);
        } else {
            $dl_ratio = 0;
            $cl_ratio = 0;
            $bl_ratio = 0;
            $al_ratio = 0;
            $ka_ratio = 0;
        }
        //赋值数据到模板
        $this->assign('dl_ratio', $dl_ratio); $this->assign('cl_ratio', $cl_ratio); $this->assign('bl_ratio', $bl_ratio); $this->assign('al_ratio', $al_ratio); $this->assign('ka_ratio', $ka_ratio);

        //计算服务费比例
        $sale_znum = sprintf('%.2f', (float)$hy_zmoney + (float)$qyser_znum);
        $this->assign('sale_znum', $sale_znum);
        // 服务费比例
        $circle_progress = 0;
        if($sale_znum != 0) $circle_progress = sprintf('%.2f', ((float)$hy_zmoney / (float)$sale_znum));
        $this->assign('circle_progress', $circle_progress);

        $maps   = $this->getMap();
        $maps[] = ['role','=',11];
        $field = 'id,parent_id,username,nickname,create_time';
        if($role == 3){
            $maps[] = ['parent_id','=',$uid];
        }
        // 销售详情列表
        $list = UserModel::getUserList($maps,$field,10);
        $list1 = $list->toArray();
        foreach ($list1['data'] as $k => $v){
            // 跟进总总数
            $v['cus_znum'] = Company::getSigningCount($v['id']);
            // 签约客户
            $v['ser_num'] = Company::getSigningNotMemberCount('4,5',$v['id']);
            // 过期客户
            $v['gqcus_znum'] = Company::getSigningExpireCount($v['id']);
            // 签约比例
            $v['gq_progress'] = 0;
            if($v['cus_znum'] != 0) $v['gq_progress'] = sprintf('%.2f', (float)($v['ser_num'] / $v['cus_znum'])) * 100;

            // D类客户
            $v['dlcus_num'] = Company::getSigningClassCount(6,$v['id']);
            // C类客户
            $v['clcus_num'] = Company::getSigningClassCount(2,$v['id']);
            // B类客户
            $v['blcus_num'] = Company::getSigningClassCount(3,$v['id']);
            // A类客户
            $v['alcus_num'] = Company::getSigningClassCount(4,$v['id']);
            // KA客户
            $v['kacus_num'] = Company::getSigningClassCount(5,$v['id']);

            // 成交会员金额
            $v['sub_zamount'] = sprintf('%.2f', Company::SigningMemberFee('',$v['id']));
            // 成交企业服务费金额
            $v['sub_zfee'] = sprintf('%.2f', Qyservese::SigningServiceFee('',$v['id']));
            // 销售客户总金额
            $v['sale_znum'] = (float)$v['sub_zamount'] + (float)$v['sub_zfee'];
            // 收取服务费比例
            $v['fee_progress'] = 0;
            if($v['sale_znum'] != 0) $v['fee_progress'] = sprintf('%.2f', ((float)$v['sub_zamount'] / (float)$v['sale_znum'])) * 100;

            $data = $v;
            $list->offsetSet($k,$data);
        }
        $data = array(
            'list' => $list,
            'page' => $list->render(),
            'count' => $list->total(),
        );
        $this->assign($data);
        $listtoarray = $list->toArray();
        $this->assign('listtoarray',$listtoarray);

        return $this->fetch();
    }

    public function saleCusData1() {
        $uid = session('member_user_auth.uid');
        //用户进入存入session的id
        $uid = session('member_user_auth.uid');
        //识别是否是子账号
        $relate = find_sub($uid);
        //是的话默认为主账号操作
        if($relate) $uid = $relate['master_uid'];
        //接收ced_id参数
        $ced_id = input('ced_id');
        //存在参数 替换用户id
        if($ced_id != '') $uid = $ced_id;
        //赋值到模板
        $this->assign('uid',$uid);
        //设置查询条件
        $map   = $this->getMap();
        $map[] = ['id','=',$uid];
        //查出当前用户角色
        $role = UserModel::getValues($map,'role');
        $this->assign('role',$role);

        $ids = [];
        //销售管理查出手下的id合集
        if($role == 3){
            $map = []; $map[] = ['parent_id','=',$uid]; $map[] = ['role','=',11];
            $rids = UserModel::getUserArray($map,'id');
            $ids = array_column($rids,'id');
        }

        // 本周新增企业
        $com_wnum = Db::table('dp_member_company')->where('sub_num','>',0);
        // 本月新增企业
        $com_mnum = Db::table('dp_member_company')->where('sub_num','>',0);
        // 总新增企业
        $com_znum = Db::table('dp_member_company')->where('sub_num','>',0);
        if($role == 3){
            $com_wnum = $com_wnum->whereIn('signing_id',$ids);
            $com_mnum = $com_mnum->whereIn('signing_id',$ids);
            $com_znum = $com_znum->whereIn('signing_id',$ids);
        }
        if($role == 11){
            $com_wnum = $com_wnum->where('signing_id',$uid);
            $com_mnum = $com_mnum->where('signing_id',$uid);
            $com_znum = $com_znum->where('signing_id',$uid);
        }
        $com_wnum = $com_wnum->whereTime('create_time', 'week')->count();
        $com_mnum = $com_mnum->whereTime('create_time', 'month')->count();
        $com_znum = $com_znum->count();

        $this->assign('com_wnum', $com_wnum);
        $this->assign('com_mnum', $com_mnum);
        $this->assign('com_znum', $com_znum);

        // 本周到期客户
        $gqcus_wnum = Db::table('dp_member_company')->where('cooperation_type',4)->where('sub_num','>',0);
        // 本月到期客户
        $gqcus_mnum = Db::table('dp_member_company')->where('cooperation_type',4)->where('sub_num','>',0);
        // 总到期客户
        $gqcus_znum = Db::table('dp_member_company')->where('cooperation_type',4)->where('sub_num','>',0);
        if($role == 3){
            $gqcus_wnum = $gqcus_wnum->whereIn('signing_id',$ids);
            $gqcus_mnum = $gqcus_mnum->whereIn('signing_id',$ids);
            $gqcus_znum = $gqcus_znum->whereIn('signing_id',$ids);
        }
        if($role == 11){
            $gqcus_wnum = $gqcus_wnum->where('signing_id',$uid);
            $gqcus_mnum = $gqcus_mnum->where('signing_id',$uid);
            $gqcus_znum = $gqcus_znum->where('signing_id',$uid);
        }
        $gqcus_wnum = $gqcus_wnum->whereTime('end_time', 'week')->count();
        $gqcus_mnum = $gqcus_mnum->whereTime('end_time', 'month')->count();
        $gqcus_znum = $gqcus_znum->count();

        $this->assign('gqcus_wnum', $gqcus_wnum);
        $this->assign('gqcus_mnum', $gqcus_mnum);
        $this->assign('gqcus_znum', $gqcus_znum);

        // 未成交客户
        $wmeb_num = Db::table('dp_member_company')->where('cooperation_type',5)->where('sub_num','>',0);
        // 非会员
        $fmeb_num = Db::table('dp_member_company')->where('cooperation_type',0)->where('sub_num','>',0);
        // 银卡会员
        $ykmeb_num = Db::table('dp_member_company')->whereIn('cooperation_type','1,6,9,10')->where('sub_num','>',0);
        // 金卡会员
        $jkmeb_num = Db::table('dp_member_company')->whereIn('cooperation_type','2,7')->where('sub_num','>',0);
        // 至尊会员
        $zzmeb_num = Db::table('dp_member_company')->whereIn('cooperation_type','3,8')->where('sub_num','>',0);
        if($role == 3){
            $wmeb_num = $wmeb_num->whereIn('signing_id',$ids);
            $fmeb_num = $fmeb_num->whereIn('signing_id',$ids);
            $ykmeb_num = $ykmeb_num->whereIn('signing_id',$ids);
            $jkmeb_num = $jkmeb_num->whereIn('signing_id',$ids);
            $zzmeb_num = $zzmeb_num->whereIn('signing_id',$ids);
        }
        if($role == 11){
            $wmeb_num = $wmeb_num->where('signing_id',$uid);
            $fmeb_num = $fmeb_num->where('signing_id',$uid);
            $ykmeb_num = $ykmeb_num->where('signing_id',$uid);
            $jkmeb_num = $jkmeb_num->where('signing_id',$uid);
            $zzmeb_num = $zzmeb_num->where('signing_id',$uid);
        }
        $wmeb_num = $wmeb_num->count();
        $fmeb_num = $fmeb_num->count();
        $ykmeb_num = $ykmeb_num->count();
        $jkmeb_num = $jkmeb_num->count();
        $zzmeb_num = $zzmeb_num->count();

        $this->assign('wmeb_num', $wmeb_num);
        $this->assign('fmeb_num', $fmeb_num);
        $this->assign('ykmeb_num', $ykmeb_num);
        $this->assign('jkmeb_num', $jkmeb_num);
        $this->assign('zzmeb_num', $zzmeb_num);

        // D类客户
        $dcus_num = Db::table('dp_member_company')->where('customer_labels',6)->where('sub_num','>',0);
        // C类客户
        $ccus_num = Db::table('dp_member_company')->where('customer_labels',2)->where('sub_num','>',0);
        // B类客户
        $bcus_num = Db::table('dp_member_company')->where('customer_labels',3)->where('sub_num','>',0);
        // A类客户
        $acus_num = Db::table('dp_member_company')->where('customer_labels',4)->where('sub_num','>',0);
        // KA类客户
        $kacus_num = Db::table('dp_member_company')->where('customer_labels',5)->where('sub_num','>',0);
        if($role == 3){
            $dcus_num = $dcus_num->whereIn('signing_id',$ids);
            $ccus_num = $ccus_num->whereIn('signing_id',$ids);
            $bcus_num = $bcus_num->whereIn('signing_id',$ids);
            $acus_num = $acus_num->whereIn('signing_id',$ids);
            $kacus_num = $kacus_num->whereIn('signing_id',$ids);
        }
        if($role == 11){
            $dcus_num = $dcus_num->where('signing_id',$uid);
            $ccus_num = $ccus_num->where('signing_id',$uid);
            $bcus_num = $bcus_num->where('signing_id',$uid);
            $acus_num = $acus_num->where('signing_id',$uid);
            $kacus_num = $kacus_num->where('signing_id',$uid);
        }
        $dcus_num = $dcus_num->count();
        $ccus_num = $ccus_num->count();
        $bcus_num = $bcus_num->count();
        $acus_num = $acus_num->count();
        $kacus_num = $kacus_num->count();

        $this->assign('dcus_num', $dcus_num);
        $this->assign('ccus_num', $ccus_num);
        $this->assign('bcus_num', $bcus_num);
        $this->assign('acus_num', $acus_num);
        $this->assign('kacus_num', $kacus_num);

        $zcus_num = $dcus_num + $ccus_num + $bcus_num + $acus_num + $kacus_num;
        if($zcus_num != 0){
            $dl_ratio = (float)$dcus_num / (float)$zcus_num * 100;
            $dl_ratio = sprintf('%.2f', $dl_ratio);
            $cl_ratio = (float)$ccus_num / (float)$zcus_num * 100;
            $cl_ratio = sprintf('%.2f', $cl_ratio);
            $bl_ratio = (float)$bcus_num / (float)$zcus_num * 100;
            $bl_ratio = sprintf('%.2f', $bl_ratio);
            $al_ratio = (float)$acus_num / (float)$zcus_num * 100;
            $al_ratio = sprintf('%.2f', $al_ratio);
            $ka_ratio = (float)$kacus_num / (float)$zcus_num * 100;
            $ka_ratio = sprintf('%.2f', $ka_ratio);
        } else {
            $dl_ratio = 0;
            $cl_ratio = 0;
            $bl_ratio = 0;
            $al_ratio = 0;
            $ka_ratio = 0;
        }

        $this->assign('dl_ratio', $dl_ratio);
        $this->assign('cl_ratio', $cl_ratio);
        $this->assign('bl_ratio', $bl_ratio);
        $this->assign('al_ratio', $al_ratio);
        $this->assign('ka_ratio', $ka_ratio);


        // 本周成交会员金额
        $hy_wamount = Db::table('dp_member_company')->whereNotIn('cooperation_type','0,5')
            ->whereTime('sign_up_time', 'week');
        // 本月成交会员金额
        $hy_mamount = Db::table('dp_member_company')->whereNotIn('cooperation_type','0,5')
            ->whereTime('sign_up_time', 'month');
        // 总成交会员金额
        $hy_zamount = Db::table('dp_member_company')->whereNotIn('cooperation_type','0,5');
        if($role == 3){
            $hy_wamount = $hy_wamount->whereIn('signing_id',$ids);
            $hy_mamount = $hy_mamount->whereIn('signing_id',$ids);
            $hy_zamount = $hy_zamount->whereIn('signing_id',$ids);
        }
        if($role == 11){
            $hy_wamount = $hy_wamount->where('signing_id',$uid);
            $hy_mamount = $hy_mamount->where('signing_id',$uid);
            $hy_zamount = $hy_zamount->where('signing_id',$uid);
        }
        $hy_wamount = $hy_wamount->sum('members_fee');
        $hy_mamount = $hy_mamount->sum('members_fee');
        $hy_zamount = $hy_zamount->sum('members_fee');

        $hy_wmoney = sprintf('%.2f', $hy_wamount);
        $hy_mmoney = sprintf('%.2f', $hy_mamount);
        $hy_zmoney = sprintf('%.2f', $hy_zamount);
        $this->assign('hy_wmoney', $hy_wmoney);
        $this->assign('hy_mmoney', $hy_mmoney);
        $this->assign('hy_zmoney', $hy_zmoney);

        // 本周成交企业服务金额
        $qyser_wnum = Db::table('dp_member_qyservese')->alias('a')->where('a.delete_time',0)
            ->whereTime('a.create_time', 'week');
        // 本月成交企业服务金额
        $qyser_mnum = Db::table('dp_member_qyservese')->alias('a')->where('a.delete_time',0)
            ->whereTime('a.create_time', 'month');
        // 总成交企业服务金额
        $qyser_znum = Db::table('dp_member_qyservese')->alias('a')->where('a.delete_time',0);
        if($role == 3){
            $qyser_wnum = $qyser_wnum->whereIn('c.signing_id',$ids);
            $qyser_mnum = $qyser_mnum->whereIn('c.signing_id',$ids);
            $qyser_znum = $qyser_znum->whereIn('c.signing_id',$ids);
        }
        if($role == 11){
            $qyser_wnum = $qyser_wnum->where('c.signing_id',$uid);
            $qyser_mnum = $qyser_mnum->where('c.signing_id',$uid);
            $qyser_znum = $qyser_znum->where('c.signing_id',$uid);
        }
        $qyser_wnum = $qyser_wnum
            ->leftJoin('dp_member_company c','a.cid = c.id')
            ->leftJoin('dp_member_authenmessenger b','c.parent_id = b.uid')
            ->sum('a.payment');
        $qyser_mnum = $qyser_mnum
            ->leftJoin('dp_member_company c','a.cid = c.id')
            ->leftJoin('dp_member_authenmessenger b','c.parent_id = b.uid')
            ->sum('a.payment');
        $qyser_znum = $qyser_znum
            ->leftJoin('dp_member_company c','a.cid = c.id')
            ->leftJoin('dp_member_authenmessenger b','c.parent_id = b.uid')
            ->sum('a.payment');

        $qyser_wnum = sprintf('%.2f', $qyser_wnum);
        $qyser_mnum = sprintf('%.2f', $qyser_mnum);
        $qyser_znum = sprintf('%.2f', $qyser_znum);
        $this->assign('qyser_wnum', $qyser_wnum);
        $this->assign('qyser_mnum', $qyser_mnum);
        $this->assign('qyser_znum', $qyser_znum);

        $sale_znum = $hy_zmoney + $qyser_znum;
        $sale_znum = sprintf('%.2f', $sale_znum);
        $this->assign('sale_znum', $sale_znum);

        if($sale_znum != 0){
            $circle_progress = sprintf('%.2f', (float)($hy_zmoney / $sale_znum));
        } else{
            $circle_progress = 0;
        }
        $this->assign('circle_progress', $circle_progress);


        // 未跟进客户
        $idss = Db::table('dp_member_followup')->alias('a')->field('a.id,a.types,a.update_time')
            ->where('a.delete_time',0)->where('a.status',0);
        if($role == 3){
            $idss = $idss->whereIn('a.uid',$ids);
        }
        if($role == 11){
            $idss = $idss->where('a.uid',$uid);
        }
        $idss = $idss->join('dp_member_company s','a.sub_id = s.id','left')
            ->order('a.update_time','DESC')->group('s.id')->select();

        $weekTime = mktime(0,0,0,date('m'),date('d')-7,date('Y'))-1;
        $tweekTime = mktime(0,0,0,date('m'),date('d')-14,date('Y'))-1;
        $mweekTime = mktime(0,0,0,date('m'),date('d')-30,date('Y'))-1;
        $jweekTime = mktime(0,0,0,date('m'),date('d')-50,date('Y'))-1;
        $wf_num = 0;
        foreach($idss as $w){
            if($w['update_time'] < $weekTime && $tweekTime < $w['update_time'] && $w['types'] == 1){
                $wf_num = $wf_num + 1;
            }
        }
        $tf_num = 0;
        foreach($idss as $t){
            if($t['update_time'] < $tweekTime && $mweekTime < $t['update_time'] && $t['types'] == 1){
                $tf_num = $tf_num + 1;
            }
        }
        $mf_num = 0;
        foreach($idss as $m){
            if($m['update_time'] < $mweekTime && $jweekTime < $m['update_time'] && $m['types'] == 1){
                $mf_num = $mf_num + 1;
            }
        }
        $jf_num = 0;
        foreach($idss as $j){
            if($j['update_time'] < $jweekTime && $j['types'] == 1){
                $jf_num = $jf_num + 1;
            }
        }
        $this->assign('wfollowup', $wf_num);
        $this->assign('tfollowup', $tf_num);
        $this->assign('mfollowup', $mf_num);
        $this->assign('jfollowup', $jf_num);

        // 销售详情列表
        $list = UserModel::field('id,parent_id,username,nickname,create_time')->where('role',11);
        if($role == 3){
            $list = $list->where('parent_id',$uid);
        }
        $list = $list->order('create_time','DESC')
            ->paginate(10,false,['query'=>request()->param()]);
        $list1 = $list->toArray();
        foreach ($list1['data'] as $k => $v){
            // 跟进总总数
            $v['cus_znum'] = Db::table('dp_member_company')->where('signing_id',$v['id'])->where('sub_num','>',0)->count();
            // 签约客户
            $v['ser_num'] = Db::table('dp_member_company')->where('signing_id',$v['id'])->where('sub_num','>',0)
                ->whereNotIn('cooperation_type','4,5')->count();
            // 过期客户
            $v['gqcus_znum'] = Db::table('dp_member_company')->where('signing_id',$v['id'])->where('sub_num','>',0)
                ->where('cooperation_type',4)->count();
            // 签约比例
            if($v['cus_znum'] != 0){
                $v['gq_progress'] = sprintf('%.2f', (float)($v['ser_num'] / $v['cus_znum'])) * 100;
            } else{
                $v['gq_progress'] = 0;
            }

            // D类客户
            $v['dlcus_num'] = Db::table('dp_member_company')->where('signing_id',$v['id'])->where('customer_labels',6)->where('sub_num','>',0)->count();
            // C类客户
            $v['clcus_num'] = Db::table('dp_member_company')->where('signing_id',$v['id'])->where('customer_labels',2)->where('sub_num','>',0)->count();
            // B类客户
            $v['blcus_num'] = Db::table('dp_member_company')->where('signing_id',$v['id'])->where('customer_labels',3)->where('sub_num','>',0)->count();
            // A类客户
            $v['alcus_num'] = Db::table('dp_member_company')->where('signing_id',$v['id'])->where('customer_labels',4)->where('sub_num','>',0)->count();
            // KA客户
            $v['kacus_num'] = Db::table('dp_member_company')->where('signing_id',$v['id'])->where('customer_labels',5)->where('sub_num','>',0)->count();

            // 成交会员金额
            $v['sub_zamount'] = Db::table('dp_member_company')->where('signing_id',$v['id'])
                ->whereNotIn('cooperation_type','0,5')->sum('members_fee');
            $v['sub_zamount'] = sprintf('%.2f', $v['sub_zamount']);
            // 成交企业服务费金额
            $v['sub_zfee'] = Db::table('dp_member_qyservese')->alias('a')->where('c.signing_id',$v['id'])
                ->where('a.delete_time',0)
                ->leftJoin('dp_member_company c','a.cid = c.id')
                ->leftJoin('dp_member_authenmessenger b','c.parent_id = b.uid')
                ->sum('a.payment');
            $v['sub_zfee'] = sprintf('%.2f', $v['sub_zfee']);
            $v['sale_znum'] = $v['sub_zamount'] + $v['sub_zfee'];
            // 收取服务费比例
            if($v['sale_znum'] != 0){
                $v['fee_progress'] = sprintf('%.2f', (float)($v['sub_zamount'] / $v['sale_znum'])) * 100;
            } else{
                $v['fee_progress'] = 0;
            }

            $data = $v;
            $list->offsetSet($k,$data);}
        $data = array(
            'list' => $list,
            'page' => $list->render(),
            'count' => $list->total(),
        );
        $this->assign($data);
        $listtoarray = $list->toArray();
        $this->assign('listtoarray',$listtoarray);

        return $this->fetch();
    }

    // 获取销售漏斗数据
    public function getSaleFunnel() {
        if($this->request->isAjax()) {
            //接收用户id
            $uid = $this->request->post('uid');
            //设置查询数据
            $map   = $this->getMap();
            $map[] = ['id','=',$uid];
            //获取用户角色
            $role = UserModel::getValues($map,'role');
            $this->assign('role',$role);
            //获取下级id合集
            $ids = [];
            if($role == 3){
                $map = []; $map[] = ['parent_id','=',$uid]; $map[] = ['role','=',11];
                $ids = UserModel::getUserIdArray($map,'id');
            }
            //时间戳参数
            $serveTime = $this->request->post('serveTime');
            // D类客户
            $dcus_num = Company::where('sub_num','>',0)->where('customer_labels',6);
            // C类客户
            $ccus_num = Company::where('sub_num','>',0)->where('customer_labels',2);
            // B类客户
            $bcus_num = Company::where('sub_num','>',0)->where('customer_labels',3);
            // A类客户
            $acus_num = Company::where('sub_num','>',0)->where('customer_labels',4);
            // KA类客户
            $kacus_num = Company::where('sub_num','>',0)->where('customer_labels',5);
            if($role == 3){
                $dcus_num = $dcus_num->whereIn('signing_id',$ids);
                $ccus_num = $ccus_num->whereIn('signing_id',$ids);
                $bcus_num = $bcus_num->whereIn('signing_id',$ids);
                $acus_num = $acus_num->whereIn('signing_id',$ids);
                $kacus_num = $kacus_num->whereIn('signing_id',$ids);
            }
            if($role == 11){
                $dcus_num = $dcus_num->where('signing_id',$uid);
                $ccus_num = $ccus_num->where('signing_id',$uid);
                $bcus_num = $bcus_num->where('signing_id',$uid);
                $acus_num = $acus_num->where('signing_id',$uid);
                $kacus_num = $kacus_num->where('signing_id',$uid);
            }
            $season = ceil(date('n') /3); //获取月份的季度
            $seasonStart = date('Y-m-01',mktime(0,0,0,($season - 1) *3 +1,1,date('Y')));
            $seasonStart = strtotime($seasonStart);
            $seasonEnd = date('Y-m-t',mktime(0,0,0,$season * 3,1,date('Y')));
            $seasonEnd = strtotime($seasonEnd);
            $lastSeasonStart = date('Y-m-01',mktime(0,0,0,($season - 2) * 3 +1,1,date('Y')));
            $lastSeasonStart = strtotime($lastSeasonStart);
            $lastSeasonEnd = date('Y-m-t',mktime(0,0,0,($season - 1) * 3,1,date('Y')));
            $lastSeasonEnd = strtotime($lastSeasonEnd);
            if($serveTime == 1){
                $dcus_num = $dcus_num->whereTime('create_time', 'week');
                $ccus_num = $ccus_num->whereTime('create_time', 'week');
                $bcus_num = $bcus_num->whereTime('create_time', 'week');
                $acus_num = $acus_num->whereTime('create_time', 'week');
                $kacus_num = $kacus_num->whereTime('create_time', 'week');
            } elseif($serveTime == 2){
                $dcus_num = $dcus_num->whereTime('create_time', 'month');
                $ccus_num = $ccus_num->whereTime('create_time', 'month');
                $bcus_num = $bcus_num->whereTime('create_time', 'month');
                $acus_num = $acus_num->whereTime('create_time', 'month');
                $kacus_num = $kacus_num->whereTime('create_time', 'month');
            } elseif($serveTime == 3){
                $dcus_num = $dcus_num->whereTime('create_time','between',[$seasonStart,$seasonEnd]);
                $ccus_num = $ccus_num->whereTime('create_time','between',[$seasonStart,$seasonEnd]);
                $bcus_num = $bcus_num->whereTime('create_time','between',[$seasonStart,$seasonEnd]);
                $acus_num = $acus_num->whereTime('create_time','between',[$seasonStart,$seasonEnd]);
                $kacus_num = $kacus_num->whereTime('create_time','between',[$seasonStart,$seasonEnd]);
            } elseif($serveTime == 4){
                $dcus_num = $dcus_num->whereTime('create_time', 'year');
                $ccus_num = $ccus_num->whereTime('create_time', 'year');
                $bcus_num = $bcus_num->whereTime('create_time', 'year');
                $acus_num = $acus_num->whereTime('create_time', 'year');
                $kacus_num = $kacus_num->whereTime('create_time', 'year');
            } elseif($serveTime == 5){
                $dcus_num = $dcus_num->whereTime('create_time', 'yesterday');
                $ccus_num = $ccus_num->whereTime('create_time', 'yesterday');
                $bcus_num = $bcus_num->whereTime('create_time', 'yesterday');
                $acus_num = $acus_num->whereTime('create_time', 'yesterday');
                $kacus_num = $kacus_num->whereTime('create_time', 'yesterday');
            } elseif($serveTime == 6){
                $dcus_num = $dcus_num->whereTime('create_time', 'last week');
                $ccus_num = $ccus_num->whereTime('create_time', 'last week');
                $bcus_num = $bcus_num->whereTime('create_time', 'last week');
                $acus_num = $acus_num->whereTime('create_time', 'last week');
                $kacus_num = $kacus_num->whereTime('create_time', 'last week');
            } elseif($serveTime == 7){
                $dcus_num = $dcus_num->whereTime('create_time', 'last month');
                $ccus_num = $ccus_num->whereTime('create_time', 'last month');
                $bcus_num = $bcus_num->whereTime('create_time', 'last month');
                $acus_num = $acus_num->whereTime('create_time', 'last month');
                $kacus_num = $kacus_num->whereTime('create_time', 'last month');
            } elseif($serveTime == 8){
                $dcus_num = $dcus_num->whereTime('create_time','between',[$lastSeasonStart,$lastSeasonEnd]);
                $ccus_num = $ccus_num->whereTime('create_time','between',[$lastSeasonStart,$lastSeasonEnd]);
                $bcus_num = $bcus_num->whereTime('create_time','between',[$lastSeasonStart,$lastSeasonEnd]);
                $acus_num = $acus_num->whereTime('create_time','between',[$lastSeasonStart,$lastSeasonEnd]);
                $kacus_num = $kacus_num->whereTime('create_time','between',[$lastSeasonStart,$lastSeasonEnd]);
            } elseif($serveTime == 9){
                $dcus_num = $dcus_num->whereTime('create_time', 'last year');
                $ccus_num = $ccus_num->whereTime('create_time', 'last year');
                $bcus_num = $bcus_num->whereTime('create_time', 'last year');
                $acus_num = $acus_num->whereTime('create_time', 'last year');
                $kacus_num = $kacus_num->whereTime('create_time', 'last year');
            }
            $dcus_num = $dcus_num->count();
            $ccus_num = $ccus_num->count();
            $bcus_num = $bcus_num->count();
            $acus_num = $acus_num->count();
            $kacus_num = $kacus_num->count();

            $zcus_num = $dcus_num + $ccus_num + $bcus_num + $acus_num + $kacus_num;
            if($zcus_num != 0){
                $dl_ratio = (float)$dcus_num / (float)$zcus_num * 100;
                $dl_ratio = sprintf('%.2f', $dl_ratio);
                $cl_ratio = (float)$ccus_num / (float)$zcus_num * 100;
                $cl_ratio = sprintf('%.2f', $cl_ratio);
                $bl_ratio = (float)$bcus_num / (float)$zcus_num * 100;
                $bl_ratio = sprintf('%.2f', $bl_ratio);
                $al_ratio = (float)$acus_num / (float)$zcus_num * 100;
                $al_ratio = sprintf('%.2f', $al_ratio);
                $ka_ratio = (float)$kacus_num / (float)$zcus_num * 100;
                $ka_ratio = sprintf('%.2f', $ka_ratio);
            } else {
                $dl_ratio = 0;
                $cl_ratio = 0;
                $bl_ratio = 0;
                $al_ratio = 0;
                $ka_ratio = 0;
            }

            $data = ['code'=>1,'dl_ratio'=>$dl_ratio,'cl_ratio'=>$cl_ratio,'bl_ratio'=>$bl_ratio,'al_ratio'=>$al_ratio,'ka_ratio'=>$ka_ratio,
                'dcus_num'=>$dcus_num,'ccus_num'=>$ccus_num,'bcus_num'=>$bcus_num,'acus_num'=>$acus_num,'kacus_num'=>$kacus_num,'zcus_num'=>$zcus_num];

            return $data;
        }
    }

    // 销售客户数据列表
    public function saleCusList() {
        //接收用户id
        $uid = input('uid');
        $this->assign('uid',$uid);
        //设置查询条件
        $map   = $this->getMap();
        $map[] = ['id','=',$uid];
        //查出当前用户角色
        $role = UserModel::getValues($map,'role');
        $this->assign('role',$role);
        //获取下级id合集
        $ids = [];
        if($role == 3){
            $map = []; $map[] = ['parent_id','=',$uid]; $map[] = ['role','=',11];
            $ids = UserModel::getUserIdArray($map,'id');
        }
        $keyword = ['company'=>'','selectTime'=>'', 'cooperation_type'=>'', 'customer_labels'=>'', 'serveTime'=>''];

        //接收参数合集
        $key  = input();
        $keyword = array_merge($keyword,$key);
        $this->assign('keyword',$keyword);
        //时间条件字段
        $time_field = ''; $time_cycle = '';
        //查询需要的字段
        $field = 'a.id,a.uid,a.company,a.cooperation_type,a.cooperation_time,a.customer_labels,a.create_time,
                b.name pname,s.cid,s.name,s.mobile,c.nickname sname';
        //查询条件合集
        $maps = $this->getMap();
        if($role == 3){
            $maps[] = ['a.signing_id','in',$ids];
        }
        if($role == 11){
            $maps[] = ['a.signing_id','=',$uid];
        }
        if($keyword['company']){
            $maps[] = ['a.company','like','%'.$keyword['company'].'%'];
        }
        if($keyword['selectTime']){
            if($keyword['selectTime'] == 2 || $keyword['selectTime'] == 3){
                $time_field = 'a.create_time';
                if($keyword['selectTime'] == 2) $time_cycle = 'week';
                else $time_cycle = 'month';
            }elseif($keyword['selectTime'] == 4 || $keyword['selectTime'] == 5){
                $time_field = 'a.end_time';
                if($keyword['selectTime'] == 4) $time_cycle = 'week';
                else $time_cycle = 'month';
            }
        }
        if($keyword['cooperation_type']){
            if($keyword['cooperation_type'] == 0) {
                $maps[] = ['a.cooperation_type','=','0'];
            } elseif($keyword['cooperation_type'] == 1){
                $maps[] = ['a.cooperation_type','in','1,6,9,10'];
            } elseif($keyword['cooperation_type'] == 2){
                $maps[] = ['a.cooperation_type','in','2,7'];
            } elseif($keyword['cooperation_type'] == 3){
                $maps[] = ['a.cooperation_type','in','3,8'];
            } elseif($keyword['cooperation_type'] == 4){
                $maps[] = ['a.cooperation_type','=',4];
            } elseif($keyword['cooperation_type'] == 5){
                $maps[] = ['a.cooperation_type','=',5];
            } elseif($keyword['cooperation_type'] == 6){
                $maps[] = ['a.cooperation_type','not in','4,5'];
            }
        }
        if($keyword['customer_labels']){
            $maps[] = ['a.customer_labels','=',$keyword['customer_labels']];
        }
        if($keyword['serveTime']){
            //获取月份的季度
            $season = ceil(date('n') /3);
            $seasonStart = date('Y-m-01',mktime(0,0,0,($season - 1) *3 +1,1,date('Y')));// 本季开始
            $seasonStart = strtotime($seasonStart);
            $seasonEnd = date('Y-m-t',mktime(0,0,0,$season * 3,1,date('Y')));// 本季结束
            $seasonEnd = strtotime($seasonEnd);
            $lastSeasonStart = date('Y-m-01',mktime(0,0,0,($season - 2) * 3 +1,1,date('Y')));// 上季开始
            $lastSeasonStart = strtotime($lastSeasonStart);
            $lastSeasonEnd = date('Y-m-t',mktime(0,0,0,($season - 1) * 3,1,date('Y')));// 上季结束
            $lastSeasonEnd = strtotime($lastSeasonEnd);
            if($keyword['serveTime'] == 1){
                $time_field = 'a.create_time';
                $time_cycle = 'week';
            } elseif($keyword['serveTime'] == 2){
                $time_field = 'a.create_time';
                $time_cycle = 'month';
            } elseif($keyword['serveTime'] == 3){
                $maps[] = ['a.create_time','between time',[$seasonStart,$seasonEnd]];
            } elseif($keyword['serveTime'] == 4){
                $time_field = 'a.create_time';
                $time_cycle = 'year';
            } elseif($keyword['serveTime'] == 5){
                $time_field = 'a.create_time';
                $time_cycle = 'yesterday';
            } elseif($keyword['serveTime'] == 6){
                $time_field = 'a.create_time';
                $time_cycle = 'last week';
            } elseif($keyword['serveTime'] == 7){
                $time_field = 'a.create_time';
                $time_cycle = 'last month';
            } elseif($keyword['serveTime'] == 8){
                $maps[] = ['a.create_time','between time',[$lastSeasonStart,$lastSeasonEnd]];
            } elseif($keyword['serveTime'] == 9){
                $time_field = 'a.create_time';
                $time_cycle = 'last year';
            }
        }
        $maps[] = ['a.sub_num','>','0'];

        //获取列表数据
        $list = Company::getLists($field,$maps,$time_field,$time_cycle);

        $list1 = $list->toArray();
        foreach ($list1['data'] as $k => $v){
            $name = Db::table('dp_member_company')->where('id',$v['id'])->value('name');
            if($name != '' && $v['name'] == ''){
                $v['name'] = $name;
            }
            $mobile = Db::table('dp_member_company')->where('id',$v['id'])->value('mobile');
            if($mobile != '' && $v['mobile'] == ''){
                $v['mobile'] = $mobile;
            }
            $data = $v;
            $list->offsetSet($k,$data);
        }

        $data = array(
            'list' => $list,
            'page' => $list->render(),
            'count' => $list->total(),
        );
        $this->assign($data);
        $listtoarray = $list->toArray();
        $this->assign('listtoarray',$listtoarray);

        return $this->fetch();
    }

    // 销售成交会员金额列表
    public function saleMemList() {
        //接收用户id
        $uid = input('uid');
        $this->assign('uid',$uid);
        //设置查询条件
        $map   = $this->getMap();
        $map[] = ['id','=',$uid];
        //查出当前用户角色
        $role = UserModel::getValues($map,'role');
        $this->assign('role',$role);
        //获取下级id合集
        $ids = [];
        if($role == 3){
            $map = []; $map[] = ['parent_id','=',$uid]; $map[] = ['role','=',11];
            $ids = UserModel::getUserIdArray($map,'id');
        }
        $keyword = ['company'=>'','selectTime'=>'', 'cooperation_type'=>'', 'customer_labels'=>'', 'serveTime'=>''];

        //接收参数合集
        $key  = input();
        $keyword = array_merge($keyword,$key);
        $this->assign('keyword',$keyword);
        //时间条件字段
        $time_field = ''; $time_cycle = '';
        //查询需要的字段
        $field = 'a.id,a.company,a.members_fee,a.pay_model,a.sign_up_time,a.cooperation_type,a.cooperation_time,
                b.name pname,s.name,s.mobile';
        //查询条件合集
        $maps = $this->getMap();
        if($role == 3){
            $maps[] = ['a.signing_id','in',$ids];
        }
        if($role == 11){
            $maps[] = ['a.signing_id','=',$uid];
        }
        if($keyword['company']){
            $maps[] = ['a.company','like','%'.$keyword['company'].'%'];
        }
        if($keyword['selectTime']){
            if($keyword['selectTime'] == 2 || $keyword['selectTime'] == 3){
                $time_field = 'a.sign_up_time';
                if($keyword['selectTime'] == 2) $time_cycle = 'week';
                else $time_cycle = 'month';
            }
        }
        if($keyword['cooperation_type']){
            if($keyword['cooperation_type'] == 0) {
                $maps[] = ['a.cooperation_type','=','0'];
            } elseif($keyword['cooperation_type'] == 1){
                $maps[] = ['a.cooperation_type','in','1,6,9,10'];
            } elseif($keyword['cooperation_type'] == 2){
                $maps[] = ['a.cooperation_type','in','2,7'];
            } elseif($keyword['cooperation_type'] == 3){
                $maps[] = ['a.cooperation_type','in','3,8'];
            } elseif($keyword['cooperation_type'] == 4){
                $maps[] = ['a.cooperation_type','=',4];
            } elseif($keyword['cooperation_type'] == 5){
                $maps[] = ['a.cooperation_type','=',5];
            } elseif($keyword['cooperation_type'] == 6){
                $maps[] = ['a.cooperation_type','not in','4,5'];
            }
        }
        if($keyword['customer_labels']){
            $maps[] = ['a.customer_labels','=',$keyword['customer_labels']];
        }
        if($keyword['serveTime']){
            //获取月份的季度
            $season = ceil(date('n') /3);
            $seasonStart = date('Y-m-01',mktime(0,0,0,($season - 1) *3 +1,1,date('Y')));// 本季开始
            $seasonStart = strtotime($seasonStart);
            $seasonEnd = date('Y-m-t',mktime(0,0,0,$season * 3,1,date('Y')));// 本季结束
            $seasonEnd = strtotime($seasonEnd);
            $lastSeasonStart = date('Y-m-01',mktime(0,0,0,($season - 2) * 3 +1,1,date('Y')));// 上季开始
            $lastSeasonStart = strtotime($lastSeasonStart);
            $lastSeasonEnd = date('Y-m-t',mktime(0,0,0,($season - 1) * 3,1,date('Y')));// 上季结束
            $lastSeasonEnd = strtotime($lastSeasonEnd);
            if($keyword['serveTime'] == 1){
                $time_field = 'a.create_time';
                $time_cycle = 'week';
            } elseif($keyword['serveTime'] == 2){
                $time_field = 'a.create_time';
                $time_cycle = 'month';
            } elseif($keyword['serveTime'] == 3){
                $maps[] = ['a.create_time','between time',[$seasonStart,$seasonEnd]];
            } elseif($keyword['serveTime'] == 4){
                $time_field = 'a.create_time';
                $time_cycle = 'year';
            } elseif($keyword['serveTime'] == 5){
                $time_field = 'a.create_time';
                $time_cycle = 'yesterday';
            } elseif($keyword['serveTime'] == 6){
                $time_field = 'a.create_time';
                $time_cycle = 'last week';
            } elseif($keyword['serveTime'] == 7){
                $time_field = 'a.create_time';
                $time_cycle = 'last month';
            } elseif($keyword['serveTime'] == 8){
                $maps[] = ['a.create_time','between time',[$lastSeasonStart,$lastSeasonEnd]];
            } elseif($keyword['serveTime'] == 9){
                $time_field = 'a.create_time';
                $time_cycle = 'last year';
            }
        }
        $maps[] = ['a.cooperation_type','not in','0,5'];
        $maps[] = ['a.members_fee','not in',',0'];

        //获取列表数据
        $list = Company::getList($field,$maps,$time_field,$time_cycle);

        $list1 = $list->toArray();
        foreach($list1['data'] as $k=>$v){
            $name = Db::table('dp_member_company')->where('id',$v['id'])->value('name');
            if($name != '' && $v['name'] == ''){
                $v['name'] = $name;
            }
            $mobile = Db::table('dp_member_company')->where('id',$v['id'])->value('mobile');
            if($mobile != '' && $v['mobile'] == ''){
                $v['mobile'] = $mobile;
            }
            if($v['members_fee'] == ''){
                $v['members_fee'] = 0;
            }
            if($v['pay_model'] == ''){
                $v['pay_model'] = 0;
            } else{
                $v['pay_model'] = substr($v['pay_model'],0,strlen($v['pay_model'])-1);
            }
            $v['ser_fee'] = ((float)$v['members_fee'] * (float)$v['pay_model'] / 100);
            $v['ser_fee'] = sprintf('%.2f', $v['ser_fee']);
            $v['members_fee'] = sprintf('%.2f', $v['members_fee']);
            $data = $v;
            $list->offsetSet($k,$data);
        }

        $data = array(
            'list' => $list,
            'page' => $list->render(),
            'count' => $list->total(),
        );
        $this->assign($data);
        $listtoarray = $list->toArray();
        $this->assign('listtoarray',$listtoarray);

        return $this->fetch();
    }

    // 销售项目申报数据列表
    public function saleSerList() {
        //接收用户id
        $uid = input('uid');
        $this->assign('uid',$uid);
        //设置查询条件
        $map   = $this->getMap();
        $map[] = ['id','=',$uid];
        //查出当前用户角色
        $role = UserModel::getValues($map,'role');
        $this->assign('role',$role);
        //获取下级id合集
        $ids = [];
        if($role == 3){
            $map = []; $map[] = ['parent_id','=',$uid]; $map[] = ['role','=',11];
            $ids = UserModel::getUserIdArray($map,'id');
        }

        $keyword = ['company'=>'','selectTime'=>'', 'cooperation_type'=>'', 'customer_labels'=>'', 'serveTime'=>''];

        //接收参数合集
        $key  = input();
        $keyword = array_merge($keyword,$key);
        $this->assign('keyword',$keyword);
        //时间条件字段
        $time_field = ''; $time_cycle = '';
        //查询需要的字段
        $field = 'a.id,a.cid,a.company,a.payment,a.qname,a.create_time,
                b.name pname,c.signing_id';
        //查询条件合集
        $maps = $this->getMap();
        if($role == 3){
            $maps[] = ['c.signing_id','in',$ids];
        }
        if($role == 11){
            $maps[] = ['c.signing_id','=',$uid];
        }
        if($keyword['company']){
            $maps[] = ['a.company','like','%'.$keyword['company'].'%'];
        }
        if($keyword['selectTime']){
            if($keyword['selectTime'] == 2 || $keyword['selectTime'] == 3){
                $time_field = 'a.create_time';
                if($keyword['selectTime'] == 2) $time_cycle = 'week';
                else $time_cycle = 'month';
            }
        }
        $maps[] = ['a.delete_time','=','0'];

        //获取列表数据
        $list = Qyservese::getList($field,$maps,$time_field,$time_cycle);

        $list1 = $list->toArray();
        foreach($list1['data'] as $k=>$v){
            $v['payment'] = sprintf('%.2f', $v['payment']);
            $v['sname'] = Db::table('dp_member_authenmessenger')->where('uid',$v['signing_id'])->value('name');
            $data = $v;
            $list->offsetSet($k,$data);
        }

        $data = array(
            'list' => $list,
            'page' => $list->render(),
            'count' => $list->total(),
        );
        $this->assign($data);
        $listtoarray = $list->toArray();
        $this->assign('listtoarray',$listtoarray);

        return $this->fetch();
    }

    // 销售未跟进客户数据
    public function saleFolList() {
        //接收用户id
        $uid = input('uid');
        $this->assign('uid',$uid);
        //设置查询条件
        $map   = $this->getMap();
        $map[] = ['id','=',$uid];
        //查出当前用户角色
        $role = UserModel::getValues($map,'role');
        $this->assign('role',$role);
        //获取下级id合集
        $ids = [];
        if($role == 12){
            $map = []; $map[] = ['parent_id','=',$uid]; $map[] = ['role','=',10];
            $ids = UserModel::getUserIdArray($map,'id');
        }

        // 未跟进客户
        $idss = Followup::field('a.id,a.types,a.update_time')->alias('a')
            ->where('a.delete_time',0)->where('a.status',0);

        if($role == 11){
            $idss = $idss->where('a.uid',$uid);
        }
        if($role == 3){
            $ids = [];
            $rids = Db::table('dp_member_user')->field('id')->where('parent_id',$uid)->where('role',11)->select();
            foreach($rids as $val){
                array_push($ids,$val['id']);
            }
            $ids = implode(',',$ids);
            $idss = $idss->whereIn('a.uid',$ids);
        }
        $company = input('company');
        $this->assign('company',$company);
        if(isset($company) && $company != ''){
            $idss = $idss->whereLike('s.company','%'.$company.'%');
        }
        $idss = $idss->join('dp_member_company s','a.sub_id = s.id','left')
            ->order('a.update_time','DESC')->group('s.id')->select();

        $weekTime = mktime(0,0,0,date('m'),date('d')-7,date('Y'))-1;
        $tweekTime = mktime(0,0,0,date('m'),date('d')-14,date('Y'))-1;
        $mweekTime = mktime(0,0,0,date('m'),date('d')-30,date('Y'))-1;
        $jweekTime = mktime(0,0,0,date('m'),date('d')-50,date('Y'))-1;

        $selectTime = input('selectTime');
        $this->assign('selectTime',$selectTime);

        $ids = [];
        if($selectTime == 1){
            foreach($idss as $v){
                if($v['update_time'] < $weekTime && $tweekTime < $v['update_time'] && $v['types'] == 1){
                    array_push($ids,$v['id']);
                }
            }
        } elseif($selectTime == 2){
            foreach($idss as $v){
                if($v['update_time'] < $tweekTime && $mweekTime < $v['update_time'] && $v['types'] == 1){
                    array_push($ids,$v['id']);
                }
            }
        } elseif($selectTime == 3){
            foreach($idss as $v){
                if($v['update_time'] < $mweekTime && $jweekTime < $v['update_time'] && $v['types'] == 1){
                    array_push($ids,$v['id']);
                }
            }
        } elseif($selectTime == 4){
            foreach($idss as $v){
                if($v['update_time'] < $jweekTime && $v['types'] == 1){
                    array_push($ids,$v['id']);
                }
            }
        }
        $ids = implode(',',$ids);

        $list = Db::table('dp_member_followup')->alias('a')
            ->field('a.sub_id,a.uid,a.content,a.update_time,
                b.name pname,s.company,s.cooperation_type,s.cooperation_time,s.id cid,c.name,c.mobile')
            ->whereIn('a.id',$ids)->where('s.sub_num','>',0)
            ->join('dp_member_company s','a.sub_id = s.id','left')
            ->join('dp_member_subsidies c','a.sub_id = c.cid','left')
            ->join('dp_member_authenmessenger b','s.parent_id = b.uid','left')
            ->order('a.update_time','DESC')->group('s.id')
            ->paginate(10,false,['query'=>request()->param()]);

        $data = array(
            'list' => $list,
            'page' => $list->render(),
            'count' => $list->total(),
        );
        $this->assign($data);
        $listtoarray = $list->toArray();
        $this->assign('listtoarray',$listtoarray);

        return $this->fetch();
    }



    // 合作伙伴数据汇总
    public function partnerData() {
        //用户进入存入session的id
        $uid = session('member_user_auth.uid');
        //识别是否是子账号
        $relate = find_sub($uid);
        //是的话默认为主账号操作
        if($relate) $uid = $relate['master_uid'];
        //接收ced_id参数
        $ced_id = input('ced_id');
        //存在参数 替换用户id
        if($ced_id != '') $uid = $ced_id;
        //赋值到模板
        $this->assign('uid',$uid);
        //设置查询条件
        $map   = $this->getMap();
        $map[] = ['id','=',$uid];
        //查出当前用户角色
        $role = UserModel::getValues($map,'role');
        $this->assign('role',$role);

        $ids = [];
        if($role == 13){
            $map = []; $map[] = ['parent_id','=',$uid]; $map[] = ['role','=',14];
            $rids = UserModel::getUserArray($map,'id');
            $ids = array_column($rids,'id');
        }

        $weekTime = mktime(0,0,0,date('m'),date('d')-7,date('Y'))-1;
        $tweekTime = mktime(0,0,0,date('m'),date('d')-14,date('Y'))-1;
        $mweekTime = mktime(0,0,0,date('m'),date('d')-30,date('Y'))-1;
        $jweekTime = mktime(0,0,0,date('m'),date('d')-50,date('Y'))-1;

        $week1 = mktime(0,0,0,date('m'),date('d')-date('N')+1,date('y'));
        $week2 = mktime(23,59,59,date('m'),date('d')-date('N')+7,date('Y'));
        $week1 = date("YmdHis",$week1);
        $week2 = date("YmdHis",$week2);

        $month1 = mktime(0,0,0,date('m'),1,date('Y'));
        $month2 = mktime(23,59,59,date('m'),date('t'),date('Y'));
        $month1 = date("YmdHis",$month1);
        $month2 = date("YmdHis",$month2);

        $where['a.totalmoney'] = array('in','399,5980,59800');
        $where['a.pay_type'] = 'xxhy';

        //总后台看到的数据
        if($role == 1){
            //本周新增的合作企业
            $com_wnum = Authenmessenger::ShopNum('week');
            //本月新增的合作企业
            $com_mnum = Authenmessenger::ShopNum('month');
            //总的合作企业
            $com_znum = Authenmessenger::ShopNum();

            // 本周合作到期伙伴
            $gqcus_wnum = Authenmessenger::getExpireCount('','week');
            // 本月合作到期伙伴
            $gqcus_mnum = Authenmessenger::getExpireCount('','month');
            // 总合作到期伙伴
            $gqcus_znum = Authenmessenger::getExpireCount();

            // 补贴小店
            $fmeb_num = Authenmessenger::getMemberCount(1);
            // 旗舰店
            $ykmeb_num = Authenmessenger::getMemberCount(2);
            // 合伙人
            $jkmeb_num = Authenmessenger::getMemberCount(4);
            // 联合运营商
            $zzmeb_num = Authenmessenger::getMemberCount('3,5');

            // C类客户
            $ccus_num = Authenmessenger::getClassCount(2);
            // B类客户
            $bcus_num = Authenmessenger::getClassCount(3);
            // A类客户
            $acus_num = Authenmessenger::getClassCount(4);
            // KA类客户
            $kacus_num = Authenmessenger::getClassCount(5);

            // 本周成交伙伴金额
            $sub_wamount = Orderbank::getDealPartnerFee($week1,$week2,'',$where);
            // 本月成交伙伴金额
            $sub_mamount = Orderbank::getDealPartnerFee($month1,$month2,'',$where);
            // 总成交伙伴金额
            $sub_zamount = Orderbank::getDealPartnerFee('','','',$where);

            // 本周伙伴提现金额
            $sub_wfee = Withdrawalen::getPartnerFee(2,'','week');
            // 本月伙伴提现金额
            $sub_mfee = Withdrawalen::getPartnerFee(2,'','month');
            // 总伙伴提现金额
            $sub_zfee = Withdrawalen::getPartnerFee(2,'');
            // 总提现金额
            $tzmoney = Withdrawalen::getPartnerFee();
            // 个人收益总金额
            $gmoney = Quity::getProfitAmount();

            $idss = Followup::getNotFollowe();

        }
        //渠道管理看到的数据
        elseif($role == 13){
            //本周新增的合作伙伴
            $com_wnum = Authenmessenger::ShopNum('week',$ids);
            //本月新增的合作伙伴
            $com_mnum = Authenmessenger::ShopNum('month',$ids);
            //总的合作伙伴
            $com_znum = Authenmessenger::ShopNum('',$ids);

            // 本周合作到期伙伴
            $gqcus_wnum = Authenmessenger::getExpireCount($ids,'week');
            // 本月合作到期伙伴
            $gqcus_mnum = Authenmessenger::getExpireCount($ids,'month');
            // 总合作到期伙伴
            $gqcus_znum = Authenmessenger::getExpireCount($ids);

            // 补贴小店
            $fmeb_num = Authenmessenger::getMemberCount(1,$ids);
            // 旗舰店
            $ykmeb_num = Authenmessenger::getMemberCount(2,$ids);
            // 合伙人
            $jkmeb_num = Authenmessenger::getMemberCount(4,$ids);
            // 联合运营商
            $zzmeb_num = Authenmessenger::getMemberCount('3,5',$ids);

            // C类客户
            $ccus_num = Authenmessenger::getClassCount(2,$ids);
            // B类客户
            $bcus_num = Authenmessenger::getClassCount(3,$ids);
            // A类客户
            $acus_num = Authenmessenger::getClassCount(4,$ids);
            // KA类客户
            $kacus_num = Authenmessenger::getClassCount(5,$ids);

            // 本周成交伙伴金额
            $sub_wamount = Orderbank::getDealPartnerFee($week1,$week2,$ids,$where);
            // 本月成交伙伴金额
            $sub_mamount = Orderbank::getDealPartnerFee($month1,$month2,$ids,$where);
            // 总成交伙伴金额
            $sub_zamount = Orderbank::getDealPartnerFee('','',$ids,$where);

            // 本周伙伴提现金额
            $sub_wfee = Withdrawalen::getPartnerFee(2,$ids,'week');
            // 本月伙伴提现金额
            $sub_mfee = Withdrawalen::getPartnerFee(2,$ids,'month');
            // 总伙伴提现金额
            $sub_zfee = Withdrawalen::getPartnerFee(2,$ids);
            // 总提现金额
            $tzmoney = Withdrawalen::getPartnerFee('',$ids);
            // 个人收益总金额
            $gmoney = Quity::getProfitAmount($ids);

            $idss = Followup::getNotFollowe($ids);
        }
        //渠道专员看到的数据
        else{
            //本周新增的合作伙伴
            $com_wnum = Authenmessenger::ShopNum('week',$uid);
            //本月新增的合作伙伴
            $com_mnum = Authenmessenger::ShopNum('month',$uid);
            //总的合作伙伴
            $com_znum = Authenmessenger::ShopNum('',$uid);

            // 本周合作到期伙伴
            $gqcus_wnum = Authenmessenger::getExpireCount($uid,'week');
            // 本月合作到期伙伴
            $gqcus_mnum = Authenmessenger::getExpireCount($uid,'month');
            // 总合作到期伙伴
            $gqcus_znum = Authenmessenger::getExpireCount($uid);

            // 补贴小店
            $fmeb_num = Authenmessenger::getMemberCount(1,$uid);
            // 旗舰店
            $ykmeb_num = Authenmessenger::getMemberCount(2,$uid);
            // 合伙人
            $jkmeb_num = Authenmessenger::getMemberCount(4,$uid);
            // 联合运营商
            $zzmeb_num = Authenmessenger::getMemberCount('3,5',$uid);

            // C类客户
            $ccus_num = Authenmessenger::getClassCount(2,$uid);
            // B类客户
            $bcus_num = Authenmessenger::getClassCount(3,$uid);
            // A类客户
            $acus_num = Authenmessenger::getClassCount(4,$uid);
            // KA类客户
            $kacus_num = Authenmessenger::getClassCount(5,$uid);

            // 本周成交伙伴金额
            $sub_wamount = Orderbank::getDealPartnerFee($week1,$week2,$uid,$where);
            // 本月成交伙伴金额
            $sub_mamount = Orderbank::getDealPartnerFee($month1,$month2,$uid,$where);
            // 总成交伙伴金额
            $sub_zamount = Orderbank::getDealPartnerFee('','',$uid,$where);

            // 本周伙伴提现金额
            $sub_wfee = Withdrawalen::getPartnerFee(2,$uid,'week');
            // 本月伙伴提现金额
            $sub_mfee = Withdrawalen::getPartnerFee(2,$uid,'month');
            // 总伙伴提现金额
            $sub_zfee = Withdrawalen::getPartnerFee(2,$uid);
            // 总提现金额
            $tzmoney = Withdrawalen::getPartnerFee('',$uid);
            // 个人收益总金额
            $gmoney = Quity::getProfitAmount($uid);

            $idss = Followup::getNotFollowe($uid);
        }

        $wf_num = 0;
        foreach($idss as $w){
            if($w['update_time'] < $weekTime && $tweekTime < $w['update_time'] && $w['types'] == 2){
                $wf_num = $wf_num + 1;
            }
        }
        $tf_num = 0;
        foreach($idss as $t){
            if($t['update_time'] < $tweekTime && $mweekTime < $t['update_time'] && $t['types'] == 2){
                $tf_num = $tf_num + 1;
            }
        }
        $mf_num = 0;
        foreach($idss as $m){
            if($m['update_time'] < $mweekTime && $jweekTime < $m['update_time'] && $m['types'] == 2){
                $mf_num = $mf_num + 1;
            }
        }
        $jf_num = 0;
        foreach($idss as $j){
            if($j['update_time'] < $jweekTime && $j['types'] == 2){
                $jf_num = $jf_num + 1;
            }
        }
        $this->assign('wfollowup', $wf_num);
        $this->assign('tfollowup', $tf_num);
        $this->assign('mfollowup', $mf_num);
        $this->assign('jfollowup', $jf_num);

        $this->assign('com_wnum', $com_wnum); $this->assign('com_mnum', $com_mnum); $this->assign('com_znum', $com_znum);

        $this->assign('gqcus_wnum', $gqcus_wnum); $this->assign('gqcus_mnum', $gqcus_mnum); $this->assign('gqcus_znum', $gqcus_znum);

        $this->assign('fmeb_num', $fmeb_num); $this->assign('ykmeb_num', $ykmeb_num); $this->assign('jkmeb_num', $jkmeb_num); $this->assign('zzmeb_num', $zzmeb_num);

        $this->assign('ccus_num', $ccus_num); $this->assign('bcus_num', $bcus_num); $this->assign('acus_num', $acus_num); $this->assign('kacus_num', $kacus_num);

        $sub_wamount = sprintf("%.2f",$sub_wamount); $sub_mamount = sprintf("%.2f",$sub_mamount); $sub_zamount = sprintf("%.2f",$sub_zamount);

        $this->assign('sub_wamount', $sub_wamount); $this->assign('sub_mamount', $sub_mamount); $this->assign('sub_zamount', $sub_zamount);

        $sub_wfee = sprintf("%.2f",$sub_wfee); $sub_mfee = sprintf("%.2f",$sub_mfee); $sub_zfee = sprintf("%.2f",$sub_zfee); $tzmoney = sprintf("%.2f",$tzmoney); $gmoney = sprintf("%.2f",$gmoney);

        $this->assign('sub_wfee', $sub_wfee); $this->assign('sub_mfee', $sub_mfee); $this->assign('sub_zfee', $sub_zfee); $this->assign('tzmoney', $tzmoney); $this->assign('gmoney', $gmoney);

        $zcus_num = $fmeb_num + $ykmeb_num + $jkmeb_num + $zzmeb_num;
        if($zcus_num != 0){
            $cl_ratio = (float)$fmeb_num / (float)$zcus_num * 100;
            $cl_ratio = sprintf('%.2f', $cl_ratio);
            $bl_ratio = (float)$ykmeb_num / (float)$zcus_num * 100;
            $bl_ratio = sprintf('%.2f', $bl_ratio);
            $al_ratio = (float)$jkmeb_num / (float)$zcus_num * 100;
            $al_ratio = sprintf('%.2f', $al_ratio);
            $ka_ratio = (float)$zzmeb_num / (float)$zcus_num * 100;
            $ka_ratio = sprintf('%.2f', $ka_ratio);
        } else {
            $cl_ratio = 0;
            $bl_ratio = 0;
            $al_ratio = 0;
            $ka_ratio = 0;
        }
        $this->assign('cl_ratio', $cl_ratio); $this->assign('bl_ratio', $bl_ratio); $this->assign('al_ratio', $al_ratio); $this->assign('ka_ratio', $ka_ratio);

        // 余额/伙伴未提现金额
        $yam_money = $gmoney - $tzmoney;
        $this->assign('yam_money', $yam_money);
        // 提现占比
        $circle_progress = 0;
        if($tzmoney != 0) $circle_progress = sprintf('%.2f', (float)($sub_zfee / $tzmoney));
        $this->assign('circle_progress', $circle_progress);


        $maps   = $this->getMap();
        $maps[] = ['role','=',14];
        $field = 'id,parent_id,username,nickname,create_time';
        if($role == 13){
            $maps[] = ['parent_id','=',$uid];
        }
        // 渠道详情列表
        $list = UserModel::getUserList($maps,$field,10);
        $list1 = $list->toArray();
        foreach ($list1['data'] as $k => $v){

            // 补贴小店
            $v['btd_num'] = Authenmessenger::getMemberCount(1,$v['id']);
            // 旗舰店
            $v['qjd_num'] = Authenmessenger::getMemberCount(2,$v['id']);
            // 合伙人
            $v['hhr_num'] = Authenmessenger::getMemberCount(4,$v['id']);
            // 联合运营商
            $v['yys_num'] = Authenmessenger::getMemberCount('3,5',$v['id']);
            // 合作过期伙伴
            $v['gqd_num'] = Authenmessenger::getExpireCount($v['id']);


            // 查询客户
            $v['cx_znum'] = Db::table('dp_member_company')->alias('a')->where('b.sig_id',$v['id'])
                ->where('a.sub_num','<>',0)
                ->join('dp_member_user b','b.id = a.uid','left')
                ->join('dp_member_subsidies c','a.id = c.uid','left')
                ->count();
            // 合作客户
            $v['hz_num'] = Db::table('dp_member_company')->alias('a')->where('b.sig_id',$v['id'])
                ->whereNotIn('a.cooperation_type','4,5')->where('a.sub_num','<>',0)
                ->join('dp_member_user b','b.id = a.uid','left')
                ->join('dp_member_subsidies c','a.id = c.uid','left')
                ->count();

            // 伙伴成交金额
            $v['sub_zamount'] = sprintf("%.2f",Orderbank::getDealPartnerFee('','',$v['id'],$where));


            // 伙伴提现金额
            $v['sub_zfee'] = sprintf("%.2f",Withdrawalen::getPartnerFee(2,$v['id']));

            // 伙伴收益金额
            $v['gmoney'] = sprintf("%.2f",Quity::getProfitAmount($v['id']));

            $v['tzmoney'] = Withdrawalen::getPartnerFee('',$v['id']);
            
            $v['yam_money'] = sprintf("%.2f",($v['gmoney'] - $v['tzmoney']));

            $data = $v;
            $list->offsetSet($k,$data);
        }
        $data = array(
            'list' => $list,
            'page' => $list->render(),
            'count' => $list->total(),
        );
        $this->assign($data);
        $listtoarray = $list->toArray();
        $this->assign('listtoarray',$listtoarray);

        return $this->fetch();
    }

    public function partnerData1() {
        //用户进入存入session的id
        $uid = session('member_user_auth.uid');
        //识别是否是子账号
        $relate = find_sub($uid);
        //是的话默认为主账号操作
        if($relate) $uid = $relate['master_uid'];
        //接收ced_id参数
        $ced_id = input('ced_id');
        //存在参数 替换用户id
        if($ced_id != '') $uid = $ced_id;
        //赋值到模板
        $this->assign('uid',$uid);
        //设置查询条件
        $map   = $this->getMap();
        $map[] = ['id','=',$uid];
        //查出当前用户角色
        $role = UserModel::getValues($map,'role');
        $this->assign('role',$role);

        $ids = [];
        if($role == 13){
            $map = []; $map[] = ['parent_id','=',$uid]; $map[] = ['role','=',14];
            $rids = UserModel::getUserArray($map,'id');
            $ids = array_column($rids,'id');
        }

        $weekTime = mktime(0,0,0,date('m'),date('d')-7,date('Y'))-1;
        $tweekTime = mktime(0,0,0,date('m'),date('d')-14,date('Y'))-1;
        $mweekTime = mktime(0,0,0,date('m'),date('d')-30,date('Y'))-1;
        $jweekTime = mktime(0,0,0,date('m'),date('d')-50,date('Y'))-1;

        $week1 = mktime(0,0,0,date('m'),date('d')-date('N')+1,date('y'));
        $week2 = mktime(23,59,59,date('m'),date('d')-date('N')+7,date('Y'));
        $week1 = date("YmdHis",$week1);
        $week2 = date("YmdHis",$week2);

        $month1 = mktime(0,0,0,date('m'),1,date('Y'));
        $month2 = mktime(23,59,59,date('m'),date('t'),date('Y'));
        $month1 = date("YmdHis",$month1);
        $month2 = date("YmdHis",$month2);

        //总后台看到的数据
        if($role == 1){
            //本周新增的合作企业
            $com_wnum = Authenmessenger::ShopNum('week');
            //本月新增的合作企业
            $com_mnum = Authenmessenger::ShopNum('month');
            //总的合作企业
            $com_znum = Authenmessenger::ShopNum();

            // 本周到期客户
            $gqcus_wnum = Company::getExpireCount('','week');
            // 本月到期客户
            $gqcus_mnum = Company::getExpireCount('','month');
            // 总到期客户
            $gqcus_znum = Company::getExpireCount();

            // 非会员
            $fmeb_num = Company::getMemberCount(0);
            // 银卡会员
            $ykmeb_num = Company::getMemberCount('1,6,9,10');
            // 金卡会员
            $jkmeb_num = Company::getMemberCount('2,7');
            // 至尊会员
            $zzmeb_num = Company::getMemberCount('3,8');

            // C类客户
            $ccus_num = Company::getClassCount(2);
            // B类客户
            $bcus_num = Company::getClassCount(3);
            // A类客户
            $acus_num = Company::getClassCount(4);
            // KA类客户
            $kacus_num = Company::getClassCount(5);

            // 本周申报补贴金额
            $sub_wamount = Subsidiese::DeclaredAmount('week');
            // 本月申报补贴金额
            $sub_mamount = Subsidiese::DeclaredAmount('month');
            // 总申报补贴金额
            $sub_zamount = Subsidiese::ActualDeclaredAmount();

            // 本周服务费支付金额
            $sub_wfee = Subsidiese::ServiceCharge('week');
            // 本月服务费支付金额
            $sub_mfee = Subsidiese::ServiceCharge('month');
            // 总支付服务费金额
            $sub_zfee = Subsidiese::ServiceCharge();

            $wf_num = Followup::getNotFolloweCount($tweekTime,$weekTime);
            $tf_num = Followup::getNotFolloweCount($mweekTime,$tweekTime);
            $mf_num = Followup::getNotFolloweCount($jweekTime,$mweekTime);
            $jf_num = Followup::getNotFolloweCount($jweekTime);

        }
        //渠道管理看到的数据
        elseif($role == 13){
            //本周新增的合作伙伴
            $com_wnum = Authenmessenger::ShopNum('week',$ids);
            //本月新增的合作伙伴
            $com_mnum = Authenmessenger::ShopNum('month',$ids);
            //总的合作伙伴
            $com_znum = Authenmessenger::ShopNum('',$ids);

            // 本周到期客户
            $gqcus_wnum = Company::getExpireCount($ids,'week');
            // 本月到期客户
            $gqcus_mnum = Company::getExpireCount($ids,'month');
            // 总到期客户
            $gqcus_znum = Company::getExpireCount($ids);

            // 非会员
            $fmeb_num = Company::getMemberCount(0,$ids);
            // 银卡会员
            $ykmeb_num = Company::getMemberCount('1,6,9,10',$ids);
            // 金卡会员
            $jkmeb_num = Company::getMemberCount('2,7',$ids);
            // 至尊会员
            $zzmeb_num = Company::getMemberCount('3,8',$ids);

            // C类客户
            $ccus_num = Company::getClassCount(2,$ids);
            // B类客户
            $bcus_num = Company::getClassCount(3,$ids);
            // A类客户
            $acus_num = Company::getClassCount(4,$ids);
            // KA类客户
            $kacus_num = Company::getClassCount(5,$ids);

            // 本周申报补贴金额
            $sub_wamount = Subsidiese::DeclaredAmount('week',$ids);
            // 本月申报补贴金额
            $sub_mamount = Subsidiese::DeclaredAmount('month',$ids);
            // 总申报补贴金额
            $sub_zamount = Subsidiese::ActualDeclaredAmount($ids);

            // 本周服务费支付金额
            $sub_wfee = Subsidiese::ServiceCharge('week',$ids);
            // 本月服务费支付金额
            $sub_mfee = Subsidiese::ServiceCharge('month',$ids);
            // 总支付服务费金额
            $sub_zfee = Subsidiese::ServiceCharge('',$ids);

            $wf_num = Followup::getNotFolloweCount($tweekTime,$weekTime,$ids);
            $tf_num = Followup::getNotFolloweCount($mweekTime,$tweekTime,$ids);
            $mf_num = Followup::getNotFolloweCount($jweekTime,$mweekTime,$ids);
            $jf_num = Followup::getNotFolloweCount($jweekTime,'',$ids);
        }
        //渠道专员看到的数据
        else{
            //本周新增的合作伙伴
            $com_wnum = Authenmessenger::ShopNum('week',$uid);
            //本月新增的合作伙伴
            $com_mnum = Authenmessenger::ShopNum('month',$uid);
            //总的合作伙伴
            $com_znum = Authenmessenger::ShopNum('',$uid);

            // 本周到期客户
            $gqcus_wnum = Company::getExpireCount($uid,'week');
            // 本月到期客户
            $gqcus_mnum = Company::getExpireCount($uid,'month');
            // 总到期客户
            $gqcus_znum = Company::getExpireCount($uid);

            // 非会员
            $fmeb_num = Company::getMemberCount(0,$uid);
            // 银卡会员
            $ykmeb_num = Company::getMemberCount('1,6,9,10',$uid);
            // 金卡会员
            $jkmeb_num = Company::getMemberCount('2,7',$uid);
            // 至尊会员
            $zzmeb_num = Company::getMemberCount('3,8',$uid);

            // C类客户
            $ccus_num = Company::getClassCount(2,$uid);
            // B类客户
            $bcus_num = Company::getClassCount(3,$uid);
            // A类客户
            $acus_num = Company::getClassCount(4,$uid);
            // KA类客户
            $kacus_num = Company::getClassCount(5,$uid);

            // 本周申报补贴金额
            $sub_wamount = Subsidiese::DeclaredAmount('week',$uid);
            // 本月申报补贴金额
            $sub_mamount = Subsidiese::DeclaredAmount('month',$uid);
            // 总申报补贴金额
            $sub_zamount = Subsidiese::ActualDeclaredAmount($uid);

            // 本周服务费支付金额
            $sub_wfee = Subsidiese::ServiceCharge('week',$uid);
            // 本月服务费支付金额
            $sub_mfee = Subsidiese::ServiceCharge('month',$uid);
            // 总支付服务费金额
            $sub_zfee = Subsidiese::ServiceCharge('',$uid);

            $wf_num = Followup::getNotFolloweCount($tweekTime,$weekTime,$uid);
            $tf_num = Followup::getNotFolloweCount($mweekTime,$tweekTime,$uid);
            $mf_num = Followup::getNotFolloweCount($jweekTime,$mweekTime,$uid);
            $jf_num = Followup::getNotFolloweCount($jweekTime,'',$uid);
        }

        $this->assign('com_wnum', $com_wnum);
        $this->assign('com_mnum', $com_mnum);
        $this->assign('com_znum', $com_znum);

        // 本周到期伙伴
        $gqcus_wnum = Db::table('dp_member_authenmessenger')->alias('a');
        // 本月到期伙伴
        $gqcus_mnum = Db::table('dp_member_authenmessenger')->alias('a');
        // 总合作到期伙伴
        $gqcus_znum = Db::table('dp_member_authenmessenger')->alias('a');

        if($role == 13){
            $gqcus_wnum = $gqcus_wnum->whereIn('b.sig_id',$ids);
            $gqcus_mnum = $gqcus_mnum->whereIn('b.sig_id',$ids);
            $gqcus_znum = $gqcus_znum->whereIn('b.sig_id',$ids);
        }
        if($role == 14){
            $gqcus_wnum = $gqcus_wnum->where('b.sig_id',$uid);
            $gqcus_mnum = $gqcus_mnum->where('b.sig_id',$uid);
            $gqcus_znum = $gqcus_znum->where('b.sig_id',$uid);
        }
        $gqcus_wnum = $gqcus_wnum->whereTime('a.end_time', 'week')
            ->join('dp_member_user b','b.id = a.uid','left')
            ->count();
        $gqcus_mnum = $gqcus_mnum->whereTime('a.end_time', 'month')
            ->join('dp_member_user b','b.id = a.uid','left')
            ->count();
        $gqcus_znum = $gqcus_znum->where('a.end_time','<=',time())
            ->join('dp_member_user b','b.id = a.uid','left')
            ->count();

        $this->assign('gqcus_wnum', $gqcus_wnum);
        $this->assign('gqcus_mnum', $gqcus_mnum);
        $this->assign('gqcus_znum', $gqcus_znum);

        // 补贴小店
        $fmeb_num = Db::table('dp_member_authenmessenger')->alias('a')->where('b.ambassador',1)->where('a.status',2);
        // 旗舰店
        $ykmeb_num = Db::table('dp_member_authenmessenger')->alias('a')->where('b.ambassador',2)->where('a.status',2);
        // 合伙人
        $jkmeb_num = Db::table('dp_member_authenmessenger')->alias('a')->where('b.ambassador',4)->where('a.status',2);
        // 联合运营商
        $zzmeb_num = Db::table('dp_member_authenmessenger')->alias('a')->whereIn('b.ambassador','3,5')->where('a.status',2);

        if($role == 13){
            $fmeb_num = $fmeb_num->whereIn('b.sig_id',$ids);
            $ykmeb_num = $ykmeb_num->whereIn('b.sig_id',$ids);
            $jkmeb_num = $jkmeb_num->whereIn('b.sig_id',$ids);
            $zzmeb_num = $zzmeb_num->whereIn('b.sig_id',$ids);
        }
        if($role == 14){
            $fmeb_num = $fmeb_num->where('b.sig_id',$uid);
            $ykmeb_num = $ykmeb_num->where('b.sig_id',$uid);
            $jkmeb_num = $jkmeb_num->where('b.sig_id',$uid);
            $zzmeb_num = $zzmeb_num->where('b.sig_id',$uid);
        }
        $fmeb_num = $fmeb_num
            ->join('dp_member_user b','b.id = a.uid','left')
            ->count();
        $ykmeb_num = $ykmeb_num
            ->join('dp_member_user b','b.id = a.uid','left')
            ->count();
        $jkmeb_num = $jkmeb_num
            ->join('dp_member_user b','b.id = a.uid','left')
            ->count();
        $zzmeb_num = $zzmeb_num
            ->join('dp_member_user b','b.id = a.uid','left')
            ->count();

        $this->assign('fmeb_num', $fmeb_num);
        $this->assign('ykmeb_num', $ykmeb_num);
        $this->assign('jkmeb_num', $jkmeb_num);
        $this->assign('zzmeb_num', $zzmeb_num);

        $zcus_num = $fmeb_num + $ykmeb_num + $jkmeb_num + $zzmeb_num;
        if($zcus_num != 0){
            $cl_ratio = (float)$fmeb_num / (float)$zcus_num * 100;
            $cl_ratio = sprintf('%.2f', $cl_ratio);
            $bl_ratio = (float)$ykmeb_num / (float)$zcus_num * 100;
            $bl_ratio = sprintf('%.2f', $bl_ratio);
            $al_ratio = (float)$jkmeb_num / (float)$zcus_num * 100;
            $al_ratio = sprintf('%.2f', $al_ratio);
            $ka_ratio = (float)$zzmeb_num / (float)$zcus_num * 100;
            $ka_ratio = sprintf('%.2f', $ka_ratio);
        } else {
            $cl_ratio = 0;
            $bl_ratio = 0;
            $al_ratio = 0;
            $ka_ratio = 0;
        }

        $this->assign('cl_ratio', $cl_ratio);
        $this->assign('bl_ratio', $bl_ratio);
        $this->assign('al_ratio', $al_ratio);
        $this->assign('ka_ratio', $ka_ratio);


        // C类客户
        $ccus_num = Db::table('dp_member_authenmessenger')->alias('a')->where('b.ambassador','<>',0)->where('channel_label',2);
        // B类客户
        $bcus_num = Db::table('dp_member_authenmessenger')->alias('a')->where('b.ambassador','<>',0)->where('channel_label',3);
        // A类客户
        $acus_num = Db::table('dp_member_authenmessenger')->alias('a')->where('b.ambassador','<>',0)->where('channel_label',4);
        // KA类客户
        $kacus_num = Db::table('dp_member_authenmessenger')->alias('a')->where('b.ambassador','<>',0)->where('channel_label',5);

        if($role == 13){
            $ccus_num = $ccus_num->whereIn('b.sig_id',$ids);
            $bcus_num = $bcus_num->whereIn('b.sig_id',$ids);
            $acus_num = $acus_num->whereIn('b.sig_id',$ids);
            $kacus_num = $kacus_num->whereIn('b.sig_id',$ids);
        }
        if($role == 14){
            $ccus_num = $ccus_num->where('b.sig_id',$uid);
            $bcus_num = $bcus_num->where('b.sig_id',$uid);
            $acus_num = $acus_num->where('b.sig_id',$uid);
            $kacus_num = $kacus_num->where('b.sig_id',$uid);
        }
        $ccus_num = $ccus_num
            ->join('dp_member_user b','b.id = a.uid','left')
            ->count();
        $bcus_num = $bcus_num
            ->join('dp_member_user b','b.id = a.uid','left')
            ->count();
        $acus_num = $acus_num
            ->join('dp_member_user b','b.id = a.uid','left')
            ->count();
        $kacus_num = $kacus_num
            ->join('dp_member_user b','b.id = a.uid','left')
            ->count();

        $this->assign('ccus_num', $ccus_num);
        $this->assign('bcus_num', $bcus_num);
        $this->assign('acus_num', $acus_num);
        $this->assign('kacus_num', $kacus_num);

        // 本周成交伙伴金额
        $where['a.totalmoney'] = array('in','399,5980,59800');
        $where['a.pay_type'] = array('in','xxhy');
        $sub_wamount = Db::table('dp_order_bank')->alias('a')->whereBetween('a.pay_time',$week1.','.$week2)
            ->where(function($query) use($where){
                $query->whereOr('a.totalmoney','in','399,5980,59800')
                    ->whereOr('a.pay_type','xxhy');
            })
            ->where('a.pay_status',1)->where('b.ambassador','<>',0);
        // 本月成交伙伴金额
        $sub_mamount = Db::table('dp_order_bank')->alias('a')->whereBetween('a.pay_time',$month1.','.$month2)
            ->where(function($query) use($where){
                $query->whereOr('a.totalmoney','in','399,5980,59800')
                    ->whereOr('a.pay_type','xxhy');
            })
            ->where('a.pay_status',1)->where('b.ambassador','<>',0);
        // 总成交伙伴金额
        $sub_zamount = Db::table('dp_order_bank')->alias('a')
            ->where(function($query) use($where){
                $query->whereOr('a.totalmoney','in','399,5980,59800')
                    ->whereOr('a.pay_type','xxhy');
            })
            ->where('a.pay_status',1)->where('b.ambassador','<>',0);

        if($role == 13){
            $sub_wamount = $sub_wamount->whereIn('b.sig_id',$ids);
            $sub_mamount = $sub_mamount->whereIn('b.sig_id',$ids);
            $sub_zamount = $sub_zamount->whereIn('b.sig_id',$ids);
        }
        if($role == 14){
            $sub_wamount = $sub_wamount->where('b.sig_id',$uid);
            $sub_mamount = $sub_mamount->where('b.sig_id',$uid);
            $sub_zamount = $sub_zamount->where('b.sig_id',$uid);
        }
        $sub_wamount = $sub_wamount->join('dp_member_user b','a.uid = b.id','left')
            ->join('dp_member_authenmessenger c','b.id = c.uid','left')
            ->sum('a.totalmoney');
        $sub_mamount = $sub_mamount->join('dp_member_user b','a.uid = b.id','left')
            ->join('dp_member_authenmessenger c','b.id = c.uid','left')
            ->sum('a.totalmoney');
        $sub_zamount = $sub_zamount->join('dp_member_user b','a.uid = b.id','left')
            ->join('dp_member_authenmessenger c','b.id = c.uid','left')
            ->sum('a.totalmoney');

        $sub_wamount = sprintf("%.2f",$sub_wamount);
        $sub_mamount = sprintf("%.2f",$sub_mamount);
        $sub_zamount = sprintf("%.2f",$sub_zamount);

        $this->assign('sub_wamount', $sub_wamount);
        $this->assign('sub_mamount', $sub_mamount);
        $this->assign('sub_zamount', $sub_zamount);

        // 本周伙伴提现金额
        $sub_wfee = Db::table('dp_member_withdrawalen')->alias('a')->where('b.ambassador','<>',0)
            ->where('a.remind_status',2)
            ->whereTime('a.create_time', 'week');
        // 本月伙伴提现金额
        $sub_mfee = Db::table('dp_member_withdrawalen')->alias('a')->where('b.ambassador','<>',0)
            ->where('a.remind_status',2)
            ->whereTime('a.create_time', 'month');
        // 总伙伴提现金额
        $sub_zfee = Db::table('dp_member_withdrawalen')->alias('a')->where('b.ambassador','<>',0)
            ->where('a.remind_status',2);
        if($role == 13){
            $sub_wfee = $sub_wfee->whereIn('b.sig_id',$ids);
            $sub_mfee = $sub_mfee->whereIn('b.sig_id',$ids);
            $sub_zfee = $sub_zfee->whereIn('b.sig_id',$ids);
        }
        if($role == 14){
            $sub_wfee = $sub_wfee->where('b.sig_id',$uid);
            $sub_mfee = $sub_mfee->where('b.sig_id',$uid);
            $sub_zfee = $sub_zfee->where('b.sig_id',$uid);
        }
        $sub_wfee = $sub_wfee->join('dp_member_user b','a.uid = b.id','left')
            ->sum('a.amount');
        $sub_mfee = $sub_mfee->join('dp_member_user b','a.uid = b.id','left')
            ->sum('a.amount');
        $sub_zfee = $sub_zfee->join('dp_member_user b','a.uid = b.id','left')
            ->sum('a.amount');

        $sub_wfee = sprintf("%.2f",$sub_wfee);
        $sub_mfee = sprintf("%.2f",$sub_mfee);
        $sub_zfee = sprintf("%.2f",$sub_zfee);

        $this->assign('sub_wfee', $sub_wfee);
        $this->assign('sub_mfee', $sub_mfee);
        $this->assign('sub_zfee', $sub_zfee);

        // 伙伴个人收益总金额
        $gmoney = Db::table('dp_member_quity')->alias('a')->whereNotIn('a.type','5,10,13')
            ->where('b.ambassador','<>',0);
        if($role == 13){
            $gmoney = $gmoney->whereIn('b.sig_id',$ids);
            $gmoney = $gmoney->whereIn('b.sig_id',$ids);
            $gmoney = $gmoney->whereIn('b.sig_id',$ids);
        }
        if($role == 14){
            $gmoney = $gmoney->where('b.sig_id',$uid);
            $gmoney = $gmoney->where('b.sig_id',$uid);
            $gmoney = $gmoney->where('b.sig_id',$uid);
        }
        $gmoney = $gmoney->join('dp_member_user b','a.uid = b.id','left')->sum('a.num');
        $gmoney = sprintf("%.2f",$gmoney);
        $this->assign('gmoney', $gmoney);

        // 总提现金额
        $tzmoney = Db::table('dp_member_withdrawalen')->alias('a')
//            ->where('a.remind_status',1)
            ->where('b.ambassador','<>',0);
        if($role == 13){
            $tzmoney = $tzmoney->whereIn('b.sig_id',$ids);
            $tzmoney = $tzmoney->whereIn('b.sig_id',$ids);
            $tzmoney = $tzmoney->whereIn('b.sig_id',$ids);
        }
        if($role == 14){
            $tzmoney = $tzmoney->where('b.sig_id',$uid);
            $tzmoney = $tzmoney->where('b.sig_id',$uid);
            $tzmoney = $tzmoney->where('b.sig_id',$uid);
        }
        $tzmoney = $tzmoney->join('dp_member_user b','a.uid = b.id','left')->sum('a.amount');
        $tzmoney = sprintf("%.2f",$tzmoney);
        $this->assign('tzmoney', $tzmoney);

        // 余额/未提现金额
//        $yam_money = $gmoney - $tzmoney - $sub_zfee;
        $yam_money = $gmoney - $tzmoney;

        $this->assign('yam_money', $yam_money);
        if($tzmoney != 0){
            $circle_progress = sprintf('%.2f', (float)($sub_zfee / $tzmoney));
        } else{
            $circle_progress = 0;
        }
        $this->assign('circle_progress', $circle_progress);

        // 未跟进客户
        $idss = Db::table('dp_member_followup')->alias('a')->field('a.id,a.types,a.update_time')
            ->where('a.delete_time',0)->where('a.status',0)->where('s.ambassador','<>',0);

        if($role == 13){
            $idss = $idss->whereIn('a.uid',$ids);
        }
        if($role == 14){
            $idss = $idss->where('a.uid',$uid);
        }
        $idss = $idss->join('dp_member_user s','a.sub_id = s.id','left')
            ->join('dp_member_authenmessenger b','a.sub_id = b.uid','left')
            ->order('a.update_time','DESC')->group('s.id')->select();

        $wf_num = 0;
        foreach($idss as $w){
            if($w['update_time'] < $weekTime && $tweekTime < $w['update_time'] && $w['types'] == 2){
                $wf_num = $wf_num + 1;
            }
        }
        $tf_num = 0;
        foreach($idss as $t){
            if($t['update_time'] < $tweekTime && $mweekTime < $t['update_time'] && $t['types'] == 2){
                $tf_num = $tf_num + 1;
            }
        }
        $mf_num = 0;
        foreach($idss as $m){
            if($m['update_time'] < $mweekTime && $jweekTime < $m['update_time'] && $m['types'] == 2){
                $mf_num = $mf_num + 1;
            }
        }
        $jf_num = 0;
        foreach($idss as $j){
            if($j['update_time'] < $jweekTime && $j['types'] == 2){
                $jf_num = $jf_num + 1;
            }
        }
        $this->assign('wfollowup', $wf_num);
        $this->assign('tfollowup', $tf_num);
        $this->assign('mfollowup', $mf_num);
        $this->assign('jfollowup', $jf_num);

        // 渠道详情列表
        $list = UserModel::field('id,parent_id,username,nickname,create_time')->where('role',14);
        if($role == 13){
            $list = $list->where('parent_id',$uid);
        }
        $list = $list->order('create_time','DESC')->paginate(10,false,['query'=>request()->param()]);
        $list1 = $list->toArray();
        foreach ($list1['data'] as $k => $v){

            // 补贴小店
            $v['btd_num'] = Db::table('dp_member_authenmessenger')->alias('a')->where('b.sig_id',$v['id'])
                ->where('b.ambassador',1)
                ->join('dp_member_user b','b.id = a.uid','left')->count();
            // 旗舰店
            $v['qjd_num'] = Db::table('dp_member_authenmessenger')->alias('a')->where('b.sig_id',$v['id'])
                ->where('b.ambassador',2)
                ->join('dp_member_user b','b.id = a.uid','left')->count();
            // 合伙人
            $v['hhr_num'] = Db::table('dp_member_authenmessenger')->alias('a')->where('b.sig_id',$v['id'])
                ->where('b.ambassador',4)
                ->join('dp_member_user b','b.id = a.uid','left')->count();
            // 联合运营商
            $v['yys_num'] = Db::table('dp_member_authenmessenger')->alias('a')->where('b.sig_id',$v['id'])
                ->whereIn('b.ambassador','3,5')
                ->join('dp_member_user b','b.id = a.uid','left')->count();
            // 合作过期伙伴
            $v['gqd_num'] = Db::table('dp_member_authenmessenger')->alias('a')->where('b.sig_id',$v['id'])
                ->where('a.end_time','<=',time())
                ->join('dp_member_user b','b.id = a.uid','left')->count();


            // 查询客户
            $v['cx_znum'] = Db::table('dp_member_company')->alias('a')->where('b.sig_id',$v['id'])
                ->where('a.sub_num','<>',0)
                ->join('dp_member_user b','b.id = a.uid','left')
                ->join('dp_member_subsidies c','a.id = c.uid','left')
                ->count();
            // 合作客户
            $v['hz_num'] = Db::table('dp_member_company')->alias('a')->where('b.sig_id',$v['id'])
                ->whereNotIn('a.cooperation_type','4,5')->where('a.sub_num','<>',0)
                ->join('dp_member_user b','b.id = a.uid','left')
                ->join('dp_member_subsidies c','a.id = c.uid','left')
                ->count();

            // 伙伴成交金额
            $v['sub_zamount'] = Db::table('dp_order_bank')->alias('a')->where('b.sig_id',$v['id'])
                ->whereIn('a.totalmoney','399,5980,59800')->where('a.pay_status',1)
                ->join('dp_member_user b','a.uid = b.id','left')->sum('a.totalmoney');
            $v['sub_zamount'] = sprintf("%.2f",$v['sub_zamount']);


            // 伙伴提现金额
            $v['sub_zfee'] = Db::table('dp_member_withdrawalen')->alias('a')->where('b.sig_id',$v['id'])
                ->where('b.ambassador','<>',0)->where('a.remind_status',2)
                ->join('dp_member_user b','a.uid = b.id','left')->sum('a.amount');
            $v['sub_zfee'] = sprintf("%.2f",$v['sub_zfee']);

            // 伙伴收益金额
            $v['gmoney'] = Db::table('dp_member_quity')->alias('a')->where('b.sig_id',$uid)
                ->whereNotIn('a.type','5,10,13')
                ->join('dp_member_user b','a.uid = b.id','left')->sum('a.num');
            $v['gmoney'] = sprintf("%.2f",$v['gmoney']);

            $v['tzmoney'] = Db::table('dp_member_withdrawalen')->alias('a')->where('b.sig_id',$uid)
                ->where('b.ambassador','<>',0)
                ->join('dp_member_user b','a.uid = b.id','left')->sum('a.amount');
            $v['tzmoney'] = sprintf("%.2f",$v['tzmoney']);

            $v['yam_money'] = $v['gmoney'] - $v['tzmoney'];
            $v['yam_money'] = sprintf("%.2f",$v['yam_money']);

            $data = $v;
            $list->offsetSet($k,$data);
        }
        $data = array(
            'list' => $list,
            'page' => $list->render(),
            'count' => $list->total(),
        );
        $this->assign($data);
        $listtoarray = $list->toArray();
        $this->assign('listtoarray',$listtoarray);

        return $this->fetch();
    }

    // 获取伙伴漏斗数据
    public function getParFunnel() {
        if($this->request->isAjax()) {
            //接收用户id
            $uid = $this->request->post('uid');
            //设置查询数据
            $map   = $this->getMap();
            $map[] = ['id','=',$uid];
            //获取用户角色
            $role = UserModel::getValues($map,'role');
            $this->assign('role',$role);
            //获取下级id合集
            $ids = [];
            if($role == 13){
                $map = []; $map[] = ['parent_id','=',$uid]; $map[] = ['role','=',14];
                $ids = UserModel::getUserIdArray($map,'id');
            }
            //时间戳参数
            $serveTime = $this->request->post('serveTime');

            // 补贴小店
            $btd_num = Authenmessenger::where('a.status',2)->alias('a')->where('b.ambassador',1);
            // 旗舰店
            $qjd_num = Authenmessenger::where('a.status',2)->alias('a')->where('b.ambassador',2);
            // 合伙人
            $hhr_num = Authenmessenger::where('a.status',2)->alias('a')->where('b.ambassador',4);
            // 联合运营商
            $yys_num = Authenmessenger::where('a.status',2)->alias('a')->whereIn('b.ambassador','3,5');

            if($role == 13){
                $btd_num = $btd_num->whereIn('b.sig_id',$ids);
                $qjd_num = $qjd_num->whereIn('b.sig_id',$ids);
                $hhr_num = $hhr_num->whereIn('b.sig_id',$ids);
                $yys_num = $yys_num->whereIn('b.sig_id',$ids);
            }
            if($role == 14){
                $btd_num = $btd_num->where('b.sig_id',$uid);
                $qjd_num = $qjd_num->where('b.sig_id',$uid);
                $hhr_num = $hhr_num->where('b.sig_id',$uid);
                $yys_num = $yys_num->where('b.sig_id',$uid);
            }

            $season = ceil(date('n') /3); //获取月份的季度
            $seasonStart = date('Y-m-01',mktime(0,0,0,($season - 1) *3 +1,1,date('Y')));
            $seasonStart = strtotime($seasonStart);
            $seasonEnd = date('Y-m-t',mktime(0,0,0,$season * 3,1,date('Y')));
            $seasonEnd = strtotime($seasonEnd);
            $lastSeasonStart = date('Y-m-01',mktime(0,0,0,($season - 2) * 3 +1,1,date('Y')));
            $lastSeasonStart = strtotime($lastSeasonStart);
            $lastSeasonEnd = date('Y-m-t',mktime(0,0,0,($season - 1) * 3,1,date('Y')));
            $lastSeasonEnd = strtotime($lastSeasonEnd);

            if($serveTime == 1){
                $btd_num = $btd_num->whereTime('a.create_time', 'week');
                $qjd_num = $qjd_num->whereTime('a.create_time', 'week');
                $hhr_num = $hhr_num->whereTime('a.create_time', 'week');
                $yys_num = $yys_num->whereTime('a.create_time', 'week');
            } elseif($serveTime == 2){
                $btd_num = $btd_num->whereTime('a.create_time', 'month');
                $qjd_num = $qjd_num->whereTime('a.create_time', 'month');
                $hhr_num = $hhr_num->whereTime('a.create_time', 'month');
                $yys_num = $yys_num->whereTime('a.create_time', 'month');
            } elseif($serveTime == 3){
                $btd_num = $btd_num->whereTime('a.create_time','between',[$seasonStart,$seasonEnd]);
                $qjd_num = $qjd_num->whereTime('a.create_time','between',[$seasonStart,$seasonEnd]);
                $hhr_num = $hhr_num->whereTime('a.create_time','between',[$seasonStart,$seasonEnd]);
                $yys_num = $yys_num->whereTime('a.create_time','between',[$seasonStart,$seasonEnd]);
            } elseif($serveTime == 4){
                $btd_num = $btd_num->whereTime('a.create_time', 'year');
                $qjd_num = $qjd_num->whereTime('a.create_time', 'year');
                $hhr_num = $hhr_num->whereTime('a.create_time', 'year');
                $yys_num = $yys_num->whereTime('a.create_time', 'year');
            } elseif($serveTime == 5){
                $btd_num = $btd_num->whereTime('a.create_time', 'yesterday');
                $qjd_num = $qjd_num->whereTime('a.create_time', 'yesterday');
                $hhr_num = $hhr_num->whereTime('a.create_time', 'yesterday');
                $yys_num = $yys_num->whereTime('a.create_time', 'yesterday');
            } elseif($serveTime == 6){
                $btd_num = $btd_num->whereTime('a.create_time', 'last week');
                $qjd_num = $qjd_num->whereTime('a.create_time', 'last week');
                $hhr_num = $hhr_num->whereTime('a.create_time', 'last week');
                $yys_num = $yys_num->whereTime('a.create_time', 'last week');
            } elseif($serveTime == 7){
                $btd_num = $btd_num->whereTime('a.create_time', 'last month');
                $qjd_num = $qjd_num->whereTime('a.create_time', 'last month');
                $hhr_num = $hhr_num->whereTime('a.create_time', 'last month');
                $yys_num = $yys_num->whereTime('a.create_time', 'last month');
            } elseif($serveTime == 8){
                $btd_num = $btd_num->whereTime('a.create_time','between',[$lastSeasonStart,$lastSeasonEnd]);
                $qjd_num = $qjd_num->whereTime('a.create_time','between',[$lastSeasonStart,$lastSeasonEnd]);
                $hhr_num = $hhr_num->whereTime('a.create_time','between',[$lastSeasonStart,$lastSeasonEnd]);
                $yys_num = $yys_num->whereTime('a.create_time','between',[$lastSeasonStart,$lastSeasonEnd]);
            } elseif($serveTime == 9){
                $btd_num = $btd_num->whereTime('a.create_time', 'last year');
                $qjd_num = $qjd_num->whereTime('a.create_time', 'last year');
                $hhr_num = $hhr_num->whereTime('a.create_time', 'last year');
                $yys_num = $yys_num->whereTime('a.create_time', 'last year');
            }
            $btd_num = $btd_num
                ->join('dp_member_user b','b.id = a.uid','left')
                ->count();
            $qjd_num = $qjd_num
                ->join('dp_member_user b','b.id = a.uid','left')
                ->count();
            $hhr_num = $hhr_num
                ->join('dp_member_user b','b.id = a.uid','left')
                ->count();
            $yys_num = $yys_num
                ->join('dp_member_user b','b.id = a.uid','left')
                ->count();

            $zcus_num = $btd_num + $qjd_num + $hhr_num + $yys_num;
            if($zcus_num != 0){
                $cl_ratio = (float)$btd_num / (float)$zcus_num * 100;
                $cl_ratio = sprintf('%.2f', $cl_ratio);
                $bl_ratio = (float)$qjd_num / (float)$zcus_num * 100;
                $bl_ratio = sprintf('%.2f', $bl_ratio);
                $al_ratio = (float)$hhr_num / (float)$zcus_num * 100;
                $al_ratio = sprintf('%.2f', $al_ratio);
                $ka_ratio = (float)$yys_num / (float)$zcus_num * 100;
                $ka_ratio = sprintf('%.2f', $ka_ratio);
            } else {
                $cl_ratio = 0;
                $bl_ratio = 0;
                $al_ratio = 0;
                $ka_ratio = 0;
            }

            $data = ['code'=>1,'cl_ratio'=>$cl_ratio,'bl_ratio'=>$bl_ratio,'al_ratio'=>$al_ratio,'ka_ratio'=>$ka_ratio,
                'btd_num'=>$btd_num,'qjd_num'=>$qjd_num,'hhr_num'=>$hhr_num,'yys_num'=>$yys_num,'zcus_num'=>$zcus_num];

            return $data;
        }
    }

    // 合作伙伴数据列表
    public function parCusList() {
        //接收用户id
        $uid = input('uid');
        $this->assign('uid',$uid);
        //设置查询条件
        $map   = $this->getMap();
        $map[] = ['id','=',$uid];
        //查出当前用户角色
        $role = UserModel::getValues($map,'role');
        $this->assign('role',$role);
        //获取下级id合集
        $ids = [];
        if($role == 13){
            $map = []; $map[] = ['parent_id','=',$uid]; $map[] = ['role','=',14];
            $ids = UserModel::getUserIdArray($map,'id');
        }

        $keyword = ['name'=>'','selectTime'=>'','endTime'=>'', 'ambassador'=>'', 'channel_label'=>'', 'serveTime'=>''];

        //接收参数合集
        $key  = input();
        $keyword = array_merge($keyword,$key);
        $this->assign('keyword',$keyword);
        //时间条件字段
        $time_field = ''; $time_cycle = '';
        //查询需要的字段
        $field = 'a.uid,a.name,a.mobile,a.create_time,a.end_time,
            b.sig_id,b.parent_id,b.username,b.ambassador,s.name sname,p.name pname';
        //查询条件合集
        $maps = $this->getMap();
        if($role == 13){
            $maps[] = ['b.sig_id','in',$ids];
        }
        if($role == 14){
            $maps[] = ['b.sig_id','=',$uid];
        }
        if($keyword['name']){
            $maps[] = ['a.name','like','%'.$keyword['name'].'%'];
        }
        if($keyword['selectTime']){
            $maps[] = ['b.ambassador','<>','0'];
            if($keyword['selectTime'] == 2 || $keyword['selectTime'] == 3){
                $time_field = 'a.create_time';
                if($keyword['selectTime'] == 2) $time_cycle = 'week';
                else $time_cycle = 'month';
            }
        }
        if($keyword['endTime']){
            if($keyword['endTime'] == 2 || $keyword['endTime'] == 3){
                $time_field = 'a.end_time';
                if($keyword['endTime'] == 2) $time_cycle = 'week';
                else $time_cycle = 'month';
            } elseif($keyword['endTime'] == 4){
                $maps[] = ['a.end_time','<=',time()];
            }
        }
        if($keyword['ambassador']){
            if($keyword['ambassador'] == 1){
                $maps[] = ['b.ambassador','=','1'];
            } elseif($keyword['ambassador'] == 2){
                $maps[] = ['b.ambassador','=','2'];
            } elseif($keyword['ambassador'] == 3){
                $maps[] = ['b.ambassador','in','3,5'];
            } elseif($keyword['ambassador'] == 4){
                $maps[] = ['b.ambassador','=',4];
            }
        }
        if($keyword['channel_label']){
            $maps[] = ['a.channel_label','=',$keyword['channel_label']];
        }
        if($keyword['serveTime']){
            //获取月份的季度
            $season = ceil(date('n') /3);
            $seasonStart = date('Y-m-01',mktime(0,0,0,($season - 1) *3 +1,1,date('Y')));// 本季开始
            $seasonStart = strtotime($seasonStart);
            $seasonEnd = date('Y-m-t',mktime(0,0,0,$season * 3,1,date('Y')));// 本季结束
            $seasonEnd = strtotime($seasonEnd);
            $lastSeasonStart = date('Y-m-01',mktime(0,0,0,($season - 2) * 3 +1,1,date('Y')));// 上季开始
            $lastSeasonStart = strtotime($lastSeasonStart);
            $lastSeasonEnd = date('Y-m-t',mktime(0,0,0,($season - 1) * 3,1,date('Y')));// 上季结束
            $lastSeasonEnd = strtotime($lastSeasonEnd);
            if($keyword['serveTime'] == 1){
                $time_field = 'a.create_time';
                $time_cycle = 'week';
            } elseif($keyword['serveTime'] == 2){
                $time_field = 'a.create_time';
                $time_cycle = 'month';
            } elseif($keyword['serveTime'] == 3){
                $maps[] = ['a.create_time','between time',[$seasonStart,$seasonEnd]];
            } elseif($keyword['serveTime'] == 4){
                $time_field = 'a.create_time';
                $time_cycle = 'year';
            } elseif($keyword['serveTime'] == 5){
                $time_field = 'a.create_time';
                $time_cycle = 'yesterday';
            } elseif($keyword['serveTime'] == 6){
                $time_field = 'a.create_time';
                $time_cycle = 'last week';
            } elseif($keyword['serveTime'] == 7){
                $time_field = 'a.create_time';
                $time_cycle = 'last month';
            } elseif($keyword['serveTime'] == 8){
                $maps[] = ['a.create_time','between time',[$lastSeasonStart,$lastSeasonEnd]];
            } elseif($keyword['serveTime'] == 9){
                $time_field = 'a.create_time';
                $time_cycle = 'last year';
            }
        }
        $maps[] = ['a.status','=','2'];

        //获取列表数据
        $list = Authenmessenger::getList($field,$maps,$time_field,$time_cycle);

        $data = array(
            'list' => $list,
            'page' => $list->render(),
            'count' => $list->total(),
        );
        $this->assign($data);
        $listtoarray = $list->toArray();
        $this->assign('listtoarray',$listtoarray);

        return $this->fetch();
    }

    // 合作伙伴成交金额列表
    public function parDealList() {
        //接收用户id
        $uid = input('uid');
        $this->assign('uid',$uid);
        //设置查询条件
        $map   = $this->getMap();
        $map[] = ['id','=',$uid];
        //查出当前用户角色
        $role = UserModel::getValues($map,'role');
        $this->assign('role',$role);
        //获取下级id合集
        $ids = [];
        if($role == 13){
            $map = []; $map[] = ['parent_id','=',$uid]; $map[] = ['role','=',14];
            $ids = UserModel::getUserIdArray($map,'id');
        }

        $keyword = ['name'=>'', 'selectTime'=>''];

        //接收参数合集
        $key  = input();
        $keyword = array_merge($keyword,$key);
        $this->assign('keyword',$keyword);
        //时间条件字段
        $time_field = ''; $time_cycle = '';
        //查询需要的字段
        $field = 'a.uid,a.totalmoney,a.pay_time,b.sig_id,b.parent_id,b.username,b.ambassador,
            c.name,c.mobile,c.create_time,s.name sname,p.name pname';
        //查询条件合集
        $maps = $this->getMap();
        if($role == 13){
            $maps[] = ['b.sig_id','in',$ids];
        }
        if($role == 14){
            $maps[] = ['b.sig_id','=',$uid];
        }
        if($keyword['name']){
            $maps[] = ['c.name','like','%'.$keyword['name'].'%'];
        }
        if($keyword['selectTime']){

            $week1 = mktime(0,0,0,date('m'),date('d')-date('N')+1,date('y'));
            $week2 = mktime(23,59,59,date('m'),date('d')-date('N')+7,date('Y'));
            $week1 = date("YmdHis",$week1);
            $week2 = date("YmdHis",$week2);

            $month1 = mktime(0,0,0,date('m'),1,date('Y'));
            $month2 = mktime(23,59,59,date('m'),date('t'),date('Y'));
            $month1 = date("YmdHis",$month1);
            $month2 = date("YmdHis",$month2);

            if($keyword['selectTime'] == 2 || $keyword['selectTime'] == 3){
                if($keyword['selectTime'] == 2) $maps[] = ['a.pay_time','between',$week1.','.$week2];
                else $maps[] = ['a.pay_time','between',$month1.','.$month2];
            }
        }
        $maps[] = ['a.pay_status','=','1'];
        $maps[] = ['b.ambassador','<>','0'];

        // 本周成交伙伴金额
        $list = Orderbank::getLists($field,$maps,$time_field,$time_cycle);

        $list1 = $list->toArray();
        foreach ($list1['data'] as $k => $v){
            $t = str_split($v['pay_time'],2);        //每两字节长度做一个数组元素
            $time = $t[0].$t[1]."-".$t[2]."-".$t[3]." ".$t[4].":".$t[5].":".$t[6];
            $v['pay_time'] = $time;
            $data = $v;
            $list->offsetSet($k,$data);
        }

        $data = array(
            'list' => $list,
            'page' => $list->render(),
            'count' => $list->total(),
        );
        $this->assign($data);
        $listtoarray = $list->toArray();
        $this->assign('listtoarray',$listtoarray);

        return $this->fetch();
    }

    // 合作伙伴成交金额列表
    public function parWenList() {
        //接收用户id
        $uid = input('uid');
        $this->assign('uid',$uid);
        //设置查询条件
        $map   = $this->getMap();
        $map[] = ['id','=',$uid];
        //查出当前用户角色
        $role = UserModel::getValues($map,'role');
        $this->assign('role',$role);
        //获取下级id合集
        $ids = [];
        if($role == 13){
            $map = []; $map[] = ['parent_id','=',$uid]; $map[] = ['role','=',14];
            $ids = UserModel::getUserIdArray($map,'id');
        }

        $keyword = ['name'=>'','remind_status'=>'', 'selectTime'=>''];

        //接收参数合集
        $key  = input();
        $keyword = array_merge($keyword,$key);
        $this->assign('keyword',$keyword);
        //时间条件字段
        $time_field = ''; $time_cycle = '';
        //查询需要的字段
        $field = 'a.uid,a.bank,a.account,a.company,a.amount,a.create_time,a.damount_time,a.remind_status,
                b.sig_id,b.parent_id,b.username,b.ambassador,c.name,c.mobile,s.name sname,p.name pname';
        //查询条件合集
        $maps = $this->getMap();
        if($role == 13){
            $maps[] = ['b.sig_id','in',$ids];
        }
        if($role == 14){
            $maps[] = ['b.sig_id','=',$uid];
        }
        if($keyword['name']){
            $maps[] = ['c.name','like','%'.$keyword['name'].'%'];
        }
        if($keyword['remind_status']){
            $maps[] = ['a.remind_status','=',$keyword['remind_status']];
        }
        if($keyword['selectTime']){
            if($keyword['selectTime'] == 2 || $keyword['selectTime'] == 3){
                $time_field = 'a.create_time';
                if($keyword['selectTime'] == 2) $time_cycle = 'week';
                else $time_cycle = 'month';
            }
        }


        $list = Withdrawalen::getList($field,$maps,$time_field,$time_cycle);

        $data = array(
            'list' => $list,
            'page' => $list->render(),
            'count' => $list->total(),
        );
        $this->assign($data);
        $listtoarray = $list->toArray();
        $this->assign('listtoarray',$listtoarray);

        return $this->fetch();
    }

    // 合作伙伴成交金额列表
    public function parComList() {
        //接收用户id
        $uid = input('uid');
        $this->assign('uid',$uid);
        //设置查询条件
        $map   = $this->getMap();
        $map[] = ['id','=',$uid];
        //查出当前用户角色
        $role = UserModel::getValues($map,'role');
        $this->assign('role',$role);
        //获取下级id合集
        $ids = [];
        if($role == 13){
            $map = []; $map[] = ['parent_id','=',$uid]; $map[] = ['role','=',14];
            $ids = UserModel::getUserIdArray($map,'id');
        }

        $list = Db::table('dp_member_company')->alias('a')
            ->field('a.id,a.uid,a.company,a.cooperation_type,a.cooperation_time,a.create_time,
                b.sig_id,b.parent_id,b.ambassador,c.name,c.mobile')
            ->where('a.sub_num','<>',0);
        if($role == 13){
            $list = $list->whereIn('b.sig_id',$ids);
        }
        if($role == 14){
            $list = $list->where('b.sig_id',$uid);
        }
        $company = input('company');
        $this->assign('company',$company);
        if(isset($company) && $company != ''){
            $list = $list->whereLike('a.company','%'.$company.'%');
        }
        $type = input('type');
        $this->assign('type',$type);
        if(isset($type) && $type != ''){
            if($type == 1){
                $list = $list->whereNotIn('a.cooperation_type','4,5');
            }
        }
        $list = $list
            ->join('dp_member_user b','a.uid = b.id','left')
            ->join('dp_member_subsidies c','a.id = c.cid','left')
            ->order('a.create_time','DESC')->group('a.id')
            ->paginate(10,false,['query'=>request()->param()]);

        $list1 = $list->toArray();
        foreach ($list1['data'] as $k => $v){
            $v['pname'] = Db::table('dp_member_authenmessenger')->where('uid',$v['parent_id'])->value('name');
            $v['sname'] = Db::table('dp_member_authenmessenger')->where('uid',$v['sig_id'])->value('name');
            $data = $v;
            $list->offsetSet($k,$data);
        }

        $data = array(
            'list' => $list,
            'page' => $list->render(),
            'count' => $list->total(),
        );
        $this->assign($data);
        $listtoarray = $list->toArray();
        $this->assign('listtoarray',$listtoarray);

        return $this->fetch();
    }

    // 未跟进合作伙伴数据
    public function parFolList() {
        $uid = input('uid');
        $this->assign('uid',$uid);

        $role = Db::table('dp_member_user')->where('id',$uid)->value('role');
        $this->assign('role',$role);

        // 未跟进客户
        $idss = Db::table('dp_member_followup')->alias('a')->field('a.id,a.types,a.update_time')
            ->where('a.delete_time',0)->where('a.status',0)->where('s.ambassador','<>',0);

        if($role == 14){
            $idss = $idss->where('a.uid',$uid);
        }
        if($role == 13){
            $ids = [];
            $rids = Db::table('dp_member_user')->field('id')->where('parent_id',$uid)->where('role',14)->select();
            foreach($rids as $val){
                array_push($ids,$val['id']);
            }
            $ids = implode(',',$ids);
            $idss = $idss->whereIn('a.uid',$ids);
        }
        $name = input('name');
        $this->assign('name',$name);
        if(isset($name) && $name != ''){
            $idss = $idss->whereLike('b.name','%'.$name.'%');
        }
        $idss = $idss->join('dp_member_user s','a.sub_id = s.id','left')
            ->join('dp_member_authenmessenger b','a.sub_id = b.uid','left')
            ->order('a.update_time','DESC')->group('s.id')->select();

        $weekTime = mktime(0,0,0,date('m'),date('d')-7,date('Y'))-1;
        $tweekTime = mktime(0,0,0,date('m'),date('d')-14,date('Y'))-1;
        $mweekTime = mktime(0,0,0,date('m'),date('d')-30,date('Y'))-1;
        $jweekTime = mktime(0,0,0,date('m'),date('d')-50,date('Y'))-1;

        $selectTime = input('selectTime');
        $this->assign('selectTime',$selectTime);

        $ids = [];
        if($selectTime == 1){
            foreach($idss as $v){
                if($v['update_time'] < $weekTime && $tweekTime < $v['update_time'] && $v['types'] == 2){
                    array_push($ids,$v['id']);
                }
            }
        } elseif($selectTime == 2){
            foreach($idss as $v){
                if($v['update_time'] < $tweekTime && $mweekTime < $v['update_time'] && $v['types'] == 2){
                    array_push($ids,$v['id']);
                }
            }
        } elseif($selectTime == 3){
            foreach($idss as $v){
                if($v['update_time'] < $mweekTime && $jweekTime < $v['update_time'] && $v['types'] == 2){
                    array_push($ids,$v['id']);
                }
            }
        } elseif($selectTime == 4){
            foreach($idss as $v){
                if($v['update_time'] < $jweekTime && $v['types'] == 2){
                    array_push($ids,$v['id']);
                }
            }
        }
        $ids = implode(',',$ids);
        $list = Db::table('dp_member_followup')->alias('a')
            ->field('b.*,s.sig_id,s.parent_id,s.username,s.ambassador')
            ->whereIn('a.id',$ids)
            ->join('dp_member_user s','a.sub_id = s.id','left')
            ->join('dp_member_authenmessenger b','s.id = b.uid','left')
            ->order('a.update_time','DESC')->group('s.id')
            ->paginate(10,false,['query'=>request()->param()]);

        $list1 = $list->toArray();
        foreach ($list1['data'] as $k => $v){
            $v['pname'] = Db::table('dp_member_authenmessenger')->where('uid',$v['parent_id'])->value('name');
            $v['sname'] = Db::table('dp_member_authenmessenger')->where('uid',$v['sig_id'])->value('name');
            $data = $v;
            $list->offsetSet($k,$data);
        }

        $data = array(
            'list' => $list,
            'page' => $list->render(),
            'count' => $list->total(),
        );
        $this->assign($data);
        $listtoarray = $list->toArray();
        $this->assign('listtoarray',$listtoarray);

        return $this->fetch();
    }





}