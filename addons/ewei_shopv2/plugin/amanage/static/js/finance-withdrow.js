define(['core'], function (core) {
   
    var modal = {
    	url:''
    };

    modal.init = function(params){
    	modal.url = params.url;
    	modal.initClick();
    }

    modal.initClick = function () {
    	var checked_applytype = $('#applytype').find("option:selected").val();
        if (checked_applytype == 2) {
            $('.ab-group').show();
            $('.alipay-group').show();
            $('.bank-group').hide();
        } else if (checked_applytype == 3) {
            $('.ab-group').show();
            $('.alipay-group').hide();
            $('.bank-group').show();
        } else {
            $('.ab-group').hide();
            $('.alipay-group').hide();
            $('.bank-group').hide();
        }

        $('#applytype').change(function () {
            var applytype = $(this).find("option:selected").val();
            if (applytype == 2) {
                $('.ab-group').show();
                $('.alipay-group').show();
                $('.bank-group').hide();
            } else if (applytype == 3) {
                $('.ab-group').show();
                $('.alipay-group').hide();
                $('.bank-group').show();
            } else {
                $('.ab-group').hide();
                $('.alipay-group').hide();
                $('.bank-group').hide();
            }
        });

        $(".submit-params").on("touchstart",function(){
        	console.log(222);
        	$(".submit-params-input").submit();
        });

        $(".cancel-params").unbind("click").click(function(){
        	window.history.back();
        });

        $('form').submit(function(){
            var html = '';
            var applytype = $('#applytype').find("option:selected").val();
            var typename = $('#applytype').find("option:selected").html();

            if (applytype == undefined) {
                $('form').attr('stop',1),FoxUI.toast.show('未选择提现方式，请您选择提现方式后重试!!');
                return false;
            }

            if (applytype == 0) {
                html = typename;
            } else if (applytype == 2) {
                if ($('#realname').isEmpty()) {
                    $('form').attr('stop',1),FoxUI.toast.show('请填写姓名!');
                    return false;
                }
                if ($('#alipay').isEmpty()) {
                    $('form').attr('stop',1),FoxUI.toast.show('请填写支付宝帐号!');
                    return false;
                }
                if ($('#alipay1').isEmpty()) {
                    $('form').attr('stop',1),FoxUI.toast.show('请填写确认帐号!');
                    return false;
                }
                if ($('#alipay').val() != $('#alipay1').val()) {
                    $('form').attr('stop',1),FoxUI.toast.show('支付宝帐号与确认帐号不一致!');
                    return false;
                }
                realname = $('#realname').val();
                alipay = $('#alipay').val();
                alipay1 = $('#alipay1').val();
                html = typename + "?<br>姓名:" + realname + "<br>支付宝帐号:" + alipay;
            } else if (applytype == 3) {
                if ($('#realname').isEmpty()) {
                    $('form').attr('stop',1),FoxUI.toast.show('请填写姓名!');
                    return false;
                }
                if ($('#bankcard').isEmpty()) {
                    $('form').attr('stop',1),FoxUI.toast.show('请填写银行卡号!');
                    return false;
                }
                if (!$('#bankcard').isNumber()) {
                    $('form').attr('stop',1),FoxUI.toast.show('银行卡号格式不正确!');
                    return false;
                }
                if ($('#bankcard1').isEmpty()) {
                    $('form').attr('stop',1),FoxUI.toast.show('请填写确认卡号!');
                    return false;
                }
                if ($('#bankcard').val() != $('#bankcard1').val()) {
                    $('form').attr('stop',1),FoxUI.toast.show('银行卡号与确认卡号不一致!');
                    return false;
                }
                realname = $('#realname').val();
                bankcard = $('#bankcard').val();
                bankname = $('#bankname').find("option:selected").html();
                html = typename + "?<br>姓名:" + realname + "<br>" + bankname + " 卡号:" + $('#bankcard').val();
            }

            if (applytype < 2) {
                var confirm_msg = '确认要' + html + "?";
            } else {
                var confirm_msg = '确认要' + html;
            }

            $('form').attr('stop',1);
            FoxUI.confirm(confirm_msg, function () {
                $('form').removeAttr('stop');
                $('form').submit();
            });

        });

    }

    return modal
});