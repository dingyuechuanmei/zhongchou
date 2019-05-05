var t = getApp(),
  app = getApp(),
  a = t.requirejs("core"),
  core = t.requirejs("core"),
  e = (t.requirejs("icons"), t.requirejs("wxParse/wxParse"));
Page({
  data: {
    page: 1,
    isfavorite: 0,    //0未关注1已关注
    merchid: 0,   //商户id
    hasMoreData: true,
    contentList: [],    //商品列表
    listStyle: 0,    //0竖排横排
    currentData: 'recommand',   //默认推荐
    shopcurrentData: 'sale',     //默认销量
    recommend_default: '/static/images/icon/favor.png',   //推荐默认图片
    recommend_select: '/static/images/icon-red/favor.png',   //推荐选中图片 
    shop_default: '/static/images/icon/home.png',   //店铺默认图片
    shop_select: '/static/images/icon-red/home.png',    //店铺选中图片
    all_num: 0,   //全部数量
    new_num: 0,   //新品数量
    info:{},      //商户信息
    background_img:    'https://chuangke.dingyuedianshang.com/attachment/images/73/merch/159/nzKuB7lY7SdYpkE6Y8nM681dCE6cC6.jpg'   //默认商户背景图
  },
  /**
   * 生命周期函数监听页面加载
   */
  onLoad: function (options){
    if (options.merchid != '' && options.merchid != undefined) {
      this.setData({
        merchid: options.merchid
      });
    }
    t.url(options)
    var that = this
    that.getMerchantInfo()
    that.getContentInfo()
  },
  /**
   * 页面相关事件处理函数-监听用户下拉
   */
  onPullDownRefresh: function () {
    var that = this;
    that.setData({
      page: 1,
      contentList: []
    })
    that.getContentInfo();
  },
  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
    if (this.data.hasMoreData) {
      this.getContentInfo()
    } else {
      wx.showToast({
        title: '没有更多数据',
      })
    }
  },
  //点击切换，滑块index赋值(一级导航)
  checkCurrent: function (e) {
    const that = this;
    if (that.data.currentData === e.target.dataset.current) {
      return false;
    } else {
      that.setData({
        currentData: e.target.dataset.current,
        contentList: [],
        page: 1,
        listStyle: 0
      })
      that.getContentInfo()
    }
  },
  //点击切换，滑块index赋值(全部商品二级导航)
  shopCheckCurrent: function (e) {
    const that = this;
    if (that.data.shopcurrentData === e.target.dataset.current) {
      return false;
    } else {
      that.setData({
        shopcurrentData: e.target.dataset.current,
        contentList: [],
        page: 1,
        listStyle: 0      //切换页面时排版恢复默认
      })
      that.getContentInfo()
    }
  },
  //获取商品信息(默认推荐商品)
  getContentInfo: function () {
    var that = this;
    if (that.data.currentData === 'shop') {
      return false;
    }
    wx.showLoading({
      title: 'loading...',
    })
    var data = {
      page: that.data.page,
      merchid: that.data.merchid,
    };
    switch (that.data.currentData) {
      case 'recommand': 
      case 'new':
        data.type = that.data.currentData
        break;
      case 'all':
        data.type = that.data.shopcurrentData
        break;
      default:
        data.type = 'recommand'
        break;
    }
    a.get("shop/get_merchant_goods", data, function (a) {
      var contentListTem = that.data.contentList;
      if (that.data.page == 1) {
        contentListTem = []
      }
      var contentList = a.list;
      if (that.data.page >= a.pageCount) {
        that.setData({
          contentList:contentListTem.concat(contentList),
          hasMoreData:false
        })
      } else {
        that.setData({
          contentList: contentListTem.concat(contentList),
          hasMoreData: true,
          page: that.data.page + 1
        })
      }
      wx.hideLoading()
      wx.hideNavigationBarLoading()
      wx.stopPullDownRefresh()
    })
  },
  /**
	 * 获取商户信息
	 */
  getMerchantInfo: function(){
    wx.showLoading({
      title: 'loading...',
    })
    var that = this;
    a.get("shop/get_merchant_info", {
      merchid: that.data.merchid
    }, function (a) {
      that.setData({
        info:a.info,
        all_num: a.all_num,
        new_num: a.new_num,
        isfavorite: a.isfavorite
      })
      //动态设置标题
      if (a.info.merchname != '') {
        wx.setNavigationBarTitle({
          title: a.info.merchname
        })
      }
      wx.hideLoading()
    })
  },
  /**
   * 跳转到商品详情
   */
  goodsDetail: function (event){
    wx.navigateTo({
      url: '/pages/goods/detail/index?id=' + event.currentTarget.dataset.id,
    })
  },
  /**
   * 返回首页 
   */
  backHome: function (){
    wx.navigateTo({
      url: '/pages/index/index',
    })
  },
  /**
   * 关注店铺
   */
  favoriteMerchant: function (){
    var that = this
    var isfavorite = !that.data.isfavorite
    that.setData({
      isfavorite: isfavorite
    })
    a.get("shop/favorite_merchant", {
      merchid: that.data.merchid,
      isfavorite: isfavorite ? 1 : 0
    }, function (a) {
      console.log(a); 
    })
  },
  /**
   * 切换列表模板
   */
  typesetting: function (){
    var that = this
    that.setData({
      listStyle: !that.data.listStyle
    })
  },
  /**
   * 拨打电话
   */
  tel: function () {
    var tel = this.data.info.tel || this.data.info.mobile
    if (!app.isEmpty(tel)) {
      wx.makePhoneCall({
        phoneNumber: tel,
      })
    }
  }
})