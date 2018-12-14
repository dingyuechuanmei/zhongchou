var app = getApp(),
  core = app.requirejs("core"),
  ij = (app.requirejs("icons"), 
  app.requirejs("jquery"));
var WxParse = require('../../../utils/wxParse/wxParse.js');
var base64  = require('../../../resource/js/base64.js');

Page({

  /**
   * 页面的初始数据
   */
  data: {
    title: '商城首页',    // 页面名称
    pageinfo: [],   // 页面所有信息
    page: [],     // 当前页面信息
    copyright: [],       // 版权信息
    diyitems: [],        // diy内容
    diyitems_length: 0,  // diy内容长度
    menus_length: [],    // 底部导航栏的长度
    index_bottom: "",     // 首页dom 距离底部的高度
    staron:'',
    value:'',
    options:[],
    shopset:[],
    menus:[],
    siteurl:'',
    cartcount:0,// 购物车数量
    cur_page:"/pages/diypage/home/index",
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.setData({
      options: options
    });
    this.get_pageinfo();
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    
  },

  // 加入购物车
  buy:function(e){
    console.log(e);
    var that = this;
    var goodsid = e.currentTarget.dataset.goodsid;

    if(goodsid > 0){
      core.post("member/cart/add", {
        id: goodsid,
        total: 1,
        optionid: 0
      }, function (json) {
        //console.log(json);
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
  // 获取自定义页面信息
  get_pageinfo: function (){
    // 加载
    var This = this;
    var that = this;
    var options = that.data.options;
    var id = options.id ? options.id : 0;
    var menus_length = 0;
    var index_bottom = 0;
    core.get("diypage/index", {id:id}, function (json) {
      var pageinfo = json.page;
      var id = pageinfo.id;

      that.setData({
        cur_page: that.data.cur_page+"?id="+id
      });

      // console.log(that.data.cur_page);
      // console.log(pageinfo);

      // console.log(json.cartcount);

      if (pageinfo.menus != undefined && pageinfo.menus != ""){
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

      // console.log(app.getJsonObjLength(pageinfo.data.items))

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

        //console.log(that.data);

      }, that);
    })
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

  // 富文本点击a标签
  wxParseTagATap:function(e){
    // 页面跳转
    var url = e.currentTarget.dataset.src;
    var type = e.currentTarget.dataset.type;
    
    console.log(2); 

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

  inputBlur:function(e){
    var name = e.currentTarget.dataset.name;
    var value = e.detail.value;
    var data = '{"' + name + '":"' + value + '"}';
    this.setData(JSON.parse(data));
    console.log(data);
  },

})