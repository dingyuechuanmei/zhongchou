var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    ischecked:false,
    region: ['广东省', '广州市', '海珠区']
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.setData({
      orderid: options.orderid
    })
    this.getAddress()
  },
  modifyAddress: function (e) {
    this.setData({
      ischecked: e.detail.value
    })
  },
  /**
   * 地区选择
   */
  bindRegionChange(e) {
    this.setData({
      region: e.detail.value
    })
  },
  /**
   * 获取收件人信息
   */
  getAddress: function () {
    var that = this
    wx.showLoading({
      title: 'loading...',
    })
    wx.request({
      url: app.globalData.api + '&r=amanage.order.changeaddress',
      data: {
        id: that.data.orderid
      },
      success(res) {
        var user = res.data.result.user;
        that.setData({
          user: user,
          region: [user.province, user.city, user.area]
        })
      },
      complete() {
        wx.hideLoading()
      }
    })
  },
  /**
   * 修改收件人信息
   */
  formSubmit: function (e) {
    var that = this
    var region = that.data.region
    var data = {
      id: this.data.orderid,
      realname: e.detail.value.realname,
      mobile: e.detail.value.mobile,
      changead: e.detail.value.changead ? 1 : 0,
      address: e.detail.value.address,
      province: region[0],
      city: region[1],
      area: region[2]
    }
    wx.showModal({
      title: '提示',
      content: '确定要修改收货信息吗?',
      success(e) {
        if (e.confirm) {
          wx.request({
            url: app.globalData.api + '&r=amanage.order.changeaddress_post',
            data: data,
            success(res) {
              if (res.data.status == 1) {
                wx.showToast({
                  title: '修改成功'
                })
                setTimeout(function () {
                  that.getAddress()
                }, 2000)
              } else {
                wx.showToast({
                  title: res.data.result.message,
                  icon: 'loading',
                  mask: true
                }), setTimeout(function () {
                  wx.hideToast()
                }, 2000)
              }
            }
          })
        }
      }
    })
  }
})