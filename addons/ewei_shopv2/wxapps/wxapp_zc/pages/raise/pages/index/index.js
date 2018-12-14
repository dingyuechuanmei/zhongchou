var header = require('../components/components.js');
var t = getApp(),
  s = t.requirejs("wxParse/wxParse"),
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
      duration: 1000,
      userInfo: {},
      hasUserInfo: false,
      canIUse: wx.canIUse('button.open-type.getUserInfo'),
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


  onShow: function (options) {
    //t.isAuthUserInfo();
    var me = this
    header.init.apply(this, [])
    // 获取数据
    a.post('raise', {}, function (json) {
      var banner_list = json.index_info.banner_list;
      delete json.index_info.banner_list;
      s.wxParse("wxParseData", "html", json.index_info.raise_intro, me, "0")
      //console.log(me)
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

    if (t.globalData.userInfo) {
      this.setData({
        userInfo: t.globalData.userInfo,
        hasUserInfo: true
      })
    } else if (this.data.canIUse) {
      // 由于 getUserInfo 是网络请求，可能会在 Page.onLoad 之后才返回
      // 所以此处加入 callback 以防止这种情况
      t.userInfoReadyCallback = res => {
        this.setData({
          userInfo: res.userInfo,
          hasUserInfo: true
        })
      }
    } else {
      // 在没有 open-type=getUserInfo 版本的兼容处理
      wx.getUserInfo({
        success: res => {
          t.globalData.userInfo = res.userInfo
          this.setData({
            userInfo: res.userInfo,
            hasUserInfo: true
          })
        }
      })
    }

  },
  // 获取用户信息
  getUserInfo: function (e) {
    console.log(e)
    t.globalData.userInfo = e.detail.userInfo
    this.setData({
      userInfo: e.detail.userInfo,
      hasUserInfo: true
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
