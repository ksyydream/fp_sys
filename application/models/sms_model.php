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

    public function sendSmsByAliyun($mobile, $smsSign, $smsParam, $templateCode)
    {
        include_once './application/libraries/api_sdk/lib/Core/Config.php';
        include_once './application/libraries/api_sdk/lib/Api/Sms/Request/V20170525/SendSmsRequest.php';

        $accessKeyId = $this->config->item('accessKeyID');
        $accessKeySecret = $this->config->item('accessKeySecret');

        //短信API产品名
        $product = "Dysmsapi";
        //短信API产品域名
        $domain = "dysmsapi.aliyuncs.com";
        //暂时不支持多Region
        $region = "cn-hangzhou";

        //初始化访问的acsCleint
        $profile = \Aliyun\Core\Profile\DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);
        \Aliyun\Core\Profile\DefaultProfile::addEndpoint("cn-hangzhou", "cn-hangzhou", $product, $domain);
        $acsClient= new \Aliyun\Core\DefaultAcsClient($profile);

        $request = new \Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;
        //必填-短信接收号码
        $request->setPhoneNumbers($mobile);
        //必填-短信签名
        $request->setSignName($smsSign);
        //必填-短信模板Code
        $request->setTemplateCode($templateCode);
        // 短信模板中字段的值
        $smsParam = json_encode($smsParam, JSON_UNESCAPED_UNICODE);
        //选填-假如模板中存在变量需要替换则为必填(JSON格式)
        $request->setTemplateParam($smsParam);
        //选填-发送短信流水号
        //$request->setOutId("1234");
        //die(var_dump($request));
        //发起访问请求
        $resp = $acsClient->getAcsResponse($request);
        //短信发送成功返回True，失败返回false
        if ($resp && $resp->Code == 'OK') {
            return array('status' => 1, 'msg' => $resp->Code);
        } else {
            return array('status' => -1, 'msg' => $resp->Message . ' subcode:' . $resp->Code);
        }
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
}