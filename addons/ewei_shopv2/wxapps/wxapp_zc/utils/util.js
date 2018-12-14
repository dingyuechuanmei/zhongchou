function formatTime(date) {
  var year = date.getFullYear()
  var month = date.getMonth() + 1
  var day = date.getDate()

  var hour = date.getHours()
  var minute = date.getMinutes()
  var second = date.getSeconds()


  return [year, month, day].map(formatNumber).join('/') + ' ' + [hour, minute, second].map(formatNumber).join(':')
}

function formatNumber(n) {
  n = n.toString()
  return n[1] ? n : '0' + n
}

//转换成多少时间前
function get_data_ago(time) {
  time = Number(time) * 1000
  var ctime = new Date()
  var ptime = new Date(time)
  var y = ctime.getFullYear() - ptime.getFullYear()
  var M = ctime.getMonth() - ptime.getMonth()
  var d = ctime.getDate() - ptime.getDate()
  var h = ctime.getHours() - ptime.getHours()
  var m = ctime.getMinutes() - ptime.getMinutes()
  var s = ctime.getSeconds() - ptime.getSeconds()
  if (y == 0) {
    if (M == 0) {
      if (d == 0) {
        if (h == 0) {
          if (m == 0) {
            return '刚刚'
          } else {
            return m + '分钟前'
          }
        } else {
          return h + '小时前'
        }
      } else {
        return d + '天前'
      }
    } else {
      return M + '个月前'
    }
  } else {
    return y + '年前'
  }
}


// 时间戳转成日期格式
function getLocalTime(nS) {
  if (!nS) {
    return 0
  }
  return new Date(parseInt(nS) * 1000).toLocaleString().replace(/:\d{1,2}$/, ' ');

}
Date.prototype.toLocaleString = function () {
  return this.getFullYear() + "." + (this.getMonth() + 1) + "." + this.getDate()
};



//保留两个小数点
function save_two_points(num) {
  num = num.toString()
  var re = /([0-9]+\.[0-9]{2})[0-9]*/;
  num = num.replace(re, "$1");
  return num
}




module.exports = {
  formatTime: formatTime,
  getLocalTime: getLocalTime,
  save_two_points: save_two_points,
  get_data_ago: get_data_ago
}
