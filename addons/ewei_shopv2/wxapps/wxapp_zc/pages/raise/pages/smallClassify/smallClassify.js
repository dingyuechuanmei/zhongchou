var header = require('../components/components.js');
var t = getApp(),
  a = t.requirejs("core");
var me 
Page({
  data: {
    forum_cate: {},
    forum_list: [],
		imglist: ['../../resource/image/browse.png', '../../resource/image/comment.png', '../../resource/image/share1.png'],
    postPage: 1
  },
  // 拨打电话
  call_phone: function (e) {
    var mobile = e.currentTarget.dataset.mobile
    if (mobile != '' && mobile != undefined && mobile != null) {
      wx.makePhoneCall({
        phoneNumber: mobile,
      })
    } else {
      wx.showModal({
        title: '提示',
        showCancel: false,
        content: '暂未联系方式',
      })
    }
  },
  // 发帖
  posting: function () {
    wx.navigateTo({
      url: '../posting/posting',
    })
  },
  // 回到顶部
  backTop: function () {
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
  onReachBottom: function () {
    if (me.data.forum_list.length == 0) {
      return
    }
    var pushdata = {
      page: me.data.postPage,
      keyword: '',
      cate: me.data.forum_cate.id
    }
    me.getForumList(pushdata, me)
  },
  onLoad: function (options) {
    header.init.apply(this, [])
    me = this
    //获取头部信息
    a.post('forum.forum_cateinfo', { cate_id: options.id}, function (json) {
      if (json.error != 0) {
        me.setData({
          forum_cate: {}
        })
      } else {
        json.forum_cate = json.forum_cate ? json.forum_cate : []

				wx.setNavigationBarTitle({
					title: json.forum_cate.title,
				})

        me.setData({
          forum_cate: json.forum_cate
        })
      }
    });
    // 获取动态列表

    var pushdata = {
      page: me.data.postPage,
      keyword: '',
      cate: options.id
    }
    me.getForumList(pushdata, me)

  },
    // 图片预览
  bro_imgs: function (e) {
    var that = this;
    var imgs = e.currentTarget.dataset.imgs;
    var img = e.currentTarget.dataset.img;
    wx.previewImage({
      current: img,
      urls: imgs,
    })
  }
})