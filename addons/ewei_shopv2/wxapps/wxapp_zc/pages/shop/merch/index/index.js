// pages/shop/merch/index/index.js
var app = getApp(),
  core = app.requirejs("core"),
  ij = (app.requirejs("icons"), 
  app.requirejs("jquery"));
Page({

  /**
   * 页面的初始数据
   */
  data: {
    category_swipe:[],
    category:[],
    merchuser:[],
    keyword:"",
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    
  },

  btn_serch:function(){
    console.log(this.data.keyword)
    var keyword = this.data.keyword;
    if (keyword == "" || keyword == undefined){
      return false
    }
    wx.redirectTo({
      url: '/pages/shop/merch/user/index?keyword='+keyword,
    })
  },

  bindinput:function(e){
    
    this.setData({
      keyword:e.detail.value
    });

  },

  // 获取首页数据
  getMerchIndex:function(){
    var that = this;
    core.get("shop/merch/getMerchIndex", {}, function (json) {
      that.setData({
        category_swipe: json.category_swipe,
        category: json.category,
        merchuser: json.merchuser,
      });
    })
  },

  location_href: function (e) {
    var url = e.currentTarget.dataset.href;
    var type = e.currentTarget.dataset.type || 0;
    
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
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
  
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    this.getMerchIndex();
  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {
  
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