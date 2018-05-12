<style type="text/css">
    .file-box{ position:relative;width:340px}
    .btn{ background-color:#FFF; border:1px solid #CDCDCD;height:21px; width:70px;}
    .file{ position:absolute; top:0; right:80px; height:24px; filter:alpha(opacity:0);opacity: 0;width:300px }
</style>
<div class="pageContent">
    <form method="post" enctype="multipart/form-data" action="<?php echo site_url('manage/save_pg_qq');?>" class="pageForm required-validate" onsubmit="return validateCallback(this, dialogAjaxDone);">
        <div class="pageFormContent" layoutH="55">
            <fieldset>
                <legend>客服QQ信息</legend>
                <dl>
                    <dt>QQ：</dt>
                    <dd>
                        <input type="hidden" name="id" value="<?php if(!empty($id)) echo $id;?>">
                        <input name="qq" id="qq" type="text" class="required" value="<?php if(!empty($qq)) echo $qq;?>" />
                    </dd>
                </dl>
                <dl>
                    <dt>备注：</dt>
                    <dd>
                        <input name="desc" type="text" value="<?php if(!empty($desc)) echo $desc;?>" />
                    </dd>
                </dl>
                <dl>
                    <dt>状态：</dt>
                    <dd>
                        <select name="flag" class="combox" id="selectFlag">
                            <option value="1" <?php if(!empty($flag)){if($flag==1){echo 'selected';}}else{echo 'selected';}?>>使用</option>
                            <option value="-1" <?php if(!empty($flag)){if($flag==-1){echo 'selected';}}?>>禁用</option>
                        </select>
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
    $("[name='qq']").keyup(function () {
        $(this).val($(this).val().replace(/[^0-9]/g, ''));
    }).blur(function(){
        $(this).val($(this).val().replace(/[^0-9]/g, ''));
    }).bind("paste", function () {  //CTR+V事件处理
        $(this).val($(this).val().replace(/[^0-9]/g, ''));
    }).css("ime-mode", "disabled"); //CSS设置输入法不可用
</script>
