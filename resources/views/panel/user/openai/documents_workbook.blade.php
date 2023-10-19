@extends('panel.layout.app')
@section('title', 'Workbook')

@section('content')
    <div class="page-header">
        <div class="container-xl">
            <div class="row g-2 items-center">
                <div class="col">
                    <div class="page-pretitle">
                        {{ __('Edit your generations.') }}
                    </div>
                    <h2 class="page-title mb-2">
                        {{ __('Workbook') }}
                    </h2>
                </div>
            </div>
        </div>
    </div>
    <!-- Page body -->
    <div class="page-body pt-6 max-md:pt-3">
        <div class="container-xl">
            <div class="row">
                <div class="col-12"></div>
                <div class="col-lg-8 mx-auto">
                    @if ($workbook->generator->type == 'code')
                        <div>
                        @else
                            <div
                                class="border-solid border-t border-r-0 border-b-0 border-l-0 border-[var(--tblr-border-color)] pt-[30px] mt-[15px] max-lg:mt-0 max-lg:pt-0 max-lg:border-t-0">
                    @endif
                    @include('panel.user.openai.documents_workbook_textarea')
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="popup" id="schedule_popup">
        <div class="popup-body col-lg-4 col-md-4 col-sm-12">
            <form id="schedule_form">
                <div class="popup-header">
                    <h2>Schedular</h2>
                    <span id="close_popup" class="close-popup">&times;</span>
                </div>
                <div class="popup-content">
                    <div class="mb-3 col-xs-12 text-center">
                        <label class="form-label">{{__('Account:')}}</label>
                        <div class="image-selector">
                            @if(array_key_exists('WordPress', $integrations))
                                <input type="radio" id="img1" name="account" value="{{ $integrations['WordPress'] }}" hidden>
                                <label for="img1"><img src={{ asset('assets/img/wordpress.png') }} alt="WordPress"></label>
                            @endif
                            @if(array_key_exists('Twitter', $integrations))
                                <input type="radio" id="img2" name="account" value="{{ $integrations['Twitter'] }}" hidden>
                                <label for="img2"><img src={{ asset('assets/img/twitter.png') }} alt="Twitter"></label>
                            @endif
                        </div>
                    </div>
                    <div class="mb-3 col-xs-12 text-center">
                        <label class="form-label">{{__('Date/time:')}}</label>
                        <div id="datetimepicker"></div>
                        <label id="scheduled-time" class="form-label mt-3" style="display:none;">{{__('The document will be scheduled for ')}}<span id="datetimevalue"></span></label>
                    </div>
                </div>
                <div class="popup-footer">
                    <button id="schedule_confirm" class="btn btn-primary w-100 py-[0.75em] flex items-center group" type="button">
    					<span class="hidden group-[.lqd-form-submitting]:inline-flex">{{__('Please wait...')}}</span>
    					<span class="group-[.lqd-form-submitting]:hidden">{{__('Schedule')}}</span>
    				</button>
                </div>
            </form>
        </div>
    </div>
    <div class="popup" id="unsplash_images">
        <div class="popup-body col-lg-11 col-md-11 col-sm-12">
            <div class="popup-header">
                <h2>Unsplash Images</h2>
                <span id="close_unsplash_popup" class="close-popup">&times;</span>
            </div>
            <div class="popup-content">
                <div class="mb-3 col-xs-12 text-center">
                    <input type="text" id="searchInput" placeholder="Search Unsplash...">
                    <button onclick="searchUnsplash()">Search</button>
                    <div id="imageResults" class="image-grid"></div>
                    <button onclick="previousPage()">Previous</button>
                    <button onclick="nextPage()">Next</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="/assets/libs/marked.umd.js"></script>
    <script src="/assets/libs/tinymce/tinymce.min.js" defer></script>
    <script>
       const workbook = @json($workbook);
       const unsplashKey = @json($unsplashKey);
    </script>
    <script src="/assets/libs/flatpickr/flatpickr.js"></script>
    <script src="/assets/js/panel/workbook.js"></script>

    @if ($openai->type == 'code')
        <link rel="stylesheet" href="/assets/libs/prism/prism.css">
        <script src="/assets/libs/prism/prism.js"></script>
        <script>
            window.Prism = window.Prism || {};
            window.Prism.manual = true;
            document.addEventListener('DOMContentLoaded', (event) => {
                "use strict";

                const codeLang = document.querySelector('#code_lang');
                const codePre = document.querySelector('#code-pre');
                const codeOutput = codePre?.querySelector('#code-output');

                if (!codeOutput) return;

                codePre.classList.add(`language-${codeLang && codeLang.value !== '' ? codeLang.value : 'javascript'}`);

                // saving for copy
                window.codeRaw = codeOutput.innerText;

                Prism.highlightElement(codeOutput);
            });
        </script>
    @endif
@endsection

@push('css')
   <link rel="stylesheet" href="/assets/libs/flatpickr/flatpickr.min.css">
   <style>
    .image-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 16px;
}

.image-grid img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;
    cursor: pointer;
    transition: transform 0.3s ease-in-out;
}
.image-grid img:hover {
        transform: scale(1.05);
    }
        </style>
@endpush