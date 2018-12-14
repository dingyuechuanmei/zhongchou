var t = getApp(),
  a = t.requirejs("core");
var u = t.requirejs("util");

// 提现金额变量
var withdrawalMonay = ''
var business_information = [
  {
    title: '商家名称',
    image: { logostate: '', logo: '', youimage: '' },
    input: { state: 'on', inputtype: 'text', inputplaceholder: '请输入商家名称', disabled: false },
    textarea: { state: '', textareaplaceholder: '' },
    sendCode: '',
    value: ''
  },
  // {
  //   title: '商家logo',
  //   image: { logostate: '', logo: '', youimage: 'on' },
  //   input: { state: 'on', inputtype: 'text', inputplaceholder: '请上传图片', disabled: true },
  //   textarea: { state: '', textareaplaceholder: '' },
  //   sendCode: '',
  //   value: ''
  // },
  {
    title: '主营项目',
    image: { logostate: '', logo: '', youimage: '' },
    input: { state: 'on', inputtype: 'text', inputplaceholder: '请输入主营项目', disabled: false },
    textarea: { state: '', textareaplaceholder: '' },
    sendCode: '',
    value: ''
  },
  {
    title: '商户介绍',
    image: { logostate: '', logo: '', youimage: '' },
    input: { state: 'on', inputtype: 'text', inputplaceholder: '填写一些简单的介绍吧', disabled: false },
    textarea: { state: '', textareaplaceholder: '' },
    sendCode: '',
    value: ''
  },
  {
    title: '联系人',
    image: { logostate: '', logo: '', youimage: '' },
    input: { state: 'on', inputtype: 'text', inputplaceholder: '请输入您的姓名', disabled: false },
    textarea: { state: '', textareaplaceholder: '' },
    sendCode: '',
    value: ''
  },
  {
    title: '手机号码',
    image: { logostate: '', logo: '', youimage: '' },
    input: { state: 'on', inputtype: 'text', inputplaceholder: '请输入您的手机号', disabled: false },
    textarea: { state: '', textareaplaceholder: '' },
    sendCode: '',
    value: ''
  },
  {
    title: '登陆账号',
    image: { logostate: '', logo: '', youimage: '' },
    input: { state: 'on', inputtype: 'text', inputplaceholder: '请输入登陆账号', disabled: false },
    textarea: { state: '', textareaplaceholder: '' },
    sendCode: '',
    value: ''
  },
  {
    title: '登陆密码',
    image: { logostate: '', logo: '', youimage: '' },
    input: { state: 'on', inputtype: 'password', inputplaceholder: '请输入登陆密码', disabled: false },
    textarea: { state: '', textareaplaceholder: '' },
    sendCode: '',
    value: ''
  },
  {
    title: '确认密码',
    image: { logostate: '', logo: '', youimage: '' },
    input: { state: 'on', inputtype: 'password', inputplaceholder: '请确认登陆密码', disabled: false },
    textarea: { state: '', textareaplaceholder: '' },
    sendCode: '',
    value: ''
  },
  // {
  //   title: '验证码',
  //   image: { logostate: '', logo: '', youimage: '' },
  //   input: { state: 'on', inputtype: 'number', inputplaceholder: '请输入验证码', disabled: false },
  //   textarea: { state: '', textareaplaceholder: ''},
  //   sendCode: '',
  //   value: ''
  // },
]

Page({
  data: {
    approot: t.globalData.approot,
    item: ['', '', '', '', '', ''],
    wu: { classname: '', text: '' },
    navArr: ['我参与的微客', '我发起的救助', '我的订单', '我的优惠券', '我的钱包', '商家入驻'],
    my_wallet: { balance: 'on', apply_withdrawal: '' },
    business_information: '',
    is_shop: false,
    topitem: [],
    delBtnWidth: 75//删除区域宽度
  },
  // 商店入驻部分
  // tenantsItembox1: function (e) {
  //   var self = this
  //   wx.chooseImage({
  //     count: 1, // 默认9
  //     sizeType: ['original', 'compressed'], // 可以指定是原图还是压缩图，默认二者都有
  //     sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
  //     success: function (res) {
  //       // 返回选定照片的本地文件路径列表，tempFilePath可以作为img标签的src属性显示图片
  //       promptshowToast('上传成功')
  //       setTimeout(function() {
  //         business_information[1].input.state = ''
  //         business_information[1].image.logostate = 'on'
  //         business_information[1].image.logo = res.tempFilePaths[0]
  //         business_information[1].value = true
  //         self.setData({
  //           business_information: business_information
  //         })
  //       },1000)
  //     }
  //   })
  // },

  // 手指开始触摸
  touchS: function(e){
    if(e.touches.length == 1){
      this.setData({
        startX: e.touches[0].clientX//手指触摸相对屏幕的初始距离
      })
      console.log('初始距离：' + this.data.startX)
    }
  },
  //手指触摸中
  touchM: function (e) {
    console.log("touchM:" + e);
    var that = this
    if (e.touches.length == 1) {
      //记录触摸点位置的X坐标
      var moveX = e.touches[0].clientX;
      //计算手指起始点的X坐标与当前触摸点的X坐标的差值
      var disX = that.data.startX - moveX;
      //delBtnWidth 为右侧按钮区域的宽度
      var delBtnWidth = that.data.delBtnWidth;
      var txtStyle = "";
      if (disX == 0 || disX < 0) {//如果移动距离小于等于0，文本层位置不变
        txtStyle = "left:0px";
      } else if (disX > 0) {//移动距离大于0，文本层left值等于手指移动距离
        txtStyle = "left:-" + disX + "px";
      if (disX >= delBtnWidth) {
        //控制手指移动距离最大值为删除按钮的宽度
        txtStyle = "left:-" + delBtnWidth + "px";
      }
      }
      //获取手指触摸的是哪一个item
      var index = e.currentTarget.dataset.index;
      var list = that.data.verify_list;
      //将拼接好的样式设置到当前item中
      list[index].style = txtStyle;
      //更新列表的状态
      this.setData({
        verify_list: list
      });
    }
  },
  //手指触摸结束
  touchE: function (e) {
    console.log("touchE" + e);
    var that = this
    if (e.changedTouches.length == 1) {
      //手指移动结束后触摸点位置的X坐标
      var endX = e.changedTouches[0].clientX;
      //触摸开始与结束，手指移动的距离
      var disX = that.data.startX - endX;
      var delBtnWidth = that.data.delBtnWidth;
      //如果距离小于删除按钮的1/2，不显示删除按钮
      var txtStyle = disX > delBtnWidth / 2 ? "left:-" + delBtnWidth + "px" : "left:0px";
      //获取手指触摸的是哪一项
      var index = e.currentTarget.dataset.index;
      var list = that.data.verify_list;
      list[index].style = txtStyle;
      //更新列表的状态
      that.setData({
        verify_list: list
      });
    }
  },
  //删除该项
  deleteItem: function(e){
    var that= this;
    var id = e.currentTarget.dataset.id;
    var list = that.data.verify_list;
    wx.showModal({
      title: '警告',
      content: '确认要删除么？',
      success: function (res) {
        if (res.confirm) {

          a.post('raise.part_starter_delete', { id: id }, function (json) {
            for (var i in list) {
              if (id == list[i].id) {
                list.splice(list[i], 1)
              }
            }
            that.setData({
              verify_list: list
            });
          })

        }
      }
    })
  }, 
  tenantsItem: function (e) {
    var idx = e.target.dataset.idx;
    business_information[idx].value = e.detail.value
  },

  sendCode: function (e) {
    var phone = business_information[5].value
    if (!checkPhone(phone)) {
      promptshowModal('手机号码有误，请重填')
      return
    }
    if (checkPhone(phone) == 18370395001) {
      promptshowModal('该手机号已绑定')
      return
    }
    promptshowToast('发送成功')
  },

  tenantsSave: function () {

    var self = this

    console.log(business_information)

    for (var i = 0; i < business_information.length; i++) {
      if (!business_information[i].value) {
        promptshowModal('请完善信息')
        return
      }
    }

    var phone = business_information[4].value
    if (!checkPhone(phone)) {
      promptshowModal('手机号码有误，请重填')
      return
    }

    if (business_information[6].value != business_information[7].value) {
      promptshowModal('两次输入验证码不一致')
      return
    }

    if (self.data.chooseImage == '' || self.data.chooseImage == undefined || self.data.chooseImage == null ) {
      promptshowModal('请上传营业执照!')
      return
    }

    var data = {
      'realname': business_information[3].value,
      'mobile': business_information[4].value,
      'uname': business_information[5].value,
      'upass': business_information[6].value,
      'merchname': business_information[0].value,
      'salecate': business_information[1].value,
      'desc': business_information[2].value,
      'license_img': self.data.chooseImage,
    }
    console.log(data);

    a.post('shop.merch.register', data, function (json) {
      console.log(json);
      if (json.error == 0) {
        promptshowToast('注册成功')
        setTimeout(function () {
          urlback(1)
        }, 1000)
      } else {
        promptshowModal(json.message)
      }

    })

    self.setData({
      business_information: business_information
    })
    urlback(1)
  },
  // 我的钱包部分
  applyWithdrawal: function () {
    urlto('/pages/member/withdraw/index');
    return false


    var self = this
    if (self.data.balance == 0) {
      wx.showModal({
        title: '',
        content: '您的余额不足',
      })
      return
    }
    var my_wallet = { balance: '', apply_withdrawal: '' }
    my_wallet.apply_withdrawal = 'on'
    wx.setNavigationBarTitle({
      title: '申请提现',
    })
    self.setData({
      my_wallet: my_wallet
    })
  },
  withdrawalMonayInput: function (e) {
    withdrawalMonay = e.detail.value
  },
  withdrawal: function (e) {
    var self = this
    if (!withdrawalMonay) {
      promptshowModal('请输入提现金额')
      return
    }
    if (withdrawalMonay > self.data.balance) {
      promptshowModal('余额不足')
      return
    }
    promptshowToast('成功提现')
    setTimeout(function () {
      urlback(1)
    }, 1000)

  },
  allWithdrawal: function () {
    var self = this
    withdrawalMonay = Number(self.data.balance)
    self.setData({
      withdrawalMonay: withdrawalMonay
    })
  },
  // 我参与的微客部分
  urlZhongchoudetail: function (e) {
    urlto('../zhongchouitemdetail/zhongchouitemdetail?id='+e.currentTarget.dataset.id)
  },
  //我发起的救助
  urlHelpdetail: function () {
    urlto('../zhongchouitemdetail/zhongchouitemdetail')
  },
  uploadimage: function () {
    var self = this
    wx.chooseImage({
      count: 1, // 默认9
      sizeType: ['original', 'compressed'], // 可以指定是原图还是压缩图，默认二者都有
      sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
      success: function (res) {
        // 返回选定照片的本地文件路径列表，tempFilePath可以作为img标签的src属性显示图片
        console.log(res);
        
        self.setData({
          'tmpchooseImage': res.tempFilePaths
        })

        var url = a.getUrl("util/uploader/upload", {file: "file"});
        wx.uploadFile({
          url: url,
          filePath: res.tempFilePaths[0],
          name: 'file',
          header: { "Content-Type": "multipart/form-data" },
          success: function (res) {
            var data = JSON.parse(res.data);
            console.log(res)
            console.log(data.files[0].url)
            self.setData({
              'chooseImage': data.files[0].url
            })
          }
        });


      }
    })
  },
  removeimage: function (e) {
    this.setData({
      'tmpchooseImage': '',
    })
  },
  topitem: function (e) {
    var that = this;
    var topitem = that.data.topitem;
    var id = e.currentTarget.dataset.id;
    for (var i = 0; i < topitem.length; i++) {
      if (topitem[i].id == id) {
        topitem[i].state = 'on';
      } else {
        topitem[i].state = '';
      }
    }

    that.setData({
      topitem: topitem,
      cateid: id
    });

    a.post('raise.part_starter', { category: id }, function (json) {
      console.log(json);
      var verify_list = [];
      if (json.error == 0) {
        json.verify_list ? json.verify_list : []
        for (var i = json.verify_list.length - 1; i >= 0; i--) {
          json.verify_list[i].createtime = u.getLocalTime(json.verify_list[i].createtime)
          json.verify_list[i].state = status(json.verify_list[i].status)
        }
        verify_list = json.verify_list;
      }

      that.setData({
        verify_list: verify_list
      })
    })

  },

  // onload部分
  onLoad: function (options) {
    // options.item = 5;
    var self = this
    var ishas = true
    var wu = { classname: '', text: '' }
    var idx = Number(options.item)
    var item = ['', '', '', '', '', '']
    item[idx] = 'on'
    switch (idx) {
      case 0:
        // 我参与的微客
        // 获取数据顶部分类
        a.post('raise.get_starter_category_all', {}, function (json) {
          console.log(json)
          var topitem = [];
          for (var i = 0; i < json.category_list.length; i++) {
            topitem.push({
              id: json.category_list[i].id,
              name: json.category_list[i].category,
              state: ''
            })
          }
          var topitemtmp = JSON.parse(JSON.stringify(topitem))
          topitemtmp[0].state = 'on'
          self.setData({
            'topitem': topitemtmp
          })

          a.post('raise.part_starter', { category: topitemtmp[0].id }, function (json) {
            console.log(json);
            if (json.error == 0) {
              json.error == '1' ? ishas = false : ishas = true
              json.verify_list ? json.verify_list : []
              for (var i = json.verify_list.length - 1; i >= 0; i--) {
                json.verify_list[i].createtime = u.getLocalTime(json.verify_list[i].createtime)
                json.verify_list[i].state = status(json.verify_list[i].status)
              }
              self.setData({
                verify_list: json.verify_list
              })
            }
          })

        })

        break;
      case 1:
        // 我发起的救助
        a.post('raise.publish_starter', {}, function (json) {
          json.error == '1' ? ishas = false : ishas = true
          for (var i = json.starter_list.length - 1; i >= 0; i--) {
            json.starter_list[i].audittime = u.getLocalTime(json.starter_list[i].audittime)
            json.starter_list[i].aborttime = u.getLocalTime(json.starter_list[i].aborttime)
            json.starter_list[i].state = status(json.starter_list[i].status)
          }
          self.setData({
            starter_list: json.starter_list
          })
        })
        break;
      case 2:
        break;
      case 3:
        break;
      case 4:
        //我的钱包
        a.post('raise.center', {}, function (json) {
          self.setData({
            'balance': Number(json.member.credit2)
          })

        })
        break;
      case 5:
        break;
      case 6:
        break;
      default:
        break;
    }

    if (options.item == 5) {
      // 验证是否注册
      a.post('shop.merch.check_register', {

      }, function (json) {
        
        var user = json.user;
        var reg = json.reg;
        
        console.log(reg)

        if (user.merchname != '' && user.merchname != undefined) {
          for (var i = 0; i < business_information.length; i++) {
            console.log(business_information[i])

            if (i == 0) {
              business_information[i].value = user.merchname;
            } else if (i == 1) {
              business_information[i].value = user.salecate;
            } else if (i == 2) {
              business_information[i].value = user.desc;
            } else if (i == 3) {
              business_information[i].value = user.realname;
            } else if (i == 4) {
              business_information[i].value = user.mobile;
            } else if (i == 5) {
              business_information[i].value = user.uname;
            }

          }
          wx.setNavigationBarTitle({
            title: '修改商户信息'
          });
          self.setData({
            business_information: business_information,
            reg:reg
          })
        }
      });
    }



    // 如果没有数据,是否有数据，数据变量为ishas = false 表示没有数据
    if (!ishas) {
      if (idx == 0) {
        wu = { classname: 'on', text: '您还没有参与众筹哦！' }
      }
      if (idx == 1) {
        wu = { classname: 'on', text: '您还没有发起救助哦！' }
      }
    }

    self.setData({
      item: item,
      wu: wu
    })

    wx.setNavigationBarTitle({
      title: self.data.navArr[idx],
    })

    self.setData({
      business_information: business_information
    })



  }
})

function status(idx) {
  idx = Number(idx)
  var state
  switch (idx) {
    case 0:
      state = '待审核 '
      break;
    case 1:
      state = '筹款中 '
      break;
    case 2:
      state = '审核失败'
      break;
    case 3:
      state = '已结束 '
      break;
    default:
      break;
  }
  return state
}

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
function urlto(url) {
  wx.navigateTo({
    url: url,
  })
}
function urlback(delta) {
  wx.navigateBack({
    delta: delta
  })
}
function checkPhone(phone) {
  var reg = /^1[34578]\d{9}$/
  return reg.test(phone)
}

