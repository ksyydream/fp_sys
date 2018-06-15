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
        for ($row = 4; $row <= $highestRow; $row++) {
            $area_ = trim((string)$sheet->getCellByColumnAndRow(2, $row)->getValue());
            $wy_ = trim((string)$sheet->getCellByColumnAndRow(4, $row)->getValue());
            $area_info = $this->db->select()->from('pg_area')->where('name', $area_)->get()->row_array();
            $wy_info = $this->db->select()->from('pg_wy')->where('name', $wy_)->get()->row_array();
            $data = array(
                'jz_date' => trim((string)$sheet->getCellByColumnAndRow(1, $row)->getValue()),
                'area_id' => $area_info ? $area_info['id'] : -1,
                'xiaoqu' => trim((string)$sheet->getCellByColumnAndRow(3, $row)->getValue()),
                'wy_id' => $wy_info ? $wy_info['id'] : -1,
                'lc' => trim((string)$sheet->getCellByColumnAndRow(5, $row)->getValue()),
                'zcs' => trim((string)$sheet->getCellByColumnAndRow(6, $row)->getValue()),
                'jzmj' => trim((string)$sheet->getCellByColumnAndRow(7, $row)->getValue()),
                'year' => trim((string)$sheet->getCellByColumnAndRow(8, $row)->getValue()),
                'y_price' => trim((string)$sheet->getCellByColumnAndRow(9, $row)->getValue()),
                'b_price' => trim((string)$sheet->getCellByColumnAndRow(10, $row)->getValue()),
                'other_name' => trim((string)$sheet->getCellByColumnAndRow(11, $row)->getValue()),
                "cdate"=>date('Y-m-d H:i:s')
            );
            if($data['xiaoqu'] == ""){
                continue;
            }
            if($data['jzmj'] == ""){
                unset($data['jzmj']);
            }
            if($data['year'] == ""){
                unset($data['year']);
            }
            $this->db->insert('pg_xiaoqu', $data);
        }

        //$rs = $this->db->insert_batch('pg_xiaoqu', $data);



        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            echo "导入异常";
        } else {
            echo "成功删除".$change_row."条园区信息<br/>";
        }
    }
}