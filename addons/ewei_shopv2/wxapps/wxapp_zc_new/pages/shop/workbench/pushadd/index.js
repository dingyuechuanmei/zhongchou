var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    id: 0,
    index:0,
    video: '',
    video_url: '',
    arrow: app.globalData.approot + 'wxapp_attr/you.png',
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.getCategory()
    if (options.id) {
      this.setData({
        id: options.id
      })
      wx.setNavigationBarTitle({
        title: '众推编辑'
      })
      this.getPusherDetail()
    } else {
      wx.setNavigationBarTitle({
        title: '添加众推'
      })
    }
  },
  bindPickerChange: function (e) {
    this.setData({
      index: e.detail.value
    })
  },
  /**
   * 获取众推详情
   */
  getPusherDetail: function () {
    wx.showLoading({
      title: 'loading...',
    })
    var that = this
    wx.request({
      url: app.globalData.api + "&r=amanage.pusher.detail",
      data: {
        id: that.data.id,
        merchid: app.getMerchId()
      },
      success(res) {
        res.data.status == 1 ? that.setData({
          item: res.data.result.item,
          video: res.data.result.item.video,
          video_url: res.data.result.item.video_url
        }) : wx.showToast({
          title: '加载失败,请重试',
          icon: 'none',
          mask: true
        })
      },
      complete() {
        wx.hideLoading();
      }
    })
  },
  /**
   * 众推分类
   */
  getCategory: function () {
    var that = this
    wx.request({
      url: app.globalData.api + "&r=amanage.pusher.cate",
      success(res) {
        that.setData({
          cate: res.data.result.cate
        })
      }
    })
  },
  /**
   * 上传视频
   */
  uploadVideo: function () {
    var that = this
    wx.chooseVideo({
      success(e){
        wx.showToast({
          icon: "loading",
          title: "正在上传"
        });
        console.log(e)
        if (e.size / 1024 / 1024 > 25) {
          wx.showToast({
            title: '视频文件不能大于25M',
            icon: 'none',
            mask: true
          })
          return
        }
        /**
         * 上传视频
         */
        wx.uploadFile({
          url: app.globalData.api + '&r=util.uploader.video&file=file',
          filePath: e.tempFilePath,
          name: 'file',
          header: { "Content-Type": "multipart/form-data" },
          success(res) {
            var data = JSON.parse(res.data);
            if (data.error == 0) {
              that.setData({
                video: data.files[0].filename,
                video_url: data.files[0].url
              })
              setTimeout(function () {
                wx.showToast({
                  title: '上传成功',
                  mask: true
                })
              },500)
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
          complete(){
            wx.hideLoading()
          }
        })
      }
    })
  },
  formSubmit: function (e) {
    wx.showLoading({
      title: 'loading...',
    })
    var that = this
    var data = {
      id: that.data.id,
      merchid: app.getMerchId(),
      title: e.detail.value.title,
      content: e.detail.value.content,
      video: that.data.video,
      category: that.data.cate[that.data.index].id,
      ifshow: e.detail.value.ifshow ? 1 : 0
    }
    if (app.isEmpty(data.video)) {
      wx.showToast({
        title: '请上传众推视频',
        icon: 'none',
        mask: true
      })
      return
    }
    if (app.isEmpty(data.title)) {
      wx.showToast({
        title: '请输入众推名称',
        icon: 'none',
        mask: true
      })
      return
    }
    wx.request({
      url: app.globalData.api + '&r=amanage.pusher.post',
      data: data,
      header: {
        'content-type': 'multipart/form-data'
      },
      success(res) {
        if (res.data.status == 1) {
          wx.showToast({
            title: '操作成功'
          })
          setTimeout(function(){
            wx.redirectTo({
              url: '../pushlist/index',
            })
          },1500)
        }
      }
    })
  }
})