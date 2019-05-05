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
  },
  /**
   * 修改备注
   */
  formSubmit: function (e) {
    var data = {
      id: this.data.orderid,
      remark: e.detail.value.remark
    }
    wx.showModal({
      title: '提示',
      content: '确定为此订单取消发货吗？',
      success(e) {
        if (e.confirm) {
          wx.request({
            url: app.globalData.api + '&r=amanage.order.cancelsend',
            data: data,
            success(res) {
              if (res.data.status == 1) {
                wx.showToast({
                  title: '取消成功'
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