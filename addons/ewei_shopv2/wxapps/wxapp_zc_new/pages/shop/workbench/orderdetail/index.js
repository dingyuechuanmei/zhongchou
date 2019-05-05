var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    arrow: app.globalData.approot + 'wxapp_attr/you.png'
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.setData({
      orderid: options.orderid
    })
    this.getOrderDetail();
  },
  /**
   * 修改发货信息
   */
  changeexpress: function (e) {
    wx.navigateTo({
      url: '../delivergoods/index?type=2&orderid=' + e.currentTarget.dataset.id,
    })
  },
  /**
   * 修改地址
   */
  modifyaddress: function (e) {
    wx.navigateTo({
      url: '../modifyaddress/index?orderid=' + e.currentTarget.dataset.id,
    })
  },
  /**
   * 会员详情
   */
  memberDetail: function (e) {
    wx.navigateTo({
      url: '../memberdetail/index?id=' + e.currentTarget.dataset.id,
    })
  },
  /**
   * 订单详情
   */
  getOrderDetail: function () {
    var that = this
    wx.showLoading({
      title: 'loading...',
    })
    var data = {
      id: that.data.orderid,
      merchid: app.getMerchId()
    };
    wx.request({
      url: app.globalData.api + "&r=amanage.order.detail",
      data: data,
      success(res) {
        that.setData({
          item: res.data.result.item,
          user: res.data.result.user,
          member: res.data.result.member
        })
      },
      complete() {
        wx.hideLoading()
        wx.hideNavigationBarLoading()
        wx.stopPullDownRefresh()
      }
    })
  }
})