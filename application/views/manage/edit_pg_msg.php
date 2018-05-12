<style type="text/css">
    .file-box{ position:relative;width:340px}
    .btn{ background-color:#FFF; border:1px solid #CDCDCD;height:21px; width:70px;}
    .file{ position:absolute; top:0; right:80px; height:24px; filter:alpha(opacity:0);opacity: 0;width:300px }
</style>
<div class="pageContent">
    <form id="save_form" method="post" enctype="multipart/form-data" action="<?php echo site_url('manage/save_pg_msg');?>" class="pageForm required-validate" onsubmit="return iframeCallback(this, dialogAjaxDone);">
        <div class="pageFormContent" layoutH="55">
            <fieldset style="height: 250px">
                <legend>用户信息</legend>
                <dl>
                    <dt>姓名：</dt>
                    <dd>
                        <input type="hidden" name="id" value="<?php if(!empty($id)) echo $id;?>">
                        <input readonly="readonly" name="username" type="text" class="required" value="<?php if(!empty($username)) echo $username;?>" />
                    </dd>
                </dl>
                <dl>
                    <dt>电话：</dt>
                    <dd>
                        <input readonly="readonly" name="mobile" type="text" class="required" value="<?php if(!empty($mobile)) echo $mobile;?>" />
                    </dd>
                </dl>


                <dl>
                    <dt>状态：</dt>
                    <dd>
                        <select name="flag" class="combox" id="selectFlag">
                            <option value="1" <?php if(!empty($flag)){if($flag==1){echo 'selected';}}else{echo 'selected';}?>>待处理</option>
                            <option value="2" <?php if(!empty($flag)){if($flag==2){echo 'selected';}}?>>确认</option>
                            <option value="-1" <?php if(!empty($flag)){if($flag==-1){echo 'selected';}}else{echo 'selected';}?>>拒绝</option>
                            <option value="-2" <?php if(!empty($flag)){if($flag==-2){echo 'selected';}}?>>犹豫</option>
                        </select>
                    </dd>
                </dl>
                <dl>
                    <dt>备注：</dt>
                    <dd>
                        <textarea name='mark'  style="height: 100px;width: 200px"><?php if(!empty($mark)) echo $mark;?></textarea>
                    </dd>
                </dl>
            </fieldset>
            <fieldset style="height: 200px">
                <dl>
                    <dt>用户留言：</dt>
                    <dd>
                        <div  style="height: 100px;width: 300px;word-wrap:break-word">
                            <?php if(!empty($demo)) echo $demo;?>
                        </div>
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
