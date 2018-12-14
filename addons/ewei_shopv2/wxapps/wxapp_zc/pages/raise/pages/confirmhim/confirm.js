var t = getApp(),
  a = t.requirejs("core");

Page({
  data: {
    user: {}
  },
  bindPickerChange: function (e) {
    var me = this
    console.log(me.data.relation_list[e.detail.value])
    me.setData({
      'user.relation': me.data.relation_list[e.detail.value],
      'relation_id': me.data.relation_id[e.detail.value]
    })
  }, 
  nameinput: function(e) {
    this.setData({
      'user.name': e.detail.value
    })
  },
  cardinput: function (e) {
    this.setData({
      'user.card': e.detail.value
    })
  },
  detail: function(e) {
    this.setData({
      'user.detail': e.detail.value
    })
  },
  btn: function() {
    var me = this
    setTimeout(function() {
      var user = me.data.user
      if (!(user.relation && user.name && user.card && user.detail)) {
        prompt('请完善身份信息')
        return
      }
      if (!isCardNo(user.card)) {
        prompt('请填写有效的身份证号')
        return
      }


      // 证实柴火微客信息
      a.post('raise.verify_post', {
        starter_id: me.data.id,
        realname: user.name,
        card: user.card,
        relation_id: me.data.relation_id,
        intro: user.detail
       }, function (json) {
        if (json.error == 1) {
          prompt(json.message)
          return
        }
        wx.showToast({
          title: '提交成功',
        })
        setTimeout(function () {
          wx.navigateBack({
            delta: 1
          })
        }, 1000)
      });

    })
    
  },
  onLoad: function (options) {
    var me = this
    // 获取关系列表
    a.post('raise.relation_list', {}, function (json) {
      var relation_list = []
      var relation_id = []
      console.log(json)
      for (var i = 0; i < json.relation_list.length; i++) {
        relation_list.push(json.relation_list[i].relation)
        relation_id.push(json.relation_list[i].id)
      }
      me.setData({
        relation_list: relation_list,
        relation_id: relation_id,
        id: options.id
      })
      console.log(options.id)
    });
 

  
  }
})

//提示 
function prompt(meg) {
  wx.showModal({
    content: meg
  })
}

// 检测身份证号
function isCardNo(card) {  
  var pattern = /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/;
  return pattern.test(card); 
} 