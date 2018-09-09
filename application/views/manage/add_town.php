<style type="text/css">
    .file-box{ position:relative;width:340px}
    .btn{ background-color:#FFF; border:1px solid #CDCDCD;height:21px; width:70px;}
    .file{ position:absolute; top:0; right:80px; height:24px; filter:alpha(opacity:0);opacity: 0;width:300px }
</style>
<div class="pageContent">
    <form method="post" enctype="multipart/form-data" action="<?php echo site_url('manage/save_fp_area');?>" class="pageForm required-validate" onsubmit="return validateCallback(this, dialogAjaxDone);">
        <div class="pageFormContent" layoutH="55">
            <fieldset>
                <legend>区域信息</legend>
                <dl>
                    <dt>区域名称：</dt>
                    <dd>
                        <input type="hidden" name="id" value="<?php if(!empty($id)) echo $id;?>">
                        <input name="area" type="text" class="required" value="<?php if(!empty($area)) echo $area;?>" />
                    </dd>
                </dl>
                <dl>
                    <dt>梯队：</dt>
                    <dd>
                        <input name="hot" type="text" class="required phone4js" value="<?php if(!empty($hot)) echo $hot;?>" />
                    </dd>
                </dl>
                <dl>
                    <dt>区域系数：</dt>
                    <dd>
                        <input name="area_ratio" type="text" class="required controll_p_3" value="<?php if(!empty($area_ratio)) echo $area_ratio;?>" />
                    </dd>
                </dl>
                <dl>
                    <dt>评估图标class类：</dt>
                    <dd>
                        <input name="hot_class" type="text" class="required" value="<?php if(!empty($hot_class)) echo $hot_class;?>" />
                    </dd>
                </dl>
            </fieldset>
        </div>
        <div class="formBar">
            <ul>
                <li><div class="buttonActive"><div class="buttonContent"><button type="submit" class="icon-save">保存</button></div></div></li>
                <li><div class="button"><div class="buttonContent"><button type="button" class="close icon-close">取消</button></div></div></li>
            </ul>
        </div>
    </form>
</div>
<script>
    $(".phone4js").keyup(function () {
        $(this).val($(this).val().replace(/[^0-9]/g, ''));
    }).blur(function(){
        $(this).val($(this).val().replace(/[^0-9]/g, ''));
    }).bind("paste", function () {  //CTR+V事件处理
        $(this).val($(this).val().replace(/[^0-9]/g, ''));
    }).css("ime-mode", "disabled"); //CSS设置输入法不可用

    $(".controll_p_3").keyup(function () {
        $(this).val($(this).val().replace(/[^0-9.]/g, ''));
        if(!/^[0-9]+(.[0-9]{0,3})?$/.test($(this).val())) {$(this).val('');}
    }).blur(function(){
        $(this).val($(this).val().replace(/[^0-9.]/g, ''));
        if(!/^[0-9]+(.[0-9]{0,3})?$/.test($(this).val())) {$(this).val('');}
    }).bind("paste", function () {  //CTR+V事件处理
        $(this).val($(this).val().replace(/[^0-9.]/g, ''));
        if(!/^[0-9]+(.[0-9]{0,3})?$/.test($(this).val())) {$(this).val('');}
    }).css("ime-mode", "disabled"); //CSS设置输入法不可用
</script>
