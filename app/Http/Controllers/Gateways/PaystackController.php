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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use App\Models\PaystackPaymentInfo;
use App\Events\PaystackWebhookEvent;

/**
 * Controls ALL Payment actions of paystack
 */
class PaystackController extends Controller
{
    protected static $client = "https://api.paystack.co/";
    protected static $product_endpoint = "product";
    protected static $plan_endpoint = "plan";
    protected static $subscription_endpoint = "subscription";
    protected static $transaction_verify_endpoint = "transaction/verify/";

    // curl post request template
    public static function curl_req($second_url = "" , $key, $data = [])
    {
        $fields_string = http_build_query($data);
        //open connection
        $ch = curl_init();
        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, self::$client . $second_url);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer ". $key,
            "Cache-Control: no-cache",
        ));
        //So that curl_exec returns the contents of the cURL; rather than echoing it
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 
        //execute post
        $request = curl_exec($ch);
        curl_close($ch);
        if ($request) {
            $result = json_decode($request, true);
            if (isset($result['status']) && $result['status'] !== true) {
                abort(400, "Paystack: ".$result['message']);
            } 
            return $result;
        } else {
            abort(400, $result);
        }
    }
    public static function curl_req_get($param ,$key)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => self::$client.$param,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer ".$key,
            "Cache-Control: no-cache",
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            abort(400, "Paystack: ".$err);
        } else {
            $result = json_decode($response, true);
            if (isset($result['status']) && $result['status'] !== true) {
                abort(400, "Paystack: ".$result['message']);
            } 
            return $result;            
        }
    }
    /**
     * Reads GatewayProducts table and returns price id of the given plan
     */
    public static function getPaystackPriceId($planId){

        //check if plan exists
        $plan = PaymentPlans::where('id', $planId)->first();
        if($plan != null){
            $product = GatewayProducts::where(["plan_id" => $planId, "gateway_code" => "paystack"])->first();
            if($product != null){
                return $product->price_id;
            }else{
                return null;
            }
        }
        return null;
    }
    /**
     * Reads GatewayProducts table and returns price id of the given plan
     */
    public static function getPaystackProductId($planId){

        //check if plan exists
        $plan = PaymentPlans::where('id', $planId)->first();
        if($plan != null){
            $product = GatewayProducts::where(["plan_id" => $planId, "gateway_code" => "paystack"])->first();
            if($product != null){
                return $product->product_id;
            }else{
                return null;
            }
        }
        return null;
    }
    public static function test(){ 
        return self::getSubscriptionRenewDate();
    }
    /**
     * Saves Membership plan product in paystack gateway.
     * @param planId ID of plan in PaymentPlans model.
     * @param productName Name of the product, plain text
     * @param price Price of product
     * @param frequency Time interval of subscription, month / annual
     * @param type Type of product subscription/one-time
     */
    public static function saveProduct($planId, $productName, $price, $frequency, $type, $incomingProvider = null){

        try{

            $gateway = Gateways::where("code", "paystack")->first();
            if($gateway == null) { abort(404); } 


            $plan = PaymentPlans::where('id', $planId)->first();
            $currency = Currency::where('id', $gateway->currency)->first()->code;
            $price = (int)(((float)$price) * 100); // Must be in cents level for stripe


            if ($gateway->mode == 'sandbox') {
                $key = $gateway->sandbox_client_secret;
            } else {
                $key = $gateway->live_client_secret;
            }

            $product = null;
            $oldProductId = null;

            //check if product exists
            $productData = GatewayProducts::where(["plan_id" => $planId, "gateway_code" => "paystack"])->first();
            if($productData != null){
                // Create product in every situation. maybe user updated paystack credentials.
                if($productData->product_id != null){ // && $productName != null
                    //Product has been created before
                    $oldProductId = $productData->product_id;
                }
                $data = [
                    'name' =>  $productName,
                    'description' =>  $productName,
                    'price' => $price,
                    'currency' => $currency,
                ];

                $newProduct = self::curl_req(self::$product_endpoint , $key, $data);

                $productData->product_id = $newProduct['data']['product_code'];
                $productData->plan_name = $productName;
                $productData->save();


                $product = $productData;
            }else{

                $data = [
                    'name' =>  $productName,
                    'description' =>  $productName,
                    'price' => $price,
                    'currency' => $currency,
                ];

                $newProduct =  self::curl_req(self::$product_endpoint, $key, $data);
                $product = new GatewayProducts();
                $product->plan_id = $planId;
                $product->plan_name = $productName;
                $product->gateway_code = "paystack";
                $product->gateway_title = "Paystack";
                $product->product_id = $newProduct['data']['product_code'];
                $product->save();
            }

            //check if price exists
            if($product->price_id != null){
                //Price exists - here price_id is plan_id in paystack ( Billing plans id )
                // One-Time price
                if($type == "o"){

                    // paystack handles one time prices with orders, so we do not need to set anything for one-time payments.
                    $product->price_id = __('Not Needed');
                    $product->save();

                }else{
                    // Subscription
                    // Deactivate old billing plan --> Moved to updateUserData()
                    $oldBillingPlanId = $product->price_id;
                    // $oldBillingPlan = $provider->deactivatePlan($oldBillingPlanId);
                    // create new billing plan with new values
                    $interval = $frequency == "m" ? 'monthly' : 'annually';
                    if($plan->trial_days != "undefined"){
                        $trials = $plan->trial_days ?? 0;
                    }else{
                        $trials = 0;
                    }
                    $billingPlan = self::curl_req(self::$plan_endpoint, $key, [
                        'name' => $productName,
                        'interval' => $interval, 
                        'amount' => $price,
                        'description' => $product->product_id,
                        'currency' => $currency,
                    ]);

                    $product->price_id = $billingPlan['data']['plan_code'];
                    $product->save();

                    $history = new OldGatewayProducts();
                    $history->plan_id = $planId;
                    $history->plan_name = $productName;
                    $history->gateway_code = 'paystack';
                    $history->product_id = $product->product_id;
                    $history->old_product_id = $oldProductId;
                    $history->old_price_id = $oldBillingPlanId;
                    $history->new_price_id = $billingPlan['data']['plan_code'];
                    $history->status = 'check';
                    $history->save();
                    $tmp = self::updateUserData();
                }
            }else{
                // price_id is null so we need to create plans
                // One-Time price
                if($type == "o"){
                    // paystack handles one time prices with orders, so we do not need to set anything for one-time payments.
                    $product->price_id = __('Not Needed');
                    $product->save();
                }else{
                    // Subscription
                    // to subscribe, first create billing plan. then subscribe with it. so price_id is billing_plan_id
                    // subscribe has different id and logic in paystack
                    $interval = $frequency == "m" ? 'monthly' : 'annually';
                    $trials = $plan->trial_days ?? 0;
                    $billingPlan = self::curl_req(self::$plan_endpoint, $key, [
                        'name' => $productName,
                        'interval' => $interval, 
                        'amount' => $price,
                        'description' => $product->product_id,
                        'currency' => $currency,
                    ]);
                    $product->price_id = $billingPlan['data']['plan_code'];
                    $product->save();
                }
            }


        }catch(\Exception $ex){
            error_log("PaystackController::saveProduct()\n".$ex->getMessage());
            return back()->with(['message' => $ex->getMessage(), 'type' => 'error']);
        }

    } 
    /**
     * Displays Payment Page of paystack gateway.
     */
    public static function subscribe($planId, $plan, $incomingException = null)
    {
        $gateway = Gateways::where("code", "paystack")->first();
        if($gateway == null) { abort(404); } 

        $settings = Setting::first();

        $user = Auth::user();

        $subscriptionId = null;
        $exception = $incomingException;
        $orderId = Str::random(12);
        $productId = self::getPaystackProductId($planId);
        $billingPlanId = self::getPaystackPriceId($planId);

        try {
            if($productId == null){
                $exception = "Product ID is not set! Please save Membership Plan again.";
            }

            if($billingPlanId == null){
                $exception = "Plan ID is not set! Please save Membership Plan again.";
            }


            if($exception == null){
                $payment = new UserOrder();
                $payment->order_id = $orderId;
                $payment->plan_id = $planId;
                $payment->user_id = $user->id;
                $payment->payment_type = 'Paystack';
                $payment->price = $plan->price;
                $payment->affiliate_earnings = ($plan->price*$settings->affiliate_commission_percentage)/100;
                $payment->status = 'Waiting';
                $payment->country = $user->country ?? 'Unknown';
                $payment->save();
            }

        } catch (\Exception $th) {
            $exception = Str::before($th->getMessage(),':');
        }

        return view('panel.user.payment.subscription.payWithPaystack', compact('plan', 'billingPlanId', 'exception', 'orderId', 'productId', 'gateway', 'planId'));
    }
    /**
     * Handles payment action of Stripe.
     * 
     * Subscribe payment page posts here.
     */
    public function subscribePay(Request $request)
    {
        try{
            $gateway = Gateways::where("code", "paystack")->first();
            if ($gateway == null) {
                abort(404);
            }

            $orderId = $request->orderId;
            $productId = $request->productId;
            $planId = $request->planId;
            $billingPlanId = $request->billingPlanId;

            $payment_response = json_decode($request->response, true);
            $payment_response_status = $payment_response['status'];
            $payment_response_message = $payment_response['message'];
            $payment_response_reference = $payment_response['reference'];


            $plan = PaymentPlans::where('id', $planId)->first();
            $payment = UserOrder::where('order_id', $orderId)->first();
            $user = Auth::user();

            if ($gateway->mode == 'sandbox') {
                $key = $gateway->sandbox_client_secret;
            } else {
                $key = $gateway->live_client_secret;
            }

            # verify transaction with paystack if it was successful then continue
            $reqs = self::curl_req_get(self::$transaction_verify_endpoint. $payment_response_reference , $key);

            if($reqs['status'] == false){ # if something went wrong with the request
                abort(404);
            }
            # failed or success
            if($reqs['data']['status'] != 'success'){ # if the transaction was not successful
                abort(400 , $reqs['data']['gateway_response']);
            }

            $bill_customer_id = $reqs['data']['customer']['id'];
            $bill_plan_id = $reqs['data']['plan_object']['id'];


            # log the transaction data to database
            $info = new PaystackPaymentInfo();
            $info->user_id = Auth::user()->id;
            $info->email = Auth::user()->email;
            $info->reference = $payment_response['reference'] ?? '';
            $info->trans = $payment_response['trans'] ?? '';
            $info->status = $payment_response['status']?? '';
            $info->message = $payment_response['message']?? '';
            $info->transaction = $payment_response['transaction']?? '';
            $info->trxref = $payment_response['trxref']?? '';
            $info->amount = ($reqs['data']['amount'] / 100) ?? '';
            $info->customer_code = $reqs['data']['customer']['customer_code']?? '';
            $info->plan_code = ($reqs['data']['plan']?? ''). " / " . $planId;
            $info->currency = $reqs['data']['currency']?? '';
            $info->other = $reqs['data']['paidAt']?? '';
            $info->save();

            $subscription_billing = self::curl_req_get(self::$subscription_endpoint. "?customer=".$bill_customer_id."&plan=".$bill_plan_id , $key);
            if($subscription_billing['status'] == false){ # if something went wrong with the request
                abort(404);
            }
            $subscription_billing_code = $subscription_billing['data'][0]['subscription_code'];

            if($payment != null){

                $subscription = new SubscriptionsModel();
                $subscription->user_id = $user->id;
                $subscription->name = $planId;
                $subscription->stripe_id = $subscription_billing_code;
                $subscription->stripe_status = 'active';
                $subscription->stripe_price = $billingPlanId;
                $subscription->quantity = 1;
                $subscription->plan_id = $planId;
                $subscription->paid_with = 'paystack';
                $subscription->save();


                $subscriptionItem = new SubscriptionItems();
                $subscriptionItem->subscription_id = $subscription->id;
                $subscriptionItem->stripe_id = $orderId;
                $subscriptionItem->stripe_product = $productId;
                $subscriptionItem->stripe_price = $billingPlanId;
                $subscriptionItem->quantity = 1;
                $subscriptionItem->save();

                $payment->status = 'Success';
                $payment->save();

                $plan->total_words == -1? ($user->remaining_words = -1) : ($user->remaining_words += $plan->total_words);
                $plan->total_images == -1? ($user->remaining_images = -1) : ($user->remaining_images += $plan->total_images);

                $user->save();

                createActivity($user->id, 'Subscribed', $plan->name.' Plan', null);

                return redirect()->route('dashboard.index')->with(['message' => 'Thank you for your purchase. Enjoy your remaining words and images.', 'type' => 'success']);

            }else{
                $msg="PaystackController::subscribePay(): Could not find required payment order!";
                error_log($msg);
                return redirect()->route('dashboard.index')->with(['message' => $msg, 'type' => 'error']);
            }

        } catch (\Exception $th) {
            error_log("PaystackController::subscribePay(): ".$th->getMessage());
            return redirect()->route('dashboard.index')->with(['message' => $th->getMessage(), 'type' => 'error']);
        }
    }
    public static function disableOldSubscriptionAndReturnNew($subscriptionId, $mail_token, $customerID, $planID){
        $gateway = Gateways::where("code", "paystack")->first();
        if($gateway == null) { abort(404); } 
        if ($gateway->mode == 'sandbox') {
            $key = $gateway->sandbox_client_secret;
        } else {
            $key = $gateway->live_client_secret;
        }
        $request = self::curl_req(self::$subscription_endpoint."/disable", $key, [
            'code' => $subscriptionId,
            'token' => $mail_token,
        ]);
        if($request['status'] == true && $request['message'] == "Subscription disabled successfully"){

            # create new subscription insted of old one
            $req = self::curl_req(self::$subscription_endpoint, $key, [
                'customer' => $customerID,
                'plan' => $planID,
            ]);
            if($req['status'] == false){
                error_log("PaystackController::disableOldSubscriptionAndReturnNew(): ".$req['message']);
                return false;
            }
            return $req['data']['subscription_code'];          
        }
        else{
            error_log("PaystackController::disableOldSubscriptionAndReturnNew(): ".$request['message']);
            return false;
        }
    }
    /**
     * Since price id (billing plan) is changed, we must update user data, i.e cancel current subscriptions.
     */
    public static function updateUserData(){

        $gateway = Gateways::where("code", "paystack")->first();
        if($gateway == null) { abort(404); }

        if ($gateway->mode == 'sandbox') {
            $key = $gateway->sandbox_client_secret;
        } else {
            $key = $gateway->live_client_secret;
        }

        $history = OldGatewayProducts::where([
            "gateway_code" => 'paystack',
            "status" => 'check'
        ])->get();

        if($history != null){
            foreach ($history as $record) {
                // check record current status from gateway
                $lookingFor = $record->old_price_id; // billingPlan id in paystack 
                # get also subscription id and customer id and mail token 

                // search subscriptions for record
                $subs = SubscriptionsModel::where([
                    'stripe_status' => 'active',
                    'stripe_price'  => $lookingFor,
                    'paid_with'     => 'paystack'
                ])->get();


                foreach ($subs ?? [] as $sub) {
                    $subscriptionId = $sub->stripe_id;

                    $reqs = self::curl_req_get(self::$subscription_endpoint. "/" . $subscriptionId , $key);
                    if($reqs['status'] == false){ # if something went wrong with the request
                        abort(404);
                    }
                    $mailToken = $reqs['data']['email_token'];
                    $customerId = $reqs['data']['customer']['customer_code'];
                    $planId = $reqs['data']['plan']['plan_code'];
                    // cancel old subscription from gateway
                    $new_subscription_code = self::disableOldSubscriptionAndReturnNew($subscriptionId, $mailToken, $customerId, $planId);
                    if($new_subscription_code == false){
                        error_log("PaystackController::updateUserData(): Could not create new subscription for user: ".$sub->user_id);
                        continue;
                    }

                    $subscription = SubscriptionsModel::where('stripe_id', $subscriptionId)->first();
                    if($subscription != null){
                        $subscription->stripe_id = $new_subscription_code;
                        $subscription->save();
                    }

                }

                $record->status = 'checked';
                $record->save();
            }
        }

    }
    /**
     * Used to generate new product id and price id of all saved membership plans in paypal gateway.
     */
    public static function saveAllProducts()
    {
        try{

            $gateway = Gateways::where("code", "paystack")->first();
            if($gateway == null) { 
                return back()->with(['message' => __('Please enable Paystack'), 'type' => 'error']);
                abort(404); 
            } 

            // Get all membership plans
            $plans = PaymentPlans::where('active', 1)->get();

            foreach ($plans as $plan) {
                // Replaced definitions here. Because if monthly or prepaid words change just updating here will be enough.
                $freq = $plan->frequency == "monthly" ? "m" : "y"; // m => month | y => year
                $typ = $plan->type == "prepaid" ? "o" : "s"; // o => one-time | s => subscription

                self::saveProduct($plan->id, $plan->name, $plan->price, $freq, $typ);
            }
            // Create webhook of paystack
            // $tmp = self::createWebhook();
            //TODO: Check webhook mater.

        }catch(\Exception $ex){
            error_log("PaypalController::saveAllProducts()\n".$ex->getMessage());
            return back()->with(['message' => $ex->getMessage(), 'type' => 'error']);
        }

    }
    /**
     * Cancels current subscription plan
     */
    public static function subscribeCancel(){


        $user = Auth::user();
        $userId=$user->id;
        // Get current active subscription
        $activeSub = SubscriptionsModel::where([['stripe_status', '=', 'active'], ['user_id', '=', $userId], ['paid_with', '=', 'paystack']])->orWhere([['stripe_status', '=', 'trialing'], ['user_id', '=', $userId], ['paid_with', '=', 'paystack']])->first();

        if($activeSub != null){

            $plan = PaymentPlans::where('id', $activeSub->plan_id)->first();

            $gateway = Gateways::where("code", "paystack")->first();
            if($gateway == null) { abort(404); } 
            if ($gateway->mode == 'sandbox') {
                $key = $gateway->sandbox_client_secret;
            } else {
                $key = $gateway->live_client_secret;
            }

            $reqs = self::curl_req_get(self::$subscription_endpoint. "/" . $activeSub->stripe_id , $key);
            if($reqs['status'] == false){ # if something went wrong with the request
                abort(404, $reqs['message']);
            }
            $mailToken = $reqs['data']['email_token'];


            $request = self::curl_req(self::$subscription_endpoint."/disable", $key, [
                'code' => $activeSub->stripe_id,
                'token' => $mailToken,
            ]);

            if($request['status'] == true && $request['message'] == "Subscription disabled successfully"){

                $activeSub->stripe_status = "cancelled";
                $activeSub->ends_at = \Carbon\Carbon::now();
                $activeSub->save();

                $recent_words = $user->remaining_words - $plan->total_words;
                $recent_images = $user->remaining_images - $plan->total_images;


                $user->remaining_words = $recent_words < 0 ? 0 : $recent_words;
                $user->remaining_images = $recent_images < 0 ? 0 : $recent_images;
                $user->save();

                createActivity($user->id, 'Cancelled', 'Subscription plan', null);

                return back()->with(['message' => 'Your subscription is cancelled succesfully.', 'type' => 'success']);

            }
            else{
                error_log("PaystackController::disableOldSubscriptionAndReturnNew(): ".$request['message']);
                return back()->with(['message' => 'Your subscription could not cancelled.', 'type' => 'error']);
            }          


        }

        return back()->with(['message' => 'Could not find active subscription. Nothing changed!', 'type' => 'error']);
    }
    /**
     * Displays Payment Page of Paystack gateway for prepaid plans.
     */
    public static function prepaid($planId, $plan, $incomingException = null){
        $gateway = Gateways::where("code", "paystack")->first();
        if($gateway == null) { abort(404); } 
        $currency = Currency::where('id', $gateway->currency)->first()->code;
        $orderId = null;
        $exception = $incomingException;
        try {
            if(self::getPaystackProductId($planId) == null){
                $exception = "Product ID is not set! Please save Membership Plan again.";
            }   
        } catch (\Exception $th) {
            $exception = Str::before($th->getMessage(),':');
        }
        return view('panel.user.payment.prepaid.payWithPaystack', compact('plan', 'orderId', 'gateway', 'exception', 'currency', 'planId'));
    }
    /**
     * Handles payment action of Stripe.
     * 
     * Prepaid payment page posts here.
     */
    public function prepaidPay(Request $request)
    {

        $payment_response = json_decode($request->response, true);
        $payment_response_reference = $payment_response['reference'];

        $gateway = Gateways::where("code", "paystack")->first();
        if ($gateway == null) {
            abort(404);
        }
        if ($gateway->mode == 'sandbox') {
            $key = $gateway->sandbox_client_secret;
        } else {
            $key = $gateway->live_client_secret;
        }

        # verify transaction with paystack if it was successful then continue
        $reqs = self::curl_req_get(self::$transaction_verify_endpoint. $payment_response_reference , $key);
        if($reqs['status'] == false){ # if something went wrong with the request
            abort(404);
        }
        # failed or success
        if($reqs['data']['status'] != 'success'){ # if the transaction was not successful
            abort(400 , $reqs['data']['gateway_response']);
        }


        # log the transaction data to database
        $info = new PaystackPaymentInfo();
        $info->user_id = Auth::user()->id;
        $info->email = Auth::user()->email;
        $info->reference = $payment_response['reference'] ?? '';
        $info->trans = $payment_response['trans'] ?? '';
        $info->status = $payment_response['status']?? '';
        $info->message = $payment_response['message']?? '';
        $info->transaction = $payment_response['transaction']?? '';
        $info->trxref = $payment_response['trxref']?? '';

        $info->amount = ($reqs['data']['amount'] / 100) ?? '';
        $info->customer_code = $reqs['data']['customer']['customer_code']?? '';
        $info->plan_code = ($reqs['data']['plan']?? ''). " / " . $request->plan;
        $info->currency = $reqs['data']['currency']?? '';
        $info->other = $reqs['data']['paidAt']?? '';
        $info->save();

        $plan = PaymentPlans::find($request->plan);
        $user = Auth::user();
        $settings = Setting::first();

        $payment = new UserOrder();
        $payment->order_id = Str::random(12);
        $payment->plan_id = $plan->id;
        $payment->type = 'prepaid';
        $payment->user_id = $user->id;
        $payment->payment_type = 'Credit, Debit Card';
        $payment->price = $plan->price;
        $payment->affiliate_earnings = ($plan->price * $settings->affiliate_commission_percentage) / 100;
        $payment->status = 'Success';
        $payment->country = $user->country ?? 'Unknown';
        $payment->save();

        $plan->total_words == -1? ($user->remaining_words = -1) : ($user->remaining_words += $plan->total_words);
        $plan->total_images == -1? ($user->remaining_images = -1) : ($user->remaining_images += $plan->total_images);
        $user->save();

        createActivity($user->id, 'Purchased', $plan->name . ' Token Pack', null);

        return redirect()->route('dashboard.index')->with(['message' => 'Thank you for your purchase. Enjoy your remaining words and images.', 'type' => 'success']);
    }
    public static function getSubscriptionDaysLeft(){

        // $gateway = Gateways::where("code", "paystack")->first();
        // if ($gateway == null) {
        //     return null;
        // }

        // if ($gateway->mode == 'sandbox') {
        //     $key = $gateway->sandbox_client_secret;
        // } else {
        //     $key = $gateway->live_client_secret;
        // }

        // $userId=Auth::user()->id;
        // // Get current active subscription
        // $activeSub = SubscriptionsModel::where([['stripe_status', '=', 'active'], ['user_id', '=', $userId], ['paid_with', '=', 'paystack']])->orWhere([['stripe_status', '=', 'trialing'], ['user_id', '=', $userId], ['paid_with', '=', 'paystack']])->first();
        // if($activeSub != null){
        //     $reqs = self::curl_req_get(self::$subscription_endpoint. "/" .$activeSub->stripe_id , $key);
        //     if($reqs['status'] == false){ # if something went wrong with the request
        //         error_log("PaypalController::getSubscriptionStatus() :\n".json_encode($subscription));
        //         return null;
        //     }
        //     //if user is in trial
        //     # TODO: add trail logic here later if possible
        // }

        $gateway = Gateways::where("code", "paystack")->first();
        if ($gateway == null) {
            return null;
        }

        if ($gateway->mode == 'sandbox') {
            $key = $gateway->sandbox_client_secret;
        } else {
            $key = $gateway->live_client_secret;
        }

        $userId=Auth::user()->id;
        // Get current active subscription
        $activeSub = SubscriptionsModel::where([['stripe_status', '=', 'active'], ['user_id', '=', $userId], ['paid_with', '=', 'paystack']])->orWhere([['stripe_status', '=', 'trialing'], ['user_id', '=', $userId], ['paid_with', '=', 'paystack']])->first();
        if($activeSub != null){
            $reqs = self::curl_req_get(self::$subscription_endpoint. "/" .$activeSub->stripe_id , $key);
            if($reqs['status'] == false){ # if something went wrong with the request
                error_log("PaystackController::getSubscriptionRenewDate() :\n".json_encode($reqs));
                return back()->with(['message' => 'Paystack Gateway : '.json_encode($reqs), 'type' => 'error']);
            }
            if(isset($reqs['data']['next_payment_date'])){
                // return \Carbon\Carbon::parse($reqs['data']['next_payment_date'])->format('F jS, Y');
                return \Carbon\Carbon::now()->diffInDays($reqs['data']['next_payment_date']);
            }
        }
        return null;
    }
    public static function getSubscriptionRenewDate(){ 
        $gateway = Gateways::where("code", "paystack")->first();
        if ($gateway == null) {
            return null;
        }

        if ($gateway->mode == 'sandbox') {
            $key = $gateway->sandbox_client_secret;
        } else {
            $key = $gateway->live_client_secret;
        }

        $userId=Auth::user()->id;
        // Get current active subscription
        $activeSub = SubscriptionsModel::where([['stripe_status', '=', 'active'], ['user_id', '=', $userId], ['paid_with', '=', 'paystack']])->orWhere([['stripe_status', '=', 'trialing'], ['user_id', '=', $userId], ['paid_with', '=', 'paystack']])->first();
        if($activeSub != null){
            $reqs = self::curl_req_get(self::$subscription_endpoint. "/" .$activeSub->stripe_id , $key);
            if($reqs['status'] == false){ # if something went wrong with the request
                error_log("PaystackController::getSubscriptionRenewDate() :\n".json_encode($reqs));
                return back()->with(['message' => 'Paystack Gateway : '.json_encode($reqs), 'type' => 'error']);
            }
            if(isset($reqs['data']['next_payment_date'])){
                return \Carbon\Carbon::parse($reqs['data']['next_payment_date'])->format('F jS, Y');
            }else{
                $activeSub->stripe_status = "cancelled";
                $activeSub->ends_at = \Carbon\Carbon::now();
                $activeSub->save();
                return \Carbon\Carbon::now()->format('F jS, Y');
            }
        }
        return null;
    }
    /**
     * Checks status directly from gateway and updates database if cancelled or suspended.
     */
    public static function getSubscriptionStatus(){
        $gateway = Gateways::where("code", "paystack")->first();
        if ($gateway == null) {
            return null;
        }

        if ($gateway->mode == 'sandbox') {
            $key = $gateway->sandbox_client_secret;
        } else {
            $key = $gateway->live_client_secret;
        }


        $userId=Auth::user()->id;
        // Get current active subscription
        $activeSub = SubscriptionsModel::where([['stripe_status', '=', 'active'], ['user_id', '=', $userId], ['paid_with', '=', 'paystack']])->orWhere([['stripe_status', '=', 'trialing'], ['user_id', '=', $userId], ['paid_with', '=', 'paystack']])->first();
        if($activeSub != null){
            $reqs = self::curl_req_get(self::$subscription_endpoint. "/" .$activeSub->stripe_id , $key);
            if($reqs['status'] == false){ # if something went wrong with the request
                error_log("PaystackController::getSubscriptionStatus() :\n".json_encode($reqs));
                return back()->with(['message' => 'Paystack Gateway : '.json_encode($reqs), 'type' => 'error']);
            }
            if ($reqs['data']['status'] == 'active'){
                return true;
            }else{
                $activeSub->stripe_status = 'cancelled';
                $activeSub->ends_at = \Carbon\Carbon::now();
                $activeSub->save();
                return false;
            }
        }
        return null;
    }
    # TODO: complete below function
    public static function checkIfTrial(){
        // $gateway = Gateways::where("code", "paystack")->first();
        // if ($gateway == null) {
        //     return null;
        // }

        // if ($gateway->mode == 'sandbox') {
        //     $key = $gateway->sandbox_client_secret;
        // } else {
        //     $key = $gateway->live_client_secret;
        // }

        // $userId=Auth::user()->id;
        // // Get current active subscription
        // $activeSub = SubscriptionsModel::where([['stripe_status', '=', 'active'], ['user_id', '=', $userId], ['paid_with', '=', 'paystack']])->orWhere([['stripe_status', '=', 'trialing'], ['user_id', '=', $userId], ['paid_with', '=', 'paystack']])->first();
        // if($activeSub != null){
        //     $reqs = self::curl_req_get(self::$subscription_endpoint. "/" .$activeSub->stripe_id , $key);
        //     if($reqs['status'] == false){ # if something went wrong with the request
        //         error_log("PaystackController::checkIfTrial() :\n".json_encode($reqs));
        //         return back()->with(['message' => 'Paystack Gateway : '.json_encode($reqs), 'type' => 'error']);
        //     }
        //     # TODO: check if trail logic here later if possible
        // }
        return false;
    }
    public static function cancelSubscribedPlan($planId, $subsId){

        $user = Auth::user();
        $currentSubscription = SubscriptionsModel::where('id', $subsId)->first();

        if($currentSubscription != null){

            $plan = PaymentPlans::where('id', $planId)->first();
            $gateway = Gateways::where("code", "paystack")->first();
            if ($gateway == null) {
                return null;
            }

            if ($gateway->mode == 'sandbox') {
                $key = $gateway->sandbox_client_secret;
            } else {
                $key = $gateway->live_client_secret;
            }

            $get_subscribe_info = self::curl_req_get(self::$cancelSubscribedPlan. "/" .$currentSubscription->stripe_id , $key);
            if($get_subscribe_info['status'] == false){ # if something went wrong with the request
                error_log("PaystackController::cancelSubscribedPlan() :\n".json_encode($get_subscribe_info));
                return back()->with(['message' => 'Paystack Gateway : '.json_encode($get_subscribe_info), 'type' => 'error']);
            }

            $request = self::curl_req(self::$subscription_endpoint."/disable", $key, [
                'code' => $currentSubscription->stripe_id,
                'token' => $get_subscribe_info['data']['email_token'],
            ]);

            if($request['status'] == true && $request['message'] == "Subscription disabled successfully"){

                $currentSubscription->stripe_status = "cancelled";
                $currentSubscription->ends_at = \Carbon\Carbon::now();
                $currentSubscription->save();
                return true;

            }
        }

        return false;
    }








    # TODO: webhook functions
    function verifyIncomingJson(Request $request){

        try{
            $gateway = Gateways::where("code", "paystack")->first();

            if($gateway->mode == 'sandbox'){
                // Paystack does not support verification on sandbox mode
                return true;
            }

            if($request->hasHeader('x-paystack-signature') == true){
                $signature = $request->header('x-paystack-signature');
            }else{
                return false;
            }

            $payload = $request->getContent();
            if($payload == null){return false;}
            if(isJson($payload) == false){return false;}

            $secret_key = $gateway->live_client_secret;
            if($secret_key == null){return false;}

            if($signature !== hash_hmac('sha512', $payload, $secret_key))
            {
                return false;
            }
            else{
                return true;
            }

        } catch (\Exception $th) {
            error_log("(Webhooks) PaystackController::verifyIncomingJson(): ".$th->getMessage());
        }

        return false;
    }
    public function handleWebhook(Request $request){

        $verified = self::verifyIncomingJson($request);

        if($verified == true){

            // Retrieve the JSON payload
            $payload = $request->getContent();

            // Fire the event with the payload
            event(new PaystackWebhookEvent($payload));

            return response()->json(['success' => true]);

        }else{
            // Incoming json is NOT verified
            abort(404);
        }

    }
    public static function createWebhook(){
        try{

            // $user = Auth::user();

            // $gateway = Gateways::where("code", "paypal")->first();

            // $webhooks = $provider->listWebHooks();

            // if(count($webhooks['webhooks']) > 0){
            //     // There is/are webhook(s) defined. Remove existing.
            //     foreach ($webhooks['webhooks'] as $hook) {
            //         $provider->deleteWebHook($hook->id);
            //     }
            // }

            // // Create new webhook

            // $url = url('/').'/webhooks/paypal';

            // $events = [
            //     'PAYMENT.SALE.COMPLETED',           // A payment is made on a subscription.
            //     'BILLING.SUBSCRIPTION.CANCELLED'   // A subscription is cancelled.
            // ];
            // // 'BILLING.SUBSCRIPTION.EXPIRED',     // A subscription expires.
            // // 'BILLING.SUBSCRIPTION.SUSPENDED'    // A subscription is suspended.

            // $response = $provider->createWebHook($url, $events);

            // $gateway->webhook_id = $response->id;
            // $gateway->save();


        } catch (\Exception $th) {
            error_log("PaypalController::createWebhook(): ".$th->getMessage());
            return back()->with(['message' => $th->getMessage(), 'type' => 'error']);
        }

    }

}