var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    bankindex: 0
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.getApplyWithdrow()
  },
  /**
   * 选择提现方式
   */
  bindPickerChange: function (e) {
    this.setData({
      index: e.detail.value
    })
  },
  /**
   * 选择银行
   */
  bindPickerChangeBank: function (e) {
    this.setData({
      bankindex: e.detail.value
    })
  },
  /**
   * 获取上次提现信息
   */
  getApplyWithdrow: function () {
    wx.showLoading({
      title: 'loading...',
    })
    var that = this
    wx.request({
      url: app.globalData.api + '&r=amanage.finance.apply_withdrow',
      data: {
        merchid: app.getMerchId()
      },
      success(res) {
        that.setData({
          type_array: res.data.result.type_array,
          banklist: res.data.result.banklist,
          last_data: res.data.result.last_data,
          index: res.data.result.index
        })
      },
      complete() {
        wx.hideLoading()
      }
    })
  },
  /**
   * 申请提现
   */
  formSubmit: function (e) {
    var index = this.data.index
    var bankindex = this.data.bankindex
    var applytype = this.data.type_array[index].type
    var data = {
      merchid: app.getMerchId(),
      applytype: applytype,
      realname: e.detail.value.realname,
      alipay: e.detail.value.alipay,
      alipay1: e.detail.value.alipay1,
      bankname: this.data.banklist[bankindex].bankname,
      bankcard: e.detail.value.bankcard,
      bankcard1: e.detail.value.bankcard1,
    }
    wx.request({
      url: app.globalData.api + '&r=amanage.finance.apply_withdrow_post',
      data: data,
      success (res) {
        if (res.data.status == 1) {
          setTimeout(function(){
            wx.showToast({
              title: '提交成功'
            })
            wx.redirectTo({
              url: '../finance/index',
            })
          },500)
        } else {
          setTimeout(function(){
            wx.showToast({
              title: res.data.result.message,
              icon: 'none'
            })
          },500)
        }
      },
      complete () {
        wx.hideLoading()
      }
    })
  }
})