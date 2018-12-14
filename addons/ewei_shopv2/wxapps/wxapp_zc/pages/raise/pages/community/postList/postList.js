var header = require('../../components/components.js');
var t = getApp(),
  a = t.requirejs("core");
var me 
Page({
  data: {
    imglist: ['../../../resource/image/browse.png', '../../../resource/image/comment.png','../../../resource/image/share1.png'],
    forum_list: [],
    postPage: 1
  },
  onReachBottom: function () {
    if (me.data.forum_list.length == 0) {
      return
    }
    var pushdata = {
      page: me.data.postPage,
      keyword: me.options.keyword,
      cate: ''
    }
    me.getForumList(pushdata, me)
  },
  onLoad: function (options) {
    header.init.apply(this, [])
    me = this
    var pushdata = {
      page: me.data.postPage,
      keyword: options.keyword,
      cate: ''
    }
    me.getForumList(pushdata, me)

  }
})