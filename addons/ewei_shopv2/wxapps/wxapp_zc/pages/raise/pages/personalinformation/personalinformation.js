var t = getApp(),
  a = t.requirejs("core");

Page({
  data: {
    btn_code_val: '获取验证码',
    btncode: true,
    approot: t.globalData.approot,
    displayArray: ['on', '', ''],
    itemArray: [
      {
        title: '头像', imagesrc: '', text: ''
      },
      {
        title: '昵称', imagesrc: '', text: ''
      },
      {
        title: '绑定手机号', imagesrc: '', text: '暂未绑定'
      },
      {
        title: '地址管理', imagesrc: '', text: ''
      }
    ]
  },
  item: function (e) {
    var self = this
    var idx = e.currentTarget.dataset.index
    // if (idx == 0) {
    //   wx.chooseImage({
    //     count: 1, 
    //     sizeType: ['original', 'compressed'], 
    //     sourceType: ['album', 'camera'],
    //     success: function (res) {
    //       var itemArray = self.data.itemArray
    //       itemArray[idx].imagesrc = res.tempFilePaths[0]
    //       self.setData({
    //         itemArray: itemArray
    //       })
    //     }
    //   })
    // }
    // if (idx == 1) {
    //   self.setData({
    //     displayArray: displayArray(idx)
    //   })
    //   wx.setNavigationBarTitle({
    //     title: '昵称'
    //   })
    // }
    if (idx == 2) {
      self.setData({
        displayArray: displayArray(idx)
      })
      wx.setNavigationBarTitle({
        title: '绑定手机号'
      })
    }
    // 此处为点击地址管理的逻辑
    if (idx == 3) {
      wx.navigateTo({
        url: '../../../member/address/index',
      })
    }
  },
  // idx2input:function(e) {
  //   var self = this
  //   self.setData({
  //     idx2value: e.detail.value
  //   })
  // },
  // idx2x: function() {
  //   var self = this
  //   self.setData({
  //     idx2value:''
  //   })
  // },
  // idx2save:function() {
  //   var self = this
  //   var itemArray = self.data.itemArray
  //   itemArray[1].text = self.data.idx2value

  //   self.setData({
  //     itemArray: itemArray,
  //     displayArray: displayArray(0)
  //   })
  //   wx.setNavigationBarTitle({
  //     title: '个人信息'
  //   })
  // },
  idx3phoneinput: function (e) {
    var self = this
    self.setData({
      idx3phone: e.detail.value
    })
  },
  sendcode: function (e) {
    var self = this
    var idx3phone = self.data.idx3phone

    if (!checkPhone(idx3phone)) {
      promptshowModal('手机号码有误，请重填')
    }

    var that = this
    a.post('raise.verifycode', { mobile: idx3phone }, function (json) {

      if (json.error == 1) {
        wx.showModal({
          title: '',
          showcancel: false,
          content: json.message,
        })
        return
      }

      that.countdown()

    })

  },

  // 倒计时
  countdown: function () {
    var that = this
    var limit = 10;
    var count = 0;

    that.setData({
      btncode: false,
      count:limit
    })

    var timer = setInterval(function () {
      count++
      if (count > limit) {
        that.setData({
          btncode: true
        })
        clearInterval(timer);
      } else {
        that.setData({
          count: limit - count
        })
      }
    }, 1000);
  },

  idx3codeinput: function (e) {
    var self = this
    self.setData({
      idx3code: e.detail.value
    })
  },
  idx3save: function () {
    var self = this
    if (!self.data.idx3phone) {
      promptshowModal('请输入手机号')
      return
    }
    if (!checkPhone(self.data.idx3phone)) {
      promptshowModal('手机号码有误，请重填')
      return
    }
    if (!self.data.idx3code) {
      promptshowModal('请输入验证码')
      return
    }

    a.post('raise.bind_mobile', { moible: Number(self.data.idx3phone), code: self.data.idx3code }, function (json) {
      console.log(json);
      if (json.error == 1) {
        wx.showModal({
          title: '',
          showcancel: false,
          content: json.message,
        })
        return
      }
    })

    var itemArray = self.data.itemArray
    itemArray[2].text = self.data.idx3phone
    self.setData({
      itemArray: itemArray,
      displayArray: displayArray(0)
    })
    wx.setNavigationBarTitle({
      title: '个人信息'
    })
  },
  onLoad: function (options) {
    var me = this
    a.post('raise.center', {}, function (json) {
      me.setData({
        'itemArray[0].imagesrc': json.member.avatar,
        'itemArray[1].text': json.member.nickname,
        'itemArray[2].text': json.member.mobile ? json.member.mobile : '暂未绑定'
      })
    })
  }
})

function displayArray(idx) {
  var displayArray = ['', '', '']
  displayArray[idx] = 'on'
  return displayArray
}
function checkPhone(phone) {
  var reg = /^1[34578]\d{9}$/
  return reg.test(phone)
}
function promptshowModal(meg) {
  wx.showModal({
    title: '提示',
    content: meg,
  })
}
