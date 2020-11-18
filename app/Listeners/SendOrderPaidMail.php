<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Notifications\OrderPaidNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

// implements ShouldQueue 代表異步監聽器
class SendOrderPaidMail implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  OrderPaid  $event
     * @return void
     */
    public function handle(OrderPaid $event)
    {
        // 從事件對象中取出對應的訂單
        $order = $event->getOrder();
        // 調用 notify() 方法來發送通知
        $order->user->notify(new OrderPaidNotification($order));
    }
}
