<?php
/**
* 	配置账号信息
*/

class WxPayConfig
{
	const SSLKEY_PATH = APPPATH."libraries/wxpay/cert/apiclient_key.pem";
	const SSLCERT_PATH= APPPATH . 'libraries/wxpay/cert/apiclient_cert.pem';
	const APPID = 'wx6eb14c27ebe160c6';
	const MCHID = '1378041402';
	const KEY = 'sEFja73shyeqwHSd9vbNik3e44DfB63s';
	const APPSECRET = '7921ff3dbf764e2c5df5964a18846554';
	const CURL_PROXY_HOST = "0.0.0.0";
	const CURL_PROXY_PORT = 0;
	const REPORT_LEVENL = 1;
}
