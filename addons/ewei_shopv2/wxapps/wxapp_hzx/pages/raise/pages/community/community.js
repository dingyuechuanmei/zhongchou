var header = require('../components/components.js');
var t = getApp(),
  a = t.requirejs("core");
var me 
Page({
  data: {
    swiper: {
      imgUrls: [],
      indicatorDots: true,
      indicatorActiveColor: '#ff6749',
      autoplay: true,
      interval: 5000,
      duration: 1000
    },
    imglist: ['../../resource/image/browse.png', '../../resource/image/comment.png','../../resource/image/share1.png'],
    forum_list: [],
    souContent: '',
    member: {},
    postPage: 1
  },
  backTop: function() {
    if (wx.pageScrollTo) {
      wx.pageScrollTo({
        scrollTop: 0
      })
    } else {
      wx.showModal({
        title: '提示',
        content: '当前微信版本过低，无法使用该功能，请升级到最新微信版本后重试。'
      })
    }
  },
  souinput: function(e) {
    me.setData({
      souContent: e.detail.value
    })
  },
  soubtn: function() {
    if (!me.data.souContent) {
      return
    } 
    wx.navigateTo({
      url: './postList/postList?keyword=' + me.data.souContent,
    })
  },
  getCate: function(e) {
    wx.navigateTo({
      url: '../smallClassify/smallClassify?id=' + e.currentTarget.dataset.id
    })
  },
  onReachBottom: function() {
    if (me.data.forum_list.length == 0) {
      return
    }
    var pushdata = {
      page: me.data.postPage,
      keyword: '',
      cate: ''
    }
    me.getForumList(pushdata, me)
  },
  posting: function() {
    wx.navigateTo({
      url: '../posting/posting',
    })
  },
  tomy: function() {
    wx.navigateTo({
      url: '../userSpace/userSpace?id=' + t.getCache('userinfo').id
    })
  },
  onShow: function() {
    var pushdata = {
      page: me.data.postPage,
      keyword: '',
      cate: ''
    }
    me.getForumList(pushdata, me,true)
  },
  onLoad: function() {
    me = this
    header.init.apply(this, [])
    
    // 获取banner图
    a.post('forum.forum_banner', {}, function (json) {
      if (json.error != 0) {
        me.setData({
          'swiper.imgUrls': []
        })
      } else {
        var imgsTmp = json.banner_list
        var imgs = []
        for (var i = imgsTmp.length - 1; i >= 0; i--) {
           imgs.push(imgsTmp[i].thumb)
        }
        me.data.swiper.imgUrls = imgs
        me.setData({
          'swiper.imgUrls': imgs 
        })
      }
    });
    // 获取分类列表
    a.post('forum.forum_cate', {}, function (json) {
      if (json.error != 0) {
        me.setData({
          cate_list: []
        })
      } else {
        json.cate_list = json.cate_list ? json.cate_list: []
        me.setData({
          cate_list: json.cate_list
        })
      }
    });
  },

  // 拨打电话
  call_phone:function(e){
    var mobile = e.currentTarget.dataset.mobile
    if(mobile != '' && mobile != undefined && mobile != null){
      wx.makePhoneCall({
        phoneNumber: mobile,
      })
    }else{
      wx.showModal({
        title: '提示',
        showCancel:false,
        content: '暂未联系方式',
      })
    }
  },

  // 分享
  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function (res) {
    var that = this
    var title = res.target.dataset.title;
    var id = res.target.dataset.id;
    if (res.from === 'button') {
      // 来自页面内转发按钮
      console.log(res.target)
    }
    return {
      title: title,
      path: '/pages/raise/pages/postDetail/postDetail?id=' + id,
      success: function (res) {
        // 转发成功
        // wx.showToast({
        // 	title: '分享成功',
        // })
      },
      fail: function (res) {
        // 转发失败
        // wx.showToast({
        // 	title: '取消分享',
        // })
      }
    }
  },

  // 图片预览
  bro_imgs:function(e){
    var that = this;
    var imgs = e.currentTarget.dataset.imgs;
    var img = e.currentTarget.dataset.img;
    wx.previewImage({
      current:img,
      urls: imgs,
    })
  }

})
