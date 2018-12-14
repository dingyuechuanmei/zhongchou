// pages/index/oneShops .js
var t = getApp(),
  app = getApp(),
  a = t.requirejs("core"),
  core = t.requirejs("core"),
  e = (t.requirejs("icons"), t.requirejs("wxParse/wxParse"));
var WxParse = require('../../utils/wxParse/wxParse.js');
var base64 = require('../../resource/js/base64.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    route: "home",
    icons: t.requirejs("icons"),
    shop: {},
    indicatorDots: false,
    autoplay: true,
    interval: 5000,
    duration: 500,
    circular: true,
    storeRecommand: [],
    total: 0,
    page: 1,
    loaded: false,
    loading: true,
    indicatorDotsHot: false,
    autoplayHot: true,
    intervalHot: 5000,
    durationHOt: 1000,
    circularHot: true,
    hotimg: "/static/images/hotdot.jpg",
    notification: "/static/images/notification.png",
    merchid:1,
    cur_page: "/pages/index/index",
    isdiypage: 0,
    diymenu: [],

    pageinfo: [],   // 页面所有信息
    page: [],     // 当前页面信息
    copyright: [],       // 版权信息
    diyitems: [],        // diy内容
    diyitems_length: 0,  // diy内容长度
    menus_length: [],    // 底部导航栏的长度
    index_bottom: "",     // 首页dom 距离底部的高度
    staron: '',
    value: '',
    options: [],
    shopset: [],
    menus: [],
    siteurl: '',
    cartcount: 0,// 购物车数量
  },
  getShop: function (ss) {
    var t = this;
    console.log(ss)
 
    a.get("shop/get_shopindex",{merchid:ss}, function (a) {
      console.log(ss)
      console.log(12121212)
      console.log(a)
      //console.log('shop index', a, new Date());

      if (a.copyright != '' && a.copyright != undefined) {
        e.wxParse("wxParseData", "html", a.copyright, t, "5")
      }

      t.setData({
        shop: a,
        menus: a.menus,
        menus_length: app.getJsonObjLength(a.menus.data),
        index_bottom: app.getJsonObjLength(a.menus.data),
        cartcount: a.cartcount,
      })
    })
  },
  onReachBottom: function () {
    this.data.loaded || this.data.storeRecommand.length == this.data.total || this.getRecommand()
  },
  getRecommand: function () {

    var t = this;
    t.setData({
      loading: true
    }),
      a.get("shop/get_recommand", {
        page: t.data.page,
        merchid: 141,//t.data.merchid,
      }, function (a) {
        // console.log(t.data.merchid)
        // console.log(a)
        var e = {
          loading: false,
          total: a.total
        };
        t.setData({
          loading: false,
          total: a.total,
          show: true
        }),
          a.list || (a.list = []),
          a.list.length > 0 && (t.setData({
            storeRecommand: t.data.storeRecommand.concat(a.list),
            page: a.page + 1
          }), a.list.length < a.pagesize && (e.loaded = true))
      })
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (a) {
    var that=this
 //   console.log(a.merchid)
    if (a.merchid != '' && a.merchid != undefined) {
     // console.log(a.merchid)
      that.setData({
        merchid: a.merchid
      });
    }
    t.url(a)
      setTimeout(function(){
        that.getShop(a.merchid)
        that.getRecommand()
      },600)
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

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  },
  // 跳转页面
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
  // 跳转页面
  click_location_href: function (e) {
    // 页面跳转
    var url = e.currentTarget.dataset.url;
    var type = e.currentTarget.dataset.type;
    var isswitch = e.currentTarget.dataset.isswitch;


    if (url != '') {
      if (isswitch > 0) {
        wx.switchTab({
          url: url,
        })
      } else {
        if (type == 2) {
          wx.redirectTo({
            url: url
          })
        } else {
          wx.navigateTo({
            url: url
          })
        }
      }
    }

  },
  navigato:function(e){
    var url = e.currentTarget.dataset.url
    var index  = e.currentTarget.dataset.index
    if(url==undefined){
      wx.showLoading({
        title: '商品未上架',
      })
      setTimeout(function () {
        wx.hideLoading()
      }, 2000)
    }
     if(index==1){
        wx.navigateTo({
          url: url,
 
        })
     }if(index==2){
       wx.navigateTo({
         url: url,

       })
     }if(index==3){
       wx.navigateTo({
         url: url,

       })
     }
  }
})
