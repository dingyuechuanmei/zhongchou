var t = getApp(),
  a = t.requirejs("core");
var me 

Page({
  data: {
    title: 1,
    focus_list: []
  },
  onLoad: function (options) {
    me = this
    // 修改标题
    var title
    if (options.title == 1) {
      title = '关注'
    } else if (options.title == 2) {
      title = '粉丝'
    }
    wx.setNavigationBarTitle({
      title: title,
    })
    me.setData({
      title: options.title,
      openid: options.openid 
    })
    if (options.title == 1) {
      // 关注列表
      a.post('forum.follow_list', { user_id: options.id }, function (json) {
        if (!json.error) {
          me.setData({
            focus_list: json.list
          })
        }
      });
    } else if (options.title == 2) {
      // 粉丝列表
      a.post('forum.fans_list', { user_id: options.id }, function (json) {
        if (!json.error) {
          me.setData({
            fans_list: json.list
          })
        }
      });
    }
  }
})