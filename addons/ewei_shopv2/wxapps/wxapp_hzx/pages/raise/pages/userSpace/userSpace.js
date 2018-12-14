var t = getApp(),
  a = t.requirejs("core");
var me 

Page({
  data: {
    id: '',
    isme: true,
    issign: false,
    counts: [1,1,1],
    itemon: ['on','',''],
    list0: [],
    list1: [],
    list2: []
  },
  nav: function (e) {
    var idx = e.currentTarget.dataset.idx
    var itemon = ['', '', '']
    itemon[idx] = 'on'
    me.setData({
      itemon: itemon
    })
  },
  changeSign: function () {
    me.setData({
      issign: true
    })
  },
  signend: function(e) {
    if (!e.detail.value) {
      return
    }
    a.post('forum.signature', { signature: e.detail.value}, function (json) {
      me.setData({
        issign: false
      })
      if (!json.error) {
        user(me.data.id)
      }
    }); 
  },
  focus: function(e) {
    a.post('forum.follow', { user_id: e.currentTarget.dataset.id}, function (json) {
      if (!json.error) {
        user(me.data.id)
      }
    });
  },
  del: function(e) {
    var counts_2 = me.data.counts[1]
    me.setData({
      counts: [1, counts_2, 1],
      list0: [],
      list2: []
    })
    a.post('forum.delete_posts', { forum_id: e.currentTarget.dataset.id }, function (json) {
      console.log(json)
      if (!json.error) {
        user(me.data.id)
        getlist(me.data.member.openid)
        getlist2(me.data.member.openid)
      }
    }); 
  },
  tofocus: function(e) {
    wx.navigateTo({
      url: './focus/focus?title=1&id=' + e.currentTarget.dataset.id,
    })
  },
  tofans: function (e) {
    wx.navigateTo({
      url: './focus/focus?title=2&id=' + e.currentTarget.dataset.id,
    })
  },
  // 跳转帖子详情
  todetail: function(e) {
    wx.navigateTo({
      url: '../postDetail/postDetail?id='+ e.currentTarget.dataset.id,
    })
  },
  onReachBottom: function() {
    wx.showLoading({
      title: '加载中',
    })
    var itemon = me.data.itemon
    var cur
    for (var i = 0; i < itemon.length; i++) {
      if (itemon[i]) {
        cur = i
      }
    }

    if (cur == 0) {
      getlist(me.data.member.openid)
    } else if (cur == 1) {
      getlist1(me.data.member.openid)
    } else if (cur == 2) {
      getlist2(me.data.member.openid)
    }
    
  },
  onLoad: function (options) {
    me = this
    // 获取自己的id
    var my_id = t.getCache('userinfo').id;

    if (my_id == options.id) {
      me.setData({
        isme: true
      })
    } else {
      me.setData({
        isme: false
      })
    }
    me.setData({
      id: options.id
    })
    // 获取个人中心
    user(options.id)
    //获取评论
    setTimeout(function () {
      getlist(me.data.member.openid)
      getlist1(me.data.member.openid)
      getlist2(me.data.member.openid)
    }, 500)
  }
})
// 获取帖子
function getlist(openid) {
  var pushdata = {
    page: me.data.counts[0],
    openid_: openid
  }
  a.post('forum.posts_list',pushdata, function (json) {
    wx.hideLoading()
    if (!json.error) {
      var counts = me.data.counts 
      counts[0] = json.list && json.list.length ? counts[0] + 1 : counts[0]
      me.setData({
        list0: json.list ? me.data.list0.concat(json.list) : me.data.list0,
        counts: counts
      })
    }
  })
}
function getlist1(openid) {
  var pushdata = {
    page: me.data.counts[1],
    openid_: openid
  }
  a.post('forum.review_list', pushdata, function (json) {
    wx.hideLoading()
    if (!json.error) {
      var counts = me.data.counts
      counts[1] = json.list && json.list.length ? counts[1] + 1 : counts[1]
      me.setData({
        list1: json.list ? me.data.list1.concat(json.list) : me.data.list1,
        counts: counts
      })
    }
  })
}
function getlist2(openid) {
  var pushdata = {
    page: me.data.counts[2],
    openid_: openid
  }
  a.post('forum.favorite_list', pushdata, function (json) {
    wx.hideLoading()
    if (!json.error) {
      var counts = me.data.counts
      counts[2] = json.list && json.list.length ? counts[2] + 1 : counts[2]
      me.setData({
        list2: json.list ? me.data.list2.concat(json.list) : me.data.list2,
        counts: counts
      })
    }
  })
}

function user(id) {
  // 获取个人中心
  a.post('forum.center', { user_id: id }, function (json) {
    if (!json.error) {
      var member = json.member
      member.is_follow = member.is_follow ? '取消关注' : '关注'
      me.setData({
        member: member
      })

    }
  });
}
