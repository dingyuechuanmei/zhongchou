var e = getApp(),
  app = getApp(),
  r = e.requirejs("core"),
  t = e.requirejs("wxParse/wxParse");
Page({
  data: {
    route: "member",
    icons: e.requirejs("icons"),
    member: {},
    menus: [],
    menus_length: 0,
    cartcount: 0,
    cur_page:"/pages/member/index/index",
    index_bottom: "",     // 首页dom 距离底部的高度
  },
  onLoad: function (r) {
    e.url(r),
      "" == e.getCache("userinfo") && wx.redirectTo({
        url: "/pages/message/auth/index"
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
  getInfo: function () {
    var e = this;
    var that = this;
    r.get("member", {}, function (r) {
      
      that.setData({
        menus: r.menus,
        menus_length: app.getJsonObjLength(r.menus.data),
        index_bottom: app.getJsonObjLength(r.menus.data),
        cartcount: r.cartcount,
      });

      0 != r.error ? wx.redirectTo({
        url: "/pages/message/auth/index"
      }) : e.setData({
        member: r,
        show: !0
      }),
        t.wxParse("wxParseData", "html", r.copyright, e, "5")
    })
  },
  onShow: function () {
    this.getInfo()
  },
  onShareAppMessage: function () {
    return r.onShareAppMessage()
  },

})