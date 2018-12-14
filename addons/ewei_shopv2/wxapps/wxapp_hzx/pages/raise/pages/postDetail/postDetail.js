var header = require('../components/components.js');
var t = getApp(),
  a = t.requirejs("core");
var me
Page({
  data: {
    id: '',
    agree_img: '../../resource/image/agree.png',
    inputdis: [true, false, false],
    reply_id: 0,
    placeholder:'写评论'
  },
  // togood: function(e) {
  //   wx.navigateTo({
  //     url: "pages/goods/detail/index" + e.currentTarget.dataset.id
  //   })
  // },
  tomy: function (e) {
    wx.navigateTo({
      url: '../userSpace/userSpace?id=' + e.currentTarget.dataset.id
    })
  },
  report: function (e) {
    wx.navigateTo({
      url: './toReport/toReport?id=' + me.data.id,
    })
  },
  //关注
  isfocus: function (e) {
    a.post('forum.follow', { user_id: e.currentTarget.dataset.mid }, function (json) {
      if (!json.error) {
        me.setData({
          'forum_info.is_follow': me.data.forum_info.is_follow.indexOf('取消') > -1 ? '关注' : '取消关注'
        })
      }
    });
  },
  // 点赞帖子
  agreetap: function () {
    a.post('forum.posts_prase', { forum_id: me.data.id }, function (json) {
      if (!json.error) {
        me.setData({
          'agree_img': me.data.agree_img.indexOf('agree-on') > -1 ? '../../resource/image/agree.png' : '../../resource/image/agree-on.png'
        })
        getDetail(me.data.id)
      }
    });
  },
  // 点赞评论
  agreecomment: function (e) {
    a.post('forum.review_prase', { review_id: e.currentTarget.dataset.review_id }, function (json) {
      console.log(json)
      if (!json.error) {
        // 获取帖子评论
        get_forum_review(me.data.id)
      }
    });
  },
  replyPost: function () {
    me.setData({
      inputdis: [false, true, false],
      reply_id: 0
    })
  },
  replyPost2: function (e) {
    var review_list = me.data.review_list
    review_list[e.currentTarget.dataset.idx].pop = false
    me.setData({
      inputdis: [false, false, true],
      review_list: review_list,
      reply_id: e.currentTarget.dataset.id,
      placeholder:'写回复',
    })



  },
  reply1: function (e) {
    me.setData({
      inputdis: [true, false, false]
    })
    var value = e.detail.value
    if (value == '') {
      return
    }
    var pushdata = {
      forum_id: me.data.id,
      reply_id: me.data.reply_id,
      context: value
    }
    a.post('forum.review_posts', pushdata, function (json) {
      if (!json.error) {
        // 获取帖子评论
        get_forum_review(me.data.id)
      }
    });
  },
  // 收藏帖子
  collectionPost: function () {
    a.post('forum.favorite', { forum_id: me.data.id }, function (json) {
      if (!json.error) {
        me.setData({
          'collect_img': me.data.collect_img.indexOf('star-on') > -1 ? '../../resource/image/star.png' : '../../resource/image/star-on.png'
        })
      }
    });
  },
  // 举报评论
  back_report: function (e) {
    var review_list = me.data.review_list
    review_list[e.currentTarget.dataset.idx].pop = true
    me.setData({
      review_list: review_list
    })
  },
  // 看跟多评论
  lookmore: function (e) {
    var review_list = me.data.review_list
    review_list[e.currentTarget.dataset.idx].isdis = true
    me.setData({
      review_list: review_list
    })
  },
  // 收起跟多评论 
  upmore: function (e) {
    var review_list = me.data.review_list
    review_list[e.currentTarget.dataset.idx].isdis = false
    me.setData({
      review_list: review_list
    })
  },
  // 返回首页
  backhome: function () {
    wx.switchTab({
      url: '../index/index',
    })
  },
  onReady: function () {
    console.log('ready');
    var that = this, me = this;
    var id = that.data.id;

    header.init.apply(this, [])

    // 获取帖子详情
    getDetail(id)
    // 获取帖子评论
    get_forum_review(id)

    // 获取热门帖子
    a.post('forum.hot_posts', {}, function (json) {
      if (json.error != 0) {
        me.setData({
          hot_list: {}
        })
      } else {
        me.setData({
          hot_list: json.forum_list
        })
      }
    });

  },
  onShow: function () {
    // getDetail(me.data.id)
  },
  onLoad: function (options) {
    me = this
    me.setData({
      id: options.id
    })
  },
  // 图片放大
  picshow: function (e) {
    var that = this
    var cur_url = e.currentTarget.dataset.src
    wx.previewImage({
      current: cur_url,
      urls: that.data.forum_info.thumbs,
    })
  }

})

// 获取帖子评论
function get_forum_review(id) {
  a.post('forum.forum_review', { forum_id: id }, function (json) {
    if (json.error != 0) {
      me.setData({
        review_list: []
      })
    } else {
      json.review_list = json.review_list ? json.review_list : []
      // 对是否点赞做处理
      var review_list = json.review_list
      var tmparr = []
      for (var i = 0; i < review_list.length; i++) {
        var item = review_list[i]
        item.is_prase = item.is_prase ? '../../resource/image/agree-on.png' : '../../resource/image/agree.png'
        var zitmparr = []
        for (var j = 0; j < item.child_review.length; j++) {
          var ziitem = item.child_review[j]
          ziitem.is_prase = ziitem.is_prase ? '../../resource/image/agree-on.png' : '../../resource/image/agree.png'
          zitmparr.push(ziitem)
        }
        item.child_review = zitmparr
        tmparr.push(item)
      }
      console.log(tmparr)
      me.setData({
        review_list: tmparr
      })
    }
  });
}
// 获取帖子详情
function getDetail(id) {
  a.post('forum.posts_info', { forum_id: id }, function (json) {
    if (json.error != 0) {
      me.setData({
        forum_info: {}
      })
    } else {
      var forum_info = json.forum_info
      forum_info.is_top = forum_info.is_top ? '置顶' : ''
      forum_info.is_follow = forum_info.is_follow ? '取消关注' : '关注'
      me.setData({
        forum_info: forum_info,
        agree_img: forum_info.is_praise ? '../../resource/image/agree-on.png' : '../../resource/image/agree.png',
        collect_img: forum_info.is_favorite ? '../../resource/image/star-on.png' : '../../resource/image/star.png'
      })
    }
  });
}