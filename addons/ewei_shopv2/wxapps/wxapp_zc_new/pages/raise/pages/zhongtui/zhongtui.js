var t = getApp(),
    a = t.requirejs("core");
var topitem = []

Page({
  data: {
    approot: t.globalData.approot,
    swiper: {
      imgUrls: [
        t.globalData.approot+'wxapp_attr/3@3x.png',
        t.globalData.approot+'wxapp_attr/3@3x.png',
        t.globalData.approot+'wxapp_attr/3@3x.png'
      ],
      indicatorDots: true,
      indicatorActiveColor: '#ff6749',
      autoplay: true,
      interval: 5000,
      duration: 1000
     
    },
    topitem:[]
  },
  zhongtuiitemdetail: function(e) {
    wx.navigateTo({
      url: '../zhongtuiitemdetail/zhongtuiitemdetail?id=' + e.currentTarget.dataset.id
    })
  },
  topitem:function(e) {
    var me = this
    var id = me.data.topitem[e.currentTarget.dataset.idx].id
    var topitemtmp = JSON.parse(JSON.stringify(topitem))
    topitemtmp[e.currentTarget.dataset.idx].state = 'on'
    me.setData({
      topitem: topitemtmp
    })

    //根据不同id获取不同分类列表的数据
    a.post('raise.get_pusher_list', { id: id }, function (json) {
      me.setData({
        pusher_list: json.pusher_list
      })
    });


  },
  onLoad: function (options) {
    var me = this
    // 获取数据顶部分类
    a.post('raise.get_pusher_category_all',{}, function (json) {
      console.log(json);
      topitem = []
      for (var i = 0; i < json.category_list.length; i++) {
        topitem.push({
          id: json.category_list[i].id,
          name: json.category_list[i].category,
          state: ''
        })
      }
      var topitemtmp = JSON.parse(JSON.stringify(topitem))
      topitemtmp[0].state = 'on'
      me.setData({
        'topitem': topitemtmp
      })
    })

    // 获取数据列表（第一个分类）
    a.post('raise.get_pusher_list',{}, function (json) {
      me.setData({
        pusher_list: json.pusher_list
      })
    })

  }
})