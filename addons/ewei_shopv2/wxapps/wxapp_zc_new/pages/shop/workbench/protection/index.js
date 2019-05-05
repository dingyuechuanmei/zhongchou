var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    page: 1,
    selectedindex: 0,
    hasMoreData: true,
    orderList: [],
    arrow: app.globalData.approot + 'wxapp_attr/you.png',
    topnav: [{
      name: "维权申请"
    }, {
      name: "维权完成"
    }],
    search: [
      '订单号', '会员信息', '收件人信息', '地址信息', '快递单号', '商品名称', '商品编码', '核销员', '核销门店', '商户名称'
    ],
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    app.merchantTabOnLoad(this)
    this.getOrderList()
  },
  /**
   * 切换选项卡
   */
  selectednavbar: function (t) {
    this.setData({
      page: 1,
      orderList: [],
      selectedindex: t.currentTarget.dataset.index
    })
    this.getOrderList()
  },
  /**
   * 确认发货
   */
  deliverGoods: function (e) {
    wx.navigateTo({
      url: '../delivergoods/index?orderid=' + e.currentTarget.dataset.id,
    })
  },
  /**
   * 备注
   */
  remark: function () {
    wx.navigateTo({
      url: '../remark/index',
    })
  },
  /**
   * 维权详情
   */
  protectiondetail: function (e) {
    wx.navigateTo({
      url: '../protectiondetail/index?orderid='+e.currentTarget.dataset.id
    })
  },
  /**
   * 订单详情
   */
  orderdetail: function (e) {
    wx.navigateTo({
      url: '../orderdetail/index?orderid=' + e.currentTarget.dataset.id,
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
    } else {
      wx.showToast({
        title: '没有更多数据',
      })
    }
  },
  /**
   * 获取订单列表
   */
  getOrderList: function () {
    var that = this
    wx.showLoading({
      title: 'loading...',
    })
    var data = {
      page: that.data.page,
      merchid: app.getMerchId(),
    };
    switch (that.data.selectedindex) {
      case 0:
        data.status = 4;
        break;
      case 1:
        data.status = 5;
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