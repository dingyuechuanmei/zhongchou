var t = getApp(),
  a = t.requirejs("core");
var me 

Page({
  data: {
    approot: t.globalData.approot,
    chooseImagecount: 8,
    detail: {
      inputprice: '',
      pickerChange: '',
      inputtitle: '',
      videosrc: '',
      inputtextarea: '',
      imagelist: [],
      tmpchooseImage:[],
      pickerChange_id: ''
    }
  },
  inputprice: function(e) {
    var self = this
    self.setData({
      'detail.inputprice': e.detail.value
    })
  },
  inputtitle: function (e) {
    var self = this
    self.setData({
      'detail.inputtitle': e.detail.value
    })
  },
  inputtextarea: function (e) {
    var self = this
    self.setData({
      'detail.inputtextarea': e.detail.value
    })
  },
  bindPickerChange: function (e) {
    var self = this
    self.setData({
      'detail.pickerChange': self.data.category_list[e.detail.value],
      'detail.pickerChange_id': self.data.category_id[e.detail.value]
    })
  },
  uploadvideo: function() {
    var self = this
    wx.chooseVideo({
      sourceType: ['album', 'camera'],
      maxDuration: 60,
      camera: 'back',
      success: function (res) {
        // 返回选定视频的本地文件路径列表，上传得到服务器路径
        if (res.size/1024/1024 > 25) {
          wx.showModal({
            title: '',
            content: '视频文件不能大于25M',
          })
        }
        upload_video(self, res.tempFilePath);

        var timer = setInterval(function () {
          if (tmpupload_video_list) {
            clearInterval(timer)
            // 得到上传文件的服务器路径
            promptshowToast('上传成功')
            console.log(tmpupload_video_list)
            self.setData({
              'detail.videosrc': tmpupload_video_list
            })
          }
        }, 13)
      }
    })
  },
  uploadimage: function() {
    var self = this
    wx.chooseImage({
      count: self.data.chooseImagecount, // 默认9
      sizeType: ['original', 'compressed'], // 可以指定是原图还是压缩图，默认二者都有
      sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
      success: function (res) {
        // 返回选定照片的本地文件路径列表，tempFilePath可以作为img标签的src属性显示图片
        var tmpchooseImage = self.data.detail.tmpchooseImage.concat(res.tempFilePaths)
        var chooseImagecount = 8 - tmpchooseImage.length
        if (chooseImagecount < 0) {
          wx.showModal({
            title: '',
            content: '上传的照片不能超过8张',
          })
          return
        }
        self.setData({
          'detail.tmpchooseImage': tmpchooseImage,
          'chooseImagecount': chooseImagecount
        })
          
          upload(self, res.tempFilePaths);
          var timer = setInterval(function () {
            if (tmpupload_img_list.length == res.tempFilePaths.length) {
              clearInterval(timer)
              // 得到上传文件的服务器路径
              promptshowToast('上传成功')
              var imagelist = self.data.detail.imagelist.concat(tmpupload_img_list)
              console.log(JSON.stringify(imagelist))
              self.setData({
                'detail.imagelist': imagelist
              })
            }
          }, 13)
   
      }
    })
  },
  removeimage: function(e) {
    var self = this
    var imagelist = self.data.detail.imagelist
    imagelist.splice(e.currentTarget.dataset.idx, 1)
    var tmpchooseImage = self.data.detail.tmpchooseImage
    tmpchooseImage.splice(e.currentTarget.dataset.idx, 1)
    self.setData({
      'detail.imagelist': imagelist,
      'detail.tmpchooseImage':tmpchooseImage,
      'chooseImagecount': 8 - tmpchooseImage.length
    })
  },
  removevideo:function(e) {
    var self = this
    self.setData({
      'detail.videosrc': ''
    })
  },
  sure:function() {
    var self = this
    var detail = self.data.detail
    var isall = true
    for (var i in detail) {
      if (detail[i].length) {
        isall = false
      }
    }    
    console.log(detail);
    if (isall) {
      promptshowModal('请完善信息')
      return
    }
    if (!detail.inputprice.length) {
      promptshowModal('请填写目标金额')
      return
    }
    if (!detail.pickerChange.length) {
      promptshowModal('请选择筹款类型')
      return
    }
    if (!detail.inputtitle.length) {
      promptshowModal('请填写筹款标题')
      return
    }
    if (!detail.videosrc.length) {
      promptshowModal('请上传小视频')
      return
    }
    if (!detail.inputtextarea.length) {
      promptshowModal('请填写求助说明')
      return
    }
    if (!detail.imagelist.length) {
      promptshowModal('请上传照片')
      return
    }
    console.log(me.data.detail.imagelist)
    // 发起微客
    a.post('raise.starter_post', {
      target_money: me.data.detail.inputprice,
      category_id: me.data.detail.pickerChange_id,
      title: me.data.detail.inputtitle,
      video: me.data.detail.videosrc,
      content: me.data.detail.inputtextarea,
      thumbs: JSON.stringify(me.data.detail.imagelist)
    }, function (json) {
      if (json.error == 1) {
        promptshowToast('提交失败')
        return
      }
      promptshowToast('提交成功')
      setTimeout(function () {
        wx.navigateBack({
          delta: 1
        })
      }, 1500)
    });
  },
  onLoad: function (options) {
    me = this
    // 获取筹款类型列表
    a.post('raise.get_starter_category_all', {}, function (json) {
      var category_list = []
      var category_id = []
      for (var i = 0; i < json.category_list.length; i++) {
        category_list.push(json.category_list[i].category)
        category_id.push(json.category_list[i].id)
      }
      me.setData({
        category_list: category_list,
        category_id: category_id,
        customer_service_number: json.customer_service_number
      })
    });

  }
})
function promptshowToast(meg) {
  wx.showToast({
    title: meg,
    duration: 1000
  })
}
function promptshowModal(meg) {
   wx.showModal({
     title: '提示',
     content: meg,
   })
}

var tmpupload_img_list = []
function upload(page, path) {
  tmpupload_img_list = []
  var tmpupload_img =''
  var image_list = page.data.imageList;

  // if (image_list && image_list.length > 1) {
  //   wx.showModal({ 'title': '温馨提示!', 'content': '图片不能超过两张' });
  //   return;
  // }
  var $url = a.getUrl("util/uploader/upload", {
    file: "file"
  });
  
  for (var i = 0; i < path.length; i++) {
    wx.uploadFile({
      url: $url,
      filePath: path[i],
      name: 'file',
      header: { "Content-Type": "multipart/form-data" },
      success: function (res) {
        console.log(res)
        if (res.data) {
          tmpupload_img_list.push(JSON.parse(res.data).files[0].url)
        }
      }
    });
  }
}

var tmpupload_video_list = ''
function upload_video(page, path) {
  tmpupload_video_list = ''
  var $url = a.getUrl("util/uploader/video", {
    file: "file"
  });
  wx.uploadFile({
    url: $url,
    filePath: path,
    name: 'file',
    header: { "Content-Type": "multipart/form-data" },
    success: function (res) {
      console.log(res)
      if (res.data) {
        tmpupload_video_list = JSON.parse(res.data).files[0].url
      }
    },
    fail: function (res) {
      console.log(res);
    }
  });
}
