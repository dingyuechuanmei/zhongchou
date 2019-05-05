var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    index: 0,
    type: 1
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.setData({
      orderid: options.orderid,
      type: options.type
    })
    wx.showLoading({
      title: 'loading...',
    })
    if (this.data.type == 1) {
      wx.setNavigationBarTitle({
        title: '订单发货'
      })
    } else {
      wx.setNavigationBarTitle({
        title: '修改物流信息'
      })
    }
    this.getAddress()
  },
  /**
   * 获取发货信息
   */
  getAddress: function () {
    wx.showLoading({
      title: 'loading...',
    })
    var that = this
    var url = app.globalData.api + '&r=amanage.order.';
    url += (that.data.type == 1) ? 'send' : 'changeexpress';
    wx.request({
      url: url,
      data: {
        id: that.data.orderid
      },
      success(res) {
        var address = res.data.result.address
        var express = res.data.result.express
        that.setData({
          address: address,
          express: express,
        })
        if (that.data.type == 2) {
          var item = res.data.result.item
          var index = 0
          for (var i=0;i<express.length;i++) {
            if (express[i].express == item.express) {
              index = i
            }
          }
          that.setData({
            item: item,
            index:index
          })
        }
      },
      complete() {
        wx.hideLoading()
      }
    })
  },
  /**
   * 选中的快递
   */
  bindPickerChange: function (e) {
    this.setData({
      index: e.detail.value
    })
  },
  /**
   * 确认发货
   */
  formSubmit: function (e) {
    var that = this
    var index = that.data.index
    var data = {
      id: that.data.orderid,
      express: that.data.express[index].express,
      expresscom: that.data.express[index].name,
      expresssn: e.detail.value.expresssn,
      sendtype: 0
    }
    wx.showModal({
      title: '提示',
      content: that.data.type == 1 ? '确定要为此订单发货吗?' : '确定修改此订单物流吗？',
      success(e) {
        if (e.confirm) {
          var url = app.globalData.api + '&r=amanage.order.';
          url += (that.data.type == 1) ? 'send_post' : 'changeexpress_post';
          wx.request({
            url: url,
            data: data,
            success(res) {
              if (res.data.status == 1) {
                wx.showToast({
                  title: that.data.type == 1 ? '发货成功' : '修改成功'
                })
                if (that.data.type == 1) {
                  setTimeout(function () {
                    wx.redirectTo({
                      url: '../order/index?selectedindex=2',
                    })
                  }, 2000)
                } else {
                  that.getAddress();
                }
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