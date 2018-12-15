var t = getApp(),
  a = t.requirejs("core");
var u = t.requirejs("util")
var topitem = []
Page({
  data: {
    approot: t.globalData.approot,
    is_merch:0,
    cur_id: 20,
    jia:0
  },
  topitem: function(e) {
    var me = this
    var id = me.data.topitem[e.currentTarget.dataset.idx].id
    var topitemtmp = JSON.parse(JSON.stringify(topitem))
    topitemtmp[e.currentTarget.dataset.idx].state = 'on'
    me.setData({
      'cur_id':id,
      'topitem': topitemtmp,
      jia: e.currentTarget.dataset.idx
    })
    //根据不同id获取不同分类列表的数据
    load_text(id, me);
  },
  zhongchouitemdetail: function(e) {
    wx.navigateTo({
      url: '../zhongchouitemdetail/zhongchouitemdetail?id=' + e.currentTarget.dataset.id + '&jia=' + this.data.jia,
    })
  },
  startzhongchou: function() {
    var me = this
    if (me.data.cur_id == 20 && me.data.is_merch != 1){
      // wx.showToast({title: '只有商户才能申请创资筹',icon: 'warn',duration: 2000});
      wx.showModal({
        title: '提醒',
        showCancel:false,
        content: '只有商户才能申请创资筹',
      })
      return;
    }
    wx.showModal({
      title: '微客说明',
      content: me.data.protocol || '',
      success: function (res) {
        if (res.confirm) {
          wx.navigateTo({
            url: '../startzhongchou/startzhongchou',
          })
        } else if (res.cancel) {
        }
      }
    })
  },
  onLoad: function (options) {
    var me = this
    // 获取数据顶部分类
    a.post('raise.get_starter_category_all', {}, function (json) {
      topitem = [];
      for (var i = 0; i < json.category_list.length; i++) {
        topitem.push({
          id: json.category_list[i].id,
          name: json.category_list[i].category,
          state: ''
        })
      }
      var topitemtmp = JSON.parse(JSON.stringify(topitem))
      topitemtmp[0].state = 'on'
      // 获取数据列表（第一个分类）
      load_text(topitemtmp[0].id, me);
      me.setData({
        'topitem': topitemtmp
      })
    })
    a.post('raise.judge_merch',{},function($json){
      if($json.error == 0){
        me.setData({
          'is_merch': $json.result
        })
      }
    });

    //获取微客协议
    a.post('raise.get_protocol', {}, function (json) {
      me.setData({
        protocol: json.protocol
      })
    })
  }
})
function load_text($id,$that){
  a.post('raise.starter_list', { category: $id }, function (json) {
    if (json.error == 0) {
      for (var i = 0; i < json.starter_list.length; i++) {
        json.starter_list[i].per = u.save_two_points(json.starter_list[i].refer_money / json.starter_list[i].target_money * 100)
      }
    } else {
      json.starter_list = [];
    }
    $that.setData({
      starter_list: json.starter_list
    })
  });
}



