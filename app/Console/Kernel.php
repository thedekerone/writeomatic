<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\PaymentPlans;
use App\Models\Integration;
use App\Models\UserOpenai;
use App\Models\ScheduledDocuments;
use App\Models\YokassaSubscriptions as YokassaSubscriptionsModel;
use App\Http\Controllers\Gateways\YokassaController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http; 

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command(\Spatie\Health\Commands\RunHealthChecksCommand::class)->everyMinute();
        $schedule->call(function () {
            $activeSub_yokassa = YokassaSubscriptionsModel::where(['subscription_status', '=', 'active'])->get();
            foreach($activeSub_yokassa as $activeSub) {
                $data_now = Carbon::now();
                $data_end_sub = $activeSub->next_pay_at;
                if($data_now->gt($data_end_sub)) $result = YokassaController::handleSubscribePay($activeSub->id);
            }
        })->daily();

        $schedule->call(function () {
            $scheduled_documents = ScheduledDocuments::where('is_executed', false)->get();
            $date_now = Carbon::now();
            foreach ($scheduled_documents as $doc) {
                if($date_now->gt($doc->run_at)) {
                    $integration = Integration::find($doc->account_id);
                    $document = UserOpenai::find($doc->document_id);
                    if($integration && $document) {
                       if($integration->name == "WordPress") {
                           $data = [
                                'title' => $document->title,
                                'content' => $document->response,
                                'status' => 'publish'
                            ];
                            $password = decrypt($integration->password);
                            $response = Http::withBasicAuth($integration->username, $password)->post($integration->url, $data);
                            if($response->successful()) {
                                $doc->is_executed = true;
                                $doc->save();
                            }
                        }
                    }
                   
                }
            }
        })->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
