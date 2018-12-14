
var e = require("../../utils/core.js");

Page({

  /**
   * 页面的初始数据
   */
  data: {

  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this
    that.getUserInfo();
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {

  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  },

  // 获取缓存
  getCache: function (e, t) {
    var i = +new Date / 1000,
      n = "";
    i = parseInt(i);
    try {
      n = wx.getStorageSync(e + this.globalData.appid),
        n.expire > i || 0 == n.expire ? n = n.value : (n = "", this.removeCache(e))
    } catch (e) {
      n = void 0 === t ? "" : t
    }
    return n = n || ""
  },
  // 设置缓存
  setCache: function (e, t, i) {
    var n = +new Date / 1000,
      a = true,
      o = {
        expire: i ? n + parseInt(i) : 0,
        value: t
      };
    try {
      wx.setStorageSync(e + this.globalData.appid, o)
    } catch (e) {
      a = false
    }
    return a
  },
  // 删除缓存
  removeCache: function (e) {
    var t = true;
    try {
      wx.removeStorageSync(e + this.globalData.appid)
    } catch (e) {
      t = false
    }
    return t
  },
  /**
   * 验证是否为空
   */
  isEmpty: function (str) {
    if (str == 'undefined' || str == undefined || str == null || str == 0 || str == '') {
      return true;
    } else {
      return false;
    }
  },
  // 获取json对象的长队
  getJsonObjLength: function (jsonObj) {
    var Length = 0;
    for (var item in jsonObj) {
      Length++;
    }
    return Length;
  },
  // 用户登陆
  getUserInfo: function (t, i) {
    var n = this,
      a = n.getCache("userinfo");
    if (a && !a.needauth)
      return void (t && "function" == typeof t && t(a));
    wx.login({
      success: function (o) {
        if (!o.code)
          return void e.alert("获取用户登录态失败:" + o.errMsg);
        e.post("wxapp/login", {
          code: o.code
        }, function (o) {
          // console.log(o);
          return o.error ? void e.alert("获取用户登录态失败1:" + o.message) : o.isclose && i && "function" == typeof i ? void i(o.closetext, true) : void wx.getUserInfo({
            success: function (i) {
              a = i.userInfo,
                e.get("wxapp/auth", {
                  data: i.encryptedData,
                  iv: i.iv,
                  sessionKey: o.session_key
                }, function (e) {
                  i.userInfo.openid = e.openId,
                    i.userInfo.id = e.id,
                    i.userInfo.uniacid = e.uniacid,
                    i.needauth = 0,
                    n.setCache("userinfo", i.userInfo, 7200),
                    t && "function" == typeof t && t(a)
                })
            },
            fail: function () {
              e.get("wxapp/check", {
                openid: o.openid
              }, function (e) {
                e.needauth = 1,
                  n.setCache("userinfo", e, 7200),
                  t && "function" == typeof t && t(a)
              })
            }
          })
        })
      },
      fail: function () {
        e.alert("获取用户信息失败!")
      }
    })
  },
  // 配置信息
  globalData: {
    appid: "wx2c2d379a1806d40f",
    api: "https://xiaochengxu.bmwtech.cn/app/ewei_shopv2_api.php?i=73",
    approot: "https://xiaochengxu.bmwtech.cn/addons/ewei_shopv2/",
    userInfo: null
  }

})