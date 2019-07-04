$(function(){
    $(".js4phone").keyup(function () {
        $("#nickname").val('');
        $("#account_warning").hide();
        $(this).val($(this).val().replace(/[^0-9]/g, ''));
        $(this).val($(this).val().toUpperCase());

    }).blur(function(){
        $("#nickname").val('');
        $("#account_warning").hide();
        $(this).val($(this).val().replace(/[^0-9]/g, ''));
        $(this).val($(this).val().toUpperCase());

    }).bind("paste", function () {  //CTR+V事件处理
        $("#nickname").val('');
        $("#account_warning").hide();
        $(this).val($(this).val().replace(/[^0-9]/g, ''));
        $(this).val($(this).val().toUpperCase());

    }).css("ime-mode", "disabled"); //CSS设置输入法不可用

    $(".js4carNo_6").keyup(function () {
        $(this).val($(this).val().replace(/[^0-9A-Za-z]/g, ''));
        $(this).val($(this).val().toUpperCase());
        if($(this).val().length > 6){
            $(this).val($(this).val().substr(0, 6));
        }
    }).blur(function(){
        $(this).val($(this).val().replace(/[^0-9A-Za-z]/g, ''));
        $(this).val($(this).val().toUpperCase());
        if($(this).val().length > 6){
            $(this).val($(this).val().substr(0, 6));
        }
    }).bind("paste", function () {  //CTR+V事件处理
        $(this).val($(this).val().replace(/[^0-9A-Za-z]/g, ''));
        $(this).val($(this).val().toUpperCase());
        if($(this).val().length > 6){
            $(this).val($(this).val().substr(0, 6));
        }
    }).css("ime-mode", "disabled"); //CSS设置输入法不可用
})

