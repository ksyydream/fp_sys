<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 5/9/16
 * Time: 13:40
 */

class Wx_members_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function get_member_info($m_id){
        return $this->db->select()->from('members')->where('m_id', $m_id)->get()->row_array();
    }

}