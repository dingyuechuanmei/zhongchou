var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    filename: null,
    backfilename: null
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.setData({
      merchid: options.merchid
    });
    this.getInfo();
  },
  /**
   * 修改logo
   */
  changeLogo:function () {
    var that = this
    wx.chooseImage({
      count:1,
      success: function(e) {
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
          success(res){
            var data = JSON.parse(res.data);
            data.error == 0 ? that.setData({
              logo: data.files[0].url,
              filename: data.files[0].filename
            }) : setTimeout(function(){
              wx.showToast({
                title: '上传成功',
                icon: 'none',
                mask: true
              })
            },500)
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
  changeBackgroundImg: function () {
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
            data.error == 0 ? that.setData({
              background_img: data.files[0].url,
              backfilename: data.files[0].filename
            }) : setTimeout(function () {
              wx.showToast({
                title: '上传成功',
                icon: 'none',
                mask: true
              })
            }, 500)
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
  //商户信息
  getInfo: function() {
    wx.showLoading({
      title: 'loading...',
    })
    var _this = this;
    wx.request({
      url: app.globalData.api +'&r=amanage.get_info',
      data:{
        merchid: _this.data.merchid
      },
      success(res) {
        res.data.info.status = parseInt(res.data.info.status)
        _this.setData({
          info: res.data.info,
          logo: res.data.info.logo,
          background_img: res.data.info.background_img,
          filename: res.data.info.filename,
          backfilename: res.data.info.backfilename,
        })
        wx.hideLoading()
      }
    })
  },
  /**
   * 修改资料
   */
  formSubmit: function (e) {
    wx.showLoading({
      title: "正在提交",
      mask: true
    })
    var that = this
    var data = {
      shopname: e.detail.value.shopname,
      shopdesc: e.detail.value.shopdesc,
      shopclose: e.detail.value.shopclose ? 1 : 0,
      shoplogo: that.data.filename,
      background_img: that.data.backfilename,
      merchid: that.data.merchid
    }
    wx.request({
      url: app.globalData.api + '&r=amanage.save_info',
      data: data,
      success(res) {
        res.data.status == 1 ? setTimeout(function(){
          wx.showToast({
            title: '保存成功',
            mask: true
          })
        },500) : setTimeout(function(){
          wx.showToast({
            title: res.data.result.message,
            icon: 'none',
            mask: true
          })
        },500)
      },
      complete() {
        that.getInfo();
        wx.hideLoading();
      }
    })
  } 
})