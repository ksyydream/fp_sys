<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 16/6/3
 * Time: 下午3:22
 */
class Sys_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * 检查token
     *
     * @return boolean
     */
    public function get_token($id){
       $row = $this->db->select()->from('user')->where('id',$id)->get()->row_array();
        if (!$row){
            return -1;
        }else{
            return $row['token'];
        }
    }
}