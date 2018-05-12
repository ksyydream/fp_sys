<style type="text/css">
    .file-box{ position:relative;width:340px}
    .btn{ background-color:#FFF; border:1px solid #CDCDCD;height:21px; width:70px;}
    .file{ position:absolute; top:0; right:80px; height:24px; filter:alpha(opacity:0);opacity: 0;width:300px }
</style>
<div class="pageContent">
    <form method="post" enctype="multipart/form-data" action="<?php echo site_url('manage/save_forum_type');?>" class="pageForm required-validate" onsubmit="return validateCallback(this, dialogAjaxDone);">
        <div class="pageFormContent" layoutH="55">
            <fieldset>
                <legend>资料类别信息</legend>
                <dl>
                    <dt>类别名称：</dt>
                    <dd>
                        <input type="hidden" name="id" value="<?php if(!empty($id)) echo $id;?>">
                        <input name="name" type="text" class="required" value="<?php if(!empty($name)) echo $name;?>" />
                    </dd>
                </dl>
                <dl>
                    <dt>状态：</dt>
                    <dd>
                        <select name="flag" class="combox" id="selectFlag">
                            <option value="1" <?php if(!empty($flag)){if($flag==1){echo 'selected';}}else{echo 'selected';}?>>使用</option>
                            <option value="2" <?php if(!empty($flag)){if($flag==2){echo 'selected';}}?>>禁用</option>
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

</script>
