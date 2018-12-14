// pages/goods/list/index.js
var app = getApp(),
  core = app.requirejs("core"),
  ij = (app.requirejs("icons"),
  app.requirejs("jquery"));
Page({

  /**
   * 页面的初始数据
   */
  data: {
    title:'全部商品',
    cateid:0,
    category:[],
    child:[],
    parentid:0,
    goods:[],
    cur_page:"/pages/goods/list/index",
    menus: [],
    menus_length: 0,
    cartcount: 0,
    index_bottom: "",     // 首页dom 距离底部的高度
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    this.getCategory(0);
  },

  /**
   * 获取分类数据
   */
  setChecked: function (parentid,childid){
    var that = this;
    var category = that.data.category;

    for (var i = 0; i < app.getJsonObjLength(category); i++) {
      if(parentid == category[i].id){
        category[i].checked = 1;

        for (var j = 0; j < app.getJsonObjLength(category[i].child); j++) {
          if ((j == 0 && childid == 0) || childid == category[i].child[j].id) {
            if (childid == 0){
              that.getGoodsList(category[i].child[j].id);
            }else{
              that.getGoodsList(childid);
            }
            category[i].child[j].checked = 1;
          }else{
            category[i].child[j].checked = 0;
          }
        }

        that.setData({
          child: category[i].child
        });

      }else{
        category[i].checked = 0;
      }
    }

    that.setData({
      category:category
    });
    console.log(category);
  },

  /**
   * 点击父分类
   */
  changeParent: function (e) {
    var parentid = e.currentTarget.dataset.parentid;
    this.setData({
      parentid:parentid
    });
    this.setChecked(parentid,0);
  },

  /**
   * 点击子分类
   */
  changeChild:function(e){
    var parentid = this.data.parentid;
    var childid = e.currentTarget.dataset.childid;
    
    this.setChecked(parentid,childid);
  },

  /**
   * 获取一级分类-及二级分类
   */
  getCategory:function(){
    var that = this;
    var parentid = that.data.parentid;
    core.get("shop/get_category", {}, function (e) {
      console.log(e)

      if (app.getJsonObjLength(e.recommands) > 0){
        var recommands = [{
          'id':"0",
          'name': '推荐',
          'thumb':'',
          'advurl': e.set.advurl,
          'advimg':e.set.advimg,
          'child': e.recommands,
        }];
        e.category = recommands.concat(e.category);
      }

      // console.log(e.category)
      
      that.setData({
        category: e.category,
        menus: e.menus,
        menus_length: app.getJsonObjLength(e.menus.data),
        index_bottom: app.getJsonObjLength(e.menus.data),
        cartcount: e.cartcount,
      })

      if(parentid == 0){
        that.setChecked(parentid,0);
      }

    })
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

  /**
   * 获取商品列表
   */
  getGoodsList:function(childid){
    var that = this;
    var usermid = app.getCache('usermid');
    var merchid = 0;
    if (usermid != '' && usermid != undefined){
      merchid = usermid.merchid;
    }
    core.post("goods/get_list", {
      cate: childid,
      merchid:merchid,
    }, function (json) {
      console.log(json);
      that.setData({
        goods:json.list
      });
    })
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

})