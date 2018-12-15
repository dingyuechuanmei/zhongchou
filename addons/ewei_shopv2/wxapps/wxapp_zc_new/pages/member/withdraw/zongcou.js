// pages/member/withdraw/zongcou.js
var a = getApp(),
  e = a.requirejs("core"),
  t = a.requirejs("foxui"),
  n = a.requirejs("jquery");
Page({

  /**
   * 页面的初始数据
   */
  data: {
      openid:"",
      keti:""
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    console.log(options);
    this.setData({
      keti:options.keti,
      openid: options.openid,
      raise_service: options.raise_service
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
   tixian:function(){
     let openid = this.data.openid 
     let keti = this.data.keti
     let raise_service = this.data.raise_service
     let l = '确认要提现到微信钱包？';
     if (raise_service > 0) {
       l += '扣除手续费'+(keti*raise_service/100)+'元,实际到账金额'+(keti-(keti*raise_service/100))+'元';
     }
     wx.showModal({
       title: "提示",
       content: l,
       showCancel: true,
       success(res) {
         if (res.confirm) {
           var d = { "openid": openid, "apply_money": keti, "service": raise_service}
           e.post("raise/apply", d, function (a) {
             if (a.error == 0) {
               wx.showLoading({
                 title: a.message,
               })
               setTimeout(function () {
                 wx.navigateTo({
                   url: '/pages/member/log/index?type=2'
                 })

               }, 1000)
             } else {
               wx.showLoading({
                 title: a.message,
               })
               setTimeout(function () {
                 wx.hideLoading()
               }, 2000)
             }
           })
         }
       }
     })
   },
  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})