var t = getApp(),
  a = t.requirejs("core");

Page({
  data: {
    amount: 0,
    meg:'',
    approot: t.globalData.approot
  },
  amount: function (e) {
    var self = this
    self.setData({
      amount: e.detail.value
    })
  },
  meg:function(e) {
    var self = this
    self.setData({
      meg: e.detail.value
    })
  },
  helphim: function () {
    var me = this
    // 帮助他
    a.post('raise.help_post', { starter_id: me.data.id, money: me.data.amount, hearten:me.data.meg} , function (json) {
      if (json.error == 1) {
        wx.showModal({title: '',content: json.message})
        return
      }
      var $_help_id = json.id;
      // 支付
      a.post('raise.pay', { help_id: $_help_id},function(json){
        if(json.error == 0){
          wx.requestPayment({
            'timeStamp': json.wechat.payinfo.timeStamp,
            'nonceStr': json.wechat.payinfo.nonceStr,
            'package': json.wechat.payinfo.package,
            'signType': json.wechat.payinfo.signType,
            'paySign': json.wechat.payinfo.paySign,
            'success': function (res) {
              a.post('raise.send_msg', { help_id: $_help_id},function(json){});
              wx.showToast({
                title: '帮助成功',
              })
              setTimeout(function () {
                wx.navigateBack({delta: 1});
              }, 1000)
            },
            'fail': function (res) {
              //a.alert(json.wechat.payinfo.message + "\n不能使用微信支付!")
              a.alert("支付失败")
            }
          })
        }
      });
    });
  },
  onLoad: function (options) {
    var me = this
    me.setData({
      id: options.id
    })
  }
})