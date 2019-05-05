var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    id: 0,
    selectedindex: 0,
    levelsIndex: 0,
    groupsIndex: 0,
    topnav: [{
      name: "基本信息"
    }, {
      name: "交易信息"
    }],
    arrow: app.globalData.approot + 'wxapp_attr/you.png'
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.setData({
      id: options.id
    })
    this.getDetail()
  },
  /**
   * 切换选项卡
   */
  selectednavbar: function (t) {
    this.setData({
      selectedindex: t.currentTarget.dataset.index
    })
  },
  /**
   * 会员等级
   */
  bindPickerChangeLevels: function (e) {
    this.setData({
      levelsIndex: e.detail.value
    })
  },
  /**
   * 会员等级
   */
  bindPickerChangeGroups: function (e) {
    this.setData({
      groupsIndex: e.detail.value
    })
  },
  /**
   * 会员详情
   */
  getDetail: function () {
    wx.showLoading({
      title: 'loading....',
    })
    var that = this
    wx.request({
      url: app.globalData.api + '&r=amanage.member.detail',
      data: {
        id: that.data.id 
      },
      success(res) {
        that.setData({
          member: res.data.result.member,
          levels: res.data.result.levels,
          groups: res.data.result.groups
        })
      },
      complete() {
        wx.hideLoading()
      }
    })
  },
  /**
   * 保存设置
   */
  formSubmit: function (e) {
    var that = this
    var levelsIndex = that.data.levelsIndex
    var groupsIndex = that.data.groupsIndex
    var data = {
      id: that.data.id,
      realname: e.detail.value.realname,
      mobile: e.detail.value.mobile,
      weixin: e.detail.value.weixin,
      isblack: e.detail.value.isblack == 1 ? 1 : 0,
      content: e.detail.value.content
    }
    if (levelsIndex > 0) {
      data.level = that.data.levels[levelsIndex].id
    }
    if (groupsIndex > 0) {
      data.groupid = that.data.groups[groupsIndex].id
    }
    wx.request({
      url: app.globalData.api + '&r=amanage.member.detail_post',
      data: data,
      success(res) {
        if (res.data.status == 1) {
          setTimeout(function () {
            wx.showToast({
              title: '保存成功'
            })
            that.getDetail()
          }, 500)
        } else {
          setTimeout(function () {
            wx.showToast({
              title: res.data.result.message,
              icon: 'none'
            })
          }, 500)
        }
      }
    })
  }
})