<?php

namespace App\Http\Controllers\Gateways;

use App\Http\Controllers\Controller;
use App\Models\Gateways;
use App\Models\PaymentPlans;
use App\Models\Setting;
use App\Models\UserOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class WalletmaxpayController extends Controller
{
    public static function getConfig($planId)
    {
        $gateway = Gateways::where("code", "walletmaxpay")->first();
        if ($gateway == null) {
            abort(404);
        }

        $user = auth()->user();
        $plan = PaymentPlans::findOrFail($planId);

        if ($gateway->mode == 'sandbox') {
            $config = [
                'api'    => $gateway->sandbox_client_id,
                'secret'    => $gateway->sandbox_client_secret,
                'client'    => $gateway->sandbox_app_id
            ];
        } else {
            $config = [
                'api'    => $gateway->live_client_id,
                'secret'    => $gateway->live_client_secret,
                'client'    => $gateway->live_app_id
            ];
        }

        $uuid = Str::uuid()->toString();
        $config += [
            'amount' => $plan->price,
            'cus_name' => $user->name,
            'cus_email' => $user->email,
            'success_url' => route('dashboard.user.payment.walletmaxpay.success', ['token' => $uuid]),
            'cancel_url' => url()->previous(),
            'position' => config('app.url')
        ];

        Session::put('plan_id_'.auth()->id(), $planId, now()->addMinutes(10));
        Session::put('payment_uuid_'.auth()->id(), $uuid, now()->addMinutes(10));
        Session::save();

        return $config;
    }

    public static function prepaid($planId, $plan, $incomingException = null)
    {
        $config = self::getConfig($planId);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://pay.walletmaxpay.com/checkout.php',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $config,
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        
        echo $response;
        exit;
    }

    public function success(Request $request)
    {
        $uuid = Session::get('payment_uuid_'.auth()->id());
        $planId = Session::get('plan_id_'.auth()->id());
        if($uuid != $request->token || !$planId) {
            return redirect()->route('dashboard.index')->with(['message' => 'Invalid request', 'type' => 'error']);
        }

        $plan = PaymentPlans::findOrFail(Session::get('plan_id_'.auth()->id()));
        Session::forget('plan_id_'.auth()->id());
        Session::forget('payment_uuid_'.auth()->id());
        
        $user = Auth::user();
        $settings = Setting::first();

        $payment = new UserOrder();
        $payment->order_id = Str::random(12);
        $payment->plan_id = $plan->id;
        $payment->type = 'prepaid';
        $payment->user_id = $user->id;
        $payment->payment_type = 'WalletMaxPay';
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
}