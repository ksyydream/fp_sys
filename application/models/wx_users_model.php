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

    /**
     * 申请赎楼一
     * @author yangyang <yang.yang@thmarket.cn>
     * @date 2019-07-09
     */
    public function save_foreclosure(){
        $insert_ = array(
            'borrower_marriage' => $this->input->post('is_marriage'),
            'borrower_name' => $this->input->post('borrower_name'),
            'borrower_code' => $this->input->post('borrower_code'),
            'borrower_mobile' => $this->input->post('borrower_mobile'),
            'now_time' => $this->input->post('now_time'),
            'user_id' => $this->session->userdata('wx_user_id'),
            'add_time' => time(),
            'status' => 1,
        );
        if(!$insert_['borrower_name'] || !$insert_['borrower_code'] || !$insert_['borrower_mobile'])
            return $this->fun_fail('借款人信息不完善');
        if(is_idcard($insert_['borrower_code']))
            return $this->fun_fail('借款人身份证号码不规范');
        if(check_mobile($insert_['borrower_mobile']))
            return $this->fun_fail('借款人电话号码不规范');
        //开始效验
        switch($insert_['borrower_marriage']){
            case -1:
                break;
            case 1:
                $insert_['borrower_spouse_name'] = $this->input->post('borrower_spouse_name');
                $insert_['borrower_spouse_mobile'] = $this->input->post('borrower_spouse_mobile');
                $insert_['borrower_spouse_code'] = $this->input->post('borrower_spouse_code');
                if(!$insert_['borrower_spouse_name'] || !$insert_['borrower_spouse_mobile'] || !$insert_['borrower_spouse_code'])
                    return $this->fun_fail('配偶信息不完善');
                if(is_idcard($insert_['borrower_spouse_code']))
                    return $this->fun_fail('配偶身份证号码不规范');
                if(check_mobile($insert_['borrower_spouse_mobile']))
                    return $this->fun_fail('配偶电话号码不规范');
                break;
            default:
                return $this->fun_fail('婚姻状况未完善');
        }
        /** 此处需要调取同盾数据
         先通过身份证获取数据库内的同盾数据,
         如果数据库内没有数据,或者数据已经过期(过期时间暂定7天,建议过期时间存config文件内),再调取同盾接口获取借款人及其配偶的信息
         */

        //暂时先保存默认数据
        $insert_['borrower_td_score'] = 90;
        if($insert_['borrower_marriage'] == 1){
            $insert_['borrower_spouse_td_score'] = 90;
        }


        return $this->fun_success('提交成功');
    }

}