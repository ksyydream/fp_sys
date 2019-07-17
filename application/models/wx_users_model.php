<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 5/9/16
 * Time: 13:40
 */

class Wx_users_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function get_user_info($user_id){
        return $this->db->select()->from('users')->where('user_id', $user_id)->get()->row_array();
    }

    public function get_user_info4region($user_id){
        $this->db->select('us.*,r1.name r1_name,r2.name r2_name,r3.name r3_name,r4.name r4_name');
        $this->db->from('users us');
        $this->db->join('region r1', 'us.province = r1.id', 'left');
        $this->db->join('region r2', 'us.city = r2.id', 'left');
        $this->db->join('region r3', 'us.district = r3.id', 'left');
        $this->db->join('region r4', 'us.twon = r4.id', 'left');
        $ret = $this->db->where(array('us.user_id' => $user_id))->get()->row_array();
        return $ret;
    }

    public function person_info_edit(){
        $data = $this->input->post();
        $update_ = array();
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
        $update_['shop_name'] = $data['shop_name'];
        $update_['province'] = $area_arr[0];
        $update_['city'] = isset($area_arr[1]) ? $area_arr[1] : 0;
        $update_['district'] = isset($area_arr[2]) ? $area_arr[2] : 0;
        $update_['twon'] = isset($area_arr[3]) ? $area_arr[3] : 0;
        $update_['address'] = $data['address'];
        if(!$update_['address']){
            return $this->fun_fail('必须选择区域!');
        }
        $this->db->where('user_id', $this->session->userdata('wx_user_id'))->update('users', $update_);
        return $this->fun_success('操作成功');
    }
}