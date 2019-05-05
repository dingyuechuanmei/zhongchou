var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    page: 1,
    index: 0,
    value:'',
    selectedindex:0,
    hasMoreData: true,
    orderList: [],
    arrow: app.globalData.approot +'wxapp_attr/you.png',
    topnav: [{
      name: "待发货"
    }, {
      name: "待付款"
    }, {
      name: "待收货"
    }, {
      name: "已完成"
    }, {
      name: "已关闭"
    }],
    searchType: ['订单号','会员信息','收件人信息','地址信息','快递单号','商品名称','商品编码','核销员','核销门店','商户名称'
      ]
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    app.merchantTabOnLoad(this)
    if (!app.isEmpty(options.selectedindex)) {
      this.setData({
        selectedindex: parseInt(options.selectedindex)
      })
    }
    this.getOrderList()
  },
  /**
   * 切换选项卡
   */
  selectednavbar: function (t) {
    this.setData({
      page: 1,
      index: 0,
      value: '',
      orderList: [],
      selectedindex: t.currentTarget.dataset.index,
    })
    this.getOrderList()
  },
  /**
   * 确认发货
   */
  deliverGoods: function (e) {
    wx.navigateTo({
      url: '../delivergoods/index?type=1&orderid='+e.currentTarget.dataset.id,
    })
  },
  /**
   * 取消发货
   */
  cancelsend: function (e) {
    wx.navigateTo({
      url: '../cancelsend/index?orderid=' + e.currentTarget.dataset.id,
    })
  },
  /**
   * 备注
   */
  remark: function (e) {
    wx.navigateTo({
      url: '../remark/index?orderid='+e.currentTarget.dataset.id,
    })
  },
  /**
   * 订单详情
   */
  orderdetail: function (e) {
    wx.navigateTo({
      url: '../orderdetail/index?orderid='+e.currentTarget.dataset.id
    })
  },
  /**
   * 确认收货
   */
  orderfinish: function (e) {
    var that = this
    var data = {
      orderid: e.currentTarget.dataset.id
    }
    wx.showModal({
      title: '提示',
      content: '确认订单收货吗?',
      success(e) {
        if (e.confirm) {
          wx.request({
            url: app.globalData.api + '&r=amanage.order.finish',
            data: data,
            success(res) {
              console.log(res);
              if (res.data.status == 1) {
                wx.showToast({
                  title: '确认成功'
                })
                setTimeout(function () {
                  that.setData({
                    selectedindex:3
                  })
                  that.getOrderList()
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
  },
  /**
   * 页面相关事件处理函数-监听用户下拉刷新事件
   */
  onPullDownRefresh: function () {
    this.setData({
      page: 1,
      orderList: []
    })
    this.getOrderList();
  },
  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
    if (this.data.hasMoreData) {
      this.getOrderList()
    }
  },
  /**
   * 选择搜索类型
   */
  bindRegionChange:function (e) {
    this.setData({
      index: e.detail.value
    })
  },
  /**
   * 搜索 
   */
  search:function (e) {
    this.setData({
      value: e.detail.value
    })
    this.getOrderList();
  },
  /**
   * 获取订单列表
   */
  getOrderList: function () {
    var that = this
    wx.showLoading({
      title: 'loading...',
    })
    var searchfield = ['ordersn', 'member', 'address', 'location', 'expresssn', 'goodstitle', 'goodssn', 'saler', 'store', 'merch'];
    var data = {
      page: that.data.page,
      merchid: app.getMerchId(),
      searchfield: searchfield[that.data.index],
      keyword: that.data.value
    };
    switch (that.data.selectedindex) {
      case 0:
        data.status = 1;
        break;
      case 1:
        data.status = 0;
        break;
      case 4:
        data.status = -1;
        break;
      default:
        data.status = that.data.selectedindex;
        break;
    }
    wx.request({
      url: app.globalData.api + "&r=amanage.order.getlist",
      data: data,
      success(res) {
        var orderListTem = that.data.orderList;
        if (that.data.page == 1) {
          orderListTem = []
        }
        var orderList = res.data.result.list;
        if (that.data.page >= res.data.result.pageCount) {
          that.setData({
            orderList: orderListTem.concat(orderList),
            hasMoreData: false
          })
        } else {
          that.setData({
            orderList: orderListTem.concat(orderList),
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
  }
})