<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
include_once( VENDOR_PATH."SDK/SmsSDK.php");

/**
 *  判断字符串数据是否为空
 * @param  string $checkstr 
 * @return string
 */
function check_null($checkstr)
{
	$checkstr = trim($checkstr);

	if (strstr($checkstr, 'null') || (!$checkstr && $checkstr != 0)) {
		$checkstr = '';
	}

	return $checkstr;
}

/**
 *  都提交的内容进行处理
 * @param  string $checkstr 处理的字符串
 * @return string
 */
function check_sql($checkstr)
{
	$checkstr = addslashes($checkstr);
	$checkstr = addcslashes($checkstr, '%');
	$checkstr = addcslashes($checkstr, '_');
	$checkstr = nl2br($checkstr);
	$checkstr = htmlspecialchars($checkstr);
	return $checkstr;
}

/**
 * 发送模板短信
 * @param to 手机号码集合,用英文逗号分开
 * @param datas 内容数据 格式为数组 例如：array('Marry','Alon')，如不需替换请填 null
 * @param $tempId 模板Id
 */
function sendTemplateSMS($to, $datas, $tempId)
{
    //主帐号
    $accountSid = '8a216da87ba59937017be3497f890d48';
    //主帐号Token
    $accountToken = '47f78377143f49a1be868acc0e3b9c7d';
    //应用Id
    $appId = '8a216da87ba59937017be3c549eb0d66';
    //请求地址，格式如下，不需要写https://
    $serverIP = 'app.cloopen.com';
    //请求端口
    $serverPort = '8883';
    //REST版本号
    $softVersion = '2013-12-26';
    // 初始化REST SDK
    $rest = new REST($serverIP, $serverPort, $softVersion);
    $rest->setAccount($accountSid, $accountToken);
    $rest->setAppId($appId);

    // 发送模板短信
    // echo "Sending TemplateSMS to $to <br/>";
    $result = $rest->sendTemplateSMS($to, $datas, 1);
    if ($result == NULL) {
        echo "result error!";
        // break;
    }
    return ['status' => (string)$result->statusCode, 'msg' => (string)$result->statusMsg];

}
