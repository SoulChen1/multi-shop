//index.js
//获取应用实例
const app = getApp()

Page({
  data: {
    type: 'bySelf',
    item: 'item1',
    toView: '',
    toViewMenu: '',
    heightArr: [],
    containerH: 0
  },
  onLoad: function() {
    
  },
  onReady: function() {
    /*获取商品栏标题位置*/
    let queryItem = wx.createSelectorQuery();
    let queryTop = wx.createSelectorQuery();
    let heightArr = [];
    let topHeight = 0;
    let containerH = 0;
    //顶部高度
    queryTop.select('.top').boundingClientRect((res) => {
      topHeight = res.height;
    }).exec();
    queryTop.select('.productList').boundingClientRect((res) => {
      containerH = res.height;
      this.setData({
        containerH: containerH
      });
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
    let containerH = Math.round(e.detail.scrollHeight - this.data.containerH - scrollArr[0]);
    
    if (scrollTop >= scrollArr[scrollArr.length - 1] || scrollTop == containerH) {
      return
    } else {
      for (let i = 0; i < scrollArr.length; i++) {
        if (scrollTop >= 0 && scrollTop < scrollArr[1] - scrollArr[0]) {
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