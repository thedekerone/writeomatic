<?php

namespace App\Http\Controllers\Gateways;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Currency;
use App\Models\CustomSettings;
use App\Models\GatewayProducts;
use App\Models\Gateways;
use App\Models\OldGatewayProducts;
use App\Models\PaymentPlans;
use App\Models\Setting;
use App\Models\SettingTwo;
use App\Models\Subscriptions as SubscriptionsModel;
use App\Models\HowitWorks;
use App\Models\User;
use App\Models\UserAffiliate;
use App\Models\UserOrder;
use App\Events\TwoCheckoutWebhookEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Route;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ClientException;


/**
 * Controls ALL Payment actions of 2Checkout
 */
class TwoCheckoutController extends Controller
{
    const API_URL = "https://api.2checkout.com/";
    const GATEWAY_CODE = "twocheckout";

    public static function getRequestHeader() 
    {

        $gateway = Gateways::where("code", self::GATEWAY_CODE)->first();
        if ($gateway == null) {
            abort(404);
        }

        $merchant_code = $gateway->live_client_id;
        $key = $gateway->live_client_secret;

        $date = gmdate('Y-m-d H:i:s'); 
        $string = strlen($merchant_code) . $merchant_code . strlen($date) . $date; 

        # sha256 or sha3-256 
        $hashAlgorithm = 'md5'; 
        $hash = hash_hmac($hashAlgorithm , $string, $key);

        // Create a Guzzle client

        $avangate = "code=\"{$merchant_code}\" date=\"{$date}\" hash=\"{$hash}\"";
        $client = new Client([
            'base_uri' => self::API_URL,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'X-Avangate-Authentication' => $avangate
            ]
        ]);
        return $client;
    }

    /**
     * Reads GatewayProducts table and returns price id of the given plan
     */
    public static function getStripePriceId($planId)
    {
        //check if plan exists
        $plan = PaymentPlans::where('id', $planId)->first();
        if ($plan != null) {
            $product = GatewayProducts::where(["plan_id" => $planId, "gateway_code" => self::GATEWAY_CODE])->first();
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
        $gateway = Gateways::where("code", self::GATEWAY_CODE)->first();
        if ($gateway == null) {
            abort(404);
        }

        $currency = Currency::where('id', $gateway->currency)->first()->code;
        $merchant_code = $gateway->live_client_id;
        $key = $gateway->live_client_secret;
        $exception = $incomingException;

        return view('panel.user.payment.subscription.payWithTwoCheckout', compact('merchant_code', 'planId', 'plan' ,'exception'));
    }


    /**
     * Handles payment action of Stripe.
     * 
     * Subscribe payment page posts here.
     */
    public function subscribePay(Request $request)
    {
        $EEStoken = $request->token;
        $plan = PaymentPlans::find($request->plan);
        $productData = GatewayProducts::where(["plan_id" => $request->plan, "gateway_code" => self::GATEWAY_CODE])->first();
        $user = Auth::user();
        $settings = Setting::first();
        $settings_two = SettingTwo::first();
        $server_url = $request->root();
        
        $gateway = Gateways::where("code", self::GATEWAY_CODE)->first();
        if ($gateway == null) {
            abort(404);
        }

        $currency = Currency::where('id', $gateway->currency)->first()->code;

        $client = self::getRequestHeader();
        // Check whether subscirption is already created or not.
        $subscription = SubscriptionsModel::where(["user_id" => $user->id, "plan_id" => $plan->id])->first();

        // if ($subscription) {
        //     try {
        //         $response = $client->post("rest/6.0/subscriptions/".$subscription->stripe_id);
        //     } catch (Exception $e) {
        //         return back()->with(['message' => 'Your subscription is not reactivated. Please contact support team', 'type' => 'success']);
        //     }     
        // } else {
            $payload = array (
                "Country" => "us",
                "Currency" => $plan->currency,
                // "CustomerReference" => 'MagicaiUserPrepaid'.$user->id,
                // "ExternalCustomerReference"   => 'MagicaiUser'.$user->id,
                "Language" => 'en',
                "BillingDetails"=> 
                    array (
                        // "Address1" => $user->address,
                        "Address1" => "Trask Ave, Westminster",
                        "City" => "city",
                        "CountryCode" => "US",
                        "Email" => $user->email,
                        "FirstName" => $user->name,
                        "LastName" => $user->surname,
                        "Phone" => $user->phone,
                        "Zip" => "70403-900"
                    ),
                "Items" => [
                    array(
                        "Code"=> $productData->product_id,
                        "Quantity"=> '1'
                    )
                ],
                "PaymentDetails" => 
                    array(
                        "Currency" => $plan->currency,
                        "PaymentMethod" => array (
                            "EesToken" => $EEStoken,
                            "RecurringEnabled" => true,
                            "Vendor3DSReturnURL" => $server_url."/dashboard/user/payment",
                            "Vendor3DSCancelURL" => $server_url."/dashboard/user/payment"
                        ),
                    ),
            );

            if ($gateway->mode == 'sandbox')
                $payload['PaymentDetails']['Type'] = "TEST";

            try {						
                $response = $client->post('rest/6.0/orders/', [
                    'json' => $payload
                ]);
                
                // Check the response status code
                if ($response->getStatusCode() == 201) {
                    $order_response = json_decode($response->getBody());
                    $subscription = new SubscriptionsModel();
                    $subscription->user_id = $user->id;
                    $subscription->name = $plan->id;
                    $subscription->plan_id = $plan->id;
                    $subscription->stripe_id = $order_response->Items[0]->ProductDetails->Subscriptions[0]->SubscriptionReference ?? "";
                    $subscription->stripe_price = $productData->product_id;
                    $subscription->paid_with = self::GATEWAY_CODE;
                }
            } catch (ClientException $e) {
                $res = json_decode($e->getResponse()->getBody()->getContents(), true);
                return response()->json(['status' => 'error', 'message' => $res['message']]);
            } catch(Exception $e){
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        // }

        $subscription->stripe_status = 'active';
        $subscription->save();

        $payment = new UserOrder();
        $payment->order_id = Str::random(12);
        $payment->plan_id = $plan->id;
        $payment->type = 'subscribe';
        $payment->user_id = $user->id;
        $payment->payment_type = 'Credit, Debit Card';
        $payment->price = $plan->price;
        $payment->affiliate_earnings = ($plan->price * $settings->affiliate_commission_percentage) / 100;
        $payment->status = 'Success';
        $payment->country = $user->country ?? 'Unknown';
        $payment->save();

        // $plan->total_words == -1? ($user->remaining_words = -1) : ($user->remaining_words += $plan->total_words);
        // $plan->total_images == -1? ($user->remaining_images = -1) : ($user->remaining_images += $plan->total_images);

        // $user->save();

        createActivity($user->id, 'Subscribed', $plan->name . ' Plan', null);

        $success_message = "Thank you for your purchase. Enjoy your remaining words and images.";
        return $success_message;
    }

    /**
     * Cancels current subscription plan
     */
    public static function subscribeCancel()
    {
        $user = Auth::user();
        $settings = Setting::first();

        $gateway = Gateways::where("code", self::GATEWAY_CODE)->first();
        if ($gateway == null) {
            abort(404);
        }

        // $currency = Currency::where('id', $gateway->currency)->first()->code;
        $activeSub = SubscriptionsModel::where([['stripe_status', '=', 'active'],['user_id','=', $user->id]])->first();
        if ($activeSub != null) {
            $plan = PaymentPlans::where('id', $activeSub->plan_id)->first();
            $client = self::getRequestHeader();

            $payload = array (
                "ChurnReasonOther" => "Cancel this plan."
            );

            try {
                $response = $client->delete("rest/6.0/subscriptions/".$activeSub->stripe_id, [
                    'json' => $payload
                ]);
            } catch (Exception $e) {
                return back()->with(['message' => 'Your subscription is not cancelled. Please contact support team', 'type' => 'success']);
            }
            
            $activeSub->stripe_status = 'cancelled';
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
        return back()->with(['message' => 'Could not find active subscription. Nothing changed!', 'type' => 'error']);
    }


    /**
     * Displays Payment Page of Stripe gateway for prepaid plans.
     */
    public static function prepaid($planId, $plan, $incomingException = null)
    {

        $gateway = Gateways::where("code", self::GATEWAY_CODE)->first();
        if ($gateway == null) {
            abort(404);
        }

        // $currency = Currency::where('id', $gateway->currency)->first()->code;
        $merchant_code = $gateway->live_client_id;
        $key = $gateway->live_client_secret;

        $exception = $incomingException;

        return view('panel.user.payment.prepaid.payWithTwoCheckout', compact('merchant_code', 'planId', 'plan' ,'exception'));
    }


    /**
     * Handles payment action of Stripe.
     * 
     * Prepaid payment page posts here.
     */
    public function prepaidPay(Request $request)
    {   
        $EEStoken = $request->token;
        $plan = PaymentPlans::find($request->plan);
        $product = GatewayProducts::where([['plan_id', '=', $request->plan], ['gateway_code','=', self::GATEWAY_CODE]])->first();
        $user = Auth::user();
        $settings = Setting::first();
        $settings_two = SettingTwo::first();
        
        $server_url = $request->root();
        
        $client = self::getRequestHeader();
        
        $payload = array(
            "Country" => "us",
            "Currency" => $plan->currency,
            // "CustomerReference" => 'MagicaiUserPrepaid'.$user->id,
            // "ExternalCustomerReference"   => 'MagicaiUser'.$user->id,
            "Language" => 'en',
            "BillingDetails"=> 
                array(
                    // "Address1" => $user->address,
                    "Address1" => "Trask Ave, Westminster",
                    "City" => "city",
                    "CountryCode" => "US",
                    "Email" => $user->email,
                    "FirstName" => $user->name,
                    "LastName" => $user->surname,
                    "Phone" => $user->phone,
                    "Zip" => "70403-900"
                )
            ,
            "Items" => [
                array(
                    "Code"=> $product->product_id,
                    "Quantity"=> '1'
                )
            ],
            "PaymentDetails" => 
                array(
                    "Currency" => $plan->currency,
                    "PaymentMethod" => array (
                        "EesToken" => $EEStoken,
                        "RecurringEnabled" => false,
                        "Vendor3DSReturnURL" => $server_url."/dashboard/user/payment",
                        "Vendor3DSCancelURL" => $server_url."/dashboard/user/payment"
                    ),
                ),
        );

        $gateway = Gateways::where("code", self::GATEWAY_CODE)->first();
        if ($gateway == null) {
            abort(404);
        }

        if ($gateway->mode == 'sandbox')
            $payload['PaymentDetails']['Type'] = "TEST";
        
        try {						
            $response = $client->post('rest/6.0/orders/', [
                'json' => $payload
            ]);
        } catch (ClientException $e) {
            $res = json_decode($e->getResponse()->getBody()->getContents(), true);
            return response()->json(['status' => 'error', 'message' => $res['message']]);
        } catch(Exception $e){
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }

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

        $success_message = "Thank you for your purchase. Enjoy your remaining words and images.";
        return $success_message;
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
            $gateway = Gateways::where("code", self::GATEWAY_CODE)->first();
            if ($gateway == null) {
                abort(404);
            }
 
            $currency = Currency::where('id', $gateway->currency)->first()->code;
            
            // Create a Guzzle client
            $client = self::getRequestHeader();

            //generate random string to make product code
            $rand_str = substr(md5(time()), 0, 12);
            
            $productCode = null;
            if ($type == "o") {
                $productCode = "magicai-Prepaid-".$planId."-".$rand_str;
                $product_payload = array(
                    "ProductCode"   => $productCode,
                    "ProductName"   => $productName,
                    "Enabled"       => true,
                    "PricingConfigurations"=> [
                        array(
                            "PricingSchema"     => "FLAT",
                            "PriceType"         => "NET",
                            "DefaultCurrency"   => $currency,
                            "Prices"            => array(
                                "Regular"   => [
                                    array(
                                        "Amount"        => $price,
                                        "Currency"      => $currency,
                                        "MinQuantity"   => 1,
                                        "MaxQuantity"   => 10
                                    )
                                ]
                            )
                            )
                    ]
                );
            } else {

                if( $frequency == "m") $biling_cycle = 1;
                else $biling_cycle = 12;
                
                $productCode = "magicai-Subscription-".$planId."-".$rand_str;
                $product_payload = array (
                    "ProductCode" => $productCode,
                    "ProductName" => $productName,
                    "Enabled" => true,
                    "PricingConfigurations"=> [
                        array (
                            "PricingSchema" => "FLAT",
                            "PriceType" => "NET",
                            "DefaultCurrency" => $currency,
                            "Prices" => array (
                                "Regular" => [
                                    array (
                                        "Amount" => $price,
                                        "Currency" => $currency,
                                        "MinQuantity" => 1,
                                        "MaxQuantity" => 10
                                        )
                                ]
                            )
                        )
                    ],
                    "GeneratesSubscription" => true,
                    "SubscriptionInformation" => array (
                        "BundleRenewalManagement" => "GLOBAL",
                        "BillingCycle" => $biling_cycle,
                        "BillingCycleUnits" => "M",
                        "IsOneTimeFee" => false,
                        "ContractPeriod" => array(
                            "Period" => "0",
                            "PeriodUnits" => "M",
                            "IsUnlimited" => false,
                            "Action" => null,
                            "EmailsDuringContract" => true
                        ),
                        "UsageBilling" => 0,
                        "GracePeriod" => array(
                            "Period" => null,
                            "PeriodUnits" => "D",
                            "IsUnlimited" => false,
                            "Type" => "GLOBAL"
                        ),
                    ),
                );
            }

            //create new product
            try {
                // Make the API request to create the product
                $response = $client->post('rest/6.0/products', [
                    'json' => $product_payload
                ]);

                // Check the response status code
                if ($response->getStatusCode() != 201) 
                    return back()->with(['message' => 'Failed to create product. Error: ' . $response->getBody(), 'type' => 'error']);
            } catch (ClientException $ex) {
                Log::error($ex->getMessage());
                return back()->with(['message' => $ex->getMessage(), 'type' => 'error']);
            }
            catch (Exception $ex) {
                return back()->with(['message' => $ex->getMessage(), 'type' => 'error']);
            }

            //check if product exists
            $productData = GatewayProducts::where(["plan_id" => $planId, "gateway_code" => self::GATEWAY_CODE])->first();

            if ($productData != null) {                

                $oldProductId = $productData->product_id;
                $productData->product_id = $productCode;
                $productData->price_id = $productCode;
                $productData->plan_name = $productName;
                $productData->save();

                if ($type == "s") { // subscription
                    $history = new OldGatewayProducts();
                    $history->plan_id = $planId;
                    $history->plan_name = $productName;
                    $history->gateway_code = self::GATEWAY_CODE;
                    $history->product_id = $productData->product_id;
                    $history->old_product_id = $oldProductId;
                    $history->status = 'check';
                    $history->save();

                    $tmp = self::updateUserData();
                }
            } else {
                $product = new GatewayProducts();
                $product->plan_id = $planId;
                $product->plan_name = $productName;
                $product->gateway_code = self::GATEWAY_CODE;
                $product->gateway_title = self::GATEWAY_CODE;
                $product->product_id = $productCode;
                $product->price_id = $productCode;
                $product->save();
            }
        } catch (\Exception $ex) {
            error_log("TwoCheckoutController::saveProduct()\n" . $ex->getMessage());
            return back()->with(['message' => $ex->getMessage(), 'type' => 'error']);
        }
    }

    /**
     * Used to generate new product id and price id of all saved membership plans in stripe gateway.
     */

    public static function saveAllProducts()
    {
        try {
            $gateway = Gateways::where("code", self::GATEWAY_CODE)->first();
            if ($gateway == null) {
                return back()->with(['message' => __('Please enable 2Checkout'), 'type' => 'error']);
                abort(404);
            }
            
            $plans = PaymentPlans::where('active', 1)->get();

            foreach ($plans as $plan) {
                // Replaced definitions here. Because if monthly or prepaid words change just updating here will be enough.
                $freq = $plan->frequency == "monthly" ? "m" : "y"; // m => month | y => year
                $typ = $plan->type == "prepaid" ? "o" : "s"; // o => one-time | s => subscription

                self::saveProduct($plan->id, $plan->name, $plan->price, $freq, $typ);
            }
            // Create webhook of stripe
            // $tmp = self::createWebhook();
        } catch (\Exception $ex) {
            error_log("TwoCheckoutController::saveAllProducts()\n" . $ex->getMessage());
            return back()->with(['message' => $ex->getMessage(), 'type' => 'error']);
        }
    }

    public static function getSubscriptionDaysLeft()
    {
        // $plan = PaymentPlans::find($request->plan);
        $user = Auth::user();
        $settings = Setting::first();
        // $gateway = Gateways::where("code", self::GATEWAY_CODE)->first();
        // if ($gateway == null) {
        //     abort(404);
        // }

        // $currency = Currency::where('id', $gateway->currency)->first()->code;
        $activeSub = SubscriptionsModel::where([['stripe_status', '=', 'active'], ['user_id', '=', $user->id]])->first();
        if ($activeSub->stripe_status == 'active') {
            return Carbon::now()->diffInDays(Carbon::createFromDate($activeSub->updated_at)->addMonth());
        } else {
            error_log($activeSub->trial_ends_at);
            return Carbon::now()->diffInDays(Carbon::parse($sub->trial_ends_at));
        }

        // return $activeSub->current_period_end;
    }

    public static function getSubscriptionRenewDate()
    {
        // $plan = PaymentPlans::find($request->plan);
        $user = Auth::user();
        $settings = Setting::first();

        $gateway = Gateways::where("code", self::GATEWAY_CODE)->first();
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

        $gateway = Gateways::where("code", self::GATEWAY_CODE)->first();
        if ($gateway == null) {
            abort(404);
        }

        $currency = Currency::where('id', $gateway->currency)->first()->code;
        $activeSub = SubscriptionsModel::where([['stripe_status', '=', 'active'], ['user_id', '=', $user->id]])->first();

        if($activeSub != null){
            if ($activeSub['stripe_status'] == 'active'){
                return true;
            } else {
                $activeSub->stripe_status = 'cancelled';
                $activeSub->save();
                return false;
            }
        }
        return false;
    }

    public static function checkIfTrial()
    {
        // $plan = PaymentPlans::find($request->plan);
        $user = Auth::user();
        $settings = Setting::first();

        $gateway = Gateways::where("code", self::GATEWAY_CODE)->first();
        if ($gateway == null) {
            abort(404);
        }

        $currency = Currency::where('id', $gateway->currency)->first()->code;

        $sub = $user->subscriptions()->where('stripe_status', 'active')->orWhere('stripe_status', 'trialing')->first();
        if ($sub != null) {
            if ($sub->paid_with == 'stripe') {
                // $activeSub = $sub->asStripeSubscription();
                // return $activeSub->onTrial();
                return $user->subscription($sub->name)->onTrial();
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
                "gateway_code" => self::GATEWAY_CODE,
                "status" => 'check'
            ])->get();

            if ($history != null) {

                $client = self::getRequestHeader();
                foreach ($history as $record) {
                    
                    // check record current status from gateway
                    $lookingFor = $record->old_product_id;

                    // if active disable it
                    if ($lookingFor != 'undefined') {
                        try {
                            // delete old product
                            $response = $client->delete('rest/6.0/products/'.$lookingFor);
            
                            // check the response status code
                            if ($response->getStatusCode() == 201) 
                                Log::error("2Checkout product disabled ".$lookingFor);
                        } catch (Exception $e) {
                            Log::error("2Checkout product disable error : ".$e->getMessage());
                        }
                    }

                    // search subscriptions for record
                    $subs = SubscriptionsModel::where([
                        'stripe_status' => 'active',
                        'stripe_price'  => $lookingFor,
                        'paid_with' => self::GATEWAY_CODE
                    ])->get();
        
                    if ($subs != null) {
                        foreach ($subs as $sub) {

                            // cancel subscription order from gateway
                
                            try {
                                $response = $client->delete("rest/6.0/subscriptions/".$sub->stripe_id, [
                                    'json' => array (
                                        "ChurnReasonOther" => "New plan created by admin."
                                    )
                                ]);
                            } catch (Exception $e) {
                                Log::error("2Checkout Subscription disable error : ".$e->getMessage());
                            }                    
        
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
            error_log("TwoCheckoutController::updateUserData(): " . $th->getMessage());
            return ["result" => Str::before($th->getMessage(), ':')];
            // return Str::before($th->getMessage(),':');
        }
    }

    public static function cancelSubscribedPlan($planId, $subsId)
    {
        try {
            $user = Auth::user();
            $settings = Setting::first();
            $subscription = SubscriptionsModel::find($subsId);
            $gateway = Gateways::where("code", self::GATEWAY_CODE)->first();
            if ($gateway == null) {
                abort(404);
            }

            $currency = Currency::where('id', $gateway->currency)->first()->code;
            // $user->subscription($planId)->cancelNow();
            // $user->save();
            if ($subscription->stripe_status == 'active' && $subscription->stripe_id != null) {

                $client = self::getRequestHeader();
                $payload = array (
                    "ChurnReasonOther" => "Cancel this plan."
                );
                
                try {
                    $response = $client->delete("rest/6.0/subscriptions/".$subscription->stripe_id, [
                        'json' => $payload
                    ]);
                    if ($response->getStatusCode() == 200) {
                        $subscription->stripe_status = 'cancelled';
                        $subscription->ends_at = \Carbon\Carbon::now();
                        $subscription->save();      
                    }
                } catch (Exception $e) {
                    Log::info($e->getMessage());
                    return back()->with(['message' => 'Your subscription is not cancelled. Please contact support team', 'type' => 'success']);
                }
            }
            return true;
        } catch (\Exception $th) {
            error_log("\n------------------------\nTwoCheckoutController::cancelSubscribedPlan(): " . $th->getMessage() . "\n------------------------\n");
            // return Str::before($th->getMessage(),':');
            return false;
        }
    }

    function ArrayExpand($array){
        $retval = "";
        if (empty($array))
            return 0;
        foreach($array as $i => $value){

            if(is_array($value)){
                $retval .= ArrayExpand($value);
            }
            else{
                if (is_null($value) || $value == '')
                    $retval = 0;
                else {
                    $size = strlen($value);
                    $retval .= $size.$value;
                } 
            }
        }
        return $retval;
    }

    function hmac ($key, $data){
        $b = 64; // byte length for md5
        if (strlen($key) > $b) {
            $key = pack("H*",md5($key));
        }
        $key = str_pad($key, $b, chr(0x00));
        $ipad = str_pad('', $b, chr(0x36));
        $opad = str_pad('', $b, chr(0x5c));
        $k_ipad = $key ^ $ipad ;
        $k_opad = $key ^ $opad;
        return md5($k_opad . pack("H*",md5($k_ipad . $data)));
    }

    function verifyIncomingJson(Request $request) {

        $productDetails = $request->all();
        
        $gateway = Gateways::where("code", self::GATEWAY_CODE)->first();
        if ($gateway == null) {
            abort(404);
        }

        $secret_key = $gateway->live_client_secret;
        
        $result = '';
        foreach ($productDetails as $key => $val) {
            if ($key == 'HASH')
                continue;
            $result .= self::ArrayExpand((array)$val);
        }

        //*********Calculated HMAC_MD5 signature:*********
        $hash = self::hmac($secret_key, $result);
        
        if ($hash === $productDetails['HASH'])
            return $productDetails;
        else
            Log::error('(Webhooks) TwoCheckoutController::verifyIncomingJson() -> Invalid signature : '. $result);
        
        return null;
    }


    public function handleWebhook(Request $request) {

        if ($request->isMethod('get')) {
            return response()->json(['success' => true]);
        }

        $verified = self::verifyIncomingJson($request);
        
        if($verified != null){

            if (!$verified['MESSAGE_TYPE'])
                return response()->json(['success' => true]);

            // Retrieve the JSON payload
            $payload = $verified;

            // Fire the event with the payload
            event(new TwoCheckoutWebhookEvent($payload));
            // event(new TwoCheckoutWebhookEvent($payload['ORDERSTATUS']));
        
            return response()->json(['success' => true]);
        
        }else{
            // Incoming json is NOT verified
            abort(404);
        }

    }


    // public static function createWebhook(){

    //     try{

    //         // $user = Auth::user();

    //         $gateway = Gateways::where("code", self::GATEWAY_CODE)->first();
    //         if ($gateway == null) {
    //             abort(404);
    //         }

    //         $key = null;

    //         if ($gateway->mode == 'sandbox') {
    //             $key = $gateway->sandbox_client_secret;
    //         } else {
    //             $key = $gateway->live_client_secret;
    //         }

    //         $stripe = new \Stripe\StripeClient($key);

    //         $webhooks = $stripe->webhookEndpoints->all();

    //         if(count($webhooks['data']) > 0){
    //             // There is/are webhook(s) defined. Remove existing.
    //             foreach ($webhooks['data'] as $hook) {
    //                 $tmp = json_decode($stripe->webhookEndpoints->delete($hook->id,[]));
    //                 if(isset($tmp->deleted)){
    //                     if($tmp->deleted == false){
    //                         Log::error('Webhook '.$hook->id.' could not deleted.');
    //                     }
    //                 }else{
    //                     Log::error('Webhook '.$hook->id.' could not deleted.');
    //                 }
    //             }
    //         }

    //         // Create new webhook

    //         $url = url('/').'/webhooks/stripe';

    //         $events = [
    //             'invoice.paid',                     // A payment is made on a subscription.
    //             'customer.subscription.deleted'     // A subscription is cancelled.
    //         ];

    //         $response = $stripe->webhookEndpoints->create([
    //             'url' => $url,
    //             'enabled_events' => $events,
    //         ]);

    //         $gateway->webhook_id = $response->id;
    //         $gateway->webhook_secret = $response->secret;
    //         $gateway->save();

    //     } catch (AuthenticationException $th) {
    //         error_log("TwoCheckoutController::createWebhook(): ".$th->getMessage());
    //         return back()->with(['message' => "2Checkout Authentication Error. Invalid API Key provided.", 'type' => 'error']);
    //     } catch (InvalidArgumentException $th) {
    //         error_log("TwoCheckoutController::createWebhook(): ".$th->getMessage());
    //         return back()->with(['message' => "You must provide 2Checkout API Key.", 'type' => 'error']);
    //     } catch (\Exception $th) {
    //         error_log("TwoCheckoutController::createWebhook(): ".$th->getMessage());
    //         return back()->with(['message' => "2Checkout Error : ".$th->getMessage(), 'type' => 'error']);
    //     }
    // }
}
