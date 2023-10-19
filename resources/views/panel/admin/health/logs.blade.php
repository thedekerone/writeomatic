@extends('panel.layout.app')
@section('title', __('Site Health'))

@section('content')
    <div class="page-header">
        <div class="container-xl">
            <div class="row g-2 items-center">
                <div class="col">
					<a href="{{ LaravelLocalization::localizeUrl( route('dashboard.admin.health.index') ) }}" class="page-pretitle flex items-center">
						<svg class="!me-2 rtl:-scale-x-100" width="8" height="10" viewBox="0 0 6 10" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
							<path d="M4.45536 9.45539C4.52679 9.45539 4.60714 9.41968 4.66071 9.36611L5.10714 8.91968C5.16071 8.86611 5.19643 8.78575 5.19643 8.71432C5.19643 8.64289 5.16071 8.56254 5.10714 8.50896L1.59821 5.00004L5.10714 1.49111C5.16071 1.43753 5.19643 1.35718 5.19643 1.28575C5.19643 1.20539 5.16071 1.13396 5.10714 1.08039L4.66071 0.633963C4.60714 0.580392 4.52679 0.544678 4.45536 0.544678C4.38393 0.544678 4.30357 0.580392 4.25 0.633963L0.0892856 4.79468C0.0357141 4.84825 0 4.92861 0 5.00004C0 5.07146 0.0357141 5.15182 0.0892856 5.20539L4.25 9.36611C4.30357 9.41968 4.38393 9.45539 4.45536 9.45539Z"/>
						</svg>
						{{__('Back to site health')}}
					</a>
                    <h2 class="page-title mb-2">
                        {{__('Logs')}}
                    </h2>
                </div>
            </div>
        </div>
    </div>
    <!-- Page body -->
    <div class="page-body">
        <div class="container-xl">
			@if(Auth::user()->type == 'admin')
                @if (env('APP_STATUS') != 'Demo') 
                <div class="">
                    <label class="mb-2" for="log" >{{__('You can copy the below info as simple text with Ctrl+C / Ctrl+V:')}}</label>
                    <textarea class="w-full form-control" name="log" id="log" cols="30" rows="20">@php echo PHP_EOL . '== ' . __('LOGS') . '==' . PHP_EOL;
                            $logFile = storage_path('logs/laravel.log');
                            if (file_exists($logFile)) {
                                $logContent = file_get_contents($logFile);
                                echo htmlentities($logContent);
                            } else {
                                echo __('No logged any data.');
                            }
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
                    <button class="btn mt-4 mb-8" id="clearLogButton">
                        <svg class="mr-2" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M4 7l16 0"></path>
                            <path d="M10 11l0 6"></path>
                            <path d="M14 11l0 6"></path>
                            <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"></path>
                            <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"></path>
                         </svg>
                        {{__('Clear Log File')}}
                    </button>
                </div>
                @endif

			@endif
        </div>
    </div>
@endsection
@section('script')
<script>
    document.querySelector('textarea').addEventListener('click', function() {
        this.select();
    });

    var clearLogButton = document.getElementById('clearLogButton');

    clearLogButton.addEventListener('click', function() {
        var confirmResult = confirm(@json(__('Are you sure you want to clear the log?')));

        if (confirmResult) {
            // AJAX request to delete the log file
            var xhr = new XMLHttpRequest();
            xhr.open('GET', '/clear-log', true);

            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        // Log file successfully deleted, reload the page
                        location.reload();
                    } else {
                        // Error occurred while deleting the log file
                        alert(@json(__('An error occurred while clearing the log.')));
                    }
                }
            };

            xhr.send();
        }
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
