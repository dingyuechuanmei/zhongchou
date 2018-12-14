var t = getApp(),
  a = t.requirejs("core");
var u = t.requirejs('util')
Page({
  data: {
    approot: t.globalData.approot,
    afull: '150rpx',
    imglist: [
      t.globalData.approot+'wxapp_attr/2@3x.png',
      t.globalData.approot+'wxapp_attr/2@3x.png',
      t.globalData.approot +'wxapp_attr/2@3x.png',
      t.globalData.approot +'wxapp_attr/2@3x.png',
      t.globalData.approot +'wxapp_attr/2@3x.png',
      t.globalData.approot +'wxapp_attr/2@3x.png'
    ],
    imgtextidx: 0,
    textlist: [
      '加油',
      '希望赶紧好',
      '3',
      '4',
      '5',
      '6'
    ]
  },
  imagetext: function(e) {
    this.setData({
      imgtextidx: e.currentTarget.dataset.idx
    })
  },
  afull:function() {
    var self = this
    self.setData({
      afull: ''
    })
  },
  helpconfirm: function(e) {
    // if(this.data.starter.category == 20){
    //   wx.navigateTo({
    //     url: '../../../index/index?merchid=' + this.data.starter.merch_id
    //   })
    // }else{
      wx.navigateTo({
        url: '../helphim/helphim?id=' + e.currentTarget.dataset.id
      })
    // }
  },
  confirm: function(e) {
    wx.navigateTo({
      url: '../confirmhim/confirm?id=' + e.currentTarget.dataset.id
    })
  },
  confirmlist: function(e) {
    wx.navigateTo({
      url: '../confirmlist/confirmlist?id=' + e.currentTarget.dataset.id
    })
  },
  onLoad: function (options) {
    var me = this;

    console.log(options.id);

    // 获取发起者，项目详情等，页面上部分数据
    a.post('raise.get_starter', { id: options.id},function(json){
      json.starter.per = u.save_two_points(json.starter.refer_money / json.starter.target_money * 100)
      me.setData({
        starter: json.starter,
      })
    });
    // 获取证实人列表
    a.post('raise.verify_list', { id: options.id }, function (json) {
      if (json.verify_list) {
        if (json.verify_list.length > 6) {
          json.verify_list.length = 6
        }
      } 
      me.setData({
        verify_list: json.verify_list,
        total: json.total
      })
    });
    //获取帮助列表
    a.post('raise.help_list', { id: options.id }, function (json) {
      if (json.error == 1) {
        me.setData({
          help_list: [],
        })
        return
      }
      console.log(json.help[0].createtime)
      for (var i = 0; i < json.help.length; i++) {
        json.help[i].createtime = u.get_data_ago(json.help[i].createtime)
      }
      me.setData({
        help_list: json.help,
      })
    });
  },
  
  // 浏览图片 zhaoxin 20180413
  bro_img:function(e){
    var taht = this
    var cur_img = e.currentTarget.dataset.img
    wx.previewImage({
      current: cur_img,
      urls: taht.data.starter.thumbs,
    })
  }

})