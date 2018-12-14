
var t = getApp(),
    a = t.requirejs("core");
var u = t.requirejs('util')
Page({
  data: {
    id:0,
    supportimgsrc: t.globalData.approot+'wxapp_attr/support.png',
    merchid:0,
    approot: t.globalData.approot,
    Isclick:false
  },
  supportimg: function (e) {
    var me = this
      let Isclick=me.data.Isclick
      if(!Isclick){
        a.post('raise.click_like_count', { id: me.data.id }, function (json) {
          console.log(json)
          me.setData({
            like_count: json.like_count,
            supportimgsrc: json.status ? me.data.approot + 'wxapp_attr/supporton.png' : me.data.approot + 'wxapp_attr/support.png',
            Isclick:true
          })
        })
      }
  },
  onLoad: function (options) {
    var me = this;

    this.setData({
      id:options.id
    });
    a.post('raise.get_pusher', { id: options.id},function(json){
        console.log(json)
      json.pusher.createtime = u.getLocalTime(json.pusher.createtime)
      me.setData({
        pusher: json.pusher,
        like_count: json.pusher.like_count,
        merchid: json.pusher.merchid ? json.pusher.merchid : json.pusher.shop_url,
        supportimgsrc: json.pusher.isclick ? me.data.approot + 'wxapp_attr/supporton.png' : me.data.approot + 'wxapp_attr/support.png',
        Isclick: json.pusher.isclick
      })
    });

    a.post('shop.merch.check_register', {}, function (json) {
      if(json.merchname != '' && json.merchname != undefined){
        me.setData({
          merchid:json.id
        });
      }
    });
  },
  redToMerchShop:function(){
    var that = this;
    console.log(this.data.merchid)
    wx.navigateTo({
      url: '/pages/index/index?merchid=' + that.data.merchid,
    })
  }
})



