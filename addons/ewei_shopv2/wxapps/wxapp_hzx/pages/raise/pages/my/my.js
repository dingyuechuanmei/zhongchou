var header = require('../components/components.js');
var t = getApp(),
  a = t.requirejs("core");

Page({
  data: {
    approot: t.globalData.approot,
    bottombar: {
      imageList: [
        '../../resource/image/index.png',
        '../../resource/image/myon.png'
      ],
      state: ['', 'on']
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
    urlto('../../../public/coupon/index')
  },
  item5: function () {
    var me = this
    urlto('../myraise/myraise?item=4')
  },
  item6: function () {
    urlto('../myraise/myraise?item=5')
  },
  item11: function () {
    urlto('../userSpace/userSpace')
  },
  item12: function () {
    urlto('../userSpace/userSpace')
  },
  item21: function () {
    urlto('../../../member/favorite/index')
  },
  item22: function () {
    urlto('../../../member/history/index')
  },
  item23: function () {
    urlto('../../../shop/manage/index')
  },

  personalinformation:function() {
    urlto('../personalinformation/personalinformation')
  },
  
  onLoad: function (options) {
    var me = this
    header.init.apply(this, [])

    //获取用户数据
    a.post('raise.center', {}, function (json) {
      console.log(json)
      me.setData({
        member: json.member,
        'itemArray[0].num': json.join_count,
        'itemArray[1].num': json.starter_count,
        'itemArray[2].num': json.member.credit2
      })
      
    })

    //获取未回复评论数量
    a.post('forum.getReplyCount', {}, function (json) {
      console.log(json)
     me.setData({
       count:json.count
     });
      
    })


  }
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