var t = getApp(),
  a = t.requirejs("core");

function init() {
  var that = this;
  that.bottombar = function (e) {
    var name = e.currentTarget.dataset.name
    if (name == 'index') {
      wx.navigateBack({
        delta: 5
      })
      return
    }
    wx.navigateTo({
      url: '../' + name + '/' + name,
    })
  };
  that.getForumList = function (pushdata, me, isrefresh) {
    pushdata.page = isrefresh ? 1 : pushdata.page
    if (isrefresh) {
      me.setData({
        postPage: 1
      })
    }
    wx.showLoading({ title: '加载中' })
    a.post('forum.forum_list', pushdata, function (json) {
      wx.hideLoading()
      if (json.error != 0) {
        return
      } else {
        var list = json.forum_list
            list = list ? list : []
        if (list.length == 0) {
          return
        }
        for (var i = list.length - 1; i >= 0; i--) {
          if (list[i].is_top == 1) {
            list[i].is_top = '置顶'
          }
          if (list[i].is_top == 0) {
            list[i].is_top = ''
          }
        }
        var data_forum_list = isrefresh ? [] : me.data.forum_list
        me.setData({
          forum_list: data_forum_list.concat(json.forum_list),
          postPage: me.data.postPage + 1
        })
      }
    });
  }
  that.toPostDetail = function(e) {
    wx.navigateTo({
      url: '/pages/raise/pages/postDetail/postDetail?id=' + e.currentTarget.dataset.id
    })
  }
};
module.exports = {
  init: init
};

