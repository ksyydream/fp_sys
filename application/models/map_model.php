<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 6/2/16
 * Time: 21:22
 */

class Map_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function __destruct()
    {
        parent::__destruct();
    }


    public function get_map_info($name = ''){
        $this->db->select()->from('map');
        $this->db->where('flag',1);
        if($this->input->post('md_key')){
            $this->db->like('name',$this->input->post('md_key'));
            $this->db->or_like('phone',$this->input->post('md_key'));
        }
        $data['items'] = $this->db->get()->result_array();
        $data['md_key'] = $this->input->post('md_key')?$this->input->post('md_key'):'';
        return $data;
    }
}