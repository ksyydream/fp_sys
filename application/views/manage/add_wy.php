<style type="text/css">
    .file-box{ position:relative;width:340px}
    .btn{ background-color:#FFF; border:1px solid #CDCDCD;height:21px; width:70px;}
    .file{ position:absolute; top:0; right:80px; height:24px; filter:alpha(opacity:0);opacity: 0;width:300px }
</style>
<div class="pageContent">
    <form method="post" enctype="multipart/form-data" action="<?php echo site_url('manage/save_fp_wy');?>" class="pageForm required-validate" onsubmit="return validateCallback(this, dialogAjaxDone);">
        <div class="pageFormContent" layoutH="55">
            <fieldset>
                <legend>物业信息</legend>
                <dl>
                    <dt>物业名称：</dt>
                    <dd>
                        <input type="hidden" name="id" value="<?php if(!empty($id)) echo $id;?>">
                        <input name="wy" type="text" class="required" value="<?php if(!empty($wy)) echo $wy;?>" />
                    </dd>
                </dl>
                <dl>
                    <dt>物业系数：</dt>
                    <dd>
                        <input name="ratio" type="text" class="required controll_p_2" value="<?php if(!empty($ratio)) echo $ratio;?>" />
                    </dd>
                </dl>
                <dl>
                    <dt>类别：</dt>
                    <dd>
                        <select name="flag" class="combox" id="selectFlag4wy">
                            <option value="1" <?php if(!empty($flag)){if($flag==1){echo 'selected';}}else{echo 'selected';}?>>楼房</option>
                            <option value="2" <?php if(!empty($flag)){if($flag==2){echo 'selected';}}?>>别墅</option>
                        </select>
                    </dd>
                </dl>

                <dl id="dl_wy_1">
                    <dt>最低层：</dt>
                    <dd>
                        <input name="min_c" type="text" class="phone4js" value="<?php if(!empty($min_c)) echo $min_c;?>" />
                    </dd>
                </dl>
                <dl id="dl_wy_2">
                    <dt>最高层：</dt>
                    <dd>
                        <input name="max_c" type="text" class="phone4js" value="<?php if(!empty($max_c)) echo $max_c;?>" />
                    </dd>
                </dl>
                <dl id="dl_wy_3">
                    <dt>顶低层系数：</dt>
                    <dd>
                        <input name="mm_ratio" type="text" class="controll_p_3" value="<?php if(!empty($mm_ratio)) echo $mm_ratio;?>" />
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

    $(".controll_p_2").keyup(function () {
        $(this).val($(this).val().replace(/[^0-9.]/g, ''));
        if(!/^[0-9]+(.[0-9]{0,2})?$/.test($(this).val())) {$(this).val('');}
    }).blur(function(){
        $(this).val($(this).val().replace(/[^0-9.]/g, ''));
        if(!/^[0-9]+(.[0-9]{0,2})?$/.test($(this).val())) {$(this).val('');}
    }).bind("paste", function () {  //CTR+V事件处理
        $(this).val($(this).val().replace(/[^0-9.]/g, ''));
        if(!/^[0-9]+(.[0-9]{0,2})?$/.test($(this).val())) {$(this).val('');}
    }).css("ime-mode", "disabled"); //CSS设置输入法不可用

    $("#selectFlag4wy").change(function(){
        if($(this).val() == '1'){
            $("#dl_wy_1").show();
            $("#dl_wy_2").show();
            $("#dl_wy_3").show();
        }else{
            $("#dl_wy_1").hide();
            $("#dl_wy_2").hide();
            $("#dl_wy_3").hide();
        }
    })
    $("#selectFlag4wy").change();
</script>
