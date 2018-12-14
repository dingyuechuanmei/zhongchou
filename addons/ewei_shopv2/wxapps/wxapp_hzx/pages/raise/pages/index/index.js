var header = require('../components/components.js');
var t = getApp(),
  a = t.requirejs("core");

Page({
  data: {
    pusher_list:[],
    swiper: {
      imgUrls: [],
      indicatorDots: true,
      indicatorActiveColor: '#ff6749',
      autoplay: true,
      interval: 5000,
      duration: 1000
    },
    approot: t.globalData.approot
  },

  // 表单提交形式
  formSubmit:function(e){
    var name = e.currentTarget.dataset.classify
    var appid = e.currentTarget.dataset.appid
    var path = e.currentTarget.dataset.path
    if (appid != undefined && appid != '' && appid != null) {
      wx.navigateToMiniProgram({
        appId: appid,
        // path: path,
        success(res) {
          // 打开成功
          console.log('success')
        },
        fail: function () {
          console.log('fail')
        },
        complete: function () {
          console.log('complete')
        },
      })
    } else {
      a.post('raise.save_fromid', {
        'form_id': e.detail.formId
      }, function (json) {});
      if (path != undefined && path != '' && path != null) {
        wx.navigateTo({
          url: path,
        })
      } else {
        wx.navigateTo({
          url: '../' + name + '/' + name,
        })
      }
    }
  },


  onLoad: function (options) {
    var me = this
    header.init.apply(this, [])
    // 获取数据
    a.post('raise', {}, function (json) {
      var banner_list = json.index_info.banner_list;
      delete json.index_info.banner_list;
      me.setData({
        'swiper.imgUrls': banner_list,
        'index_info': json.index_info
      })
    });
    a.post('raise.get_pusher_list', {}, function (json) {
      me.setData({
        pusher_list: json.pusher_list
      })
    })
  },
  // 众推搬移过来功能
  zhongtuiitemdetail: function (e) {
    wx.navigateTo({
      url: '../zhongtuiitemdetail/zhongtuiitemdetail?id=' + e.currentTarget.dataset.id
    })
  },


  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }

})
