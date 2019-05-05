var app = getApp(), a = app.requirejs("core");
Page({

  /**
   * 页面的初始数据
   */
  data: {

  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    app.merchantTabOnLoad(this);
    this.getInfo();
  },
  /**
   * 退出登录
   */
  logout: function () {
    wx.showModal({
      title: '提示',
      content: '当前已登录,确认要退出吗?',
      success(res) {
        if (res.confirm) {
          wx.removeStorage({
            key: 'merchid',
            success(res) {
              wx.redirectTo({
                url: '../login/index',
              })
            }
          })
        }
      }
    })
  },
  /**
   * 获取用户资料
   */
  getInfo: function () {
    var that = this
    wx.showLoading({
      title: 'loading...',
    })
    var data = {
      merchid: app.getMerchId()
    };
    wx.request({
      url: app.globalData.api + "&r=amanage.set",
      data: data,
      success(res) {
        that.setData({
          info: res.data.result.info
        })
      },
      complete() {
        wx.hideLoading()
      }
    })
  },
  /**
   * 修改用户信息
   */
  formSubmit: function (e) {
    wx.showLoading({
      title: "正在提交",
      mask: true
    })
    var that = this
    var data = {
      merchid: app.getMerchId(),
      realname: e.detail.value.realname,
      mobile: e.detail.value.mobile,
      password: e.detail.value.password,
      password2: e.detail.value.password2,
    }
    wx.request({
      url: app.globalData.api + "&r=amanage.set.saveInfo",
      data: data,
      success(res) {
        if (res.data.status == 1) {
          wx.showToast({
            title: '保存成功'
          })
          /**
           * 如果修改密码,重新登录
           */
          if (res.data.result.changepass == 1) {
            wx.removeStorage({
              key: 'merchid',
              success(res) {
                wx.redirectTo({
                  url: '../login/index',
                })
              }
            })
          }
          this.getInfo();
        } else {
          wx.showModal({
            title: "提示",
            content: res.data.result.message
          })
        }
      },
      complete() {
        wx.hideLoading();
      }
    })
  }
})