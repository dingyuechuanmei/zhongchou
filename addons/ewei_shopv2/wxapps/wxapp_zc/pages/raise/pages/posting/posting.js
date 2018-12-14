var t = getApp(),
    a = t.requirejs("core");
var me 
Page({
  data: {
    user: {
      title: '',
      content: '',
      post_img: [],
      good_img: [],
      plate: ''
    },
    goods_list: [],
    chooseImagecount: 9,
    detail: {
      imagelist: [],
      tmpchooseImage: [],
    }
  },
  inputTitle: function(e) {
    getInoutValue(e,'title')
  },
  inputContent: function (e) {
    getInoutValue(e, 'content')
  },
  chooseImg: function() {
    wx.chooseImage({
      count: me.data.chooseImagecount, // 默认9
      sizeType: ['original', 'compressed'], // 可以指定是原图还是压缩图，默认二者都有
      sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
      success: function (res) {
        // 返回选定照片的本地文件路径列表，tempFilePath可以作为img标签的src属性显示图片
        var tmpchooseImage = me.data.detail.tmpchooseImage.concat(res.tempFilePaths)
        var chooseImagecount = 9 - tmpchooseImage.length
        if (chooseImagecount < 0) {
          wx.showModal({
            title: '',
            content: '上传的照片不能超过9张',
          })
          return
        }
        me.setData({
          'detail.tmpchooseImage': tmpchooseImage,
          'chooseImagecount': chooseImagecount
        })

        upload(me, res.tempFilePaths);
        var timer = setInterval(function () {
          if (tmpupload_img_list.length == res.tempFilePaths.length) {
            clearInterval(timer)
            // 得到上传文件的服务器路径
            wx.showToast({
              title: '上传成功',
            })
            var imagelist = me.data.detail.imagelist.concat(tmpupload_img_list)
            me.setData({
              'detail.imagelist': imagelist
            })
          }
        }, 13)
      }
    })
  },
  removeimage: function (e) {
    var imagelist = me.data.detail.imagelist
    imagelist.splice(e.currentTarget.dataset.idx, 1)
    var tmpchooseImage = me.data.detail.tmpchooseImage
    tmpchooseImage.splice(e.currentTarget.dataset.idx, 1)
    me.setData({
      'detail.imagelist': imagelist,
      'detail.tmpchooseImage': tmpchooseImage,
      'chooseImagecount': 8 - tmpchooseImage.length
    })
  },
  addGood: function() {
    wx.navigateTo({
      url: './goods/goods',
    })
  },
  removegoods: function(e) {
    var goods_list = me.data.goods_list
    goods_list.splice(e.currentTarget.dataset.idx, 1)
    me.setData({
      goods_list: goods_list
    })
    var tmp_goods_list = []
    for (var i = 0; i < goods_list.length; i++) {
      var item = goods_list[i]
      item = item.join(',')
      tmp_goods_list.push(item)
    }
    wx.setStorage({
      key: "goods_list",
      data: tmp_goods_list,
      success: function () {
      }
    })
  },
  chooseModel: function(e) {
    var cate_list = me.data.cate_list
    for (var i = cate_list.length - 1; i >= 0; i--) {
      cate_list[i].state = ''
      if (cate_list[i].id == e.currentTarget.dataset.id) {
        cate_list[i].state = 'on'
      }
    }
    me.setData({
      cate_list: cate_list,
      'user.plate': e.currentTarget.dataset.id
    })
  },
  surebtn: function() {
    if (!me.data.user.title || me.data.user.title.length < 3 || me.data.user.title.length > 30) {
      wx.showModal({
        content: '帖子标题在3~30个字之内',
      })
      return
    }
    if (!me.data.user.content || me.data.user.content.length < 5 || me.data.user.content.length > 5000) {
      wx.showModal({
        content: '帖子内容在5~5000个字之内',
      })
      return
    }
    if (!me.data.user.plate) {
      wx.showModal({
        content: '请选择版块',
      })
      return
    }
    var tecom_good = me.data.goods_list
    var tmp_tecom_good = []
    for (var i = 0; i < tecom_good.length; i++) {
      tmp_tecom_good.push(tecom_good[i][0])
    }

    var pushdata = {
      title: me.data.user.title,
      context: me.data.user.content,
      cate: me.data.user.plate,
      thumbs: me.data.detail.imagelist.join(','),
      tecom_good: tecom_good.join(',')
    }
    // 发帖
    a.post('forum.issue_posts', pushdata, function (json) {
      wx.showToast({
        title: json.msg,
      })
      setTimeout(function() {
        wx.navigateBack();   //返回上一个页面
      })
    });
  },
  onShow: function() {
     // 获取子集返回的值
    wx.getStorage({
      key: 'goods_list',
      success: function (res) {
        var goods_list = res.data ? res.data : []
        goods_list = ArrayHeavy(goods_list)
        var tmp_goods_list = []
        for (var i = 0; i < goods_list.length; i++) {
          var item = goods_list[i]
              item = item.split(',')
              tmp_goods_list.push(item)
          
        }
        me.setData({
          goods_list: tmp_goods_list
        })
      }
    })
  },
  onLoad: function (options) {
    me = this
    // 获取分类列表
    a.post('forum.forum_cate', {}, function (json) {
      if (json.error != 0) {
        me.setData({
          cate_list: []
        })
      } else {
        var cate_list = json.cate_list
        for (var i = cate_list.length - 1; i >= 0; i--) {
          cate_list[i].state = ''
        }
        me.setData({
          cate_list: cate_list
        })
      }
    });
  }
})

//获取input的值
function getInoutValue(e,inputname) {
  var inputnameTmp = inputname
  inputname = 'user.' + inputname
  me.data.user[inputnameTmp] = e.detail.value
}

// 数值去重
function ArrayHeavy(arr) {
  var len = arr.length
  for (var i = 0; i < len; i++) {
    for (var j = i + 1; j < len; j++) {
      if (arr[i] == arr[j]) {
        arr.splice(j, 1);
        len--;
        j--;
      }
    }
  }
  return arr
}

// 上传图片
var tmpupload_img_list = []
function upload(page, path) {
  tmpupload_img_list = []
  var tmpupload_img = ''
  var image_list = page.data.imageList;
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
        if (res.data) {
          tmpupload_img_list.push(JSON.parse(res.data).files[0].url)
        }
      }
    });
  }
}
