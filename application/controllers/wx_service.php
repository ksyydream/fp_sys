<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 5/2/16
 * Time: 09:56
 */

 if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Wx_service extends CI_controller {

    public function __construct()
    {
        parent::__construct();
        ini_set('date.timezone','Asia/Shanghai');
        $this->load->model('wx_index_model');

    }


    public function index() {

        $echoStr = $_GET["echostr"];
        if(isset($echoStr)) {
            if($this->checkSignature()){
                echo $echoStr;
                exit;
            }
        } else {
            $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
            if (!empty($postStr)){
                $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $RX_TYPE = trim($postObj->MsgType);
                $result = "";
                switch ($RX_TYPE) {
                    case "text":
                        //$result = $this->receiveText($postObj);
                        break;
                    case "event":
                        $result = $this->receiveEvent($postObj);
                        break;
                    case "image":
                        //$result = $this->receiveImage($postObj);
                        break;
                    default:
                        $result = "Unknow msg type: ".$RX_TYPE;
                        break;
                }
                echo $result;
                exit;
            } else {
                echo "";
                exit;
            }
        }
    }

    private function receiveText($object) {
        $keyword = trim($object->Content);
        $content = json_decode($this->post('http://ws.ksls.com.cn/api/search_house', $keyword, $object->FromUserName), true);
        if(!empty($content)) {
            return $this->transmitNews($object, $content);
        }
        return $this->transmitText($object, '请输入楼盘名称查询楼盘信息，或点击底部菜单进入微店浏览更多楼盘');
    }

    private function receiveEvent($object) {
        $content = "";
        switch ($object->Event) {
            case "subscribe":
                $content = "欢迎关注房猫微店公众账号。";
                if (!empty($object->EventKey)){
                    $invite_code = str_replace("qrscene_", "", $object->EventKey);
                    $member_info = $this->wx_index_model->getMemberByInvite($invite_code);
                    if($member_info){
                        return $this->transmitDBY($object, $member_info);
                    }
                }
                break;
            case "unsubscribe":
                $content = "取消关注";
                //file_get_contents('http://ws.ksls.com.cn/api/unsubscribe_weixin_user/' . $object->FromUserName);
                break;
            case "SCAN":
                $content = "欢迎关注房猫微店公众账号!";
                $invite_code = $object->EventKey;
                //$member_info = $this->wx_index_model->getMemberByInvite($invite_code);
                //if($member_info){
                    //return $this->transmitDBY($object, $member_info);
                //}

                break;
            case "CLICK":
                $content = "点击菜单拉取消息： " . $object->EventKey;
                break;
            case "VIEW":
                $content = "点击菜单跳转链接： " . $object->EventKey;
                break;
            case "LOCATION":
                $content = "上传位置：纬度 " . $object->Latitude . ";经度 " . $object->Longitude;
                break;
        }
        return $this->transmitText($object, $content);
    }

    private function transmitText($object, $content) {
        $textTpl = "
			<xml>
				<ToUserName><![CDATA[%s]]></ToUserName>
				<FromUserName><![CDATA[%s]]></FromUserName>
				<CreateTime>%s</CreateTime>
				<MsgType><![CDATA[text]]></MsgType>
				<Content><![CDATA[%s]]></Content>
				<FuncFlag>0</FuncFlag>
			</xml>
		";
        return sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content);
    }

    private function transmitNews($object, $arr_item) {
        if(!is_array($arr_item))
            return;

        $itemTpl = "
			<item>
		        <Title><![CDATA[%s]]></Title>
		        <Description><![CDATA[%s]]></Description>
		        <PicUrl><![CDATA[%s]]></PicUrl>
		        <Url><![CDATA[%s]]></Url>
    		</item>
		";
        $item_str = "";
        foreach ($arr_item as $item)
            $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);

        $newsTpl = "
		<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[news]]></MsgType>
		<Content><![CDATA[]]></Content>
		<ArticleCount>%s</ArticleCount>
		<Articles>$item_str</Articles>
		</xml>
		";
        return sprintf($newsTpl, $object->FromUserName, $object->ToUserName, time(), count($arr_item));
    }

    private function transmitDBY($object, $member_info) {
        if(!$member_info)
            return;

        $newsTplHead = "<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[news]]></MsgType>
                <ArticleCount>1</ArticleCount>
                <Articles>";
        $newsTplBody = "<item>
                <Title><![CDATA[%s]]></Title>
                <Description><![CDATA[%s]]></Description>
                <PicUrl><![CDATA[%s]]></PicUrl>
                <Url><![CDATA[%s]]></Url>
                </item>";
        $newsTplFoot = "</Articles>
                <FuncFlag>0</FuncFlag>
                </xml>";
        $header = sprintf($newsTplHead, $object->FromUserName, $object->ToUserName, time());
        $title = '请注册并绑定管理员';
        $desc = '管理员邀请码:' . $member_info['invite_code'];
        $picUrl = 'http://sys.ksls.com.cn/assets/i/gz_weixin.jpg';
        $url = 'http://sys.ksls.com.cn/wx_index/register?invite_code_temp=' . $member_info['invite_code'];
        $body = sprintf($newsTplBody, $title, $desc, $picUrl, $url);
        return $header . $body . $newsTplFoot;
    }

    private function checkSignature() {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = $this->config->item('wx_service_token');
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if($tmpStr == $signature){
            return true;
        } else {
            return false;
        }
    }

    private function post($url, $keyword, $open_id, $timeout = 300){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'keyword=' . $keyword . '&open_id=' . $open_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch,CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_POST, true);

        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }



}