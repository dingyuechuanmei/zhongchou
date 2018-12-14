var header = require('../components/components.js');
var t = getApp()
 var a = t.requirejs("core");
var app = getApp()
var s = t.requirejs("wxParse/wxParse");
Page({
  data: {
    userInfo: {},
    isss: "",
    member: {},
    hasUserInfo: false,
    canIUse: wx.canIUse('button.open-type.getUserInfo'),
    approot: t.globalData.approot,
    isopen:1,
    bottombar: {
      imageList: [
        '../../resource/image/index.png',
        '../../resource/image/myon.png'
      ],
      state: ['', 'on'],
			merch:{}
    },
    itemArray:[

      {
        classname: 'item3',
        imagesrc: t.globalData.approot + 'wxapp_attr/05@3x.png',
        title: '我的钱包',
        num: 50.00
      },
      {
        classname: 'item4',
        imagesrc: t.globalData.approot+'wxapp_attr/03@3x.png',
        title: '我的订单',
        num: 0
      },
      {
        classname: 'item5',
        imagesrc: t.globalData.approot+'wxapp_attr/04@3x.png',
        title: '我的优惠券',
        num: 0
      },
      {
        classname: 'item6',
        imagesrc: t.globalData.approot+'wxapp_attr/06@3x.png',
        title: '商家入驻',
        num: 0
      },

      // 社区贴吧
      {
        classname: 'item11',
        imagesrc: '../../resource/image/fabu.png',
        title: '我的发布',
        num: 0
      },
      {
        classname: 'item12',
        imagesrc: '../../resource/image/comment.png',
        title: '我的评论',
        num: 0
      },
      
      {
        classname:'item1',
        imagesrc:t.globalData.approot+'wxapp_attr/01@3x.png',
        title:'我参与的微客',
        num: 2
      },
      {
        classname: 'item2',
        imagesrc: t.globalData.approot+'wxapp_attr/02@3x.png',
        title: '我发起的救助',
        num: 1
      },

    

      // 商城个人中心
      {
        classname: 'item21',
        imagesrc: '/static/images/icon/like.png',
        title: '我的关注',
        num: 0
      },
      {
        classname: 'item22',
        imagesrc: '/static/images/icon/footprint.png',
        title: '我的足迹',
        num: 0
      },
      {
        classname: 'item23',
        imagesrc: t.globalData.approot + 'wxapp_attr/06@3x.png',
        title: '商家登陆',
        num: 0
      },

    ]
  },

  item1:function() {
    urlto('../myraise/myraise?item=0')
  },
  item2: function () {
    urlto('../myraise/myraise?item=1')
  },
  item3: function () {
    urlto('../../../order/index')
  },
  item4: function () {
    // url = "/pages/sale/coupon/my/index/index"
    urlto('/pages/sale/coupon/my/index/index')
  },
  item5: function () {
    var me = this
    urlto('../myraise/myraise?item=4')
  },
  item6: function () {
    urlto('../myraise/myraise?item=5')
  },
  item11: function () {
    
    urlto('../userSpace/userSpace?id=' + this.data.isss)
  },
  item12: function () {
    urlto('../userSpace/userSpace?id=' + this.data.isss +'&pin=1')
  },
  item21: function () {
    urlto('/pages/member/favorite/index')
  },
  item22: function () {
    urlto('../../../member/history/index')
  },
  item23: function () {
    urlto('../../../shop/manage/index')
  },
  item215: function () {
    urlto('/pages/member/log/index')
  },
	my_shop: function () {
    urlto('/pages/index/index?merchid=' + this.data.merch.id)
	},

  personalinformation:function() {
    urlto('../personalinformation/personalinformation')
  },
  
  onLoad: function (options) {

    header.init.apply(this, [])
    var me = this
    //获取用户数据
    a.post('raise.center', {}, function (json) {
      me.setData({
        member: json.member,
        'itemArray[0].num': json.join_count,
        'itemArray[1].num': json.starter_count,
        'itemArray[2].num': json.member.credit2,
				merch: json.merch,
        isopen: json.member.isopen
      })
      
    })
    //获取未回复评论数量
    a.post('forum.getReplyCount', {}, function (json) {
     me.setData({
       count:json.count
     });
      
    })
 // 获取用户信息
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

  },
  onShow(){
      this.getInfo()
    var that = this
    //获取用户数据
setTimeout(function(){
  a.post('raise.center', {}, function (json) {

    that.setData({
      member: json.member,
      'itemArray[0].num': json.join_count,
      'itemArray[1].num': json.starter_count,
      'itemArray[2].num': json.member.credit2,
      merch: json.merch
    })
    console.log(json.member.isopen);
  })
},500)
 
  },
  getInfo: function () {
    var e = this;
    var that = this;
    a.get("member", {}, function (r) {
        console.log(r)
      that.setData({
        isss:r.id,
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
        s.wxParse("wxParseData", "html", r.copyright, e, "5")
    })
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
})
function urlto(url) {
  wx.navigateTo({
    url: url,
  })
}
function urlback(detail) {
  wx.navigateBack({
    delta: detail
  })
}