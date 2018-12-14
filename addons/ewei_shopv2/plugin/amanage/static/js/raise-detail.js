define(['core', './sortable.js'], function (core, Sortable) {
    var modal = {paction: false};
    modal.initDetail = function (params) {
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
    modal.initClick = function () {
        $(".btn-submit").unbind('click').click(function () {
            if (modal.stop) {
                return
            }
            var obj = {
                id: modal.getVal('id', true),
                title: modal.getVal('title'),
                video: modal.getVal('video'),
                content: modal.getVal('content'),
                ifshow: modal.checkVal('ifshow'),
                category:$('#category option:selected') .val()
            };
            if (obj.title == '') {
                FoxUI.toast.show("请填写众筹标题");
                return
            }
            if (obj.category == '') {
                FoxUI.toast.show("请选择众推分类");
                return
            }
            if (obj.video == '') {
                FoxUI.toast.show("视频上传失败，请重新上传");
                return
            }
            FoxUI.confirm("确定保存编辑吗？", function () {
                modal.stop = true;
                var postUrl = obj.id < 1 ? "amanage/goods/raisePusherAdd" : "amanage/goods/edit";
                core.json(postUrl, obj, function (json) {
                    if (json.status == 1) {
                        FoxUI.toast.show("操作成功");
                        if (json.result.id) {
                            location.href = core.getUrl('amanage/goods/edit', {id: json.result.id});
                            return
                        }
                    } else {
                        FoxUI.toast.show(json.result.message)
                    }
                    modal.stop = false
                }, true, true)
            })
        });
    };

    return modal
});