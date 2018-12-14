//index.js
var t = getApp(),
  app = getApp(),
  a = t.requirejs("core"),
  core = t.requirejs("core"),
  e = (t.requirejs("icons"), t.requirejs("wxParse/wxParse"));
var WxParse = require('../../utils/wxParse/wxParse.js');
var base64 = require('../../resource/js/base64.js');
Page({
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
    merchid: 0,
    cur_page:"/pages/index/index",
    isdiypage:0,
    diymenu:[],

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
  // 加入购物车
  buy: function (e) {
    var that = this;
    var goodsid = e.currentTarget.dataset.goodsid;

    if (goodsid > 0) {
      core.post("member/cart/add", {
        id: goodsid,
        total: 1,
        optionid: 0
      }, function (json) {
        wx.showToast({
          title: '添加成功',
          icon: 'success',
          duration: 1000
        })
        that.setData({
          cartcount: json.cartcount
        });
      })
    }
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
      if (isswitch > 0){
        wx.switchTab({
          url: url,
        })
      }else{
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
  // 富文本点击a标签
  wxParseTagATap: function (e) {
    // 页面跳转
    var url = e.currentTarget.dataset.src;
    var type = e.currentTarget.dataset.type;

    if (url != '') {
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
  },

  inputBlur: function (e) {
    var name = e.currentTarget.dataset.name;
    var value = e.detail.value;
    var data = '{"' + name + '":"' + value + '"}';
    this.setData(JSON.parse(data));
  },
  getShop: function () {
    var t = this;
    a.get("shop/get_shopindex", { merchid: t.data.merchid }, function (a) {
        
        if(a.copyright != '' && a.copyright != undefined){
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
        merchid: t.data.merchid,
      }, function (a) {
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
  onLoad: function (a) {
    if (a.merchid != '' && a.merchid != undefined) {
      this.setData({
        merchid: a.merchid
      });
    }
    t.url(a)
  },
  get_isdiyhome:function(){
    var that = this;
    a.get("shop/get_isdiyhome", {}, function (json) {
      if(json.isdiyhome == 1){
        that.setData({
          isdiypage:1
        });
        that.get_pageinfo();
      }
    })
  },
  // 获取自定义页面信息
  get_pageinfo: function () {
    // 加载
    var that = this;
    var options = that.data.options;
    var id = options.id ? options.id : 0;
    var menus_length = 0;
    var index_bottom = 0;
    core.get("diypage/index", { id: id }, function (json) {
      var pageinfo = json.page;
      var id = pageinfo.id;
      if (pageinfo.menus != undefined && pageinfo.menus != "") {
        menus_length = app.getJsonObjLength(pageinfo.menus.data);
        index_bottom = app.getJsonObjLength(pageinfo.menus.data);
        for (var i in pageinfo.menus.data) {
          if ('../index/index?id=' + id == pageinfo.menus.data[i].linkurl) {
            pageinfo.menus.data[i].checked = 'on';
          } else {
            pageinfo.menus.data[i].checked = '0';
          }
        }
      }
      that.setData({
        pageinfo: pageinfo,
        copyright: pageinfo.copyright,
        page: pageinfo.data.page,
        diyitems: pageinfo.data.items,
        diyitems_length: app.getJsonObjLength(pageinfo.data.items),
        menus_length: menus_length,
        menus: pageinfo.menus,
        cartcount: json.cartcount,
        index_bottom: index_bottom > 0 ? "margin-bottom:3rem" : " ",
        shopset: pageinfo.shopset,
        siteurl: pageinfo.siteurl,
      });

      app.getTemplate(function (json) {

        // 设置导航标题
        wx.setNavigationBarTitle({
          title: json.data.page.title,
        });

        // 渲染数据
        that.setData({
          diyitems: json.data.diyitems,
        });

      }, that);
    })
  },
  onShow: function () {

    var a = t.getCache("sysset");
    wx.setNavigationBarTitle({
      title: a.shopname || "商城首页"
    }),
    
    this.getShop(),
    this.getRecommand()

    this.get_isdiyhome()
  },
  onShareAppMessage: function () {
    return a.onShareAppMessage()
  },
  imagesHeight: function (t) {
    var a = t.detail.width,
      e = t.detail.height,
      o = t.target.dataset.type,
      i = {},
      s = this;
    wx.getSystemInfo({
      success: function (t) {
        i[o] = t.windowWidth / a * e,
          (!s.data[o] || s.data[o] && i[o] < s.data[o]) && s.setData(i)
      }
    })
  },
})