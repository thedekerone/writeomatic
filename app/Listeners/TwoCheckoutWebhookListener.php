<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use App\Http\Controllers\Gateways\StripeController;
use App\Models\PaymentPlans;
use App\Models\Setting;
use App\Models\GatewayProducts;
use App\Models\Subscriptions as SubscriptionsModel;
use App\Models\User;
use App\Models\UserOrder;
use App\Models\WebhookHistory;
use App\Events\TwoCheckoutWebhookEvent;
use Carbon\Carbon;

use Throwable;

class TwoCheckoutWebhookListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TwoCheckoutWebhookEvent $event): void
    {
        try {
            $settings = Setting::first();
            $incomingJson = $event->payload;    
            $product_id = $incomingJson['IPN_PCODE'][0];

            if (str_contains($product_id, 'Prepaid')) {
                return;
            }

            $product = GatewayProducts::where('product_id', $product_id)->first();
            $plan = PaymentPlans::find($product->plan_id);
            $order_status = $incomingJson['ORDERSTATUS'];
            $customer_email = $incomingJson['CUSTOMEREMAIL'];
            $subscription_ref = $incomingJson['IPN_LICENSE_REF'][0];
            $user = User::where('email', $customer_email)->first();
            $user_id = $user->id;

            if ($order_status == "COMPLETE") {
                $subscription =  SubscriptionsModel::where([['user_id', '=', $user_id], ['stripe_price', '=', $product_id], ['stripe_status', '=', 'active']])->first();
                if (!$subscription)
                    return;
                $subscription->stripe_id =  $subscription_ref;
                $subscription->save();

                $plan->total_words == -1? ($user->remaining_words = -1) : ($user->remaining_words += $plan->total_words);
                $plan->total_images == -1? ($user->remaining_images = -1) : ($user->remaining_images += $plan->total_images);

                $user->save();
            } elseif ($order_status == "CANCELED") {
                $subscription =  SubscriptionsModel::where([['user_id', '=', $user_id], ['stripe_price', '=', $product_id], ['stripe_status', '=', 'active']])->first();
                $subscription->stripe_status = 'cancelled';
                $subscription->ends_at = \Carbon\Carbon::now();
                $subscription->save();
            }
            
        }catch(\Exception $ex){
            Log::error("TwoCheckoutWebhookListener::handle()\n".$ex->getMessage());
            error_log("TwoCheckoutWebhookListener::handle()\n".$ex->getMessage());
        }
    }
}
