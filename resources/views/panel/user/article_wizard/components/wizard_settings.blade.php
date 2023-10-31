<div class="row row-cards">
    {{-- <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add_modal">
        Launch demo modal
      </button> --}}
    <div class="col-12 col-sm-6 col-lg-5 lg:pr-14" id="settings">

        <div class="card-body mb-10" id="current_step">
            <div class="row steps mb-[10px]">

                <div class="py-2 step cursor-default hover:bg-[#eeeeee] w-1/4 p-0 flex flex-wrap justify-center hidden">
                    <p class="block w-[21px] h-[21px] m-0 rounded-[5px] bg-[#330583] text-center text-white">
                        1</p>
                    <p class="mx-2 block m-0">{{ __('Topic') }}</p>
                </div>
                <div class="py-2 step cursor-default hover:bg-[#eeeeee] w-1/4 p-0 hidden">
                    <p class="text-center m-0">1</p>
                </div>

                <div class="py-2 step cursor-default hover:bg-[#eeeeee] w-1/4 p-0 flex flex-wrap justify-center hidden">
                    <p class="block w-[21px] h-[21px] m-0 rounded-[5px] bg-[#330583] text-center text-white">
                        2</p>
                    <p class="mx-2 block m-0">{{ __('Title') }}</p>
                </div>
                <div class="py-2 step cursor-default hover:bg-[#eeeeee] w-1/4 p-0 hidden">
                    <p class="text-center m-0">2</p>
                </div>

                <div class="py-2 step cursor-default hover:bg-[#eeeeee] w-1/4 p-0 flex flex-wrap justify-center hidden">
                    <p class="block w-[21px] h-[21px] m-0 rounded-[5px] bg-[#330583] text-center text-white">
                        3</p>
                    <p class="mx-2 block m-0">{{ __('Outline') }}</p>
                </div>
                <div class="py-2 step cursor-default hover:bg-[#eeeeee] w-1/4 p-0 hidden">
                    <p class="text-center m-0">3</p>
                </div>

                <div class="py-2 step cursor-default hover:bg-[#eeeeee] w-1/4 p-0 flex flex-wrap justify-center hidden">
                    <p class="block w-[21px] h-[21px] m-0 rounded-[5px] bg-[#330583] text-center text-white">
                        4</p>
                    <p class="mx-2 block m-0">{{ __('Image') }}</p>
                </div>
                <div class="py-2 step cursor-default hover:bg-[#eeeeee] w-1/4 p-0 hidden">
                    <p class="text-center m-0">4</p>
                </div>

            </div>
            <div class="relative">
                <div id="progress_bar"
                    class="absolute h-2 bg-gradient-to-br from-[#82E2F4] to-[#8A8AED] rounded-full w-1/4">
                </div>
                <div class="h-2 w-full bg-[#e7e9f4] rounded-full"></div>
            </div>
        </div>
        <form class="row" id="article_wizard_setting_form">

            <div class="mb-4 col-xs-12 topic hidden">
                <div class="row flex justify-between inline-block">
                    <label class="form-label">{{ __('Topic') }} <p class="float-right text-[#C5C7CB]"></p>
                    </label>
                </div>
                <textarea class="form-control" placeholder="{{ __('What is this article about?') }}" id='txtforkeyword'
                    name='txtforkeyword' rows="5"></textarea>
            </div>

            <div class="mb-4 col-xs-12 topic hidden">
                <div class="row flex justify-between inline-block">
                    <label class="form-label">{{ __('Title Topic(Optional)') }} </label>
                </div>
                <textarea class="form-control" placeholder="{{ __('Explain your idea') }}" id='txtfortitle' name='txtfortitle'
                    rows="5"></textarea>
            </div>

            <div class="mb-4 col-xs-12 topic hidden">
                <div class="row flex justify-between inline-block">
                    <label class="form-label">{{ __('Outline Topic(Optional)') }} </label>
                </div>
                <textarea class="form-control" placeholder="{{ __('Explain your idea') }}" id='txtforoutline' name='txtforoutline'
                    rows="5"></textarea>
            </div>

            <div class="mb-4 col-xs-12 topic hidden">
                <div class="row flex justify-between inline-block">
                    <label class="form-label">{{ __('Explain Your Image(Optional)') }}</label>
                </div>
                <textarea class="form-control" placeholder="{{ __('riding horse on mars') }}" id='txtforimage' name='txtforimage'
                    rows="5"></textarea>
            </div>

            <div class="setting hidden">
                <label class="form-label">{{ __('Number of Keywords') }}</label>
                <input type="number" class="form-control" name="number_of_keywords" id="number_of_keywords"
                    value="10" placeholder="{{ __('Number of keywords') }}" min="5" max="50">
            </div>
            <div class="setting hidden">
                <div class="mb-3 col-xs-12">
                    <label class="form-label">{{ __('Keywords') }}</label>
                    <input type="text" class="form-control" id="keywords" name="keywords"
                        placeholder="{{ __('Keywords') }}" readonly>
                </div>
                <div class="mb-3 col-xs-12">
                    <label class="form-label">{{ __('Number of Titles') }}</label>
                    <input type="number" class="form-control" name="number_of_titles" id="number_of_titles"
                        value="3" placeholder="{{ __('Number of titles') }}" min="3" max="15">
                </div>
                <div class="mb-3 col-xs-12">
                    <label class="form-label">{{ __('Maximum Title length') }}</label>
                    <input type="number" class="form-control" name="title_length" id="title_length" value="30"
                        placeholder="{{ __('Maximum Title length') }}" min="20" max="100">
                </div>
            </div>
            <div class="setting hidden">
                <div class="mb-3 col-xs-12">
                    <label class="form-label">{{ __('Keywords') }}</label>
                    <input type="text" class="form-control" id="keywords_outline" name="keywords"
                        placeholder="{{ __('Keywords') }}" readonly>
                </div>
                <div class="mb-3 col-xs-12">
                    <label class="form-label">{{ __('Number of Subtitles') }}</label>
                    <input type="number" class="form-control" name="number_of_outline_subtitles"
                        id="number_of_outline_subtitles" value="10"
                        placeholder="{{ __('Number of Subtitles') }}" min="5" max="20">
                </div>
                <div class="mb-3 col-xs-12">
                    <label class="form-label">{{ __('Number of Outlines') }}</label>
                    <input type="number" class="form-control" name="number_of_ " id="number_of_outlines"
                        value="3" placeholder="{{ __('Number of outlines') }}" min="3" max="5">
                </div>
            </div>
            <div class="setting hidden">
                <div class="mb-3 col-xs-12">
                    <label class="form-label">{{ __('Size (Optional)') }}</label>
                    <select type="text" class="form-select" name="size_of_images" id="size_of_images">
                        <option value="thumb">Very Small</option>
                        <option value="small">Small</option>
                        <option value="small_s3">Normal</option>
                        <option value="full">Big</option>
                        <option value="raw">Very Big</option>
                    </select>
                </div>
                <div class="mb-3 col-xs-12">
                    <label class="form-label">{{ __('Number of Images') }}</label>
                    <input type="number" class="form-control" name="number_of_images" id="number_of_images"
                        value="4" placeholder="{{ __('Number of images') }}" min="1" max="6">
                </div>
            </div>

            <div class="mb-3 col-xs-12">
                <button type="button"
                    class="outline-0 border-0 bg-white text-black w-100 py-[0.75em] flex items-center  mt-[0.5rem] cursor-default"
                    id="advanced_option">
                    <div class="flex flex-row w-full items-center justify-center">
                        <hr class="h-px bg-[#F4F4F4] border-0 dark:bg-gray-700 grow">
                        <label class="mx-[40px] grow-0">{{ __('Advanced Options') }} &#9013;</label>
                        <hr class="h-px bg-[#F4F4F4] border-0 dark:bg-gray-700 grow">
                    </div>
                </button>
            </div>

            <div id="advanced_setting" class="hidden">
                <div class="mb-3 col-xs-12 result_count hidden">
                    {{-- <label class="form-label">{{ __('Number of Keywords') }}</label>
						<input type="number" class="form-control" name="number_of_keywords" id="number_of_keywords"
							value="10" placeholder="{{ __('Number of keywords') }}" min="5" max="50"> --}}
                </div>
                <div class="mb-3 col-xs-12 result_count hidden">
                </div>
                <div class="mb-3 col-xs-12 result_count hidden">
                </div>
                <div class="mb-3 col-xs-12 result_count hidden">
                </div>
                <div class="mb-3 col-xs-12">
                    <label class="form-label">{{ __('Language') }}</label>
                    <select type="text" class="form-select" name="language" id="language">
                        @include('panel.user.openai.components.countries')
                    </select>
                </div>
                <div class="mb-3 col-xs-12">
                    <label class="form-label">{{ __('Blog Post Length') }}</label>
                    <input type="text" class="form-control" id="blog_post_length" name="blog_post_length"
                        placeholder="800">
                </div>
                <div class="mb-3 col-xs-12">
                    <label class="form-label">{{ __('Creativity') }}</label>
                    <select type="text" class="form-select" name="creativity" id="creativity" required>
                        <option value="0.25" {{ $setting->openai_default_creativity == 0.25 ? 'selected' : '' }}>
                            {{ __('Economic') }}</option>
                        <option value="0.5" {{ $setting->openai_default_creativity == 0.5 ? 'selected' : '' }}>
                            {{ __('Average') }}</option>
                        <option value="0.75" {{ $setting->openai_default_creativity == 0.75 ? 'selected' : '' }}>
                            {{ __('Good') }}</option>
                        <option value="1" {{ $setting->openai_default_creativity == 1 ? 'selected' : '' }}>
                            {{ __('Premium') }}</option>
                    </select>
                </div>
            </div>

            <div class="col-xs-12 mt-[10px]">
                <button id="generator_btn"
                    class="btn bg-[#F1EDFF] hover:bg-[#c1bDFF] text-black w-100 py-[0.75em] flex items-center group"
                    type="submit">
                    <span class="group-[.lqd-form-submitting]:inline-flex hidden">{{ __('Please wait...') }}</span>

                    <span
                        class="group-[.lqd-form-submitting]:hidden generate_title hidden">{{ __('Generate Keywords') }}</span>
                    <span
                        class="group-[.lqd-form-submitting]:hidden generate_title hidden">{{ __('Generate Title') }}</span>
                    <span
                        class="group-[.lqd-form-submitting]:hidden generate_title hidden">{{ __('Generate Outline') }}</span>
                    <span
                        class="group-[.lqd-form-submitting]:hidden generate_title hidden">{{ __('Generate Image') }}</span>
                </button>
                <button type="button"
                    class="btn bg-white hover:bg-[#c1bDFF] text-black w-100 py-[0.75em] flex items-center group hidden mt-[1.5rem]"
                    id="skip_image">
                    {{ __('Skip this step') }}
                </button>
            </div>

        </form>
    </div>

    <div class="sm:sticky sm:top-20 col-12 col-sm-6 col-lg-5 lg:pr-14 flex flex-col items-center max-h-[400px] hidden"
        id="final_settings">
        {{-- <div class="sticky top-20"> --}}
            <img src="/images/articlewizard/magicLogo.png" class="mt-[40px]" />
            <p class="font-bold mt-[40px] text-[27px] text-center" id="result_title">
                {{ __('Generating the article') }}...</p>
            <p class="font-bold mt-[40px] text-[27px] text-center hidden" id="result_success_title">
                {{ __('Successfully Generated') }}</p>
            <p class="font-bold mt-[40px] text-[27px] text-center hidden" id="result_abort_title">
                {{ __('Generating is aborted.') }}</p>
            <p class="text-[#272D3880] font-medium text-[15px] text-center max-w-[300px]">
                {{ __('You can edit your article in documents once it is generated.') }}
            </p>
        {{-- </div> --}}
    </div>

    <div class="relative col-12 col-sm-6 col-lg-7 lg:pl-16 lg:border-l lg:border-solid border-t-0 border-r-0 border-b-0 border-[var(--tblr-border-color)] [&_.tox-edit-area__iframe]:!bg-transparent hidden"
        id="wizard_area">
        <div class="card-body mb-10">
            <div
                class="row flex-nowrap mb-[10px] flex justify-between items-center p-[16px] border-[#f9f9f9] border-[1px] border-solid rounded-[10px]">
                <div class="flex items-center w-fit p-0">
                    <p class="block w-[21px] h-[21px] m-0 rounded-[5px] bg-[#F1EDFF] text-center text-black"
                        id="area_title">
                    </p>

                    <h2 class="my-0 mx-[10px] select_title_right hidden">{{ __('Select Keywords') }}</h2>
                    <h2 class="my-0 mx-[10px] select_title_right hidden">{{ __('Choose a Title') }}</h2>
                    <h2 class="my-0 mx-[10px] select_title_right hidden">{{ __('Outline') }}</h2>
                    <h2 class="my-0 mx-[10px] select_title_right hidden">{{ __('Image (optional)') }}</h2>
                </div>
                <div class="w-fit">
                    <a class="btn-primary cursor-default font-bold" id="add_btn">{{ __('Add +') }}</a>
                    <div class="absolute" id='popover'>
                        <div class="popover__back hidden"></div>
                        <div class="popover__content w-[400px] left-[-400px]">
                            <div class="absolute w-[100wh] h-[100vh] bg-black"></div>
                            <div class="new_data hidden m-1">
                                <p class="popover__message"><label class="text-base">{{ __('Add Keyword') }}</label>
                                </p>
                                <input type="text" class="w-full form-control"
                                    placeholder="{{ __('New Keyword') }}" id="new_keyword">
                            </div>
                            <div class="new_data hidden m-1">
                                <p class="popover__message"><label class="text-base">{{ __('Add Title') }}</label>
                                </p>
                                <input type="text" class="w-full form-control"
                                    placeholder="{{ __('New Title') }}" id="new_title">
                            </div>
                            <div class="new_data hidden m-1">
                                <p class="popover__message"><label class="text-base">{{ __('Add Outline') }}</label>
                                </p>
                                <textarea type="text" rows="6" class="w-full form-control" placeholder="{{ __('New Outline') }}"
                                    id="new_outline"></textarea>
                            </div>
                            <div class="new_data hidden m-1">
                                <p class="popover__message"><label class="text-base">{{ __('Add Image') }}</label>
                                </p>
                                <img src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                                    alt="new image" class="w-full form-control m-1" id="new_image">
                                <input type="file" class="w-full form-control m-1" accept="image/*"
                                    id="new_file"></button>
                            </div>
                            <div class="modal-footer p-1">
                                <button type="button" class="btn w-full me-auto"
                                    id="btn_add_new">{{ __('Add') }}</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="row mt-0">
                <div class="select_area hidden">
                    <div class="col-xs-12 bg">
                        <button type="button"
                            class="p-0 bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-black border-none hover:border-transparent rounded m-0"
                            id="select_all_keyword">
                            <label class="select_all_label">{{ __('Select All') }}<label>
                        </button>
                        <button type="button"
                            class="p-0 bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-black border-none hover:border-transparent rounded m-0"
                            id="unselect_all_keyword">
                            <label class="select_all_label">{{ __('Unselect All') }}<label>
                        </button>
                    </div>
                    <div class="row col-xs-12 my-[10px]" id="select_keywords">
                    </div>
                </div>
                <div class="select_area hidden">
                    <div class="row col-xs-12 my-[10px]" id="select_title">
                    </div>
                </div>
                <div class="select_area hidden">
                    <div class="row col-xs-12 my-[10px]" id="select_outline">
                    </div>
                </div>
                <div class="select_area row hidden" id="select_image">
                </div>
            </div>
            <div class="row">
                <div id="next_btn" class="hidden">
                    <div class="col-xs-12 mt-[30px]">
                        <button id="next_page_btn"
                            class="btn bg-[#F1EDFF] hover:bg-[#c1bDFF] text-black w-100 py-[0.75em] flex items-center group"
                            onclick="goNextStep()">
                            <span
                                class="hidden group-[.lqd-form-submitting]:inline-flex">{{ __('Please wait...') }}</span>
                            <span class="group-[.lqd-form-submitting]:hidden flex items-center">{{ __('Next') }}<p
                                    class="my-0 mx-[5px] w-[30px] text-xl bg-white rounded-full text-center">&#8250;
                                </p>
                            </span>
                        </button>
                    </div>
                </div>
                <div id="generate_btn" class="hidden">
                    <div class="col-xs-12 mt-[30px]">
                        <button id="generate_article"
                            class="btn btn-primary w-100 py-[0.75em] flex items-center group" onclick="goNextStep()">
                            <span
                                class="hidden group-[.lqd-form-submitting]:inline-flex">{{ __('Please wait...') }}</span>
                            <span
                                class="group-[.lqd-form-submitting]:hidden flex items-center">{{ __('Generate the Article') }}
                                <p class="my-0 mx-[5px] w-[30px] text-xl bg-white rounded-full text-center text-black">
                                    &#8250;</p>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-7 lg:pl-16 lg:border-l lg:border-solid border-t-0 border-r-0 border-b-0 border-[var(--tblr-border-color)] [&_.tox-edit-area__iframe]:!bg-transparent min-h-[400px] hidden"
        id="result_area">
        <div class="card-body mb-10">
            <div class="row mb-[10px] flex justify-between items-center">
                <div class="flex items-center w-fit">
                    <p class="block w-[21px] h-[21px] m-0 rounded-[5px] bg-[#F1EDFF] text-center text-black">&#10003;
                    </p>

                    <h2 class="my-0 mx-[10px] select_title_right hidden">{{ __('Result') }}</h2>
                </div>
                <div class="w-fit">
                    <p class=" cursor-default font-bold hidden" id="saved_documents"><svg width="24"
                            height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M21.419 15.7321C21.419 19.3101 19.31 21.4191 15.732 21.4191H7.95C4.363 21.4191 2.25 19.3101 2.25 15.7321V7.93212C2.25 4.35912 3.564 2.25012 7.143 2.25012H9.143C9.861 2.25112 10.537 2.58812 10.967 3.16312L11.88 4.37712C12.312 4.95112 12.988 5.28912 13.706 5.29012H16.536C20.123 5.29012 21.447 7.11612 21.447 10.7671L21.419 15.7321Z"
                                stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M7.48047 14.463H16.2155" stroke="black" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        {{ __('Saved to') }}
                        <a class="underline text-black"
                            href="/dashboard/user/openai/documents/all">{{ __('Documents') }}</a>
                    </p>
                    <button class="btn btn-primary" id="stop_generating">{{ __('Stop') }}</button>
                </div>
            </div>
        </div>
        <div class="row mt-[30px]">
            <div class="border-none focus:outline-none border-2 focus:border-transparent min-h-[800px]"
                id="result_article"></div>
        </div>
    </div>
</div>

<template id="selected_keyword">
    <label
        class="keyword w-fit border-[1px] hover:bg-[#d1cDFF] py-1 border-solid border-[#F3F3F3] rounded-[20px] mb-3 mx-2 font-medium bg-[#F1EDFF]">success</label>
</template>
<template id="unselected_keyword">
    <label
        class="keyword w-fit border-[1px] hover:bg-[#eeeeee] py-1 border-solid border-[#F3F3F3] rounded-[20px] mb-3 mx-2 font-medium">victory</label>
</template>

<template id="selected_title">
    <div
        class="title select_title flex border-[#F3F3F3] border-[1px] my-2 border-solid rounded-[15px] items-center p-3 cursor-default shadow-[0_3px_19px_0_rgba(47, 58, 99, 0.5)]">
        <div class="bg-[#f1edff] rounded-full w-10 h-10 flex justify-center items-center min-w-[2.5rem]">
            <p class="m-0 text-lg">✓</p>
        </div>
        <h3 class="my-0 mx-3 title_text"></h3>
    </div>
</template>

<template id="unselected_title">
    <div
        class="title flex border-[#F3F3F3] border-[1px] hover:shadow-[0_3px_19px_0_rgba(47, 58, 99, 0.1)]  my-2 border-solid rounded-[15px] items-center p-3 cursor-default">
        <div class="bg-[#F4F4F4] rounded-full w-10 h-10 min-w-[2.5rem] "></div>
        <h3 class="my-0 mx-3 title_text"></h3>
    </div>
</template>

<template id="sample_outline_template">
    <li><label class="my-1 mx-3 font-medium text-base"></label></li>
</template>

<template id="selected_outline">
    <div data="0"
        class="outline_ select_outline flex flex-col border-[#F3F3F3] border-[1px] my-2 border-solid rounded-[15px] p-3  cursor-default"
        style="box-shadow: 0px 3px 19px 0px rgba(47,58, 99, 0.06);">
        <ul class="mr-[50px] my-0">
        </ul>
        <div
            class="absolute right-[20px] bg-[#f1edff] rounded-full w-10 h-10 min-w-[2.5rem] flex justify-center items-center">
            <p class="m-0 text-lg">✓</p>
        </div>
    </div>
</template>

<template id="unselected_outline">
    <div data="0"
        class="outline_ flex flex-col border-[#F3F3F3] border-[1px] my-2 border-solid rounded-[15px] p-3 hover:shadow-[0_3px_19px_0_rgba(47, 58, 99, 0.1)]  cursor-default">
        <ul class="mr-[50px] my-0">
        </ul>
    </div>
</template>

<template id="selected_image">
    <div
        class="image_ relative col-md-6 my-[10px] transition ease-in-out delay-50 hover:-translate-y-1 hover:scale-110 ">
        <img class="w-full hover:transition-opacity border-0 my-2 border-solid rounded-[15px]"
            src="/uploads/RMIO1HP7tOc2-DALL-E-minions-fight.png">
        <div
            class="absolute right-0 top-0 bg-[#f1edff] rounded-full w-10 h-10 flex justify-center items-center min-w-[2.5rem]">
            <p class="m-0 text-lg">✓</p>
        </div>
    </div>
</template>

<template id="unselected_image">
    <div class="image_ col-md-6 my-[10px] transition ease-in-out delay-50 hover:-translate-y-1 hover:scale-110 ">
        <img class="w-full hover:transition-opacity border-0 my-2 border-solid rounded-[15px]"
            src="">
    </div>
</template>
