@extends('panel.layout.app')
@section('title', __('Site Health'))

@section('content')
    <div class="page-header">
        <div class="container-xl">
            <div class="row g-2 items-center">
                <div class="col">
					<a href="{{ LaravelLocalization::localizeUrl( route('dashboard.index') ) }}" class="page-pretitle flex items-center">
						<svg class="!me-2 rtl:-scale-x-100" width="8" height="10" viewBox="0 0 6 10" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
							<path d="M4.45536 9.45539C4.52679 9.45539 4.60714 9.41968 4.66071 9.36611L5.10714 8.91968C5.16071 8.86611 5.19643 8.78575 5.19643 8.71432C5.19643 8.64289 5.16071 8.56254 5.10714 8.50896L1.59821 5.00004L5.10714 1.49111C5.16071 1.43753 5.19643 1.35718 5.19643 1.28575C5.19643 1.20539 5.16071 1.13396 5.10714 1.08039L4.66071 0.633963C4.60714 0.580392 4.52679 0.544678 4.45536 0.544678C4.38393 0.544678 4.30357 0.580392 4.25 0.633963L0.0892856 4.79468C0.0357141 4.84825 0 4.92861 0 5.00004C0 5.07146 0.0357141 5.15182 0.0892856 5.20539L4.25 9.36611C4.30357 9.41968 4.38393 9.45539 4.45536 9.45539Z"/>
						</svg>
						{{__('Back to dashboard')}}
					</a>
                    <h2 class="page-title mb-2">
                        {{__('Site Health')}}
                    </h2>
                </div>
            </div>
        </div>
    </div>
    <!-- Page body -->
    <div class="page-body">
        <div class="container-xl">
			@if(Auth::user()->type == 'admin')
            @php
                function backgroundColor($status) {
                    return match ($status) {
                        Spatie\Health\Enums\Status::ok()->value => 'bg-green-500',
                        Spatie\Health\Enums\Status::warning()->value => 'bg-yellow-500',
                        Spatie\Health\Enums\Status::skipped()->value => 'bg-blue-500',
                        Spatie\Health\Enums\Status::failed()->value, Spatie\Health\Enums\Status::crashed()->value => 'bg-red-500',
                        default => 'bg-gray-500'
                    };
                }

                function iconColor($status)
                {
                    return match ($status) {
                        Spatie\Health\Enums\Status::ok()->value => 'text-green-500',
                        Spatie\Health\Enums\Status::warning()->value => 'text-yellow-500',
                        Spatie\Health\Enums\Status::skipped()->value => 'text-blue-500',
                        Spatie\Health\Enums\Status::failed()->value, Spatie\Health\Enums\Status::crashed()->value => 'text-red-500',
                        default => 'text-gray-500'
                    };
                }

                function icon($status)
                {
                    return match ($status) {
                        Spatie\Health\Enums\Status::ok()->value => 'check-circle',
                        Spatie\Health\Enums\Status::warning()->value => 'exclamation-circle',
                        Spatie\Health\Enums\Status::skipped()->value => 'arrow-circle-right',
                        Spatie\Health\Enums\Status::failed()->value, Spatie\Health\Enums\Status::crashed()->value => 'x-circle',
                        default => ''
                    };
                }
            @endphp

                @if (env('APP_STATUS') != 'Demo') 
                <div class="mb-3">
                    <button class="btn btn-lg" id="cleanCacheBtn">
                        <svg class="mr-2" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M4 7l16 0"></path>
                            <path d="M10 11l0 6"></path>
                            <path d="M14 11l0 6"></path>
                            <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"></path>
                            <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"></path>
                        </svg>
                        {{__('Clean Up Cache')}}
                    </button>  
                </div>
                @endif

                @if (count($checkResults?->storedCheckResults ?? []))
                    <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($checkResults->storedCheckResults as $result)
                            <div class="flex items-start p-6 space-x-2 rtl:space-x-reverse overflow-hidden text-opacity-0 transform bg-[#fff] rounded-xl shadow-[0_7px_25px_rgba(0,0,0,0.07)] dark:bg-white-500 dark:bg-opacity-5">
                                <div class="flex justify-center items-center rounded-full p-2.5 {{ backgroundColor($result->status) }} bg-opacity-25">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 {{ iconColor($result->status) }}" viewBox="0 0 20 20" fill="currentColor">
                                        @if(icon($result->status) == 'check-circle')
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        @elseif(icon($result->status) == 'exclamation-circle')
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        @elseif(icon($result->status) == 'arrow-circle-right')
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd" />
                                        @elseif(icon($result->status) == 'x-circle')
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        @else
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                        @endif
                                    </svg>
                                </div>

                                <div>
                                    <dd class="-mt-1 font-semibold text-gray-800 md:text-xl dark:text-gray-200">
                                        {{ $result->label }}
                                    </dd>
                                    <dt class="mt-0 text-sm font-medium text-gray-600 dark:text-gray-400">
                                        @if (!empty($result->notificationMessage))
                                            @if ( $result->notificationMessage === 'Crashed')
                                                {{ __('Failed to calculate. Your server configuration is preventing this feature from being calculated.') }}
                                            @else
                                                {{ str_replace( 'The debug mode was expected to be `false`, but actually was `true`', __('Debug mode is enabled. If this is your production site, it is recommended to disable it.'), $result->notificationMessage) }}
                                            @endif
                                        @else
                                            {{ $result->shortSummary }}
                                        @endif
                                    </dt>
                                </div>
                            </div>
                        @endforeach
                    </dl>
                @endif

                @if (env('APP_STATUS') != 'Demo') 
                <div class="mt-10">
                    <h3>{{__('Server Details')}}</h3>
                    <label class="mb-2" for="log" >{{__('You can copy the below info as simple text with Ctrl+C / Ctrl+V:')}}</label>
                    <textarea class="w-full form-control" name="log" id="log" cols="30" rows="13">@php echo '== ' . __('GENERAL') . '==' . PHP_EOL;
                            echo __('Operating System') . ': ' . php_uname() . PHP_EOL;
                            echo __('PHP Version') .': '. phpversion() . PHP_EOL;
                            echo __('Laravel Version') .': '. DB::connection()->getPdo()->getAttribute(PDO::ATTR_SERVER_VERSION) . PHP_EOL;
                            echo __('Mermory Limit') .': '. ini_get('memory_limit') . PHP_EOL;
                            echo __('Max Input Vars') .': '. ini_get('max_input_vars') . PHP_EOL;
                            echo __('Post Max Size') .': '. ini_get('post_max_size') . PHP_EOL . PHP_EOL;
                            echo '== ' . __('ENVIRONMENT') . '==' . PHP_EOL;
                            echo __('APP_STATUS') .': '. env('APP_STATUS') . PHP_EOL;
                            echo __('APP_DEBUG') .': '. env('APP_DEBUG') . PHP_EOL;
                            echo __('APP_LOG_LEVEL') .': '. env('APP_LOG_LEVEL') . PHP_EOL;
                            echo __('APP_ENV') .': '. env('APP_ENV') . PHP_EOL;
                            // echo PHP_EOL . '== ' . __('LOGS') . '==' . PHP_EOL;
                            // $logFile = storage_path('logs/laravel.log');
                            // if (file_exists($logFile)) {
                            //     $logContent = file_get_contents($logFile);
                            //     echo htmlentities($logContent);
                            // } else {
                            //     echo __('No logged any data.');
                            // }
                        @endphp
                    </textarea>

                    <button class="btn mr-2" id="copyButton">
                        <svg class="mr-2" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M8 8m0 2a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2z"></path>
                            <path d="M16 8v-2a2 2 0 0 0 -2 -2h-8a2 2 0 0 0 -2 2v8a2 2 0 0 0 2 2h2"></path>
                         </svg>
                        {{__('Copy')}}
                    </button>
                    <a class="btn mt-4 mb-8" href="{{route('dashboard.admin.health.logs')}}">
                        <svg  class="mr-2" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 512 512" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <title>log</title>
                            <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <g id="log-white" fill="#515151" transform="translate(85.572501, 42.666667)">
                                    <path d="M236.349632,7.10542736e-15 L1.68296533,7.10542736e-15 L1.68296533,234.666667 L44.349632,234.666667 L44.349632,192 L44.349632,169.6 L44.349632,42.6666667 L218.642965,42.6666667 L300.349632,124.373333 L300.349632,169.6 L300.349632,192 L300.349632,234.666667 L343.016299,234.666667 L343.016299,106.666667 L236.349632,7.10542736e-15 L236.349632,7.10542736e-15 Z M4.26325641e-14,405.333333 L4.26325641e-14,277.360521 L28.8096875,277.360521 L28.8096875,382.755208 L83.81,382.755208 L83.81,405.333333 L4.26325641e-14,405.333333 Z M153.17,275.102708 C173.279583,275.102708 188.692917,281.484792 199.41,294.248958 C209.705625,306.47125 214.853437,322.185625 214.853437,341.392083 C214.853437,362.404792 208.772396,379.112604 196.610312,391.515521 C186.134062,402.232604 171.653958,407.591146 153.17,407.591146 C133.060417,407.591146 117.647083,401.209062 106.93,388.444896 C96.634375,376.222604 91.4865625,360.267396 91.4865625,340.579271 C91.4865625,319.988021 97.5676042,303.490937 109.729687,291.088021 C120.266146,280.431146 134.74625,275.102708 153.17,275.102708 Z M153.079687,297.680833 C142.663646,297.680833 134.625833,302.015833 128.96625,310.685833 C123.848542,318.512917 121.289687,328.567708 121.289687,340.850208 C121.289687,355.059375 124.330208,366.0775 130.41125,373.904583 C136.131042,381.310208 143.717292,385.013021 153.17,385.013021 C163.525833,385.013021 171.59375,380.647917 177.37375,371.917708 C182.491458,364.211042 185.050312,354.035833 185.050312,341.392083 C185.050312,327.483958 182.009792,316.616354 175.92875,308.789271 C170.208958,301.383646 162.592604,297.680833 153.079687,297.680833 Z M343.91,333.715521 L343.91,399.011458 C336.564583,401.48 331.386667,403.105625 328.37625,403.888333 C319.043958,406.356875 309.019271,407.591146 298.302187,407.591146 C277.229271,407.591146 261.18375,402.292812 250.165625,391.696146 C237.943333,380.015729 231.832187,363.729375 231.832187,342.837083 C231.832187,318.813958 239.418437,300.69125 254.590937,288.468958 C265.609062,279.558125 280.480521,275.102708 299.205312,275.102708 C315.220729,275.102708 330.122292,278.022812 343.91,283.863021 L334.065937,306.350833 C327.563437,303.099583 321.87375,300.826719 316.996875,299.53224 C312.12,298.23776 306.761458,297.590521 300.92125,297.590521 C286.952917,297.590521 276.657292,302.13625 270.034375,311.227708 C264.435,318.934375 261.635312,329.079479 261.635312,341.663021 C261.635312,356.775312 265.849896,368.154687 274.279062,375.801146 C281.022396,381.942396 289.391354,385.013021 299.385937,385.013021 C305.226146,385.013021 310.765312,384.019583 316.003437,382.032708 L316.003437,356.293646 L293.967187,356.293646 L293.967187,333.715521 L343.91,333.715521 Z" id="XLS"></path>
                                </g>
                            </g>
                        </svg>
                        {{__('View Log File')}}
                    </a>
                </div>
                @endif

			@endif
        </div>
    </div>
@endsection
@section('script')
<script>
    
    var cleanCacheBtn = document.getElementById('cleanCacheBtn');
    cleanCacheBtn.addEventListener('click', function() {
        var confirmResultCache = confirm(@json(__('Are you sure you want to clear the cache?')));
        if (confirmResultCache) {
            // AJAX request to delete the log file
            var xhr = new XMLHttpRequest();
            xhr.open('GET', '/dashboard/admin/health/cache-clear', true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        // Log file successfully deleted, reload the page
                        location.reload();
                    } else {
                        // Error occurred while deleting the log file
                        alert(@json(__('An error occurred while clearing the cache.')));
                    }
                }
            };
            xhr.send();
        }
    });
</script>
<script>
    document.querySelector('textarea').addEventListener('click', function() {
        this.select();
    });

    var copyButton = document.getElementById('copyButton');
    var logTextarea = document.getElementById('log');

    copyButton.addEventListener('click', function() {
        logTextarea.select();
        document.execCommand('copy');
        toastr.success( @json(__('Copied')) );
    });
</script>
@endsection
