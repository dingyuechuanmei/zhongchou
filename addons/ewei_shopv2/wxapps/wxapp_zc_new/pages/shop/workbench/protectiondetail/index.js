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
    this.getRefundDetail();
  },
  /**
   * 维权处理
   */
  handleprotection: function () {
    wx.navigateTo({
      url: '../handleprotection/index?orderid='+this.data.orderid,
    })
  },
  /**
   * 订单详情
   */
  getRefundDetail: function () {
    var that = this
    wx.showLoading({
      title: 'loading...',
    })
    var data = {
      id: that.data.orderid
    };
    wx.request({
      url: app.globalData.api + "&r=amanage.refund",
      data: data,
      success(res) {
        that.setData({
          refund: res.data.result.refund,
          item: res.data.result.item,
          member: res.data.result.member
        })
      },
      complete() {
        wx.hideLoading()
      }
    })
  }
})