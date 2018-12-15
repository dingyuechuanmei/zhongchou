// pages/shop/merch/user/index.js
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
    cate:0,
    pindex:0,
    psize:30,
    list:[],
    lat:"",
    lng:"",
    is_loadmore:true,
    type:"",
    sorttype:"",
    range:""
  },

  change_range:function(e){
    var range = e.currentTarget.dataset.range;
    this.setData({
      range:range,
      type:""
    });
    this.onShow();
  },
  
  change_cate: function (e) {
    var cate = e.currentTarget.dataset.cateid;
    console.log(cate);
    this.setData({
      cate: cate,
      type: ""
    });
    this.onShow();
  },

  change_sorttype: function (e) {
    var sorttype = e.currentTarget.dataset.sorttype;
    this.setData({
      sorttype: sorttype,
      type: ""
    });
    this.onShow();
  },

  click_type:function(e){
    console.log(e)
    var change_type = e.currentTarget.dataset.class;

    if(this.data.type == change_type){
      this.setData({ type: '' });
    }else{
      if (change_type == 'sortmenu_rule'){
        this.setData({ type:'sortmenu_rule'});
      } else if (change_type == 'sortmenu_cate'){
        this.setData({ type: 'sortmenu_cate' });
      } else if (change_type == 'sortmenu_sort'){
        this.setData({ type: 'sortmenu_sort' });
      }
    }



    console.log(this.data.type)


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
    console.log(options.cate)
    this.setData({
      cate: options.cate
    });
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
    this.getMerchCategory();
    this.getMerchUsers();
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
    var that = this;

    this.getMerchUsers();

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
  
  },
  // 打电话
  click_phone:function(e){
    var phone = e.currentTarget.dataset.href;
    wx.makePhoneCall({
      phoneNumber: phone
    })
  },

  // 获取分类信息
  getMerchCategory:function(){
    var that = this;
    core.get("shop/merch/getMerchCagetory", {}, function (json) {
      that.setData({
        category: json.category,
      });
    })

  },

  // 获取商户列表
  getMerchUsers:function(){
    var that = this;
    var keyword = this.data.keyword;
    var cate = this.data.cate;
    var pindex = this.data.pindex;
    var psize = this.data.psize;
    var lat = this.data.lat;
    var lng = this.data.lng;

    var sorttype = this.data.sorttype;
    var range = this.data.range;


    // 加载
    // wx.showNavigationBarLoading();
    
    // if (that.data.is_loadmore == false) {
    //     wx.hideNavigationBarLoading();
    //     return false;
    // }

    core.get("shop/merch/ajaxmerchuser", {
      psize:psize,
      pindex:pindex,
      cate:cate,
      keyword: keyword,
      lat:lat,
      lng:lng,
      sorttype:sorttype,
      range:range
    }, function (json) {
      
      console.log(json)

      that.setData({
        list: json.list
      });
      
    })
  },
  // 跳转链接
  location_href: function (e) {
    var url = e.currentTarget.dataset.href;
    var type = e.currentTarget.dataset.type || 0;
    if (url == '' || url == undefined) {
      return false
    }
    console.log(url)
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

  location:function(){
    console.log(1111)
  },

  //获取位置信息
  loadMuchDishData: function () {
      var that = this;
      
      var latitude = 0;
      var longitude = 0;
      wx.getLocation({
          type: 'gcj02',
          success: function (res) {
              latitude = res.latitude;
              longitude = res.longitude;
              that.setData({ lat: latitude, lng: longitude });
          },
          complete: function () {
              
          }
      });
  },

  //导航
  get_location_bind: function () {
     
      var that = this;
      var loc_lat = that.data.list.lat;
      var loc_lng = that.data.list.lng;

      if(loc_lat == "" || loc_lng == "" || loc_lat == undefined || loc_lng == undefined){
        wx.showModal({
          title: '提示',
          content: '当前门店未设置门店地址',
          showCancel:false,
          success: function(res) {
            if (res.confirm) {
              console.log('用户点击确定')
            } else if (res.cancel) {
              console.log('用户点击取消')
            }
          }
        })
        return false;
      }

      wx.showToast({
          title: '地图加载中',
          icon: 'loading',
          duration: 10000,
          mask: true
      });
      wx.openLocation({
          latitude: parseFloat(loc_lat),
          longitude: parseFloat(loc_lng),
          scale: 18,
          name: that.data.list.merchname,
          address: that.data.list.address
      });
  },
})