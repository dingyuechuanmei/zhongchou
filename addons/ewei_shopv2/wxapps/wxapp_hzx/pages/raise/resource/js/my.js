Date.prototype.toLocaleString = function () {
  return this.getFullYear() + "." + (this.getMonth() + 1) + "." + this.getDate()
};

var my = {
  get: function (url, data, cb) {
    wx.request({
      url: url, //仅为示例，并非真实的接口地址,
      data: data,
      header: {
        'content-type': 'application/json' // 默认值
      },
      success: function (res) {
        typeof cb == "function" && cb(res.data, "");
      },
      fail: (err) => {
        typeof cb == "function" && cb(null, err.message);
      }
    })
  },
  post: function(url, data, cb){
    wx.request({
      url: url, //仅为示例，并非真实的接口地址,
      method: 'POST',
      data: data,
      header: {
        "Content-Type": "application/x-www-form-urlencoded"//跨域请求
      },
      success: function (res) {
        typeof cb == "function" && cb(res.data, "");
      },
      fail: (err) => {
        typeof cb == "function" && cb(null, err.message);
      }
    })
  },
  getLocalTime: function(nS) {
    return new Date(parseInt(nS) * 1000).toLocaleString().replace(/:\d{1,2}$/, ' ');
  }
}

module.exports = {
  my: my
}