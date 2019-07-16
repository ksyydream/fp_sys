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
}