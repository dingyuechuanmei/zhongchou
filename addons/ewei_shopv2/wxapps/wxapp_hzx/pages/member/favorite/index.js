var t = getApp(),
  e = t.requirejs("core");
t.requirejs("foxui");
Page({
  data: {
    icons: t.requirejs("icons"),
    page: 1,
    loading: false,
    loaded: false,
    isedit: false,
    isCheckAll: false,
    checkObj: {},
    checkNum: 0,
    list: [],
    merch_goods: [],
  },
  onLoad: function (e) {
    t.url(e),
      this.getList()
  },
  onReachBottom: function () {
    this.data.loaded || this.data.list.length == this.data.total || this.getList()
  },
  onPullDownRefresh: function () {
    wx.stopPullDownRefresh()
  },
  getList: function () {
    var t = this;
    t.setData({
      loading: true
    }),
      e.get("member/favorite/get_list", {
        page: t.data.page
      }, function (e) {
        var a = {
          loading: false,
          loaded: true,
          total: e.total,
          pagesize: e.pagesize,
          show: true
        };
        e.list.length > 0 && (a.page = t.data.page + 1, a.list = t.data.list.concat(e.list), e.list.length < e.pagesize && (a.loaded = true)),
        t.setData(a)
        
        t.setData({
          merch_goods: e.merch_goods
        })

      })

  },
  itemClick: function (t) {
    var a = this,
      i = e.pdata(t).id,
      s = e.pdata(t).goodsid;
    if (a.data.isedit) {
      var c = a.data.checkObj,
        l = a.data.checkNum;
      c[i] ? (c[i] = false, l--) : (c[i] = true, l++);
      var o = true;
      for (var n in c)
        if (!c[n]) {
          o = false;
          break
        }
      a.setData({
        checkObj: c,
        isCheckAll: o,
        checkNum: l
      })
    } else
      wx.navigateTo({
        url: "/pages/goods/detail/index?id=" + s
      })
  },
  btnClick: function (t) {
    var a = this,
      i = t.currentTarget.dataset.action;
    if ("edit" == i) {
      var s = {};
      for (var c in this.data.list) {
        s[this.data.list[c].id] = false
      }
      a.setData({
        isedit: true,
        checkObj: s,
        isCheckAll: false
      })
    } else if ("delete" == i || "addcart" == i) {
      var s = a.data.checkObj,
        l = [];
      for (var c in s)
        s[c] && l.push(c);
      if (l.length < 1)
        return;

      if ("addcart" == i) {
        console.log(l)
        e.post("member/favorite/addcart", {
          favid: l
        }, function (json) {
          console.log(json);

          wx.showToast({
            title: '成功',
            icon: 'success',
            duration: 2000
          })

        })
        return;
      }

      e.confirm("删除后不可恢复，确定要删除吗？", function () {
        e.post("member/favorite/remove", {
          ids: l
        }, function (t) {
          a.setData({
            isedit: false,
            checkNum: 0,
            page: 0,
            list: []
          }),
            a.getList()
        })
      })

    } else
      "finish" == i && a.setData({
        isedit: false,
        checkNum: 0
      })
  },
  checkAllClick: function () {
    var t = !this.data.isCheckAll,
      e = this.data.checkObj,
      a = {
        isCheckAll: t,
        checkObj: e
      };
    for (var i in e)
      a.checkObj[i] = !!t;
    a.checkNum = t ? this.data.list.length : 0,
      this.setData(a)
  },
  addCartGoods: function (dom) {
    var favid = dom.target.dataset.favid;
    if (favid != '' || favid != undefined || favid != 'undefined') {
      e.post("member/favorite/addcart", {
        favid: favid
      }, function (json) {
        wx.showToast({
          title: '成功',
          icon: 'success',
          duration: 2000
        })
      })
    }
  }
})