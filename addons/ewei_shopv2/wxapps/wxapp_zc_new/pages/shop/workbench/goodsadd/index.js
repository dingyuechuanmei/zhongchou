var app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    id: 0,
    item: {},
    index: 0,
    piclist: new Array(),
    thumb: new Array(),
    stockIndex: 0,
    arrow: app.globalData.approot + 'wxapp_attr/you.png',
    type: [
      '实物商品', '虚拟商品', '虚拟商品(卡密)'
    ],
    stock: [
      '拍下减库存', '付款减库存', '永不减库存'
    ],
    checkboxItems: [{
      name: "推荐",
      value: "isrecommand"
    }, {
      name: "新品",
      value: "isnew"
    }, {
      name: "热卖",
      value: "ishot"
    }, {
      name: "包邮",
      value: "issendfree"
    }],
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    if (options.id) {
      this.setData({
        id: options.id
      })
      wx.setNavigationBarTitle({
        title: '商品编辑'
      })
      this.getGoodsDetail();
    } else {
      wx.setNavigationBarTitle({
        title: '添加商品'
      })
    }

  },
  /**
   * 选择商品属性
   */
  checkboxChange: function (e) {
    for (var a = this.data.checkboxItems, t = e.detail.value, s = 0, o = a.length; s < o; ++s) {
      a[s].checked = !1;
      for (var i = 0, n = t.length; i < n; ++i) if (a[s].value == t[i]) {
        a[s].checked = !0;
        break
      }
    }
    this.setData({
      checkboxItems: a
    })
  },
  //选择商品类型
  bindPickerChangeType: function (e) {
    this.setData({
      index: e.detail.value
    })
  },
  //库存设置
  bindPickerChangeStock: function (e) {
    this.setData({
      stockIndex: e.detail.value
    })
  },
  /**
   * 删除商品图片
   */
  removegoodsimg: function (e) {
    var piclist = this.data.piclist
    var thumb = this.data.thumb
    for (var len = piclist.length,i = 0; i < len; i++) {
      if (i == e.currentTarget.dataset.idx) {
        piclist.splice(i,1)
        thumb.splice(i, 1)
      }
    }
    this.setData({
      piclist: piclist,
      thumb: thumb
    })
  },
  /**
   * 上传商品图片
   */
  uploadgoods: function (e) {
    var that = this
    wx.chooseImage({
      count: 1,
      success: function (e) {
        wx.showToast({
          icon: "loading",
          title: "正在上传"
        });
        var tempFilePaths = e.tempFilePaths
        wx.uploadFile({
          url: app.globalData.api + '&r=util.uploader.upload&file=file',
          filePath: e.tempFilePaths[0],
          name: 'file',
          header: { "Content-Type": "multipart/form-data" },
          success(res) {
            var data = JSON.parse(res.data);
            if (data.error == 0) {
              var thumb = that.data.thumb
              var piclist = that.data.piclist
              if (piclist != null) {
                piclist.push(data.files[0].url)
                thumb.push(data.files[0].filename)
              } else {
                thumb[0] = data.files[0].filename
                piclist[0] = data.files[0].url
              }
              that.setData({
                piclist: piclist,
                thumb: thumb
              })
            } else {
              setTimeout(function () {
                wx.showToast({
                  title: '上传失败',
                  icon: 'none',
                  mask: true
                })
              }, 500)
            }
          },
          fail: function (e) {
            setTimeout(function () {
              wx.showToast({
                title: '上传失败',
                icon: 'none',
                mask: true
              })
            }, 500)
          },
          complete: function () {
            wx.hideToast()
          }
        })
      },
    })
  },
  /**
   * 提交表单
   */
  formSubmit: function (e) {
    wx.showLoading({
      title: 'loading...',
    })
    var that = this
    var data = {
      id: that.data.id,
      merchid: app.getMerchId(),
      title: e.detail.value.title,
      subtitle: e.detail.value.subtitle,
      unit: e.detail.value.unit,
      productprice: e.detail.value.productprice,
      marketprice: e.detail.value.marketprice,
      costprice: e.detail.value.costprice,
      total: e.detail.value.total,
      showtotal: e.detail.value.showtotal ? 1 : 0,
      weight: e.detail.value.weight,
      goodssn: e.detail.value.goodssn,
      productsn: e.detail.value.productsn,
      maxbuy: e.detail.value.maxbuy,
      minbuy: e.detail.value.minbuy,
      usermaxbuy: e.detail.value.usermaxbuy,
      cash: e.detail.value.cash ? 2 : 0,
      invoice: e.detail.value.invoice ? 1 : 0,
      status: e.detail.value.status ? 1 : 0,
      displayorder: e.detail.value.displayorder,
      totalcnf: that.data.stockIndex
    }
    if (that.data.checkboxItems[0].checked) {
      data.isrecommand = 1
    }
    if (that.data.checkboxItems[1].checked) {
      data.isnew = 1
    }
    if (that.data.checkboxItems[2].checked) {
      data.ishot = 1
    }
    if (that.data.checkboxItems[3].checked) {
      data.issendfree = 1
    }
    var str = ''
    for (var i=0;i<that.data.thumb.length;i++) {
      str += that.data.thumb[i]+','
    }
    data.thumbs = str
    if (app.isEmpty(data.thumbs)) {
      wx.showToast({
        title: '请选择商品主图',
        icon: 'none',
        mask: true
      })
      return
    }
    if (app.isEmpty(data.title)) {
      wx.showToast({
        title: '请输入商品名称',
        icon: 'none',
        mask: true
      })
      return
    }
    wx.request({
      url: app.globalData.api + '&r=amanage.goods.post',
      data: data,
      header:{
        'content-type': 'multipart/form-data'
      },
      success(res) {
        if (res.data.status == 1) {
          wx.showToast({
            title: '操作成功'
          })
          if (data.id != 0) {
            setTimeout(function () {
              that.getGoodsDetail()
            }, 1500)
          }
        }
      }
    })
  },
  /**
   * 商品详情
   */
  getGoodsDetail: function () {
    wx.showLoading({
      title: 'loading...',
    })
    var that = this
    wx.request({
      url: app.globalData.api + '&r=amanage.goods.detail',
      data: {
        id: that.data.id,
        merchid: app.getMerchId()
      },
      success(res) {
        if (res.data.status == 1) {
          //显示商品属性
          var item = res.data.result.item
          var checkboxItems = that.data.checkboxItems
          checkboxItems[0].checked = item.isrecommand == 1
          checkboxItems[1].checked = item.isnew == 1
          checkboxItems[2].checked = item.ishot == 1
          checkboxItems[3].checked = item.issendfree == 1
          that.setData({
            item: item,
            checkboxItems: checkboxItems,
            index: item.type > 0 ? item.type -1 : 0,
            stockIndex: item.totalcnf,
            piclist: res.data.result.piclist,
            thumb: res.data.result.thumb
          })
        } else {
          setTimeout(function () {
            wx.showToast({
              title: res.data.result.message,
              icon: 'none',
              mask: true
            })
          }, 500)
        }
      },
      complete() {
        wx.hideLoading()
      }
    })
  }
})