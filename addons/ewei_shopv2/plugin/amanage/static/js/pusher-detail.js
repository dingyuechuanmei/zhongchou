define(['core', './sortable.js'], function (core, Sortable) {
    var modal = {paction: false};
    modal.initDetail = function (params) {
        modal.category   = params.category;
        modal.pusher     = params.pusher;
        modal.lastcateid = params.lastcateid;
        modal.initClick();
        modal.initSort()
    };
    modal.getVal = function (elm, int, isClass) {
        var mark = isClass ? "." : "#";
        var value = $.trim($(mark + elm).val());
        if (int) {
            if (value == '') {
                return 0
            }
            value = parseInt(value)
        }
        return value
    };
    modal.checkVal = function (elm, isClass) {
        var mark = isClass ? "." : "#";
        var checked = $(mark + elm).is(":checked") ? 1 : 0;
        return checked
    };
    modal.radioVal = function (name, int) {
        if (!name || name == '') {
            return int ? 0 : ''
        }
        var value = $("input[name='" + name + "']:checked").val();
        return value
    };
    modal.initSort = function () {
        new Sortable(thumbs, {draggable: 'li'})
    };
    modal.initClick = function () {
        console.log(core.getUrl('amanage/util/uploader'))

        $('.fui-uploader').uploader({
            uploadUrl: core.getUrl('amanage/util/uploader'),
            removeUrl: core.getUrl('amanage/util/uploader/remove')
        });


        $(".btn-submit").unbind('click').click(function () {
            if (modal.stop) {
                return
            }

            var obj = {
                id: modal.getVal('id', true),
                title: modal.getVal('title'),
                ifshow:  modal.checkVal('ifshow'),
                content:  modal.getVal('content'),
                category:  modal.lastcateid,
                pusher:  modal.pusher,
            };

            var thumbs = [];
            $("#thumbs li").each(function () {
                var filename = $.trim($(this).data('filename'));
                if (filename) {
                    thumbs.push(filename)
                }
            });
            if (thumbs.length < 1) {
                FoxUI.toast.show("请选择视频文件");
                return
            }
            obj.video = thumbs[0];

            if (obj.title == '') {
                FoxUI.toast.show("请填写众推标题");
                return
            }

            if (obj.category == '') {
                FoxUI.toast.show("请选择众推分类");
                return
            }

            if (obj.pusher == '') {
                FoxUI.toast.show("请选择众推发起者");
                return
            }

            FoxUI.confirm("确定保存编辑吗？", function () {
                modal.stop = true;
                var postUrl = obj.id < 1 ? "amanage/pusher/add" : "amanage/pusher/edit";
                core.json(postUrl, obj, function (json) {
                    console.log(json);
                    if (json.status == 1) {
                        FoxUI.toast.show("操作成功");
                        
                        location.href = core.getUrl('amanage/pusher');
                        
                        if (json.result.id) {
                            location.href = core.getUrl('amanage/pusher/edit', {id: json.result.id});
                            return
                        }
                    } else {
                        FoxUI.toast.show(json.result.message)
                    }
                    modal.stop = false
                }, true, true)
            })
        });

        $(".check-param").unbind('click').click(function () {
            var action = $(this).data('action');
            if (action) {
                modal.paction = action;
                modal.showParams()
            }
        });

        $(".cancel-params").unbind('click').click(function () {
            modal.hideParams()
        });

        $(".submit-params").unbind('click').click(function () {
            var action = modal.paction;
            if (!action) {
                modal.hideParams();
                return
            }

            if (action == 'member') {
                modal.pusher = $("input[name=member]:checked").val();
                $(".check-param[data-action='member']").find('.fui-cell-info').text($("input[name=member]:checked").parent().next().find('.subtitle').text() || "未选择")
            } else if (action == 'cate') {
                $(".check-param[data-action='cate']").find('.fui-cell-info').text(modal.lastcatename || "未分类")
            }
            modal.hideParams()
        });

        $(".bindclick").unbind('click').click(function () {
            var item = $(this).closest(".fui-list");
            var input = item.find("input");
            if (!input.is(":checked")) {
                input.prop('checked', 'checked').trigger('change')
            } else {
                if (input.attr('type') == 'checkbox') {
                    input.removeAttr('checked').trigger('change')
                }
            }
            var show = $(this).data('show');
            var hide = $(this).data('hide');
            if (hide) {
                $("." + hide).hide()
            }
            if (show) {
                $("." + show).show()
            }
        });

        $(".toggle").unbind('click').click(function () {
            var show = $(this).data('show');
            var hide = $(this).data('hide');
            if (hide) {
                $("." + hide).hide()
            }
            if (show) {
                $("." + show).show()
            }
        });

        $(document).off('click', '.cate-list nav');
        $(document).on('click', '.cate-list nav', function () {
            var catlevel = $(this).closest('.cate-list').data('catlevel');
            var item = $(this).closest(".item");
            var level = item.data('level');
            $(this).addClass('active').siblings().removeClass('active');
            modal.lastcateid = $(this).data('id');
            modal.lastcatename = $.trim($(this).text());
            
            console.log(modal.lastcateid);
            console.log(modal.lastcatename);
        });
    };

    modal.showParams = function () {
        if (!modal.paction) {
            return
        } else if (modal.paction == 'cate' || modal.paction == 'member') {
            $(".params-block .fui-navbar .submit-params").css('display', 'table-cell')
        }
        var params_item = $(".params-block").find(".param-" + modal.paction);
        if (params_item.length < 1) {
            return
        }
        params_item.show();
        $(".params-block").addClass('in');
        $(".btn-back").hide()
    };
    modal.hideParams = function () {
        $(".params-block .fui-navbar .nav-item").hide();
        $(".params-block").find(".param-item").hide();
        $(".params-block").removeClass('in');
        $(".btn-back").show();
        modal.paction = false
    };
    return modal
});