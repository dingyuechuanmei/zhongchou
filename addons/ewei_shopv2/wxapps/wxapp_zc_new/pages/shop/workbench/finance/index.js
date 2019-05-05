var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    page: 1,
    selectedindex: 0,
    hasMoreData: true,
    financeList: [],
    value: '',
    arrow: app.globalData.approot + 'wxapp_attr/you.png',
    topnav: [{
      name: "可提现"
    }, {
      name: "待审核"
    }, {
      name: "待结算"
    }, {
      name: "已结算"
    }, {
      name: "已无效"
    }]
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    app.merchantTabOnLoad(this)
    this.getFinanceList()
  },
  selectednavbar: function (t) {
    this.setData({
      page: 1,
      financeList: [],
      value: '',
      selectedindex: t.currentTarget.dataset.index
    })
    this.getFinanceList();
  },
  /**
  * 页面相关事件处理函数-监听用户下拉刷新事件
  */
  onPullDownRefresh: function () {
    this.setData({
      page: 1,
      financeList: []
    })
    this.getFinanceList();
  },
  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
    if (this.data.hasMoreData) {
      this.getFinanceList()
    }
  },
  /**
 * 获取财务列表
 */
  /**
   * 搜索 
   */
  search: function (e) {
    this.setData({
      value: e.detail.value
    })
    this.getFinanceList();
  },
  /**
   * 申请提现
   */
  applyWithdrow: function () {
    wx.navigateTo({
      url: '../apply_withdrow/index',
    })
  },
  getFinanceList: function () {
    var that = this
    wx.showLoading({
      title: 'loading...',
    })
    var data = {
      page: that.data.page,
      merchid: app.getMerchId(),
      type: that.data.selectedindex,
      keyword: that.data.value
    };
    wx.request({
      url: app.globalData.api + "&r=amanage.finance.getlist",
      data: data,
      success(res) {
        var financeListTem = that.data.financeList;
        if (that.data.page == 1) {
          financeListTem = []
        }
        var financeList = res.data.result.list;
        if (that.data.page >= res.data.result.pageCount) {
          that.setData({
            financeList: financeListTem.concat(financeList),
            hasMoreData: false
          })
        } else {
          that.setData({
            financeList: financeListTem.concat(financeList),
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