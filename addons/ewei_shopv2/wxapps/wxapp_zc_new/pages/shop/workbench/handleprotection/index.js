var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    radioIndex:0
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.setData({
      orderid: options.orderid
    })
  },
  radioChange: function (e) {
    this.setData({
      radioIndex: e.detail.value
    })
  },
  formSubmit: function (e) {
    if (this.data.radioIndex == 0) {
      wx.showToast({
        title: '请选择处理方式',
        icon: 'none'
      })
      return
    }
    wx.showLoading({
      title: 'loading...',
    })
    var data = {
      id: this.data.orderid,
      refundstatus: this.data.radioIndex
    }
    if (this.data.radioIndex == -1) {
      data.refundcontent = e.detail.value.refundcontent
    }
    wx.request({
      url: app.globalData.api + "&r=amanage.refund.submit",
      data: data,
      success(res) {
        console.log(res)
        if (res.data.status == 1) {
          setTimeout(function(){
            wx.showToast({
              title: '提交成功'
            })
            wx.redirectTo({
              url: '../protection/index'
            })
          },500)
        } else {
          setTimeout(function(){
            wx.showToast({
              title: res.data.result.message,
              icon: 'none'
            })
          },500)
        }
      },
      complete() {
        wx.hideLoading()
      }
    })
  }
})