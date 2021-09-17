<?php
// +----------------------------------------------------------------------
// | 博派PHP框架 [ JlmPHP ]
// +----------------------------------------------------------------------
// | 版权所有 2016~2019 博派 [ http://www.zrthink.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://JlmPHP.com
// +----------------------------------------------------------------------

namespace app\member\model;

use think\facade\Session;
use think\Model;
use think\helper\Hash;
use app\member\model\Role as RoleModel;
use think\Db;

/**
 * 后台用户模型
 * @package app\member\model
 */
class User extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $name = 'member_user';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 对密码进行加密
    public function setPasswordAttr($value)
    {
        return Hash::make((string)$value);
    }

    // 获取注册ip
    public function setSignupIpAttr()
    {
        return get_client_ip(1);
    }

    public function getVipAttr($value){
        $vip = [0=>'基础版',1=>'普通版',2=>'至尊版',3=>'招聘客',4=>'拓客版',5=>'会员'];
        return $vip[$value];
    }

//    public function getAmbassadorAttr($value){
//        $ambassador = [0=>'不是',1=>'普通大使',2=>'渠道大使',3=>'联合运营商'];
//        return $ambassador[$value];
//    }

    /**
     * @param $uid
     * @return array|null|\PDOStatement|string|Model
     * 获取单独用户数据
     */
    static function getUser($uid){
        return self::where('id',$uid)->find();
    }

    /**
     * @param $map array(条件)
     * @param $field string(需要的字段)
     * @return mixed
     * 通过查询条件获取单独数据
     */
    static function getValues($map,$field){
        return self::where($map)->value($field);
    }

    static function getBalance(){
        return self::sum('yam_money');
    }

    /**
     * @param $map array(条件)
     * @param $field string(需要的字段)
     * @param $strip int(分页条数)
     * @return \think\Paginator
     * 通过查询条件获取用户列表
     */
    static function getUserList($map,$field,$strip){
        $result = self::field($field)->where($map)->order('create_time','DESC')
                    ->paginate($strip,false,['query'=>request()->param()]);
        return $result;
    }

    /**
     * @param $map
     * @param $field
     * @return array
     * 自设自动 查询条件 查数组
     */
    static function getUserArray($map,$field){
        return self::field($field)->where($map)->select()->toArray();
    }

    /**
     * @param $map
     * @param $field string(单独的字段)
     * @return array
     * 获取id合集
     */
    static function getUserIdArray($map,$field){
        return self::where($map)->column($field);
    }

    /**
     * @param string $field
     * @param string $time
     * @return \think\db\Query
     * 根据时间范围查用户数量
     */
    static function getCount($field = '',$time = ''){
        if($field != '' && $time != '') return self::whereTime($field,$time)->count('id');
        else return self::count('id');
    }

    /**
     * @param $map array(查询条件)
     * @param string $time string(时间范围)
     * @return float|string
     * 根据查询条件查用户数量
     */
    static function getConditionCount($map,$time = ''){
        if($time != '') $result = self::where($map)->whereTime('create_time',$time)->count('id');
        else $result = self::where($map)->count('id');
        return $result;
    }

    /**
     * @param string $start_time
     * @param string $end_time
     * @return \think\db\Query
     * 用户数量
     */
    static function getBetweenCount($start_time = '',$end_time = ''){
        return self::whereBetweenTime('create_time',$start_time,$end_time)->count('id');
    }

    /**
     * @param $ids
     * @param string $time
     * @return \think\db\Query
     * 下级用户数量（分支的总下级）
     */
    static function getAddedNum($ids,$time = ''){
        if($time != '')$result = self::whereIn('id',$ids)->whereTime('create_time',$time)->count('id');
        else $result = self::whereIn('id',$ids)->count('id');
        return $result;
    }

    /**
     * @return \think\db\Query
     * 下级用户付费数量（分支的总下级）
     */
    public static function getPayNum($ids,$time = ''){
        if($time != '') $result = self::alias('a')->whereTime('pay_time',$time)->whereIn('a.id',$ids)
            ->where('b.pay_status',1)->group('b.uid')->leftJoin('dp_order_bank b','a.id = b.uid')->count('a.id');
        else $result = self::alias('a')->where('b.pay_status',1)->whereIn('a.id',$ids)->group('b.uid')
            ->leftJoin('dp_order_bank b','a.id = b.uid')->count('a.id');
        return $result;
    }

    /**
     * @param $ids
     * @param string $vip
     * @return float|string
     * 下级用户会员数量（分支的总下级）
     */
    public static function getVipNum($ids,$vip = ''){
        $result = self::whereIn('id',$ids)->where('vip',$vip)->count('id');
        return $result;
    }

    /**
     * @param $ids
     * @param $role
     * @return float|string
     * 按照角色查下级数量（分支的总下级）
     */
    public static function getSubordRoleNum($ids,$role){
        $result = self::whereIn('id',$ids)->where('role',$role)->count('id');
        return $result;
    }

    /**
     * @param $ids
     * @param string $vip
     * @return float|string
     * 下级用户店铺等级数量（分支的总下级）
     */
    public static function getAmbassadorNum($ids,$vip = ''){
        $result = self::whereIn('id',$ids)->where('ambassador',$vip)->count('id');
        return $result;
    }

    /**
     * @param $uid
     * @return array|\PDOStatement|string|\think\Collection
     * 查出有身份的直属下级id数组
     */
    public static function getSubordinate($uid){
        $result = self::field('id')->where('parent_id',$uid)->where('ambassador','<>',0)->select();
        return $result;
    }



    /**
     * @return array
     * 查出有身份的全部用户
     */
    static function getUsers(){
        $user = self::field('id,parent_id')->where('ambassador','>',0)->select()->toArray();
        return $user;
    }

    /**
     * @param $field
     * @param $map
     * @return array
     * 自设字段与查询条件查数组 连表查询
     */
    static function getPartnerList($field,$map,$order,$strip = 10){
        $user = self::alias('a')->field($field)->where($map)
            ->leftJoin('dp_member_authenmessenger b','a.id = b.uid')
            ->order($order)
            ->paginate($strip,false,['query'=>request()->param()]);
        return $user;
    }

    /**
     * 微信登录
     * @param string $username 用户名
     * @param string $uid 用户id
     * @param bool $rememberme 记住登录
     * @author 博派
     * @return bool|mixed
     */
    public function wx_login($username = '', $uid = '', $rememberme = false)
    {

        $username = trim($username);
        $uid = trim($uid);
        // 用户名登录
        $map['username'] = $username;
        $map['id'] = $uid;
        $map['status'] = 1;
        // 查找用户
        $user = $this::get($map);
        if (!$user) {
            $this->error = '用户不存在或被禁用！';
        } else {
            // 检查是否分配用户组
            if ($user['role'] == 0) {
                $this->error = '禁止访问，原因：未分配角色！';
                return false;
            }
            // 检查是可登录后台
            if (!RoleModel::where(['id' => $user['role'], 'status' => 1])->value('access')) {
                $this->error = '禁止访问，用户所在角色未启用或禁止访问后台！';
                return false;
            }

            $uid = $user['id'];
            // 更新登录信息
            $user['last_login_time'] = request()->time();
            $user['last_login_ip']   = request()->ip(1);
            if ($user->save()) {
                // 自动登录
                return $this->autoLogin($this::get($uid), $rememberme);
            } else {
                // 更新登录信息失败
                $this->error = '登录信息更新失败，请重新登录！';
                return false;
            }
        }
        return false;
    }


    /**
     * 用户登录
     * @param string $username 用户名
     * @param string $password 密码
     * @param bool $rememberme 记住登录
     * @author 博派
     * @return bool|mixed
     */
    public function login($username = '', $password = '', $rememberme = false)
    {

        $username = trim($username);
        $password = trim($password);


        // 匹配登录方式
//        if (preg_match("/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/", $username)) {
//            // 邮箱登录
//            $map['email'] = $username;
//        }
//        elseif (preg_match("/^1\d{10}$/", $username)) {
//            // 手机号登录
//            $map['mobile'] = $username;
//        }
//        else {
            // 用户名登录
        $map['username'] = $username;
//        }
        $map['status'] = 1;
        // 查找用户
        $user = $this::get($map);
        if (!$user) {
            $this->error = '用户不存在或被禁用！';
        } else {
            // 检查是否分配用户组
            if ($user['role'] == 0) {
                $this->error = '禁止访问，原因：未分配角色！';
                return false;
            }
            // 检查是可登录后台
            if (!RoleModel::where(['id' => $user['role'], 'status' => 1])->value('access')) {
                $this->error = '禁止访问，用户所在角色未启用或禁止访问后台！';
                return false;
            }
            if (!Hash::check((string)$password, $user['password'])) {
                $this->error = '账号或者密码错误！';
            } else {
                $uid = $user['id'];

                // 更新登录信息
                $user['last_login_time'] = request()->time();
                $user['last_login_ip']   = request()->ip(1);
                if ($user->save()) {
                    // 自动登录
                    return $this->autoLogin($this::get($uid), $rememberme);
                } else {
                    // 更新登录信息失败
                    $this->error = '登录信息更新失败，请重新登录！';
                    return false;
                }
            }
        }
        return false;
    }

    /**
     * 自动登录
     * @param object $user 用户对象
     * @param bool $rememberme 是否记住登录，默认7天
     * @author 博派
     * @return bool|int
     */
    public function autoLogin($user, $rememberme = false)
    {
        // 记录登录SESSION和COOKIES
        $auth = array(
            'uid'             => $user->id,
            'group'           => $user->group,
            'role'            => $user->role,
//            'vip'            => $user->vip,
//            'vip'             => Db::name('member_user')->where('id', $user->id)->value('vip'),
            'role_name'       => Db::name('member_role')->where('id', $user->role)->value('name'),
            'avatar'          => $user->avatar,
            'username'        => $user->username,
            'nickname'        => $user->nickname,
            'last_login_time' => $user->last_login_time,
            'last_login_ip'   => get_client_ip(1),
        );
        session('vip', Db::name('member_user')->where('id', $user->id)->value('vip'));
        session('member_user_auth', $auth);

        $auth2 = array(
            'uid'             => $user->id,
            'group'           => $user->group,
            'avatar'          => $user->avatar,
            'username'        => $user->username,
            'nickname'        => $user->nickname,
            'last_login_time' => $user->last_login_time,
            'last_login_ip'   => get_client_ip(1),
        );
        session('member_user_auth_sign', data_auth_sign($auth2));

        // 保存用户节点权限
        if ($user->role != 1) {
            $menu_auth = Db::name('member_role')->where('id', session('member_user_auth.role'))->value('menu_auth');
            $menu_auth = json_decode($menu_auth, true);
            if (!$menu_auth) {
                session('member_user_auth', null);
                session('member_user_auth_sign', null);
                $this->error = '未分配任何节点权限！';
                return false;
            }
        }


        // 记住登录
        if ($rememberme) {
            $signin_token = $user->username.$user->id.$user->last_login_time;
            cookie('uid', $user->id, 24 * 3600 * 7);
            cookie('signin_token', data_auth_sign($signin_token), 24 * 3600 * 7);
        }

        return $user->id;
    }


    //注册验证code
   static function Verification($mobile,$code){
        $code_info = session($mobile);
        $res = ['data'=>$code_info,'code'=>1,'msg'=>'验证通过'];
        $time = time();
        if($time>$code_info['time']){
            session($mobile,null);
            $res['msg']='验证码超过有效期';
            $res['code']=0;
            return $res;
        }
        if($code!=$code_info['code']){
            $res['msg']='验证码错误';
            $res['code']=0;
            return $res;
        }
        session($mobile,null);
        return $res;
    }


}
