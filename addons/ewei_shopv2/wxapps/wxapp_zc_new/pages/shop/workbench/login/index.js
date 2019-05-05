var app = getApp(),a = app.requirejs("core");
Page({

  /**
   * 页面的初始数据
   */
  data: {
    color: '#34AAFF',   //标题颜色
    tel:'10086-10087',   //客服电话
    backgroundimg: app.globalData.approot + 'wxapp_attr/bj.jpg',
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var _this = this;
    wx.getStorage({
      key: 'merchid',
      success: function(res) {
        if (!app.isEmpty(res.data)) {
          wx.redirectTo({
            url: '../admin/index',
          })
        }
      },
    })
  },
  name: function (t) {
    this.setData({
      name: t.detail.value
    })
  },
  password: function (t) {
    this.setData({
      password: t.detail.value
    })
  },
  sign: function (t) {
    wx.showLoading({
      title: "正在提交",
      mask: !0
    })
    wx.request({
      url: app.globalData.api + '&r=amanage.login',
      data: {
        username: this.data.name,
        password: this.data.password
      },
      success (res) {
        1 != res.data.error ? (wx.setStorageSync("merchid", res.data.merchid), wx.redirectTo({
          url: "../admin/index"
        })) : wx.showModal({
          title: "提示",
          content: res.data.message
        })
        wx.hideLoading();
      }
    })
  },
  /**
   * 微信登录
   */
  weixin: function () {
    var userinfo = app.getCache("userinfo");
    wx.showModal({
      title: '提示',
      content: '确定使用此微信号绑定的操作员身份登录吗？',
      success: function (e) {
        if (e.confirm) {
          wx.request({
            url: app.globalData.api + '&r=amanage.login.weixin_login',
            data: {
              openid: 'sns_wa_'+userinfo.openid,
            },
            success(res) {
              1 != res.data.error ? (wx.setStorageSync("merchid", res.data.merchid), wx.redirectTo({
                url: "../admin/index"
              })) : wx.showModal({
                title: "提示",
                content: res.data.message
              })
              wx.hideLoading();
            }
          })
        }
      }
    })
  },
  //拨打客服电话
  tel: function () {
    wx.makePhoneCall({
      phoneNumber: this.data.tel
    })
  }
})