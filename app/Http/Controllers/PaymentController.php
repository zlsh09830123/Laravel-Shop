<?php

namespace App\Http\Controllers;

use App\Events\OrderPaid;
use Carbon\Carbon;
use App\Models\Order;
use App\Exceptions\InvalidRequestException;

use \ECPay_PaymentMethod as ECPayMethod;

class PaymentController extends Controller
{
    public function payByECPay(Order $order)
    {
        // 判斷訂單是否屬於當前用戶
        $this->authorize('own', $order);
        // 訂單已支付或關閉
        if ($order->paid_at || $order->closed) {
            throw new InvalidRequestException('訂單狀態錯誤');
        }

        // 調用綠界的網頁支付
        try {
            $obj = new \ECPay_AllInOne();
       
            //服務參數
            $obj->ServiceURL  = "https://payment-stage.ecpay.com.tw/Cashier/AioCheckOut/V5";   //服務位置
            $obj->HashKey     = env('ECPAY_HASH_KEY');                                         //測試用Hashkey，請自行帶入ECPay提供的HashKey
            $obj->HashIV      = env('ECPAY_HASH_IV');                                          //測試用HashIV，請自行帶入ECPay提供的HashIV
            $obj->MerchantID  = env('ECPAY_MERCHANT_ID');                                      //測試用MerchantID，請自行帶入ECPay提供的MerchantID
            $obj->EncryptType = '1';                                                           //CheckMacValue加密類型，請固定填入1，使用SHA256加密
    
    
            //基本參數(請依系統規劃自行調整)
            $MerchantTradeNo = $order->no;
            $obj->Send['ReturnURL']         = "https://shop.mrhanji.com/callback";      //付款完成通知回傳的網址
            $obj->Send['ClientBackURL']     = "https://shop.mrhanji.com/success";        //付款完成通知回傳的網址
            $obj->Send['MerchantTradeNo']   = $MerchantTradeNo;                          //訂單編號
            $obj->Send['MerchantTradeDate'] = date('Y/m/d H:i:s');                       //交易時間
            $obj->Send['TotalAmount']       = $order->total_amount;                      //交易金額
            $obj->Send['TradeDesc']         = "good to drink";                           //交易描述
            $obj->Send['ChoosePayment']     = ECPayMethod::Credit;                       //付款方式:Credit
            $obj->Send['IgnorePayment']     = ECPayMethod::GooglePay;                    //不使用付款方式:GooglePay
    
            //訂單的商品資料
            foreach ($order->items as $item) {
                array_push($obj->Send['Items'], array('Name' => $item->product->title, 'Price' => (int)$item->price, 'Currency' => "元", 'Quantity' => $item->amount, 'URL' => "dedwed"));
            }

            //產生訂單(auto submit至ECPay)
            $obj->CheckOut();
    
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function callback()
    {
        // dd(request());
        $order = Order::where('no', '=', request('MerchantTradeNo'))->firstOrFail();
        // 正常來說不太可能出現支付了一筆不存在的訂單，這個判斷只是增加系統健壯性
        if (!$order) {
            return 'fail';
        }
        // 如果這筆訂單的狀態已經是已支付
        if ($order->paid_at) {
            return 'success';
        }
        
        if (request('RtnCode') == '1') { // RtnCode=1表示付款成功
            $order->update([
                'paid_at' => Carbon::now(), // 支付時間
                'payment_method' => 'ecpay', // 支付方式
                'payment_no' => request('MerchantTradeNo'), // 綠界訂單編號
                ]);
                echo '1|OK'; // 系統收到綠界回傳結果，正確回應1|OK
        }
        
        $this->afterPaid($order);
    }

    public function redirectFromECPay()
    {
        return redirect('/orders'); // 返回訂單列表
    }

    protected function afterPaid(Order $order)
    {
        event(new OrderPaid($order));
    }
}
