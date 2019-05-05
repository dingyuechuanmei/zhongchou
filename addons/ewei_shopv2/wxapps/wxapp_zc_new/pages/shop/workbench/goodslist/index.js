var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    page: 1,
    selectedindex: 0,
    hasMoreData: true,
    goodsList: [],
    value: '',
    topnav: [{
      name: "出售中"
    }, {
        name: "已售罄"
    }, {
        name: "仓库中"
    }, {
        name: "回收站"
    }],
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function () {
    this.getGoodsList()
  },
  /**
  * 切换选项卡
  */
  selectednavbar: function (t) {
    this.setData({
      page: 1,
      goodsList: [],
      value: '',
      selectedindex: t.currentTarget.dataset.index,
    })
    this.getGoodsList();
  },
  /**
   * 弹出操作栏 
   */
  showFrame: function (e) {
    var goodsList = this.data.goodsList;
    for (var i = 0; i < goodsList.length; i++) {
      if (goodsList[i].id == e.currentTarget.dataset.id) {
        goodsList[i].frame = !goodsList[i].frame
      } else {
        goodsList[i].frame = false
      }
    }
    this.setData({
      goodsList: goodsList
    })
  },
  /**
   * 添加
   */
  goodsadd: function () {
    wx.navigateTo({
      url: '../goodsadd/index',
    })
  },
  /**
   * 编辑
   */
  goodsedit: function (e) {
    this.hiddenMenu()
    wx.navigateTo({
      url: '../goodsadd/index?id=' + e.currentTarget.dataset.id,
    })
  },
  /**
   * 查看
   */
  view: function (e) {
    this.hiddenMenu()
    wx.navigateTo({
      url: '/pages/goods/detail/index?id=' + e.currentTarget.dataset.id,
    })
  },
  /**
   * 上下架
   */
  status: function (e) {
    var that = this
    var id = e.currentTarget.dataset.id;
    var status = e.currentTarget.dataset.status;
    var statusText = status == 1 ? '上架' : '下架';
    wx.showModal({
      title: '提示',
      content: '确认' + statusText + '吗?',
      success(e) {
        if(e.confirm) {
          wx.request({
            url: app.globalData.api + "&r=amanage.goods.status",
            data: {
              id: id,
              status: status,
              merchid: app.getMerchId()
            },
            success(res) {
              if (res.data.status == 1) {
                wx.showToast({
                  title: statusText+'成功'
                }), setTimeout(function () {
                  that.getGoodsList()
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
            url: app.globalData.api + "&r=amanage.goods.delete",
            data: {
              id: id,
              merchid: app.getMerchId()
            },
            success(res) {
              if (res.data.status == 1) {
                wx.showToast({
                  title: '删除成功'
                }), setTimeout(function () {
                  that.getGoodsList()
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
 * 还原
 */
  restore: function (e) {
    var that = this
    var id = e.currentTarget.dataset.id;
    wx.showModal({
      title: '提示',
      content: '确认还原吗?',
      success(e) {
        if (e.confirm) {
          wx.request({
            url: app.globalData.api + "&r=amanage.goods.restore",
            data: {
              id: id,
              merchid: app.getMerchId()
            },
            success(res) {
              if (res.data.status == 1) {
                wx.showToast({
                  title: '还原成功'
                }), setTimeout(function () {
                  that.getGoodsList()
                }, 500)
              } else {
                wx.showToast({
                  title: '还原失败',
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
  * 页面相关事件处理函数-监听用户下拉刷新事件
  */
  onPullDownRefresh: function () {
    this.setData({
      page: 1,
      goodsList: []
    })
    this.getGoodsList();
  },
  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
    if (this.data.hasMoreData) {
      this.getGoodsList()
    } else {
      wx.showToast({
        title: '没有更多数据',
      })
    }
  },
  /**
   * 搜索 
   */
  search: function (e) {
    this.setData({
      value: e.detail.value
    })
    this.getGoodsList();
  },
  /**
   * 获取商品列表
   */
  getGoodsList: function () {
    var that = this
    wx.showLoading({
      title: 'loading...',
    })
    var data = {
      page: that.data.page,
      merchid: app.getMerchId(),
      keywords: that.data.value
    };
    switch (that.data.selectedindex) {
      case 0:
        data.status = 'sale';
        break;
      case 1:
        data.status = 'out';
        break;
      case 2:
        data.status = 'stock';
        break;
      case 3:
        data.status = 'cycle';
        break;
    }
    wx.request({
      url: app.globalData.api + "&r=amanage.goods.getlist",
      data: data,
      success(res) {
        var goodsListTem = that.data.goodsList;
        if (that.data.page == 1) {
          goodsListTem = []
        }
        var goodsList = res.data.result.list;
        if (that.data.page >= res.data.result.pageCount) {
          that.setData({
            goodsList: goodsListTem.concat(goodsList),
            hasMoreData: false
          })
        } else {
          that.setData({
            goodsList: goodsListTem.concat(goodsList),
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
    var goodsList = this.data.goodsList
    for (var i = 0, len = goodsList.length;i<len;i++) {
      goodsList[i].frame = false
    }
    this.setData({
      goodsList: goodsList
    })
  }
})