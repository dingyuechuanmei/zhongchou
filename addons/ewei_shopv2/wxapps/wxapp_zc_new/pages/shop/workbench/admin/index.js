var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    merchid: null
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var _this = this;
    _this.setData({
      merchid: app.getMerchId()
    })
    _this.getMerchInfo();
    app.merchantTabOnLoad(_this)
  },
  /**
   * 页面相关事件处理函数-监听用户下拉刷新事件
   */
  onPullDownRefresh: function () {
    this.setData({
      info: {}
    })
    this.getMerchInfo();
  },
  //获取商户信息
  getMerchInfo: function () {
    wx.showLoading({
      title: 'loading...',
    })
    var _this = this;
    wx.request({
      url: app.globalData.api + "&r=amanage.get_merch_info",
      data: {
        merchid: _this.data.merchid
      },
      success(res){
        _this.setData({
          info: res.data.info
        })
        wx.hideLoading()
        wx.hideNavigationBarLoading()
        wx.stopPullDownRefresh()
      }
    })
  },
  //跳转到对应的订单页面
  allOder: function (event) {
    wx.navigateTo({
      url: '../order/index?selectedindex='+event.currentTarget.dataset.id
    })
  },
  //修改商户信息
  modifyInfo: function () {
    wx.navigateTo({
      url: 'modifyInfo?merchid='+this.data.merchid
    })
  },
  //财务管理
  financeManage: function () {
    wx.navigateTo({
      url: '../finance/index'
    })
  },
  //维权订单
  protection: function () {
    wx.navigateTo({
      url: '../protection/index'
    })
  },
  //商品列表
  goodslist: function () {
    wx.navigateTo({
      url: '../goodslist/index',
    })
  },
  pushlist: function () {
    wx.navigateTo({
      url: '../pushlist/index',
    })
  }
})