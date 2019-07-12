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

    $(".js4IDcard").keyup(function () {
        $(this).val($(this).val().replace(/[^0-9Xx]/g, ''));
        $(this).val($(this).val().toUpperCase());
    }).blur(function(){
        $(this).val($(this).val().replace(/[^0-9Xx]/g, ''));
        $(this).val($(this).val().toUpperCase());
    }).bind("paste", function () {  //CTR+V事件处理
        $(this).val($(this).val().replace(/[^0-9Xx]/g, ''));
        $(this).val($(this).val().toUpperCase());
    }).css("ime-mode", "disabled"); //CSS设置输入法不可用

    $(".js4float1").keyup(function () {
        $(this).val($(this).val().replace(/[^0-9.]/g, ''));
        $(this).val($(this).val().replace(/\.{2,}/g, "."));
        $(this).val($(this).val().replace(".","$#$").replace(/\./g,"").replace("$#$","."));
        if(!/^[0-9]+(.[0-9]{0,1})?$/.test($(this).val())) {$(this).val($(this).val().replace(/^(\-)*(\d+)\.(\d).*$/,'$1$2.$3'));}

    }).blur(function(){
        $(this).val($(this).val().replace(/[^0-9.]/g, ''));
        $(this).val($(this).val().replace(/\.{2,}/g, "."));
        $(this).val($(this).val().replace(".","$#$").replace(/\./g,"").replace("$#$","."));
        if(!/^[0-9]+(.[0-9]{0,1})?$/.test($(this).val())) {$(this).val($(this).val().replace(/^(\-)*(\d+)\.(\d).*$/,'$1$2.$3'));}

    }).bind("paste", function () {  //CTR+V事件处理
        $(this).val($(this).val().replace(/[^0-9.]/g, ''));
        $(this).val($(this).val().replace(/\.{2,}/g, "."));
        $(this).val($(this).val().replace(".","$#$").replace(/\./g,"").replace("$#$","."));
        if(!/^[0-9]+(.[0-9]{0,1})?$/.test($(this).val())) {$(this).val($(this).val().replace(/^(\-)*(\d+)\.(\d).*$/,'$1$2.$3'));}
    }).css("ime-mode", "disabled"); //CSS设置输入法不可用
});


//身份证号合法性验证
//支持15位和18位身份证号
//支持地址编码、出生日期、校验位验证
function IdentityCodeValid(code) {
    var city={11:"北京",12:"天津",13:"河北",14:"山西",15:"内蒙古",21:"辽宁",22:"吉林",23:"黑龙江 ",31:"上海",32:"江苏",33:"浙江",34:"安徽",35:"福建",36:"江西",37:"山东",41:"河南",42:"湖北 ",43:"湖南",44:"广东",45:"广西",46:"海南",50:"重庆",51:"四川",52:"贵州",53:"云南",54:"西藏 ",61:"陕西",62:"甘肃",63:"青海",64:"宁夏",65:"新疆",71:"台湾",81:"香港",82:"澳门",91:"国外 "};
    var tip = "";
    var pass= true;

    if(!code || !/^\d{6}(18|19|20)?\d{2}(0[1-9]|1[012])(0[1-9]|[12]\d|3[01])\d{3}(\d|X)$/i.test(code)){
        tip = "身份证号格式错误";
        pass = false;
    }

    else if(!city[code.substr(0,2)]){
        tip = "地址编码错误";
        pass = false;
    }
    else{
        //18位身份证需要验证最后一位校验位
        if(code.length == 18){
            code = code.split('');
            //∑(ai×Wi)(mod 11)
            //加权因子
            var factor = [ 7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2 ];
            //校验位
            var parity = [ 1, 0, 'X', 9, 8, 7, 6, 5, 4, 3, 2 ];
            var sum = 0;
            var ai = 0;
            var wi = 0;
            for (var i = 0; i < 17; i++)
            {
                ai = code[i];
                wi = factor[i];
                sum += ai * wi;
            }
            var last = parity[sum % 11];
            if(parity[sum % 11] != code[17]){
                tip = "校验位错误";
                pass =false;
            }
        }
    }
    return pass;
}

