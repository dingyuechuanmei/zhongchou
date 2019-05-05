var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    page: 1,
    value: '',
    selectedindex: 0,
    hasMoreData: true,
    pusherList: []
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.getPusherList()
  },
  /**
   * 弹出操作栏 
   */
  showFrame: function (e) {
    var pusherList = this.data.pusherList;
    for (var i = 0; i < pusherList.length; i++) {
      if (pusherList[i].id == e.currentTarget.dataset.id) {
        pusherList[i].frame = !pusherList[i].frame
      } else {
        pusherList[i].frame = false
      }
    }
    this.setData({
      pusherList: pusherList
    })
  },
  /**
   * 添加众推
   */
  pushadd: function () {
    wx.navigateTo({
      url: '../pushadd/index',
    })
  },
  /**
   * 众推编辑
   */
  pushedit: function (e) {
    this.hiddenMenu()
    wx.navigateTo({
      url: '../pushadd/index?id=' + e.currentTarget.dataset.id,
    })
  },
  /**
  * 页面相关事件处理函数-监听用户下拉刷新事件
  */
  onPullDownRefresh: function () {
    this.setData({
      page: 1,
      pusherList: []
    })
    this.getPusherList();
  },
  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
    if (this.data.hasMoreData) {
      this.getPusherList()
    } else {
      wx.showToast({
        title: '没有更多数据',
      })
    }
  },
  /**
   * 显示/隐藏
   */
  status: function (e) {
    var that = this
    var id = e.currentTarget.dataset.id;
    var status = e.currentTarget.dataset.status;
    var statusText = status == 1 ? '隐藏' : '显示';
    wx.showModal({
      title: '提示',
      content: '确认'+statusText+'吗?',
      success(e){
        if(e.confirm) {
          wx.request({
            url: app.globalData.api + "&r=amanage.pusher.status",
            data: {
              id: id,
              status: status == 1 ? 0 : 1,
              merchid: app.getMerchId()
            },
            success(res) {
              if (res.data.status == 1) {
                wx.showToast({
                  title: statusText + '成功'
                }), setTimeout(function () {
                  that.getPusherList()
                }, 500)
              } else {
                wx.showToast({
                  title: statusText + '失败',
                  icon: 'loading',
                  mask: true
                }), setTimeout(function () {
                  wx.hideToast()
                }, 500)
              }
            }
          })
        }
      }
    })
  },
  /**
   * 删除
   */
  delete: function (e) {
    var that = this
    var id = e.currentTarget.dataset.id;
    wx.showModal({
      title: '提示',
      content: '确认删除吗?',
      success(e) {
        if (e.confirm) {
          wx.request({
            url: app.globalData.api + "&r=amanage.pusher.delete",
            data: {
              id: id,
              merchid: app.getMerchId()
            },
            success(res) {
              if (res.data.status == 1) {
                wx.showToast({
                  title: '删除成功'
                }), setTimeout(function () {
                  that.getPusherList()
                }, 500)
              } else {
                wx.showToast({
                  title: '删除失败',
                  icon: 'loading',
                  mask: true
                }), setTimeout(function () {
                  wx.hideToast()
                }, 500)
              }
            }
          })

        }
      }
    })
  },
  /**
  * 搜索 
  */
  search: function (e) {
    this.setData({
      value: e.detail.value
    })
    this.getPusherList();
  },
  /**
   * 获取众推列表
   */
  getPusherList: function () {
    var that = this
    wx.showLoading({
      title: 'loading...',
    })
    var data = {
      page: that.data.page,
      keywords: that.data.value,
      merchid: app.getMerchId(),
    };
    wx.request({
      url: app.globalData.api + "&r=amanage.pusher.getlist",
      data: data,
      success(res) {
        var pusherListTem = that.data.pusherList;
        if (that.data.page == 1) {
          pusherListTem = []
        }
        var pusherList = res.data.result.list;
        if (that.data.page >= res.data.result.pageCount) {
          that.setData({
            pusherList: pusherListTem.concat(pusherList),
            hasMoreData: false
          })
        } else {
          that.setData({
            pusherList: pusherListTem.concat(pusherList),
            hasMoreData: true,
            page: that.data.page + 1
          })
        }
      },
      complete() {
        wx.hideLoading()
        wx.hideNavigationBarLoading()
        wx.stopPullDownRefresh()
      }
    })
  },
  /**
   * 隐藏操作栏
   */
  hiddenMenu() {
    var pusherList = this.data.pusherList
    for (var i = 0, len = pusherList.length; i < len; i++) {
      pusherList[i].frame = false
    }
    this.setData({
      pusherList: pusherList
    })
  }
})