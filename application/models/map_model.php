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

    public function upload_excel(){
        if (is_readable('./././uploadfiles/excel_upload') == false) {
            mkdir('./././uploadfiles/excel_upload');
        }
        $change_row = 0;
        $config['upload_path']="./uploadfiles/excel_upload";
        $config['allowed_types']="xl|xlt|xll|xlc|xlw|xlm|xla|xlsx|xls";
        $config['encrypt_name'] = true;
        $config['max_size'] = '200000';
        //$config['encrypt_name']=true;
        $this->load->library('upload',$config);
        if( !$this->upload->do_upload('file')){
            die(var_dump($this->upload->display_errors()));
        }
        $data=$this->upload->data();
        require_once (APPPATH . 'libraries/PHPExcel/PHPExcel.php');
        require_once (APPPATH . 'libraries/PHPExcel/PHPExcel/IOFactory.php');
        //die(APPPATH . 'libraries/PHPExcel/PHPExcel/IOFactory.php');
        $uploadfile='./uploadfiles/excel_upload/'.$data['file_name'];//获取上传成功的Excel
        if($data['file_ext']==".xlsx"){
            $objReader = PHPExcel_IOFactory::createReader('Excel2007');
        }else{
            $objReader = PHPExcel_IOFactory::createReader('Excel5');
        }
        //use excel2007 for 2007 format 注意 linux下需要大小写区分 填写Excel2007   //xlsx使用2007,其他使用Excel5
        $objPHPExcel = $objReader->load($uploadfile);//加载目标Excel
        //处理企业信息
        $sheet = $objPHPExcel->getSheet(0);//读取第一个sheet
        $highestRow = $sheet->getHighestRow(); // 取得总行数

        $this->db->trans_start();//--------开始事务
        //$data = array();
        $old_xiaoqu = '';
        $old_area = '';
        for ($row = 2; $row <= $highestRow; $row++) {
            $xiaoqu_name = trim((string)$sheet->getCellByColumnAndRow(1, $row)->getValue());
            $area_ = trim((string)$sheet->getCellByColumnAndRow(2, $row)->getValue());
            if (!$xiaoqu_name) {
                $xiaoqu_name = $old_xiaoqu;
                $area_ = $old_area;
            } else {
                $old_xiaoqu = $xiaoqu_name;
                $old_area = $area_;
            }
            $address = trim((string)$sheet->getCellByColumnAndRow(3, $row)->getValue());
            $wy_ = trim((string)$sheet->getCellByColumnAndRow(4, $row)->getValue());
            $pg_price_ = trim((string)$sheet->getCellByColumnAndRow(5, $row)->getValue());
            $other_name = trim((string)$sheet->getCellByColumnAndRow(6, $row)->getValue());
            $area_info = $this->db->select()->from('fp_area')->where('area', $area_)->get()->row_array();
            $data = array(
                'name' => $xiaoqu_name,
                'address' => $address,
                'other_name' => $other_name,
                'area_id' => $area_info['id']
            );
            if($data['name'] == ''){
                continue;
            }
            $check_xiaoqu = $this->db->select()->from('fp_xiaoqu')->where('name', $xiaoqu_name)->get()->row_array();
            if (!$check_xiaoqu) {
                $this->db->insert('fp_xiaoqu', $data);
                $check_xiaoqu['id'] = $this->db->insert_id();
            }
            $wy_info = $this->db->select()->from('fp_wy')->where('wy', $wy_)->get()->row_array();
            if ($wy_info){
                $check_price = $this->db->select()->from('fp_xiaoqu_price')
                    ->where(array('xiaoqu_id' => $check_xiaoqu['id'], 'wy_id' => $wy_info['id']))
                    ->get()->row_array();
                if (!$check_price) {
                    $insert_data = array(
                        'xiaoqu_id' => $check_xiaoqu['id'],
                        'wy_id' => $wy_info['id'],
                        'price' => $pg_price_
                    );
                    $this->db->insert('fp_xiaoqu_price', $insert_data);
                }
            }

        }

        //$rs = $this->db->insert_batch('pg_xiaoqu', $data);



        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            echo "导入异常";
        } else {
            echo "成功导入".$change_row."小区信息<br/>";
        }
    }
}