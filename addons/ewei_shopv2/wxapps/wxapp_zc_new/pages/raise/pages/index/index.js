var header = require('../components/components.js');
var t = getApp(),
  s = t.requirejs("wxParse/wxParse"),
  a = t.requirejs("core");

Page({
  data: {
    page: 1,
    hasMoreData: true,
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
    approot: t.globalData.approot,
    wmimg: t.globalData.approot + 'wxapp_attr/wm.png',
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
        },
        fail: function () {
        },
        complete: function () {
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
    //header.init.apply(this, [])
    // 获取数据
    wx.showLoading({
      title: 'loading...',
    })
    a.post('raise', {}, function (json) {
      var banner_list = json.index_info.banner_list;
      delete json.index_info.banner_list;
      s.wxParse("wxParseData", "html", json.index_info.raise_intro, me, "0")
      me.setData({
        'swiper.imgUrls': banner_list,
        'index_info': json.index_info
      })
    });
    me.get_pusher_list()
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
    wx.hideLoading()
  },
  /**
   * 页面隐藏/切入后台时触发----模拟器可以,真机不管用
   */
  onHide: function () {
    if (this.videoFirstContext != undefined) {
      this.videoFirstContext.pause()
    }
    if (this.videoContextList != undefined) {
      for (var i = 0; i < this.videoContextList.length; i++) {
        this.videoContextList[i].pause()
      }
    }
  },
  /**
 * 页面卸载时触发
 */
  onUnload: function () {
    if (this.videoFirstContext != undefined) {
      this.videoFirstContext.pause()
    }
    if (this.videoContextList != undefined) {
      for (var i = 0; i < this.videoContextList.length; i++) {
        this.videoContextList[i].pause()
      }
    }
  },
  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
    if (this.data.hasMoreData) {
      this.get_pusher_list()
    }
  },
  /**
   * 获取视频列表
   */
  get_pusher_list: function () {
    wx.showLoading({
      title: 'loading...',
    })
    var that = this
    var data = {
      page: that.data.page
    }
    a.post('raise.get_pusher_list', data, function (json) {
      setTimeout(function () {
        wx.hideLoading()
      }, 500)
      var pusher_list_tem = that.data.pusher_list;
      if (that.data.page == 1) {
        pusher_list_tem = []
      }
      var pusher_list = json.pusher_list
      if (that.data.page >= json.pageCount) {
        that.setData({
          pusher_list: pusher_list_tem.concat(pusher_list),
          hasMoreData: false
        })
      } else {
        that.setData({
          pusher_list: pusher_list_tem.concat(pusher_list),
          hasMoreData: true,
          page: that.data.page + 1
        })
      }
    })
  },
  // 获取用户信息
  getUserInfo: function (e) {
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
  //点击播放时关闭上个正在播放的视频,同时只能有一个视频在放
  videoPlay: function (e) {
    var index = e.target.dataset.index
    this.videoFirstContext = wx.createVideoContext('first')
    var index_info = this.data.index_info
    if (index != 'first') {
      index_info.videoshow = false
      this.setData({
        index_info: index_info
      })
      this.videoFirstContext.stop()
    } else if (index == 'first') {
      index_info.videoshow = true
      this.setData({
        index_info: index_info
      })
      this.videoFirstContext.play()
    }
    this.videoContextList = []
    var pusher_list = this.data.pusher_list
    for (var i = 0; i < pusher_list.length;i++) {
      this.videoContextList[i] = wx.createVideoContext('video-' + i)
      if (i != index) {
        pusher_list[i].videoshow = false
        this.videoContextList[i].stop()
      } else {
        pusher_list[i].videoshow = true
        this.videoContextList[i].play()
      }
      this.setData({
        pusher_list: pusher_list
      })
    }
  },
  //跳转到外卖平台
  redirectToWm:function () {
    wx.navigateToMiniProgram({
      appId: 'wx8edec291eba5f4b6',
      path: 'zh_cjdianc/pages/index/index'
    })
  },
  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }

})
