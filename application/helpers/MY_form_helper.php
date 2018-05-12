<?php
/**
 * 返回AJAX提交表单后的JSON
 * $callbackType 默认的参数"closeCurrent"可以用于关闭当前窗体，'forward'跳转到$forwardUrl的网址。
 * 成功返回格式：{"statusCode":"200", "message":"操作成功", "navTabId":"navNewsLi", "forwardUrl":"", "callbackType":"closeCurrent"}
 * 失败返回格式:{"statusCode":"300", "message":"操作失败"}
 */
function form_submit_json($statusCode,$message,$navTabId="",$forwardUrl="",$callbackType="closeCurrent"){
    $returnType['statusCode'] =  $statusCode;
    $returnType['message'] = $message;
    $returnType['navTabId'] = $navTabId;
    $returnType['forwardUrl'] = $forwardUrl;
    $returnType['callbackType'] = $callbackType;
    echo (json_encode($returnType));
}