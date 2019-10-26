//index.js
//获取应用实例
const app = getApp()

Page({
  data: {
    motto: 'Hello World',
    userInfo: {},
    hasUserInfo: false,
    canIUse: wx.canIUse('button.open-type.getUserInfo'),
    type: 'bySelf',
    item: 'item1',
    toView: '',
    toViewMenu: '',
    heightArr: []
  },
  //事件处理函数
  bindViewTap: function() {
    wx.navigateTo({
      url: '../logs/logs'
    })
  },
  onLoad: function() {
    //WU
    if (app.globalData.userInfo) {
      this.setData({
        userInfo: app.globalData.userInfo,
        hasUserInfo: true
      })
    } else if (this.data.canIUse) {
      // 由于 getUserInfo 是网络请求，可能会在 Page.onLoad 之后才返回
      // 所以此处加入 callback 以防止这种情况
      app.userInfoReadyCallback = res => {
        this.setData({
          userInfo: res.userInfo,
          hasUserInfo: true
        })
      }
    } else {
      // 在没有 open-type=getUserInfo 版本的兼容处理
      wx.getUserInfo({
        success: res => {
          app.globalData.userInfo = res.userInfo
          this.setData({
            userInfo: res.userInfo,
            hasUserInfo: true
          })
        }
      })
    }
  },
  onReady: function() {
    /*获取商品栏标题位置*/
    let queryItem = wx.createSelectorQuery();
    let queryTop = wx.createSelectorQuery();
    let heightArr = [];
    let topHeight = 0;
    //顶部高度
    queryTop.select('.top').boundingClientRect((res) => {
      topHeight = res.height;
    }).exec();
    //商品标题高度
    queryItem.selectAll('.item-title').boundingClientRect((react) => {
      react.forEach((res) => {
        heightArr.push(res.top - topHeight);
      });
    }).exec();
    this.setData({
      heightArr: heightArr
    });
    //console.log(heightArr);
  },
  getUserInfo: function(e) {
    console.log(e)
    app.globalData.userInfo = e.detail.userInfo
    this.setData({
      userInfo: e.detail.userInfo,
      hasUserInfo: true
    })
  },
  onSelectType: function(e) {
    this.setData({
      type: e.currentTarget.id
    });
  },
  onSelectItem: function(e) {
    this.setData({
      item: e.currentTarget.dataset.id,
      toView: e.currentTarget.dataset.id
    });
  },
  scrollRight: function(e) {
    let scrollArr = this.data.heightArr;
    let scrollTop = e.detail.scrollTop - scrollArr[0];
    // console.log(scrollTop);
    
    
    if (scrollTop >= scrollArr[scrollArr.length - 1]) {
      return
    } else {
      for (let i = 0; i < scrollArr.length; i++) {
        if (scrollTop >= 0 && scrollTop < scrollArr[1] - scrollArr[0]) {
          console.log(i);
          this.setData({
            toViewMenu: 'menuitem1',
            item: 'item1'
          });
          return;
        } else if (scrollTop >= scrollArr[i - 1] - scrollArr[0] && scrollTop < scrollArr[i] - scrollArr[0]) {
          this.setData({
            toViewMenu: 'menuitem' + (i + 1),
            item: 'item' + i
          })
          return;
        }
      }
    }
  }
})