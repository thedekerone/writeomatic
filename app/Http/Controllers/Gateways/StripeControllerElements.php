<?php

namespace App\Http\Controllers\Gateways;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Currency;
use App\Models\CustomSettings;
use App\Models\GatewayProducts;
use App\Models\Gateways;
use App\Models\OldGatewayProducts;
use App\Models\PaymentPlans;
use App\Models\Setting;
use App\Models\Subscriptions as SubscriptionsModel;
use App\Models\SubscriptionItems;
use App\Models\HowitWorks;
use App\Models\User;
use App\Models\UserAffiliate;
use App\Models\UserOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Subscription;
use Laravel\Cashier\Payment;
use Stripe\PaymentIntent;
use Stripe\Plan;
use Stripe\Exception\AuthenticationException;
use Stripe\Exception\InvalidArgumentException;
use App\Events\StripeWebhookEvent;


/**
 * Controls ALL Payment actions of Stripe
 */
class StripeControllerElements extends Controller
{
    /**
     * Reads GatewayProducts table and returns price id of the given plan
     */
    public static function getStripePriceId($planId)
    {

        //check if plan exists
        $plan = PaymentPlans::where('id', $planId)->first();
        if ($plan != null) {
            $product = GatewayProducts::where(["plan_id" => $planId, "gateway_code" => "stripe"])->first();
            if ($product != null) {
                return $product->price_id;
            } else {
                return null;
            }
        }
        return null;
    }

    /**
     * Displays Payment Page of Stripe gateway.
     */
    public static function subscribe($planId, $plan, $incomingException = null)
    {

        $gateway = Gateways::where("code", "stripe")->first();
        if ($gateway == null) {
            abort(404);
        }

        $currency = Currency::where('id', $gateway->currency)->first()->code;

        if ($gateway->mode == 'sandbox') {
            config(['cashier.key' => $gateway->sandbox_client_id]);
            config(['cashier.secret' => $gateway->sandbox_client_secret]);
            config(['cashier.currency' => $currency]);
        } else {
            config(['cashier.key' => $gateway->live_client_id]); //$settings->stripe_key
            config(['cashier.secret' => $gateway->live_client_secret]); //$settings->stripe_secret
            config(['cashier.currency' => $currency]); //currency()->code
        }

        if ($gateway->mode == 'sandbox') {
            $key = $gateway->sandbox_client_secret;
        } else {
            $key = $gateway->live_client_secret;
        }

        \Stripe\Stripe::setApiKey($key);
        $stripe = new \Stripe\StripeClient($key);

        $user = Auth::user();

        $currentCustomerIdsArray = [];
        foreach ($stripe->customers->all()->data as $data) {
            array_push($currentCustomerIdsArray, $data->id);
        }

        if (in_array($user->stripe_id, $currentCustomerIdsArray) == false) {

            $userData = [
                "email" => $user->email,
                "name" => $user->name . " " . $user->surname,
                "phone" => $user->phone,
                "address" => [
                    "line1" => $user->address,
                    "postal_code" => $user->postal,
                ],
            ];

            $stripeCustomer = $stripe->customers->create($userData);

            $user->stripe_id = $stripeCustomer->id;
            $user->save();
        }


        $email = $user->email();
        $activesubs = $user->subscriptions()->where('stripe_status', 'active')->orWhere('stripe_status', 'trialing')->get();
        $paymentIntent = null;

        try {

            $product = GatewayProducts::where(["plan_id" => $planId, "gateway_code" => "stripe"])->first();
            $plan = PaymentPlans::where('id', $planId)->first();

            $exception = $incomingException;
            if($product != null){
                if ($product->price_id == null) {
                    $exception = "Stripe product ID is not set! Please save Membership Plan again.";
                }else{

                    $subscriptionInfo = [
                        'customer' => $user->stripe_id,
                        'items' => [[
                            'price' => $product->price_id,
                        ]],
                        'payment_behavior' => 'default_incomplete',
                        'payment_settings' => ['save_default_payment_method' => 'on_subscription'],
                        'expand' => ['latest_invoice.payment_intent'],
                        'metadata' => [
                            'product_id' => $product->product_id,
                            'price_id' => $product->price_id,
                            'plan_id' => $planId
                        ],
                    ];

                    if($plan->trial_days != 0){
                        $subscriptionInfo = [
                            'customer' => $user->stripe_id,
                            'items' => [[
                                'price' => $product->price_id,
                            ]],
                            'payment_behavior' => 'default_incomplete',
                            'payment_settings' => ['save_default_payment_method' => 'on_subscription'],
                            'expand' => ['latest_invoice.payment_intent'],
                            'metadata' => [
                                'product_id' => $product->product_id,
                                'price_id' => $product->price_id,
                                'plan_id' => $planId
                            ],
                            //'trial_period_days' => $plan->trial_days ?? 0,
                            //'trial_from_plan' => true,
                            'trial_end' => strval(\Carbon\Carbon::now()->addDays($plan->trial_days)->timestamp),
                            'billing_cycle_anchor' => strval(\Carbon\Carbon::now()->addDays($plan->trial_days)->timestamp),
                        ];

                        //Log::info('StripeController::subscribe() - subscriptionInfo: ' . json_encode($subscriptionInfo));
                    }

                    // Create the subscription with the customer ID, price ID, and necessary options.
                    $newSubscription = $stripe->subscriptions->create($subscriptionInfo);

                    //Log::info('StripeController::subscribe() - newSubscription: ' . json_encode($newSubscription));

                    $subscription = new SubscriptionsModel();
                    $subscription->user_id = $user->id;
                    $subscription->name = $planId;
                    $subscription->stripe_id = $newSubscription->id;
                    $subscription->stripe_status = "AwaitingPayment"; // $plan->trial_days != 0 ? "trialing" : "AwaitingPayment";
                    $subscription->stripe_price = $product->price_id;
                    $subscription->quantity = 1;
                    $subscription->trial_ends_at = $plan->trial_days != 0 ? \Carbon\Carbon::now()->addDays($plan->trial_days) : null;
                    $subscription->ends_at = $plan->trial_days != 0 ? \Carbon\Carbon::now()->addDays($plan->trial_days) : \Carbon\Carbon::now()->addDays(30);
                    $subscription->plan_id = $planId;
                    $subscription->paid_with = 'stripe';
                    $subscription->save();

                    $subscriptionItem = new SubscriptionItems();
                    $subscriptionItem->subscription_id = $subscription->id;
                    $subscriptionItem->stripe_id = $newSubscription->items->data[0]->id;
                    $subscriptionItem->stripe_product = $product->product_id;
                    $subscriptionItem->stripe_price = $product->price_id;
                    $subscriptionItem->quantity = 1;
                    $subscriptionItem->save();

                    // $paymentIntent = [
                    //     'subscription_id' => $newSubscription->id,
                    //     'trial' => $plan->trial_days != 0 ? true : false,
                    //     'client_secret' => $newSubscription->latest_invoice->payment_intent->client_secret,
                    // ];

                    if($plan->trial_days != 0){
                        $setupIntent = $stripe->setupIntents->retrieve(
                            $newSubscription->pending_setup_intent,
                            []
                        );
                        $paymentIntent = [
                            'subscription_id' => $newSubscription->id,
                            'client_secret' => $setupIntent->client_secret,
                            'trial' => true,
                            'currency' => $currency,
                            'amount' => $plan->price * 100,
                        ];
                    }else{
                        $paymentIntent = [
                        'subscription_id' => $newSubscription->id,
                        'client_secret' => $newSubscription->latest_invoice->payment_intent->client_secret,
                        'trial' => false,
                        'currency' => $currency,
                        'amount' => $plan->price * 100,
                    ];
                    }

                    

                }
            }else{
                $exception = "Stripe product is not defined! Please save Membership Plan again.";
            }
        } catch (\Exception $th) {
            // $exception = $th;
            Log::error($th->getMessage());
            $exception = Str::before($th->getMessage(), ':');
        }

        return view('panel.user.payment.subscription.payWithStripeElements', compact('plan', 'paymentIntent', 'gateway', 'exception', 'activesubs', 'product', 'email'));
    }


    /**
     * Handles payment action of Stripe.
     * 
     * Subscribe payment page posts here.
     */
    public function subscribePay(Request $request)
    {
        //Log::info('StripeController::subscribePay() - request: ' . json_encode($request->all()));

        if($request->has('payment_intent') && $request->has('payment_intent_client_secret') && $request->has('redirect_status')){

            $payment_intent = $request->input('payment_intent');
            $payment_intent_client_secret = $request->input('payment_intent_client_secret');
            $redirect_status = $request->input('redirect_status');

            if($redirect_status == "succeeded"){

                $gateway = Gateways::where("code", "stripe")->first();
                if ($gateway == null) {
                    abort(404);
                }

                $currency = Currency::where('id', $gateway->currency)->first()->code;

                if ($gateway->mode == 'sandbox') {
                    config(['cashier.key' => $gateway->sandbox_client_id]);
                    config(['cashier.secret' => $gateway->sandbox_client_secret]);
                    config(['cashier.currency' => $currency]);
                } else {
                    config(['cashier.key' => $gateway->live_client_id]); //$settings->stripe_key
                    config(['cashier.secret' => $gateway->live_client_secret]); //$settings->stripe_secret
                    config(['cashier.currency' => $currency]); //currency()->code
                }

                $stripeSecretKey = "";

                if ($gateway->mode == 'sandbox') {
                    $stripeSecretKey = $gateway->sandbox_client_secret;
                } else {
                    $stripeSecretKey = $gateway->live_client_secret;
                }

                if($stripeSecretKey == ""){
                    abort(404);
                }

                \Stripe\Stripe::setApiKey($stripeSecretKey);
                $stripe = new \Stripe\StripeClient($stripeSecretKey);
                
                $intent = $stripe->paymentIntents->retrieve($payment_intent); 

                //Log::info('StripeController::subscribePay() - intent: ' . json_encode($intent));

                if($intent != null){

                    if($intent->client_secret == $payment_intent_client_secret){

                        if($intent->status=="succeeded"){

                            $user = Auth::user();
                            $settings = Setting::first();

                            self::cancelAllSubscriptions();

                            $subscription = SubscriptionsModel::where(['user_id' => $user->id, 'stripe_status' => "AwaitingPayment"])->latest()->first();

                            $planId = $subscription->plan_id;
                            $plan = PaymentPlans::where('id', $planId)->first();

                            $subscription->stripe_status = $plan->trial_days != 0 ? "trialing" : "active";
                            $subscription->save();

                            $payment = new UserOrder();
                            $payment->order_id = Str::random(12);
                            $payment->plan_id = $planId;
                            $payment->user_id = $user->id;
                            $payment->payment_type = 'Stripe';
                            $payment->price = $plan->price;
                            $payment->affiliate_earnings = ($plan->price * $settings->affiliate_commission_percentage) / 100;
                            $payment->status = 'Success';
                            $payment->country = Auth::user()->country ?? 'Unknown';
                            $payment->save();

                            $plan->total_words == -1? ($user->remaining_words = -1) : ($user->remaining_words += $plan->total_words);
                            $plan->total_images == -1? ($user->remaining_images = -1) : ($user->remaining_images += $plan->total_images);

                            $user->save();

                            //check if any other "AwaitingPayment" subscription exists if so cancel it
                            $awaitingPaymentSubscriptions = SubscriptionsModel::where(['user_id' => $user->id, 'stripe_status' => "AwaitingPayment"])->get();
                            if ($awaitingPaymentSubscriptions != null) {
                                foreach ($awaitingPaymentSubscriptions as $subs) {
                                    if ($subs->stripe_id != 'undefined' && $subs->stripe_id != null && $subs->user_id == $user->id) {
                                        try{
                                            $subscription = $stripe->subscriptions->retrieve($subs->stripe_id);
                                        }catch(\Exception $ex){
                                            $subscription = null;
                                            error_log("StripeController::subscribePay()\n" . $ex->getMessage());
                                        }
                    
                                        if($subscription != null) {
                                            $subscription->delete();
                                        }
                                        $subs->stripe_status = "cancelled";
                                        $subs->save();
                                    }
                                }
                            }

                            createActivity($user->id, 'Subscribed', $plan->name . ' Plan', null);

                            return redirect()->route('dashboard.index')->with(['message' => 'Thank you for your purchase. Enjoy your remaining words and images.', 'type' => 'success']);
                                
                        }else{
                            Log::error('StripeController::subscribePay() - intent->status != succeeded');
                            return back()->with(['message' => 'A problem occured! '.$intent->status, 'type' => 'error']);
                        }

                    }else{
                        Log::error('StripeController::subscribePay() - intent->client_secret != $payment_intent_client_secret');
                        //Falsified data
                        abort(404);
                    }

                }else{
                    //Falsified data
                    abort(404);
                }
                
                
            }else{
                return back()->with(['message' => "A problem occured! $redirect_status", 'type' => 'error']);
            }

        }else if($request->has('setup_intent') && $request->has('setup_intent_client_secret') && $request->has('redirect_status')){

            $setup_intent = $request->input('setup_intent');
            $setup_intent_client_secret = $request->input('setup_intent_client_secret');
            $redirect_status = $request->input('redirect_status');

            if($redirect_status == "succeeded"){

                $gateway = Gateways::where("code", "stripe")->first();
                if ($gateway == null) {
                    abort(404);
                }

                $currency = Currency::where('id', $gateway->currency)->first()->code;

                if ($gateway->mode == 'sandbox') {
                    config(['cashier.key' => $gateway->sandbox_client_id]);
                    config(['cashier.secret' => $gateway->sandbox_client_secret]);
                    config(['cashier.currency' => $currency]);
                } else {
                    config(['cashier.key' => $gateway->live_client_id]); //$settings->stripe_key
                    config(['cashier.secret' => $gateway->live_client_secret]); //$settings->stripe_secret
                    config(['cashier.currency' => $currency]); //currency()->code
                }

                $stripeSecretKey = "";

                if ($gateway->mode == 'sandbox') {
                    $stripeSecretKey = $gateway->sandbox_client_secret;
                } else {
                    $stripeSecretKey = $gateway->live_client_secret;
                }

                if($stripeSecretKey == ""){
                    abort(404);
                }

                \Stripe\Stripe::setApiKey($stripeSecretKey);
                $stripe = new \Stripe\StripeClient($stripeSecretKey);
                
                $intent = $stripe->setupIntents->retrieve($setup_intent); 

                //Log::info('StripeController::subscribePay() - intent: ' . json_encode($intent));

                if($intent != null){

                    if($intent->client_secret == $setup_intent_client_secret){

                        if($intent->status=="succeeded"){

                            $user = Auth::user();
                            $settings = Setting::first();

                            self::cancelAllSubscriptions();

                            $subscription = SubscriptionsModel::where(['user_id' => $user->id, 'stripe_status' => "AwaitingPayment"])->latest()->first();

                            $planId = $subscription->plan_id;
                            $plan = PaymentPlans::where('id', $planId)->first();

                            $subscription->stripe_status = $plan->trial_days != 0 ? "trialing" : "active";
                            $subscription->save();

                            $payment = new UserOrder();
                            $payment->order_id = Str::random(12);
                            $payment->plan_id = $planId;
                            $payment->user_id = $user->id;
                            $payment->payment_type = 'Stripe';
                            $payment->price = $plan->price;
                            $payment->affiliate_earnings = ($plan->price * $settings->affiliate_commission_percentage) / 100;
                            $payment->status = 'Success';
                            $payment->country = Auth::user()->country ?? 'Unknown';
                            $payment->save();

                            $plan->total_words == -1? ($user->remaining_words = -1) : ($user->remaining_words += $plan->total_words);
                            $plan->total_images == -1? ($user->remaining_images = -1) : ($user->remaining_images += $plan->total_images);
                            $user->save();

                            //check if any other "AwaitingPayment" subscription exists if so cancel it
                            $awaitingPaymentSubscriptions = SubscriptionsModel::where(['user_id' => $user->id, 'stripe_status' => "AwaitingPayment"])->get();
                            if ($awaitingPaymentSubscriptions != null) {
                                foreach ($awaitingPaymentSubscriptions as $subs) {
                                    if ($subs->stripe_id != 'undefined' && $subs->stripe_id != null && $subs->user_id == $user->id) {
                                        try{
                                            $subscription = $stripe->subscriptions->retrieve($subs->stripe_id);
                                        }catch(\Exception $ex){
                                            $subscription = null;
                                            error_log("StripeController::subscribePay()\n" . $ex->getMessage());
                                        }

                                        if($subscription != null) {
                                            $subscription->delete();
                                        }
                                        $subs->stripe_status = "cancelled";
                                        $subs->save();
                                    }
                                }
                            }

                            createActivity($user->id, 'Subscribed', $plan->name . ' Plan', null);

                            return redirect()->route('dashboard.index')->with(['message' => 'Thank you for your purchase. Enjoy your remaining words and images.', 'type' => 'success']);
                                
                        }else{
                            Log::error('StripeController::subscribePay() - intent->status != succeeded');
                            return back()->with(['message' => 'A problem occured! '.$intent->status, 'type' => 'error']);
                        }

                    }else{
                        Log::error('StripeController::subscribePay() - intent->client_secret != $payment_intent_client_secret');
                        //Falsified data
                        abort(404);
                    }

                }else{
                    //Falsified data
                    abort(404);
                }
                
                
            }else{
                return back()->with(['message' => "A problem occured! $redirect_status", 'type' => 'error']);
            }


        }else{
            abort(404);
        }

    }

    /**
     * This function is stripe specific.
     */
    public function cancelAllSubscriptions()
    {
        $gateway = Gateways::where("code", "stripe")->first();
        if ($gateway == null) {
            abort(404);
        }

        $currency = Currency::where('id', $gateway->currency)->first()->code;

        if ($gateway->mode == 'sandbox') {
            $key = $gateway->sandbox_client_secret;
        } else {
            $key = $gateway->live_client_secret;
        }

        $stripe = new \Stripe\StripeClient($key);

        $product = null;

        $user = Auth::user();

        //$allSubscriptions = $user->subscriptions()->where('stripe_status', 'active')->orWhere('stripe_status', 'trialing')->all();
        $allSubscriptions = SubscriptionsModel::where(['user_id' => $user->id, 'stripe_status' => "active"])->get();
        if ($allSubscriptions != null) {
            foreach ($allSubscriptions as $subs) {
                if ($subs->stripe_id != 'undefined' && $subs->stripe_id != null && $subs->user_id == $user->id) {
                    // $user->subscription($subs->stripe_id)->cancelNow();
                    try{
                        $subscription = $stripe->subscriptions->retrieve($subs->stripe_id);
                    }catch(\Exception $ex){
                        $subscription = null;
                        error_log("StripeController::cancelAllSubscriptions()\n" . $ex->getMessage());
                        //return back()->with(['message' => 'Could not find active subscription. Nothing changed!', 'type' => 'error']);
                    }

                    if($subscription != null) {
                        $subscription->delete();
                    }
                }
            }
        }

        $allSubscriptions = SubscriptionsModel::where(['user_id' => $user->id, 'stripe_status' => "trialing"])->get();
        if ($allSubscriptions != null) {
            foreach ($allSubscriptions as $subs) {
                if ($subs->stripe_id != 'undefined' && $subs->stripe_id != null && $subs->user_id == $user->id) {
                    // $user->subscription($subs->stripe_id)->cancelNow();
                    try{
                        $subscription = $stripe->subscriptions->retrieve($subs->stripe_id);
                    }catch(\Exception $ex){
                        $subscription = null;
                        error_log("StripeController::cancelAllSubscriptions()\n" . $ex->getMessage());
                        //return back()->with(['message' => 'Could not find active subscription. Nothing changed!', 'type' => 'error']);
                    }

                    if($subscription != null) {
                        $subscription->delete();
                    }
                }
            }
        }

    }

    /**
     * Cancels current subscription plan
     */
    public static function subscribeCancel()
    {

        $user = Auth::user();
        $settings = Setting::first();

        $gateway = Gateways::where("code", "stripe")->first();
        if ($gateway == null) {
            abort(404);
        }

        $currency = Currency::where('id', $gateway->currency)->first()->code;

        if ($gateway->mode == 'sandbox') {
            config(['cashier.key' => $gateway->sandbox_client_id]);
            config(['cashier.secret' => $gateway->sandbox_client_secret]);
            config(['cashier.currency' => $currency]);
        } else {
            config(['cashier.key' => $gateway->live_client_id]); //$settings->stripe_key
            config(['cashier.secret' => $gateway->live_client_secret]); //$settings->stripe_secret
            config(['cashier.currency' => $currency]); //currency()->code
        }

        if ($gateway->mode == 'sandbox') {
            $key = $gateway->sandbox_client_secret;
        } else {
            $key = $gateway->live_client_secret;
        }

        \Stripe\Stripe::setApiKey($key);
        $stripe = new \Stripe\StripeClient($key);

        $activeSub = $user->subscriptions()->where('stripe_status', 'active')->orWhere('stripe_status', 'trialing')->first();

        //Log::info('activeSub : '. $activeSub);

        if ($activeSub != null) {
            $plan = PaymentPlans::where('id', $activeSub->plan_id)->first();

            $recent_words = $user->remaining_words - $plan->total_words;
            $recent_images = $user->remaining_images - $plan->total_images;

            //if($user->subscription($activeSub->stripe_id)){ }

                //if(self::getSubscriptionStatus() == true){}

                    try{
                        //$user->subscription($activeSub->stripe_id)->cancelNow();
                        $subscription = $stripe->subscriptions->retrieve($activeSub->stripe_id);
                        $subscription->delete();
                    }catch(\Exception $ex){
                        error_log("StripeController::subscribeCancel()\n" . $ex->getMessage());
                        return back()->with(['message' => 'Could not find active subscription. Nothing changed!', 'type' => 'error']);
                    }

                    $user->remaining_words = $recent_words < 0 ? 0 : $recent_words;
                    $user->remaining_images = $recent_images < 0 ? 0 : $recent_images;
                    $user->save();

                    createActivity($user->id, 'cancelled', $plan->name, null);

                    //return back()->with(['message' => 'Your subscription is cancelled succesfully.', 'type' => 'success']);
                    return redirect()->route('dashboard.user.index')->with(['message' => 'Your subscription is cancelled succesfully.', 'type' => 'success']);
                
           
        }

        return back()->with(['message' => 'Could not find active subscription. Nothing changed!', 'type' => 'error']);
    }


    /**
     * Displays Payment Page of Stripe gateway for prepaid plans.
     */
    public static function prepaid($planId, $plan, $incomingException = null)
    {

        $gateway = Gateways::where("code", "stripe")->first();
        if ($gateway == null) {
            abort(404);
        }

        $currency = Currency::where('id', $gateway->currency)->first()->code;

        $stripeSecretKey = "";

        if ($gateway->mode == 'sandbox') {
            config(['cashier.key' => $gateway->sandbox_client_id]);
            config(['cashier.secret' => $gateway->sandbox_client_secret]);
            config(['cashier.currency' => $currency]);
            $stripeSecretKey = $gateway->sandbox_client_secret;
        } else {
            config(['cashier.key' => $gateway->live_client_id]); //$settings->stripe_key
            config(['cashier.secret' => $gateway->live_client_secret]); //$settings->stripe_secret
            config(['cashier.currency' => $currency]); //currency()->code
            $stripeSecretKey = $gateway->live_client_secret;
        }

        $user = Auth::user();
        $email = $user->email();
        $activesubs = $user->subscriptions()->where('stripe_status', 'active')->orWhere('stripe_status', 'trialing')->get();
        $paymentIntent = null;

        try {
            
            \Stripe\Stripe::setApiKey($stripeSecretKey);

            $product = GatewayProducts::where(["plan_id" => $planId, "gateway_code" => "stripe"])->first();

            $exception = $incomingException;
            if($product != null){
                if ($product->price_id == null) {
                    $exception = "Stripe product ID is not set! Please save Membership Plan again.";
                }else{

                    // Create a PaymentIntent with amount and currency
                    $paymentIntent = \Stripe\PaymentIntent::create([
                        'amount' => $plan->price * 100,
                        'currency' => $currency,
                        'automatic_payment_methods' => [
                            'enabled' => true,
                        ],
                        'metadata' => [
                            'product_id' => $product->product_id,
                            'price_id' => $product->price_id,
                            'plan_id' => $planId
                        ],
                    ]);

                }
            }else{
                $exception = "Stripe product is not defined! Please save Membership Plan again.";
            }
        } catch (\Exception $th) {
            $exception = Str::before($th->getMessage(), ':');
        }

        return view('panel.user.payment.prepaid.payWithStripeElements', compact('plan', 'paymentIntent', 'gateway', 'exception', 'activesubs', 'product', 'email'));
    }


    /**
     * Handles payment action of Stripe.
     * 
     * Prepaid payment page posts here.
     */
    public function prepaidPay(Request $request)
    {

        if($request->has('payment_intent') && $request->has('payment_intent_client_secret') && $request->has('redirect_status')){

            $payment_intent = $request->input('payment_intent');
            $payment_intent_client_secret = $request->input('payment_intent_client_secret');
            $redirect_status = $request->input('redirect_status');

            if($redirect_status == "succeeded"){

                $gateway = Gateways::where("code", "stripe")->first();
                if ($gateway == null) {
                    abort(404);
                }

                $currency = Currency::where('id', $gateway->currency)->first()->code;

                $stripeSecretKey = "";

                if ($gateway->mode == 'sandbox') {
                    $stripeSecretKey = $gateway->sandbox_client_secret;
                } else {
                    $stripeSecretKey = $gateway->live_client_secret;
                }

                $stripe = new \Stripe\StripeClient($stripeSecretKey);
                
                $intent = $stripe->paymentIntents->retrieve($payment_intent); 

                if($intent != null){

                    if($intent->client_secret == $payment_intent_client_secret){

                        if($intent->status=="succeeded"){

                            $planId = $intent->metadata->plan_id;
                            $productId = $intent->metadata->product_id;
                            $priceId = $intent->metadata->price_id;
                            
                            $plan = PaymentPlans::where("id", $planId)->first();

                            if($plan != null){
                                if($plan->id != null){

                                    $user = Auth::user();
                                    $settings = Setting::first();

                                    $payment = new UserOrder();
                                    $payment->order_id = Str::random(12);
                                    $payment->plan_id = $plan->id;
                                    $payment->type = 'prepaid';
                                    $payment->user_id = $user->id;
                                    $payment->payment_type = 'Stripe';
                                    $payment->price = $plan->price;
                                    $payment->affiliate_earnings = ($plan->price * $settings->affiliate_commission_percentage) / 100;
                                    $payment->status = 'Success';
                                    $payment->country = $user->country ?? 'Unknown';
                                    $payment->save();

                                    $plan->total_words == -1? ($user->remaining_words = -1) : ($user->remaining_words += $plan->total_words);
                                    $plan->total_images == -1? ($user->remaining_images = -1) : ($user->remaining_images += $plan->total_images);
                                    
                                    $user->save();

                                    createActivity($user->id, 'Purchased', 'Manuel Test Token Pack', null);

                                    return redirect()->route('dashboard.index')->with(['message' => 'Thank you for your purchase. Enjoy your remaining words and images.', 'type' => 'success']);                            

                                }
                            }

                        }else{
                            return back()->with(['message' => 'A problem occured! '.$intent->status, 'type' => 'error']);
                        }

                    }else{
                        //Falsified data
                        abort(404);
                    }

                }else{
                    //Falsified data
                    abort(404);
                }
                
                
            }else{
                return back()->with(['message' => 'A problem occured! $redirect_status', 'type' => 'error']);
            }

        }else{
            abort(404);
        }

    }


    /**
     * Saves Membership plan product in stripe gateway.
     * @param planId ID of plan in PaymentPlans model.
     * @param productName Name of the product, plain text
     * @param price Price of product
     * @param frequency Time interval of subscription, month / annual
     * @param type Type of product subscription/one-time
     */
    public static function saveProduct($planId, $productName, $price, $frequency, $type)
    {

        try {

            $price = (int)(((float)$price) * 100); // Must be in cents level for stripe

            $gateway = Gateways::where("code", "stripe")->first();
            if ($gateway == null) {
                abort(404);
            }

            $currency = Currency::where('id', $gateway->currency)->first()->code;

            if ($gateway->mode == 'sandbox') {
                $key = $gateway->sandbox_client_secret;
            } else {
                $key = $gateway->live_client_secret;
            }

            $stripe = new \Stripe\StripeClient($key);

            $product = null;
            $oldProductId = null;

            //check if product exists
            $productData = GatewayProducts::where(["plan_id" => $planId, "gateway_code" => "stripe"])->first();
            if ($productData != null) {

                // Create product in every situation. maybe user updated stripe credentials.

                if ($productData->product_id != null && $productName != null) {
                    //Product has been created before
                    $oldProductId = $productData->product_id;
                } else {
                    //Product has not been created before but record exists. Create new product and update record.
                }

                $newProduct = $stripe->products->create(['name' => $productName,]);
                $productData->product_id = $newProduct->id;
                $productData->plan_name = $productName;
                $productData->save();

                $product = $productData;
            } else {

                $newProduct = $stripe->products->create(['name' => $productName,]);

                $product = new GatewayProducts();
                $product->plan_id = $planId;
                $product->plan_name = $productName;
                $product->gateway_code = "stripe";
                $product->gateway_title = "Stripe";
                $product->product_id = $newProduct->id;
                $product->save();
            }


            //check if price exists
            if ($product->price_id != null) {
                //Price exists
                // Since stripe api does not allow to update recurring values, we deactivate all prices added to this product before and add a new price object.

                // Deactivate all prices
                foreach ($stripe->prices->all(['product' => $product->product_id]) as $oldPrice) {
                    $stripe->prices->update($oldPrice->id, ['active' => false]);
                }

                // One-Time price
                if ($type == "o") {
                    $updatedPrice = $stripe->prices->create([
                        'unit_amount' => $price,
                        'currency' => $currency,
                        'product' => $product->product_id,
                    ]);
                    $product->price_id = $updatedPrice->id;
                    $product->save();
                } else {
                    // Subscription

                    $oldPriceId = $product->price_id;

                    $updatedPrice = $stripe->prices->create([
                        'unit_amount' => $price,
                        'currency' => $currency,
                        'recurring' => ['interval' => $frequency == "m" ? 'month' : 'year'],
                        'product' => $product->product_id,
                    ]);
                    $product->price_id = $updatedPrice->id;
                    $product->save();

                    $history = new OldGatewayProducts();
                    $history->plan_id = $planId;
                    $history->plan_name = $productName;
                    $history->gateway_code = 'stripe';
                    $history->product_id = $product->product_id;
                    $history->old_product_id = $oldProductId;
                    $history->old_price_id = $oldPriceId;
                    $history->new_price_id = $updatedPrice->id;
                    $history->status = 'check';
                    $history->save();

                    $tmp = self::updateUserData();
                }
            } else {
                // One-Time price
                if ($type == "o") {
                    $updatedPrice = $stripe->prices->create([
                        'unit_amount' => $price,
                        'currency' => $currency,
                        'product' => $product->product_id,
                    ]);
                    $product->price_id = $updatedPrice->id;
                    $product->save();
                } else {
                    // Subscription
                    $updatedPrice = $stripe->prices->create([
                        'unit_amount' => $price,
                        'currency' => $currency,
                        'recurring' => ['interval' => $frequency == "m" ? 'month' : 'year'],
                        'product' => $product->product_id,
                    ]);
                    $product->price_id = $updatedPrice->id;
                    $product->save();
                }
            }
        } catch (\Exception $ex) {
            error_log("StripeController::saveProduct()\n" . $ex->getMessage());
            return back()->with(['message' => $ex->getMessage(), 'type' => 'error']);
        }
    }


    /**
     * Used to generate new product id and price id of all saved membership plans in stripe gateway.
     */
    public static function saveAllProducts()
    {
        try {

            $gateway = Gateways::where("code", "stripe")->first();
            if ($gateway == null) {
                return back()->with(['message' => __('Please enable Stripe'), 'type' => 'error']);
                abort(404);
            }


            if ($gateway->mode == 'sandbox') {
                $key = $gateway->sandbox_client_secret;
            } else {
                $key = $gateway->live_client_secret;
            }

            $stripe = new \Stripe\StripeClient($key);

            // Create customers if not exist

            $currentCustomerIdsArray = [];
            foreach ($stripe->customers->all()->data as $data) {
                array_push($currentCustomerIdsArray, $data->id);
            }

            $allUsers = User::all();
            foreach ($allUsers as $aUser) {

                if (in_array($aUser->stripe_id, $currentCustomerIdsArray) == false) {

                    $userData = [
                        "email" => $aUser->email,
                        "name" => $aUser->name . " " . $aUser->surname,
                        "phone" => $aUser->phone,
                        "address" => [
                            "line1" => $aUser->address,
                            "postal_code" => $aUser->postal,
                        ],
                    ];

                    $stripeCustomer = $stripe->customers->create($userData);

                    $aUser->stripe_id = $stripeCustomer->id;
                    $aUser->save();
                }
            }

            // Get all membership plans

            $plans = PaymentPlans::where('active', 1)->get();

            foreach ($plans as $plan) {
                // Replaced definitions here. Because if monthly or prepaid words change just updating here will be enough.
                $freq = $plan->frequency == "monthly" ? "m" : "y"; // m => month | y => year
                $typ = $plan->type == "prepaid" ? "o" : "s"; // o => one-time | s => subscription

                self::saveProduct($plan->id, $plan->name, $plan->price, $freq, $typ);
            }

            // Create webhook of stripe
            $tmp = self::createWebhook();

        } catch (\Exception $ex) {
            error_log("StripeController::saveAllProducts()\n" . $ex->getMessage());
            return back()->with(['message' => $ex->getMessage(), 'type' => 'error']);
        }
    }



    public static function getSubscriptionDaysLeft()
    {

        // $plan = PaymentPlans::find($request->plan);
        $user = Auth::user();
        $settings = Setting::first();

        $gateway = Gateways::where("code", "stripe")->first();
        if ($gateway == null) {
            abort(404);
        }

        $currency = Currency::where('id', $gateway->currency)->first()->code;

        if ($gateway->mode == 'sandbox') {
            config(['cashier.key' => $gateway->sandbox_client_id]);
            config(['cashier.secret' => $gateway->sandbox_client_secret]);
            config(['cashier.currency' => $currency]);
        } else {
            config(['cashier.key' => $gateway->live_client_id]); //$settings->stripe_key
            config(['cashier.secret' => $gateway->live_client_secret]); //$settings->stripe_secret
            config(['cashier.currency' => $currency]); //currency()->code
        }

        $sub = $user->subscriptions()->where('stripe_status', 'active')->orWhere('stripe_status', 'trialing')->first();
        $activeSub = $sub->asStripeSubscription();

        if ($activeSub->status == 'active') {
            return \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::createFromTimeStamp($activeSub->current_period_end));
        } else {
            error_log($sub->trial_ends_at);
            return \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($sub->trial_ends_at));
        }

        // return $activeSub->current_period_end;

    }


    public static function getSubscriptionRenewDate()
    {

        // $plan = PaymentPlans::find($request->plan);
        $user = Auth::user();
        $settings = Setting::first();

        $gateway = Gateways::where("code", "stripe")->first();
        if ($gateway == null) {
            abort(404);
        }

        $currency = Currency::where('id', $gateway->currency)->first()->code;

        if ($gateway->mode == 'sandbox') {
            config(['cashier.key' => $gateway->sandbox_client_id]);
            config(['cashier.secret' => $gateway->sandbox_client_secret]);
            config(['cashier.currency' => $currency]);
        } else {
            config(['cashier.key' => $gateway->live_client_id]); //$settings->stripe_key
            config(['cashier.secret' => $gateway->live_client_secret]); //$settings->stripe_secret
            config(['cashier.currency' => $currency]); //currency()->code
        }

        $activeSub = $user->subscriptions()->where('stripe_status', 'active')->orWhere('stripe_status', 'trialing')->first()->asStripeSubscription();

        return \Carbon\Carbon::createFromTimeStamp($activeSub->current_period_end)->format('F jS, Y');
    }

    /**
     * Checks status directly from gateway and updates database if cancelled or suspended.
     */
    public static function getSubscriptionStatus($incomingUserId = null)
    {

        // $plan = PaymentPlans::find($request->plan);
        if($incomingUserId != null){
            $user = User::where('id', $incomingUserId)->first();
        }else{
            $user = Auth::user();
        }
        $settings = Setting::first();

        $gateway = Gateways::where("code", "stripe")->first();
        if ($gateway == null) {
            abort(404);
        }

        $currency = Currency::where('id', $gateway->currency)->first()->code;

        if ($gateway->mode == 'sandbox') {
            config(['cashier.key' => $gateway->sandbox_client_id]);
            config(['cashier.secret' => $gateway->sandbox_client_secret]);
            config(['cashier.currency' => $currency]);
        } else {
            config(['cashier.key' => $gateway->live_client_id]); //$settings->stripe_key
            config(['cashier.secret' => $gateway->live_client_secret]); //$settings->stripe_secret
            config(['cashier.currency' => $currency]); //currency()->code
        }

        $sub = $user->subscriptions()->where('stripe_status', 'active')->orWhere('stripe_status', 'trialing')->first();
        if ($sub != null) {
            if ($sub->paid_with == 'stripe') {
                $activeSub = $sub->asStripeSubscription();

                if ($activeSub->status == 'active' or $activeSub->status == 'trialing') {
                    return true;
                } else {
                    $sub->stripe_status = 'cancelled';
                    $sub->ends_at = \Carbon\Carbon::now();
                    $sub->save();
                    return false;
                }
            }
        }

        return false;
    }


    public static function checkIfTrial()
    {

        // $plan = PaymentPlans::find($request->plan);
        $user = Auth::user();
        $settings = Setting::first();

        $gateway = Gateways::where("code", "stripe")->first();
        if ($gateway == null) {
            abort(404);
        }

        $currency = Currency::where('id', $gateway->currency)->first()->code;

        if ($gateway->mode == 'sandbox') {
            config(['cashier.key' => $gateway->sandbox_client_id]);
            config(['cashier.secret' => $gateway->sandbox_client_secret]);
            config(['cashier.currency' => $currency]);
        } else {
            config(['cashier.key' => $gateway->live_client_id]); //$settings->stripe_key
            config(['cashier.secret' => $gateway->live_client_secret]); //$settings->stripe_secret
            config(['cashier.currency' => $currency]); //currency()->code
        }

        $sub = $user->subscriptions()->where('stripe_status', 'active')->orWhere('stripe_status', 'trialing')->first();
        if ($sub != null) {
            if ($sub->paid_with == 'stripe') {
                // $activeSub = $sub->asStripeSubscription();
                // return $activeSub->onTrial();
                return $user->subscription($sub->name)->onTrial();
                //return $user->subscription($sub->stripe_id)->onTrial();
            }
        }

        return false;
    }



    /**
     * Since price id is changed, we must update user data, i.e cancel current subscriptions.
     */
    public static function updateUserData()
    {

        try {

            $history = OldGatewayProducts::where([
                "gateway_code" => 'stripe',
                "status" => 'check'
            ])->get();

            if ($history != null) {

                $user = Auth::user();

                $gateway = Gateways::where("code", "stripe")->first();
                if ($gateway == null) {
                    abort(404);
                }

                $key = null;

                if ($gateway->mode == 'sandbox') {
                    $key = $gateway->sandbox_client_secret;
                } else {
                    $key = $gateway->live_client_secret;
                }

                $stripe = new \Stripe\StripeClient($key);

                foreach ($history as $record) {

                    // check record current status from gateway
                    $lookingFor = $record->old_price_id;

                    // if active disable it
                    if ($lookingFor != 'undefined') {
                        $stripe->prices->update($lookingFor, ['active' => false]);
                    }

                    // search subscriptions for record
                    $subs = SubscriptionsModel::where([
                        'stripe_status' => 'active',
                        'stripe_price'  => $lookingFor
                    ])->get();

                    if ($subs != null) {
                        foreach ($subs as $sub) {
                            // cancel subscription order from gateway
                            $user->subscription($sub->name)->cancelNow();

                            // cancel subscription from our database
                            $sub->stripe_status = 'cancelled';
                            $sub->ends_at = \Carbon\Carbon::now();
                            $sub->save();
                        }
                    }

                    $record->status = 'checked';
                    $record->save();
                }
            }
        } catch (\Exception $th) {
            error_log("StripeController::updateUserData(): " . $th->getMessage());
            return ["result" => Str::before($th->getMessage(), ':')];
            // return Str::before($th->getMessage(),':');
        }
    }



    public static function cancelSubscribedPlan($planId, $subsId)
    {
        try {
            $user = Auth::user();
            $settings = Setting::first();

            $gateway = Gateways::where("code", "stripe")->first();
            if ($gateway == null) {
                abort(404);
            }

            $currency = Currency::where('id', $gateway->currency)->first()->code;

            if ($gateway->mode == 'sandbox') {
                config(['cashier.key' => $gateway->sandbox_client_id]);
                config(['cashier.secret' => $gateway->sandbox_client_secret]);
                config(['cashier.currency' => $currency]);
            } else {
                config(['cashier.key' => $gateway->live_client_id]); //$settings->stripe_key
                config(['cashier.secret' => $gateway->live_client_secret]); //$settings->stripe_secret
                config(['cashier.currency' => $currency]); //currency()->code
            }

            $user->subscription($planId)->cancelNow();
            $user->save();

            return true;
        } catch (\Exception $th) {
            error_log("\n------------------------\nStripeController::cancelSubscribedPlan(): " . $th->getMessage() . "\n------------------------\n");
            // return Str::before($th->getMessage(),':');
            return false;
        }
    }

    function verifyIncomingJson(Request $request){

        $gateway = Gateways::where("code", "stripe")->first();

        if(isset($gateway->webhook_secret)){
            $secret = $gateway->webhook_secret;
            if(Str::startsWith($secret, 'whsec') == true){
                $endpoint_secret = $secret;

                if($request->hasHeader('stripe-signature') == true){
                    $sig_header = $request->header('stripe-signature');
                }else{
                    Log::error('(Webhooks) StripeController::verifyIncomingJson() -> Invalid header');
                    return null;
                }

                $payload = $request->getContent();
                $event = null;

                try {
                    $event = \Stripe\Webhook::constructEvent(
                        $payload, $sig_header, $endpoint_secret
                    );
                    return json_encode($event);
                } catch(\UnexpectedValueException $e) {
                    // Invalid payload
                    Log::error('(Webhooks) StripeController::verifyIncomingJson() -> Invalid payload : '. $payload);
                    return null;
                } catch(\Stripe\Exception\SignatureVerificationException $e) {
                    // Invalid signature
                    Log::error('(Webhooks) StripeController::verifyIncomingJson() -> Invalid signature : '. $payload);
                    return null;
                }
            }
        }

        return null;

    }


    public function handleWebhook(Request $request){

        // Log::info($request->getContent());
        // $verified = $request->getContent();

        $verified = self::verifyIncomingJson($request);

        if($verified != null){

            // Retrieve the JSON payload
            $payload = $verified;

            // Fire the event with the payload
            event(new StripeWebhookEvent($payload));
        
            return response()->json(['success' => true]);
        
        }else{
            // Incoming json is NOT verified
            abort(404);
        }

    }


    public static function createWebhook(){

        try{

            // $user = Auth::user();

            $gateway = Gateways::where("code", "stripe")->first();
            if ($gateway == null) {
                abort(404);
            }

            $key = null;

            if ($gateway->mode == 'sandbox') {
                $key = $gateway->sandbox_client_secret;
            } else {
                $key = $gateway->live_client_secret;
            }

            $stripe = new \Stripe\StripeClient($key);

            $webhooks = $stripe->webhookEndpoints->all();

            if(count($webhooks['data']) > 0){
                // There is/are webhook(s) defined. Remove existing.
                foreach ($webhooks['data'] as $hook) {
                    $tmp = json_decode($stripe->webhookEndpoints->delete($hook->id,[]));
                    if(isset($tmp->deleted)){
                        if($tmp->deleted == false){
                            Log::error('Webhook '.$hook->id.' could not deleted.');
                        }
                    }else{
                        Log::error('Webhook '.$hook->id.' could not deleted.');
                    }
                }
            }

            // Create new webhook

            $url = url('/').'/webhooks/stripe';

            $events = [
                'invoice.paid',                     // A payment is made on a subscription.
                'customer.subscription.deleted'     // A subscription is cancelled.
            ];

            $response = $stripe->webhookEndpoints->create([
                'url' => $url,
                'enabled_events' => $events,
            ]);

            $gateway->webhook_id = $response->id;
            $gateway->webhook_secret = $response->secret;
            $gateway->save();

        } catch (AuthenticationException $th) {
            error_log("StripeController::createWebhook(): ".$th->getMessage());
            return back()->with(['message' => "Stripe Authentication Error. Invalid API Key provided.", 'type' => 'error']);
        } catch (InvalidArgumentException $th) {
            error_log("StripeController::createWebhook(): ".$th->getMessage());
            return back()->with(['message' => "You must provide Stripe API Key.", 'type' => 'error']);
        } catch (\Exception $th) {
            error_log("StripeController::createWebhook(): ".$th->getMessage());
            return back()->with(['message' => "Stripe Error : ".$th->getMessage(), 'type' => 'error']);
        }
    }
}
