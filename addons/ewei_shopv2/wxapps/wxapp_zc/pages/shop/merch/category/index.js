// pages/shop/merch/category/index.js
var app = getApp(),
  core = app.requirejs("core"),
  ij = (app.requirejs("icons"),
  app.requirejs("jquery"));

Page({

  /**
   * 页面的初始数据
   */
  data: {
    category:[],
    keyword:""
  },

  btn_serch: function () {
    var keyword = this.data.keyword;
    console.log(keyword)
    this.onShow();
  },

  bindinput: function (e) {

    this.setData({
      keyword: e.detail.value
    });

  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

  },

  // 获取首页数据
  getCategory: function () {
    var that = this;
    var keyword = this.data.keyword;
    core.get("shop/merch/getMerchCagetory", {keyword:keyword}, function (json) {
      console.log(json.category)
      that.setData({
        category: json.category,
      });
    })
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
  
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    this.getCategory()
  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {
  
  },

  location_href: function (e) {
    var url = e.currentTarget.dataset.href;
    var type = e.currentTarget.dataset.type || 0;
    console.log(url)
    console.log(type)
    if (url == '' || url == undefined) {
      return false
    }
    if (type == 0) {
      wx.navigateTo({
        url: url
      })
    } else if (type == 1) {
      wx.redirectTo({
        url: url
      })
    }
  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {
  
  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {
  
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
  
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
  
  }
})