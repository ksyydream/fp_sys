<style type="text/css">
    .file-box{ position:relative;width:340px}
    .btn{ background-color:#FFF; border:1px solid #CDCDCD;height:21px; width:70px;}
    .file{ position:absolute; top:0; right:80px; height:24px; filter:alpha(opacity:0);opacity: 0;width:300px }
</style>
<div class="pageContent">
    <form method="post" enctype="multipart/form-data" action="<?php echo site_url('manage/save_menu');?>" class="pageForm required-validate" onsubmit="return validateCallback(this, dialogAjaxDone);">
        <div class="pageFormContent" layoutH="55">
            <fieldset>
                <legend>进程信息</legend>
                <dl>
                    <dt>进程名称：</dt>
                    <dd>
                        <input type="hidden" name="id" value="<?php if(!empty($id)) echo $id;?>">
                        <input name="menu_name" type="text" class="required" value="<?php if(!empty($menu_name)) echo $menu_name;?>" />
                    </dd>
                </dl>
                <dl>
                    <dt>状态：</dt>
                    <dd>
                        <label>
                            <input
                                <?php if (!empty($flag) && $flag==1){
                                    echo "checked";
                                } ?>
                                name="flag" type="checkbox">启用
                        </label>

                    </dd>
                </dl>
            </fieldset>
            <fieldset>
                <legend>使用模块</legend>
                <?php
                if (!empty($icon_list)):
                    foreach ($icon_list as $row):
                        $checked = '';
                        if(!empty($menu_detail)){
                            foreach ($menu_detail as $id){
                                if($row['id'] == $id['p_id']){
                                    $checked = 'checked';
                                }
                            }
                        }
                        ?>

                        <label><input <?php echo $checked; ?> name="p_id[]" type="checkbox" value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></label>

                        <?php
                    endforeach;
                endif;
                ?>
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
