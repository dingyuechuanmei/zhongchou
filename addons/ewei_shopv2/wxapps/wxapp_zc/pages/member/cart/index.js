// pages/member/cart/index.js
var t = getApp(),
  app = getApp(),
  e = t.requirejs("core"),
  i = t.requirejs("foxui"),
  a = t.requirejs("jquery");
Page({
  data: {
    route: "cart",
    icons: t.requirejs("icons"),
    merch_list: !1,
    list: !1,
    edit_list: [],
    diymenu: [],
    menus:[],
    menus_length: 0,
    cartcount: 0,
    cur_page: "/pages/member/cart/index",
    index_bottom: "",     // 首页dom 距离底部的高度
    userInfo: {},
    hasUserInfo: false,
    canIUse: wx.canIUse('button.open-type.getUserInfo'),
  },
  onLoad: function (e) {
    t.url(e)
var that=this

    if (t.globalData.userInfo) {
      this.setData({
        userInfo: t.globalData.userInfo,
        hasUserInfo: true
      })
    } else if (this.data.canIUse) {
      // 由于 getUserInfo 是网络请求，可能会在 Page.onLoad 之后才返回
      // 所以此处加入 callback 以防止这种情况
      t.userInfoReadyCallback = res => {
        this.setData({
          userInfo: res.userInfo,
          hasUserInfo: true
        })
      }
    } else {
      // 在没有 open-type=getUserInfo 版本的兼容处理
      wx.getUserInfo({
        success: res => {
          t.globalData.userInfo = res.userInfo
          this.setData({
            userInfo: res.userInfo,
            hasUserInfo: true
          })
        }
      })
    }
      setTimeout(function(){
        var ds = true,
          hhh = that.allgoods(!i);

        that.setData({
          edit_list: hhh,
          editcheckall: !ds
        })
      },1000)
  },
  // 获取用户信息
  getUserInfo: function (e) {
    console.log(e)
    t.globalData.userInfo = e.detail.userInfo
    this.setData({
      userInfo: e.detail.userInfo,
      hasUserInfo: true
    })
  },
  onShow: function () {
    this.get_cart()
    
  },
  get_cart: function () {
    var t,
      i = this;
    var that = this;
    e.get("member/cart/get_cart", {}, function (e) {
      that.setData({
        menus: e.menus,
        menus_length: app.getJsonObjLength(e.menus.data),
        index_bottom: app.getJsonObjLength(e.menus.data),
        cartcount: e.cartcount,
      });
      t = {
        show: !0,
        ismerch: !1,
        ischeckall: e.ischeckall,
        total: e.total,
        cartcount: e.total,
        totalprice: e.totalprice,
        empty: e.empty || !1
      },
        void 0 === e.merch_list ? (t.list = e.list || [], i.setData(t)) : (t.merch_list = e.merch_list || [], t.ismerch = !0, i.setData(t))
    })
  },
  edit: function (t) {
    var i,
      s = e.data(t),
      c = this;
    switch (s.action) {
      case "edit":
        this.setData({
          edit: !0
        });
        break;
      case "complete":
        this.allgoods(!1),
          this.setData({
            edit: !1
          });
        break;
      case "move":
        i = this.checked_allgoods().data,
          a.isEmptyObject(i) || e.post("member/cart/tofavorite", {
            ids: i
          }, function (t) {
            wx.showToast({
              title: '移动成功',
              duration: 2000
            })
            c.get_cart()
          });
        break;
      case "delete":
        i = this.checked_allgoods().data,
          a.isEmptyObject(i) || e.confirm("是否确认删除该商品?", function () {
            e.post("member/cart/remove", {
              ids: i
            }, function (t) {
              c.get_cart()
            })
          });
        break;
      case "pay":
        this.data.total > 0 && wx.navigateTo({
          url: "/pages/order/create/index"
        })
    }
  },
  checkall: function (t) {
    e.loading();
    var i = this,
      a = this.data.ischeckall ? 0 : 1;
    e.post("member/cart/select", {
      id: "all",
      select: a
    }, function (t) {
      i.get_cart(),
        e.hideLoading()
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
  update: function (t) {
    var i = this,
      a = this.data.ischeckall ? 0 : 1;
    e.post("member/cart/select", {
      id: "all",
      select: a
    }, function (t) {
      i.get_cart()
    })
  },
  number: function (t) {
    var a = this,
      s = e.pdata(t),
      c = i.number(this, t),
      r = s.id,
      o = s.optionid;
    1 == c && 1 == s.value && "minus" == t.target.dataset.action || s.value == s.max && "plus" == t.target.dataset.action || e.post("member/cart/update", {
      id: r,
      optionid: o,
      total: c
    }, function (t) {
      a.get_cart()
    })
  },
  selected: function (t) {
    e.loading();
    var i = this,
      a = e.pdata(t),
      s = a.id,
      c = 1 == a.select ? 0 : 1;
    e.post("member/cart/select", {
      id: s,
      select: c
    }, function (t) {
      i.get_cart(),
        e.hideLoading()
    })
  },
  allgoods: function (t) {
    var e = this.data.edit_list;

    if (!a.isEmptyObject(e) && void 0 === t)
      return e;
    if (t = void 0 !== t && t, this.data.ismerch)
      for (var i in this.data.merch_list)
        for (var s in this.data.merch_list[i].list)
          e[this.data.merch_list[i].list[s].id] = t;
    else
      for (var i in this.data.list)
        e[this.data.list[i].id] = t;
    return e
  },
  checked_allgoods: function () {
    var t = this.allgoods(),
      e = [],
      i = 0;
    for (var a in t)
      t[a] && (e.push(a), i++);
    return {
      data: e,
      cartcount: i
    }
  },
  editcheckall: function (t) {
    var i = e.pdata(t).check,
      a = this.allgoods(!i);
   
    this.setData({
      edit_list: a,
      editcheckall: !i
    }),
      this.editischecked()
  },
  editischecked: function () {

    var t = !1,
      e = !0,
      i = this.allgoods();

    for (var a in this.data.edit_list)
      if (this.data.edit_list[a]) {
        t = !0;
        break
      }
    for (var s in i)
      if (!i[s]) {
        e = !1;
        break
      }

    this.setData({
      editischecked: t,
      editcheckall: e
    })
    
  },
  edit_list: function (t) {
    var i = e.pdata(t),
      a = this.data.edit_list;

    void 0 !== a[i.id] && 1 == a[i.id] ? a[i.id] = !1 : a[i.id] = !0,
      this.setData({
        edit_list: a
      }),
      this.editischecked()
  },
  url: function (t) {
    var i = e.pdata(t);
    wx.navigateTo({
      url: i.url
    })
  },
  onShareAppMessage: function () {
    return e.onShareAppMessage()
  }
})