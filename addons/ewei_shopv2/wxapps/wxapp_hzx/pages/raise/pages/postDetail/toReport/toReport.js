var t = getApp(),
  a = t.requirejs("core");
var me 
Page({
  data: {
    cate_list: [],
    radio_id: '',
  },
  radioChange: function(e) {
    me.setData({
      radio_id: e.detail.value
    })
  },
  submit: function() {
    if (!me.data.radio_id) {
      return
    }
    var pushdata = {
      cate: me.data.radio_id,
      detch_id: me.data.id,
      type: 0
    }
    a.post('forum.recom_good', pushdata, function (json) {
      if (json.error) {
        wx.showToast({
          title: '提交失败',
        })
      } else {
        wx.showToast({
          title: '提交成功',
        })
        setTimeout(function() {
          wx.navigateBack();   //返回上一个页面
        },1500)
      }
    })
  },
  onLoad: function (options) {
    me = this
    me.setData({
      id: options.id
    })
    // 获取举报列表列表
    a.post('forum.report_cate', {}, function (json) {
      if (json.error != 0) {
        me.setData({
          cate_list: []
        })
      } else {
        me.setData({
          cate_list: json.cate_list
        })
      }
    });
  }
})