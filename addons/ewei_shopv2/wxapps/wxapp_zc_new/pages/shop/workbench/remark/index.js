var app = getApp();
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
    this.setData({
      orderid: options.orderid
    })
    this.remarksaler()
  },
  remarksaler:function () {
    wx.showLoading({
      title: 'loading...',
    })
    var _this = this;
    wx.request({
      url: app.globalData.api + '&r=amanage.order.remarksaler',
      data: {
        id: _this.data.orderid
      },
      success(res) {
        _this.setData({
          item: res.data.result.item
        })
      },
      complete() {
        wx.hideLoading()
      }
    })
  },
  /**
   * 修改备注
   */
  formSubmit: function (e) {
    var data = {
      id: this.data.orderid,
      remarksaler: e.detail.value.remarksaler
    }
    wx.showModal({
      title: '提示',
      content: '确定保存修改吗',
      success(e) {
        if (e.confirm) {
          wx.request({
            url: app.globalData.api + '&r=amanage.order.remarksaler_post',
            data: data,
            success(res) {
              if (res.data.status == 1) {
                wx.showToast({
                  title: '保存成功'
                })
                setTimeout(function () {
                  wx.redirectTo({
                    url: '../order/index',
                  })
                }, 2000)
              } else {
                wx.showToast({
                  title: res.data.result.message,
                  icon: 'loading',
                  mask: true
                }), setTimeout(function () {
                  wx.hideToast()
                }, 2000)
              }
            }
          })
        }
      }
    })
  }
})