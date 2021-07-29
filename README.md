# Laravel Shop 電商系統
此專案是採用 Laravel 8 針對單一店家所開發的簡易電商系統。

### 功能
* 用戶
	* 註冊、登入 (大部份功能需經 Email 驗證後才可以使用)
	* 新增、刪除、修改、查看收件地址
* 管理後台 (使用 laravel-admin 建構)
	* 用戶管理
	* 商品管理
	* 訂單管理 (列表、詳情、出貨、拒絕退款)
	* 優惠券管理
	* 系統管理 (角色權限控管)
* 商品
	* 每個商品可以有多種商品貨號 (SKU)，即多種規格
	* 用戶端商品列表、詳情頁面
	* 用戶可下關鍵字搜尋商品
	* 收藏商品、取消收藏
* 購物車與訂單
	* 將商品加入購物車
	* 避免高併發情況下商品超賣問題發生
	* 使用延遲佇列自動關閉 30 分鐘尚未付款的訂單
	* 用戶端訂單列表、詳情頁面
	* 使用預載入與延遲預載入避免 N + 1 查詢問題
	* 使用 Service 模式封裝業務邏輯，提高程式碼可重用性
	* 用戶確認收到商品
	* 使用事件與監聽器實現商品評分的更新
	* 用戶申請退款
* 付款
	* 串接綠界支付
	* 使用事件與監聽器實現付款成功後更新商品銷量與信件通知
* 優惠券
	* 管理後台新增、刪除、修改、查看優惠券
	* 可以設定優惠券總量限制、開始時間、結束時間、是否啟用
	* 下單時使用優惠券扣除付款金額 (固定金額或比例，需滿足訂單最低金額限制)
	* 每名用戶只能使用同一張優惠券一次
***
### 專案截圖
![](https://i.imgur.com/8FuaQK3.png)
![](https://i.imgur.com/fxiEUph.png)
![](https://i.imgur.com/BjP4jS7.png)
![](https://i.imgur.com/YoxKtxD.png)
![](https://i.imgur.com/PPqNVxk.png)
![](https://i.imgur.com/vEYI6Ch.png)
![](https://i.imgur.com/OlLfaja.png)
![](https://i.imgur.com/9FnuxfH.png)
![](https://i.imgur.com/dGJqHKa.png)
![](https://i.imgur.com/7QMn2lK.png)
![](https://i.imgur.com/8aejb7Y.png)
![](https://i.imgur.com/40gamCw.png)
![](https://i.imgur.com/AVBHYAU.png)
![](https://i.imgur.com/7is9Cg1.png)
![](https://i.imgur.com/ykU6kkk.png)
![](https://i.imgur.com/7YqVmqC.png)
![](https://i.imgur.com/ANTpB0F.png)
![](https://i.imgur.com/Gu4rErM.png)
![](https://i.imgur.com/zz1zM8i.png)

* Demo 網址: https://shop.mrhanji.com
* 測試用帳號: shop@test
* 測試用密碼: password
* 信用卡測試卡號: 4311-9522-2222-2222
* 信用卡測試安全碼: 222
