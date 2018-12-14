var t = getApp(),
    a = t.requirejs("core");
var me 
Page({
  data: {
    goods_list: [],
    page: 1,
    arr:[]
  },
  checkboxChange: function (e) {
    me.setData({
      arr: e.detail.value
    })
  },
  sure: function() {
    wx.getStorage({
      key: 'goods_list',
      success: function (res) {
        var arr = res.data ? res.data : []
        wx.setStorage({
          key: "goods_list",
          data: arr.concat(me.data.arr),
          success: function () {
            wx.navigateBack();   //返回上一个页面
          }
        })
      }
    })
  },
  onReachBottom: function () {
    if (me.data.goods_list.length == 0) {
      return
    }
    getGoods()
  },
  onLoad: function() {
    // 获取推荐商品
    me = this
    getGoods()
  }
})

function getGoods() {
  a.post('forum.recom_good', { page: me.data.page }, function (json) {
    if (json.error != 0) {
      me.setData({
        goods_list: []
      })
    } else {
      json.goods_list = json.goods_list ? json.goods_list : []
      if (json.goods_list.length == 0) {
        return
      }
      me.data.page = me.data.page + 1
      me.setData({
        goods_list: me.data.goods_list.concat(json.goods_list),
      })
    }
  });
}
