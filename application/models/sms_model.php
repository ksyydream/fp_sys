<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 16/6/3
 * Time: 下午3:22
 */
require_once dirname(__DIR__) . '/libraries/api_sdk/vendor/autoload.php';

use Aliyun\Core\Config;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;
use Aliyun\Api\Sms\Request\V20170525\SendBatchSmsRequest;
use Aliyun\Api\Sms\Request\V20170525\QuerySendDetailsRequest;

// 加载区域结点配置
Config::load();
class Sms_model extends MY_Model
{
    static $acsClient = null;
    public function __construct()
    {
        parent::__construct();

    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public static function getAcsClient($accessKeyId_config, $accessKeySecret_config) {
        //产品名称:云通信短信服务API产品,开发者无需替换
        $product = "Dysmsapi";

        //产品域名,开发者无需替换
        $domain = "dysmsapi.aliyuncs.com";

        $accessKeyId = $accessKeyId_config; // AccessKeyId

        $accessKeySecret =$accessKeySecret_config; // AccessKeySecret

        // 暂时不支持多Region
        $region = "cn-hangzhou";

        // 服务结点
        $endPointName = "cn-hangzhou";


        if(static::$acsClient == null) {

            //初始化acsClient,暂不支持region化
            $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);

            // 增加服务结点
            DefaultProfile::addEndpoint($endPointName, $region, $product, $domain);

            // 初始化AcsClient用于发起请求
            static::$acsClient = new DefaultAcsClient($profile);
        }
        return static::$acsClient;
    }

    /**
     * 发送短信
     * @return stdClass
     */
    public function sendSms($mobile, $smsSign, $code, $templateCode) {
        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = new SendSmsRequest();

        //可选-启用https协议
        //$request->setProtocol("https");

        // 必填，设置短信接收号码
        $request->setPhoneNumbers($mobile);

        // 必填，设置签名名称，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $request->setSignName($smsSign);

        // 必填，设置模板CODE，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $request->setTemplateCode($templateCode);

        // 可选，设置模板参数, 假如模板中存在变量需要替换则为必填项
        $request->setTemplateParam(json_encode(array(  // 短信模板中字段的值
            "code" => $code
        ), JSON_UNESCAPED_UNICODE));

        // 可选，设置流水号
        //$request->setOutId("yourOutId");

        // 选填，上行短信扩展码（扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段）
        //$request->setSmsUpExtendCode("1234567");

        // 发起访问请求
        $accessKeyId = $this->config->item('accessKeyID');
        $accessKeySecret = $this->config->item('accessKeySecret');
        $acsResponse = static::getAcsClient($accessKeyId, $accessKeySecret)->getAcsResponse($request);

        return $acsResponse;
    }

    public function send_code($mobile, $smsSign, $code, $type){
        $ali_templateCode = $this->config->item('ali_templateCode');
        if(!isset($ali_templateCode[$type])){
            return $this->fun_fail('请求类型不存在');
        }
        //当$type=2时,也就是登录时判断下,账号是否存在
        if($type == 2){
            if($this->input->get('sms_class') == 'u'){
                $check_info_ = $this->db->select()->from('users')->where('mobile', $mobile)->get()->row_array();
                if(!$check_info_)
                    return $this->fun_fail('账号不存在,请先注册!');
            }
            if($this->input->get('sms_class') == 'm'){
                $check_info_ = $this->db->select()->from('members')->where('mobile', $mobile)->get()->row_array();
                if(!$check_info_)
                    return $this->fun_fail('账号不存在,请先注册!');
            }
        }
        $sms_log = $this->db->select('*')->from('sms_log')->where(array('mobile' => $mobile, 'status' => 1))->limit(1)->order_by('add_time','desc')->get()->row_array();
        if($sms_log){
            $sms_time_out = $this->config->item('sms_time_out');
            $sms_time_out = $sms_time_out ? $sms_time_out : 120;
            if ((time() - $sms_log['add_time']) < $sms_time_out) {
                return $this->fun_fail($sms_time_out . '秒内不允许重复发送');
            }
        }
        $res = $this->sendSms($mobile, $smsSign, $code, $ali_templateCode[$type]);
        $insert_ = array(
            'mobile' => $mobile,
            'code' => $code,
            'template' => $ali_templateCode[$type],
            'scene' => $type,
            'add_time' => time()
        );
        if ($res && $res->Code == 'OK') {
            $insert_['status'] = 1;
            $this->db->insert('sms_log', $insert_);
            return $this->fun_success('发送成功');
        }else{
            if($res){
                $insert_['status'] = 0;
                $insert_['error_msg'] = $res->Message . ' subcode:' . $res->Code;
                $this->db->insert('sms_log', $insert_);
            }
        }
        return $this->fun_fail('发送失败');
    }
}