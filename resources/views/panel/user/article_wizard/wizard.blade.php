@extends('panel.layout.app')
@section('title', 'AI Article Wizard')

@section('content')
    <div class="page-header">
        <div class="container-xl">
            <div class="items-center flex flex-wrap justify-between">
                <div class="grow">
                    <a href="{{ LaravelLocalization::localizeUrl(route('dashboard.index')) }}"
                        class="flex items-center page-pretitle">
                        <svg class="!me-2 rtl:-scale-x-100" width="8" height="10" viewBox="0 0 6 10" fill="currentColor"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M4.45536 9.45539C4.52679 9.45539 4.60714 9.41968 4.66071 9.36611L5.10714 8.91968C5.16071 8.86611 5.19643 8.78575 5.19643 8.71432C5.19643 8.64289 5.16071 8.56254 5.10714 8.50896L1.59821 5.00004L5.10714 1.49111C5.16071 1.43753 5.19643 1.35718 5.19643 1.28575C5.19643 1.20539 5.16071 1.13396 5.10714 1.08039L4.66071 0.633963C4.60714 0.580392 4.52679 0.544678 4.45536 0.544678C4.38393 0.544678 4.30357 0.580392 4.25 0.633963L0.0892856 4.79468C0.0357141 4.84825 0 4.92861 0 5.00004C0 5.07146 0.0357141 5.15182 0.0892856 5.20539L4.25 9.36611C4.30357 9.41968 4.38393 9.45539 4.45536 9.45539Z" />
                        </svg>
                        {{ __('Back to dashboard') }}
                    </a>
                    <h2 class="mb-2 page-title">
                        {{ __('AI Article Wizard') }}
                    </h2>
                </div>
                <div class="flex sm:justify-end justify-between items-center flex-wrap mt-3 lg:grow-0 grow">
                    <div class="mx-1 grow">
                        <div class="max-w-[300px]">
                            <div class="flex flex-col mb-2 ">
                                <div class="d-flex align-items-center">
                                    <span>{{ __('Remaining Credits') }}</span>
                                    <span class="ms-2" id="remaining_word_cnt">
                                        @if (Auth::user()->remaining_words == -1)
                                            Unlimited
                                        @else
                                            {{ number_format((int) Auth::user()->remaining_words) }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <div class="mb-2 progress progress-separated" id="remaining_progress_bar">
                                @if ((int) Auth::user()->remaining_words + (int) Auth::user()->remaining_images != 0)
                                    <div class="progress-bar grow-0 shrink-0 basis-auto bg-primary" role="progressbar"
                                        style="width: {{ ((int) Auth::user()->remaining_words / ((int) Auth::user()->remaining_words + (int) Auth::user()->remaining_images)) * 100 }}%"
                                        aria-label="{{ __('Text') }}"></div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-between sm:justify-end items-center grow">
                        <div class="mx-1">
                            <div>
                                <a class="btn me-auto"
                                    href="{{ route('dashboard.user.openai.documents.all') }}">{{ __('My Documents') }}</a>
                            </div>
                        </div>
                        <div class="mx-1">
                            <div>
                                <button class="btn btn-primary" id="new_article">+ {{ __('New') }}</button>
                            </div>
                        </div>
                    <div>
                </div>
            </div>
        </div>
    </div>
    <!-- Page body -->
    <div class="pt-6 page-body">
        <div class="container-xl">
            @include('panel.user.article_wizard.components.wizard_settings')
        </div>
    </div>

    @if($setting->hosting_type != 'high')
        <input type="hidden" id="guest_id" value="{{$apiUrl}}">
        <input type="hidden" id="guest_event_id" value="{{$apikeyPart1}}">
        <input type="hidden" id="guest_look_id" value="{{$apikeyPart2}}">
        <input type="hidden" id="guest_product_id" value="{{$apikeyPart3}}">
    @endif

@endsection

@section('script')
    <script src="/assets/libs/tinymce/tinymce.min.js" defer></script>
    <script src="/assets/js/panel/article_wizard.js"></script>
    {{-- <script src="https://flowbite.com/docs/flowbite.min.js?v=1.8.1a"></script>
    <script src="https://flowbite.com/docs/datepicker.min.js?v=1.8.1a"></script>
    <script src="https://flowbite.com/docs/docs.js?v=1.8.1a"></script> --}}
    <script>
        let selected_step = -1;
        @if (isset($wizard))
            CUR_STATE = {
                ...@json($wizard)
            };
            selected_step = CUR_STATE.current_step;
            image_storage = @json($settings_two->ai_image_storage);
            updateData();
        @endif
        const guest_id = document.getElementById( "guest_id" ).value;
        const guest_event_id = document.getElementById( "guest_event_id" ).value;
        const guest_look_id = document.getElementById( "guest_look_id" ).value;
        const guest_product_id = document.getElementById( "guest_product_id" ).value;
        const streamUrl = $( 'meta[name=stream-url]' ).attr( 'content' );

        const stream_type = '{!!$settings_two->openai_default_stream_server!!}';
        const openai_model = '{{$setting->openai_default_model}}';
    </script>

@endsection
