<div class="pageContent">
    
    <?php 
    $attributes = array('class' => 'pageForm', 'onsubmit' => 'return validateCallback(this, dialogAjaxDone)'); 
    echo form_open('manage_login/check_login', $attributes);
    ?>
        <div class="pageFormContent" layoutH="58">
            <div class="unit">
                <label>用户名：</label>
                <input type="text" name="username" size="30" class="required"/>
            </div>
            <div class="unit">
                <label>密码：</label>
                <input type="password" name="password" size="30" class="required"/>
            </div>
        </div>
        <div class="formBar">
            <ul>
                <li><div class="buttonActive"><div class="buttonContent"><button type="submit">提交</button></div></div></li>
                <li><div class="button"><div class="buttonContent"><button type="button" class="close">取消</button></div></div></li>
            </ul>
        </div>
    <?php echo form_close();?>
    
</div>