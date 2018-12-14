var t = getApp(),
  a = t.requirejs("core");

Page({
  data: {
    approot: t.globalData.approot
  },
  onLoad: function (options) {
    var me = this;
    a.post('raise.verify_list', { id: options.id || 1 }, function (json) {
      console.log(json.verify_list)
      me.setData({
        verify_list: json.verify_list,
        total: json.total
      })
    });
  }
})