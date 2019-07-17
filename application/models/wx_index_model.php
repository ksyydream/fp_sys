<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 16/6/3
 * Time: 下午3:22
 */
class Wx_index_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function logout(){
        $this->db->where('user_id',$this->session->userdata('wx_user_id'))->update('users',array('openid'=>''));
        $this->db->where('m_id',$this->session->userdata('wx_m_id'))->update('members',array('openid'=>''));
        $this->session->unset_userdata('wx_user_id');
        $this->session->unset_userdata('wx_m_id');
        $this->session->unset_userdata('wx_class');
        $this->session->sess_destroy();
    }

    public function submit_login(){
        $openid = $this->session->userdata('openid');
        $username = $this->input->post('name');
        $password = $this->input->post('passwd');
        $this->db->from('user');
        $this->db->where('username', $username);
        $this->db->where('password', sha1($password));
        $rs = $this->db->get();
        if ($rs->num_rows() > 0) {
            $this->db->where('openid',$openid)->set('openid','')->update('user');
            $res = $rs->row();
            $this->db->where('id',$res->id)->update('user',array('openid'=>$openid));
            return 1;
        }
        return -1;
    }

    public function api_user_info(){
        $openid = $this->session->userdata('openid');
        $check_ = $this->db->select()->from('fp_wx_user')->where('openid', $openid)->get()->row_array();
        if($check_){
            if($check_['true_mobile'] == ""){
                $check_['success'] = false;
            }else{
                $check_['success'] = true;
            }
            return $check_;
        }else{
            return array(
                'success' => false,
                'true_mobile' => '',
                'true_company' => '',
                'true_name' => ''
            );
        }
    }

    public function get_region($parent_id = 0)
    {
        if($parent_id == 0){
            $this->db->select('id,name')->from('region');
            $this->db->where('parent_id', 0);
            //$this->db->where_in('id', array(10543, 10808));
            $this->db->order_by('id', 'asc');
        }else{
            $this->db->select('id,name')->from('region')->where('parent_id', $parent_id)->order_by('id', 'asc');
        }
        return $this->db->get()->result_array();
    }

    public function check_region(){
        $province = $this->input->post('val1');
        $city = $this->input->post('val2');
        $district = $this->input->post('val3');
        $twon = $this->input->post('val4');
        if(!isset($twon) || $twon == null)
            $twon = 0;
        if(!$province || !$city || !$district){
            return $this->fun_fail('地址缺失!');
        }
        //开始检查地址是否对应
        if($twon){
            $check_val4 = $this->db->select()->from('region')->where(array('id' => $twon, 'parent_id' => $district))->get()->row_array();
            if(!$check_val4){
                $res_ = $this->new_region($district, $twon);
                return $this->fun_fail('地址错误!', $res_);
            }
        }
        $check_val3 = $this->db->select()->from('region')->where(array('id' => $district, 'parent_id' => $city))->get()->row_array();
        if(!$check_val3){
            $res_ = $this->new_region($district, $twon);
            return $this->fun_fail('地址错误!', $res_);
        }
        $check_val2 = $this->db->select()->from('region')->where(array('id' => $city, 'parent_id' => $province))->get()->row_array();
        if(!$check_val2){
            $res_ = $this->new_region($district, $twon);
            return $this->fun_fail('地址错误!', $res_);
        }
        return $this->fun_success('确认正确!');
    }

    public function new_region($district, $twon=0){
        $index_1 = 0;
        $index_2 = 0;
        $index_3 = 0;
        $index_4 = 0;
        if($twon){
            $check_val4 = $this->db->select()->from('region')->where(array('id' => $twon))->get()->row_array();
            $district = $check_val4['parent_id'];
        }
        $check_val3 = $this->db->select()->from('region')->where(array('id' => $district))->get()->row_array();
        $city = $check_val3['parent_id'];
        $check_val2 = $this->db->select()->from('region')->where(array('id' => $city))->get()->row_array();
        $province = $check_val2['parent_id'];
        $retrun_['value_arr'] = array('province_p' => 0, 'city_p' => $province, 'district_p' => $city, 'twon_p' => $district);

        $region_1 = $this->db->select()->from('region')->where("parent_id", 0)->get()->result_array();
        foreach($region_1 as $k1 => $v1){
            if($v1['id'] == $province){
                $index_1 = $k1;
                break;
            }
        }
        $region_2 = $this->db->select()->from('region')->where("parent_id", $province)->get()->result_array();
        foreach($region_2 as $k2 => $v2){
            if($v2['id'] == $city){
                $index_2 = $k2;
                break;
            }
        }
        $region_3 = $this->db->select()->from('region')->where("parent_id", $city)->get()->result_array();
        foreach($region_3 as $k3 =>$v3){
            if($v3['id'] == $district){
                $index_3 = $k3;
                break;
            }
        }
        $region_4 = $this->db->select()->from('region')->where("parent_id", $district)->get()->result_array();
        foreach($region_4 as $k4 => $v4){
            if($v4['id'] == $twon){
                $index_4 = $k4;
                break;
            }
        }
        $retrun_['index_arr'] = array('index_1' => $index_1, 'index_2' => $index_2, 'index_3' => $index_3, 'index_4' => $index_4);
        return $retrun_;
    }


    public function begin_cal(){
        $param = array(
            'biz_code' => 'loan',
            'account_name' => $this->input->post('user_name') ? $this->input->post('user_name') : '',
            'id_number' => $this->input->post('user_card') ? $this->input->post('user_card') : '',
            'account_mobile' => $this->input->post('user_mobile') ? $this->input->post('user_mobile') : ''
        );
        $curlPost = $param;
        header("Content-Type: text/html;charset=utf-8");
        $ch = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_URL, "https://apitest.tongdun.cn/bodyguard/apply/v4.3?partner_code=funmall&partner_key=" . $this->config->item('partner_key') ."&app_name=funmall_web"); //抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0); //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1); //post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($curlPost));
        $data = curl_exec($ch); //运行curl
        //$data = iconv("utf-8", "GBK//ignore", $data);
        curl_close($ch);
        $res = json_decode($data);
       return $res;

    }

    //用户注册申请
    public function reg_save($data){
        $insert = array(
            'reg_time' => time(),
            'openid' => $this->session->userdata('openid'),
            'token' => uniqid()
        );
        if(!$data['rel_name']){
            return $this->fun_fail('姓名不能为空!');
        }
        if(!$data['mobile']){
            return $this->fun_fail('手机号不能为空!');
        }
        if(!check_mobile($data['mobile'])){
            return $this->fun_fail('手机号不规范!');
        }
        if(!$data['code']){
            return $this->fun_fail('短信验证码不能为空!');
        }
        if(!$data['invite_code']){
            return $this->fun_fail('邀请码不能为空!');
        }
        //开始验证电话号码是否已经注册
        $check_reg_ = $this->db->from('users')->where(array('mobile' => $data['mobile']))->get()->row_array();
        if($check_reg_){
            return $this->fun_fail('电话号码已注册!');
        }
        //验证手机短信
        $check_sms_ = $this->check_sms($data['mobile'], $data['code']);
        if($check_sms_['status'] != 1){
            return $check_sms_;
        }
        $insert['mobile'] = $data['mobile'];
        $insert['rel_name'] = $data['rel_name'];
        //验证邀请码是否合法
        $check_member_ = $this->db->from('members')->where(array('invite_code' => $data['invite_code']))->where_in('level', array(2, 3))->get()->row_array();
        if(!$check_member_){
            return $this->fun_fail('邀请码不存在!');
        }
        if($check_member_['status'] != 1){
            return $this->fun_fail('邀请码已不可使用!');
        }
        $insert['invite'] = $check_member_['m_id'];
        switch($data['type_id']){
            case 1:
                //门店注册需要保存门店信息
                if(!$data['shop_name']){
                    return $this->fun_fail('请填写门店名称!');
                }

                $area_value = $data['area_value'];
                if(!$area_value){
                    return $this->fun_fail('请选择区域!');
                }
                $area_arr = explode(',', $area_value);
                if(!$area_arr[0] || !isset($area_arr[1]) || !isset($area_arr[2])){
                    return $this->fun_fail('必须选择区域!');
                }
                //区域保存
                $insert['shop_name'] = $data['shop_name'];
                $insert['province'] = $area_arr[0];
                $insert['city'] = isset($area_arr[1]) ? $area_arr[1] : 0;
                $insert['district'] = isset($area_arr[2]) ? $area_arr[2] : 0;
                $insert['twon'] = isset($area_arr[3]) ? $area_arr[3] : 0;
                $insert['address'] = $data['address'];
                if(!$insert['address']){
                    return $this->fun_fail('必须选择区域!');
                }
                break;
            case 2:
                break;
            default:
                return $this->fun_fail('请选择注册类型!');
        }
        $insert['type_id'] = $data['type_id'];
        $insert['pic'] = $data['pic'];
        //die(var_dump($insert));
        $this->db->insert('users', $insert);
        $user_id = $this->db->insert_id();
        //以防万一 去除其他账号相同openid的状态
        $this->delOpenidById($user_id, $insert['openid'], 'users');
        $this->set_user_session_wx($user_id);
        return $this->fun_success('注册成功!');
    }

    public function user_login($data){
        $update_ = array(
            'openid' => $this->session->userdata('openid'),
            'token' => uniqid()
        );
        if(!$data['mobile']){
            return $this->fun_fail('手机号不能为空!');
        }
        if(!check_mobile($data['mobile'])){
            return $this->fun_fail('手机号不规范!');
        }
        if(!$data['code']){
            return $this->fun_fail('短信验证码不能为空!');
        }
        //开始验证电话号码是否已经注册
        $check_reg_ = $this->db->from('users')->where(array('mobile' => $data['mobile']))->get()->row_array();
        if(!$check_reg_){
            return $this->fun_fail('电话号码未注册!');
        }
        if($check_reg_['status'] != 1){
            return $this->fun_fail('账号异常!');
        }
        //验证手机短信
        $check_sms_ = $this->check_sms($data['mobile'], $data['code']);
        if($check_sms_['status'] != 1){
            return $check_sms_;
        }
        //以防万一 去除其他账号相同openid的状态
        $this->db->where('user_id', $check_reg_['user_id'])->update('users', $update_);
        $this->delOpenidById($check_reg_['user_id'], $update_['openid'], 'users');
        $this->set_user_session_wx($check_reg_['user_id']);
        return $this->fun_success('登录成功!');
    }

    public function member_login($data){
        $update_ = array(
            'openid' => $this->session->userdata('openid'),
            'token' => uniqid()
        );
        if(!$data['mobile']){
            return $this->fun_fail('手机号不能为空!');
        }
        if(!check_mobile($data['mobile'])){
            return $this->fun_fail('手机号不规范!');
        }
        if(!$data['code']){
            return $this->fun_fail('短信验证码不能为空!');
        }
        //开始验证电话号码是否已经注册
        $check_reg_ = $this->db->from('members')->where(array('mobile' => $data['mobile']))->get()->row_array();
        if(!$check_reg_){
            return $this->fun_fail('电话号码未注册!');
        }
        if($check_reg_['status'] != 1){
            return $this->fun_fail('账号异常!');
        }
        //验证手机短信
        $check_sms_ = $this->check_sms($data['mobile'], $data['code']);
        if($check_sms_['status'] != 1){
            return $check_sms_;
        }
        //以防万一 去除其他账号相同openid的状态
        $this->db->where('m_id', $check_reg_['m_id'])->update('members', $update_);
        $this->delOpenidById($check_reg_['m_id'], $update_['openid'], 'members');
        $this->set_user_session_wx($check_reg_['m_id']);
        return $this->fun_success('登录成功!');
    }
}