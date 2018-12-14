//app.js
var e = require("utils/core.js");
App({
  onLaunch: function () {
    var e = this.getCache("userinfo");
    var extConfig = wx.getExtConfigSync ? wx.getExtConfigSync() : '';

    if (this.getJsonObjLength(extConfig) > 0){
      this.globalData.appid = extConfig.appid;
      this.globalData.api = extConfig.api,
      this.globalData.approot = extConfig.approot
    }

    // wx.redirectTo({
    //   url:'/pages/raise/pages/myraise/myraise?item=5'
    // });
    
    ("" == e || e.needauth) && this.getUserInfo(function (e) { }, function (e, t) {
      var t = t ? 1 : 0,
        e = e || "";
      //页面重定向
      t && wx.redirectTo({
        url: "/pages/message/auth/index?close=" + t + "&text=" + e
      })
    })
  
  },
  requirejs: function (e) {
    return require("utils/" + e + ".js")
  },
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
  removeCache: function (e) {
    var t = true;
    try {
      wx.removeStorageSync(e + this.globalData.appid)
    } catch (e) {
      t = false
    }
    return t
  },
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
  getSet: function () {
    var t = this;
    "" == t.getCache("sysset") && setTimeout(function () {
      var i = t.getCache("cacheset");
      e.get("cacheset", {
        version: i.version
      }, function (e) {
        e.update && t.setCache("cacheset", e.data),
          t.setCache("sysset", e.sysset, 7200)
      })
    }, 10)
  },
  url: function (e) {
    e = e || {};
    var t = {
      mid : 0,
      merchid:e.merchid
    },
      i = "",
      n = "",
      a = this.getCache("usermid");
    i = e.mid || "",
      n = e.merchid || "",
      "" != a ? ("" != a.mid && void 0 !== a.mid || (t.mid = i), "" != a.merchid && void 0 !== a.merchid || (t.merchid = n)) : (t.mid = i, t.merchid = n),
      this.setCache("usermid", t, 7200)
  },
// 获取json对象的长队
  getJsonObjLength: function (jsonObj) {
    var Length = 0;
    for (var item in jsonObj) {
      Length++;
    }
    return Length;
  },

  /**
 * 分配模板
 * @param cb
 * @param that
 */
  getTemplate: function (cb, that) {
    var This = this;
    var WxParse = This.requirejs('wxParse/wxParse');
    var base64  = require('/resource/js/base64.js');
    var base = new base64.Base64();

    this.init_emoji();

    // 父级页面的样式 start
    var that_data = that.data;
    var navbar = "";
    var padding_bottom = "";
    var background_color = that_data.page.background;

    if (that_data.pageinfo.diymenu > -1) {
      navbar = "navbar";
      padding_bottom = "padding-bottom: 0;";
    }

    that_data.page.navbar = navbar;
    that_data.page.padding_bottom = padding_bottom;
    that_data.page.background_color = background_color;
    // 父级页面的样式 end  

    // 子级模板转换 start
    var diyitem = [], template = "", copyright = "", audio = "", video = "", notice = "", tabbar = "", listmenu = "", richtext = "", title = "", line = "", blank = "", menu = "", menu2 = "", picture = "", banner = "", picturew = "", pictures = "", icongroup = "", goods = "", search = "", fixedsearch="";

    for (var i in that_data.diyitems) {

      diyitem = that_data.diyitems[i];
      switch (diyitem.id) {
        case 'audio':
          console.log('audio');
          // console.log(diyitem);
          continue;
        case 'video':
          console.log('video');
          // console.log(diyitem);

          if (diyitem.params.loopplay == 1) {
            that.data.diyitems[i].params.loopplay = true;
          } else {
            that.data.diyitems[i].params.loopplay = false;
          }

          if (diyitem.params.autoplay == 1) {
            that.data.diyitems[i].params.autoplay = true;
          } else {
            that.data.diyitems[i].params.autoplay = false;
          }

          if (diyitem.params.mutedplay == 1) {
            that.data.diyitems[i].params.mutedplay = true;
          } else {
            that.data.diyitems[i].params.mutedplay = false;
          }
          // console.log(diyitem);
          continue;
        case 'notice':
          console.log('notice');
          // console.log(diyitem);
          continue;
        case 'tabbar':
          console.log('tabbar');
          // console.log(diyitem);

          that.data.diyitems[i].diyitem_data_len = This.getJsonObjLength(diyitem.data);
          continue;
        case 'listmenu':
          console.log('listmenu');
          // console.log(diyitem);
          continue;
        case 'richtext':
          console.log('richtext');

          richtext += base.decode(diyitem.params.content);

          // console.log(richtext);

          template += richtext;

          continue;
        case 'title':
          console.log('title');
          // console.log(diyitem);
          continue;
        case 'line':
          console.log('line');
          // console.log(diyitem);
          continue;
        case 'blank':
          console.log('blank');
          // console.log(diyitem);
          continue;
        case 'menu':
          console.log('menu');
          // console.log(diyitem);

          that.data.diyitems[i].style.background = diyitem.style.background = '#ffffff' ? "" : diyitem.style.background;
          that.data.diyitems[i].style.pagenum = diyitem.style.pagenum > 0 ? diyitem.style.pagenum : 8;
          that.data.diyitems[i].diyitem_data_len = This.getJsonObjLength(diyitem.data);

          continue;
        case 'menu2':
          console.log('menu2');
          // console.log(diyitem);
          continue;
        case 'picture':
          console.log('picture');
          // console.log(diyitem);
          continue;
        case 'banner':
          console.log('banner');
          // console.log(diyitem);
          continue;
        case 'picturew':
          console.log('picturew');
          // console.log(diyitem);

          var fui_cube_padding = "", fui_cube_right_padding = "", fui_cube_right2_padding = "";
          var diyitem_data_len = This.getJsonObjLength(diyitem.data);

          if (diyitem_data_len == 1) {
            fui_cube_padding = "padding:" + diyitem.style.paddingtop + "px " + diyitem.style.paddingleft + "px";
          } else if (diyitem_data_len == 2) {
            fui_cube_right_padding = 'padding:' + diyitem.style.paddingtop + 'px ' + diyitem.style.paddingleft + 'px';
          } else if (diyitem_data_len == 3) {
            fui_cube_right2_padding = 'padding:' + diyitem.style.paddingtop + 'px ' + diyitem.style.paddingleft + 'px';
          }

          that.data.diyitems[i].style.fui_cube_padding = fui_cube_padding;
          that.data.diyitems[i].style.fui_cube_right_padding = fui_cube_right_padding;
          that.data.diyitems[i].style.fui_cube_right2_padding = fui_cube_right2_padding;
          that.data.diyitems[i].diyitem_data_len = This.getJsonObjLength(diyitem.data);

          that.data.diyitems[i].data = arrayToNewArray(that.data.diyitems[i].data);

          console.log(that.data.diyitems[i].data);

          continue;
        case 'pictures':
          console.log('pictures');
          // console.log(diyitem);

          that.data.diyitems[i].diyitem_data_len = This.getJsonObjLength(diyitem.data);

          continue;
        case 'icongroup':
          console.log('icongroup');
          // console.log(diyitem);

          var bordertop = "", borderbottom = "";

          if (diyitem.params.bordertop == 1) {
            bordertop = "border-top: 1px solid " + diyitem.style.bordercolor;
          } else {
            bordertop = "border-top: none;";
          }

          if (diyitem.params.borderbottom == 1) {
            bordertop = "border-bottom: 1px solid " + diyitem.style.bordercolor;
          } else {
            bordertop = "border-bottom: none;";
          }

          that.data.diyitems[i].params.bordertop = bordertop;
          that.data.diyitems[i].params.borderbottom = borderbottom;

          continue;
        case 'goods':
          console.log('goods');
          continue;
        case 'search':
          console.log('search');
          continue;
        case 'fixedsearch':
          console.log('fixedsearch');
          continue;
        default:
          console.log('未知 = ' + that_data.diyitems[i].id);
      }
    }

    // 添加底部版权
    if (!This.isEmpty(that_data.copyright)) {
      var bgcolor = "";
      if (!This.isEmpty(that_data.copyright.bgcolor)) {
        bgcolor = "background: " + that_data.copyright.bgcolor + ";";
      }

      copyright += `<div class="footer" style='width:100%; display: block;` + bgcolor + `'>` + that_data.copyright.data + `</div>`;

      template += copyright;
    }

    WxParse.wxParse('template', 'html', template, that, 5);
    // 子级模板转换 end

    typeof cb == "function" && cb(that);
  },

  /**
 * 初始化emoji设置
 */
  init_emoji: function () {
    var WxParse = this.requirejs('wxParse/wxParse');
    WxParse.emojisInit('[]', "/wxParse/emojis/", {
      "00": "00.gif",
      "01": "01.gif",
      "02": "02.gif",
      "03": "03.gif",
      "04": "04.gif",
      "05": "05.gif",
      "06": "06.gif",
      "07": "07.gif",
      "08": "08.gif",
      "09": "09.gif",
      "09": "09.gif",
      "10": "10.gif",
      "11": "11.gif",
      "12": "12.gif",
      "13": "13.gif",
      "14": "14.gif",
      "15": "15.gif",
      "16": "16.gif",
      "17": "17.gif",
      "18": "18.gif",
      "19": "19.gif",
    });
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
  globalData: {
    appid: "	wx2c2d379a1806d40f",
    api: "https://xiaochengxu.bmwtech.cn/app/ewei_shopv2_api.php?i=73",
    approot: "https://xiaochengxu.bmwtech.cn/addons/ewei_shopv2/",
    userInfo: null
  },

})