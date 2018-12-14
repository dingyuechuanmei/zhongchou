// index.js
var app = getApp(),
  core = app.requirejs("core"),
  ij = (app.requirejs("icons"), app.requirejs("jquery"));
var t = getApp(),
  a = t.requirejs("core"),
  e = t.requirejs("jquery");
Page({
  /**
   * 页面的初始数据
   */
  data: {
    route: "category",
    category: {},
    icons: app.requirejs("icons"),
    selector: 0,
    advimg: "",
    page:1,
    params: {},
    recommands: {},
    level: 0,
    back: 0,
    child: {},
    parent: {},
    diymenu: [],
    menus_length: 0,
    cartcount: 0,
    cur_page: "/pages/shop/caregory/index",
    index_bottom: "",     // 首页dom 距离底部的高度
    list: [],
  },

  /**
   * 分类函数--标签切换
   */
  tabCategory: function (t) {
    console.log(t);
    this.setData({
      selector: t.target.dataset.id,
      advimg: t.target.dataset.src,
      child: t.target.dataset.child,
      back: 0
    }),
      ij.isEmptyObject(t.target.dataset.child) ? this.setData({
        level: 0
      }) : this.setData({
        level: 1
      })
  },

  /**
   * 分类函数--更新当前数据
   */
  cateChild: function (t) {
    this.setData({
      parent: t.currentTarget.dataset.parent,
      child: t.currentTarget.dataset.child,
      back: 1
    })
  },

  /**
   * 分类函数--更新上级数据
   */
  backParent: function (t) {
    this.setData({
      child: t.currentTarget.dataset.parent,
      back: 0
    })
  },

  /**
   * 分类函数-获取并更新分类数据
   */
  getCategory: function () {
    var t = this;
    var that = this;
    core.get("shop/get_category", {}, function (e) {
      console.log(e)
      var index_bottom = that.getJsonObjLength(e.menus.data);
      var menus_length = that.getJsonObjLength(e.menus.data);
      t.setData({
        category: e.category,
        show: true,
        set: e.set,
        advimg: e.set.advimg,
        recommands: e.recommands,
        child: e.recommands,
        menus_length: menus_length,
        menus: e.menus,
        cartcount: e.cartcount,
        index_bottom: index_bottom > 0 ? "margin-bottom:3rem" : " ",
      })
    })
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
  getJsonObjLength: function (jsonObj) {
    var Length = 0;
    for (var item in jsonObj) {
      Length++;
    }
    return Length;
  },
  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    this.getCategory()
    //this.getList()
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
    return core.onShareAppMessage()
  },
  getList: function () {
    var t = this;
    t.setData({
      loading: true
    }),
     
      t.data.params.page = t.data.page,
      a.get("goods/get_list", t.data.params, function (a) {
        var e = {
          loading: false,
          total: a.total
        };
        a.list || (a.list = []),
          a.list.length > 0 && (e.page = t.data.page + 1, e.list = t.data.list.concat(a.list), a.list.length < a.pagesize && (e.loaded = true)),
          t.setData(e)
      })
  },
  onReachBottom: function () {
    this.data.loaded || this.data.list.length == this.data.total || this.getList()
  },
})