<!-- Start image generator -->
@if($openai->type == 'image')
    <div class="row row-deck row-cards">
        <div class="col-12 flex-column">
            <div class="card bg-[#F3E2FD] !shadow-sm dark:bg-[#14171C] dark:shadow-black">
                <div class="card-body md:p-10">
                    <div class="mb-3">
                        <div class="flex flex-wrap justify-between">
                            <div class="form-selectgroup flex flex-row">
                                <label class="form-selectgroup-item-image-gen" image-generator="dall-e">
                                    <input type="radio" name="icons" value="dall-e" class="form-selectgroup-input" checked/>
                                    <h3 class="form-selectgroup-label border-none dark:!text-white">DALL-E</h3>
                                </label>
                                <label class="form-selectgroup-item-image-gen" image-generator="stablediffusion" hidden>
                                    <input type="radio" name="icons" value="stablediffusion" class="form-selectgroup-input" disabled />
                                    <h3 class="form-selectgroup-label border-none dark:!text-white">Stable Diffusion</h3>
                                </label>
                            </div>
                            <div class="max-sm:-order-1 max-sm:mb-4 max-sm:w-full">
                                <div class="flex justify-between flex-wrap mb-2">
                                    <div class="flex items-center mr-3">
                                        <span class="legend !me-2 rounded-full bg-primary" style="--tblr-legend-size:0.5rem;"></span>
                                        <span>{{__('Words')}}</span>
                                        <span class="ms-2 text-heading font-medium">
                                            @if(Auth::user()->remaining_words == -1)
                                                Unlimited
                                            @else
                                                {{number_format((int)Auth::user()->remaining_words)}}
                                            @endif
                                        </span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="legend !me-2 rounded-full bg-[#9E9EFF]" style="--tblr-legend-size:0.5rem;"></span>
                                        <span>{{__('Images')}}</span>
                                        <span class="ms-2 text-heading font-medium">
                                            @if(Auth::user()->remaining_images == -1)
                                                Unlimited
                                            @else
                                                {{number_format((int)Auth::user()->remaining_images)}}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                <div class="progress progress-separated h-1">
                                    @if((int)Auth::user()->remaining_words+(int)Auth::user()->remaining_images != 0)
                                        <div class="progress-bar grow-0 shrink-0 basis-auto bg-primary" role="progressbar" style="width: {{(int)Auth::user()->remaining_words/((int)Auth::user()->remaining_words+(int)Auth::user()->remaining_images)*100}}%" aria-label="{{__('Text')}}"></div>
                                    @endif
                                    @if((int)Auth::user()->remaining_words+(int)Auth::user()->remaining_images != 0)
                                        <div class="progress-bar grow-0 shrink-0 basis-auto bg-[#9E9EFF]" role="progressbar" style="width: {{(int)Auth::user()->remaining_images/((int)Auth::user()->remaining_words+(int)Auth::user()->remaining_images)*100}}%" aria-label="{{__('Images')}}"></div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div dall-e>
                        <div class="row">
                            <label for="description" class="h2 mb-3">{{__('Explain your idea')}}. | <a onclick="return fillAnExample('image-input-for-fillanexample');" class="text-success" href="">{{__('Generate example prompt')}}</a> </label>
                            <form id="openai_generator_form" onsubmit="return sendOpenaiGeneratorForm();">
                                <div class="relative mb-3">
                                    @php
                                        $placeholders = [
                                            'Cityscape at sunset in retro vector illustration ',
                                            'Painting of a flower vase on a kitchen table with a window in the backdrop.',
                                            'Memphis style painting of a flower vase on a kitchen table with a window in the backdrop.',
                                            'Illustration of a cat sitting on a couch in a living room with a coffee mug in its hand.',
                                            'Delicious pizza with all the toppings.'];
                                    @endphp
                                    @foreach(json_decode($openai->questions) as $question)
                                        @if($question->type == 'textarea')
                                            <textarea class="image-input-for-fillanexample form-control bg-[#fff] rounded-full h-[53px] text-[#000] resize-none !shadow-sm placeholder:text-black placeholder:text-opacity-50 focus:bg-white focus:border-white dark:!border-none dark:!bg-[--lqd-header-search-bg] dark:focus:!bg-[--lqd-header-search-bg] dark:placeholder:text-[#a5a9b1] max-md:!min-h-[120px] max-md:rounded-md" type="text" id="{{$question->name}}" name="{{$question->name}}" placeholder="{{__($placeholders[array_rand($placeholders)])}}"></textarea>
                                        @endif
                                    @endforeach
                                    <button id="openai_generator_button" class="btn btn-primary h-[36px] absolute top-1/2 end-[1rem] -translate-y-1/2 hover:-translate-y-1/2 hover:scale-110 max-lg:relative max-lg:top-auto max-lg:right-auto max-lg:translate-y-0 max-lg:w-full max-lg:mt-2" type="submit">
                                        {{__('Generate')}}
                                        <svg class="!ms-2 rtl:-scale-x-100 translate-x-0 translate-y-0" width="14" height="13" viewBox="0 0 14 13" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M7.25 13L6.09219 11.8625L10.6422 7.3125H0.75V5.6875H10.6422L6.09219 1.1375L7.25 0L13.75 6.5L7.25 13Z"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="flex flex-wrap justify-between">
                                    <a href="#advanced-settings" class="flex items-center text-[11px] font-semibold text-heading hover:no-underline group collapsed" data-bs-toggle="collapse" data-bs-auto-close="false">
                                        {{__('Advanced Settings')}}
                                        <span class="inline-flex items-center justify-center w-[36px] h-[36px] p-0 !ms-2 bg-white !shadow-sm rounded-full dark:!bg-[--tblr-bg-surface]">
                                            <svg class="hidden group-[&.collapsed]:block" width="12" height="12" viewBox="0 0 12 12" fill="var(--lqd-heading-color)" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M6.76708 5.464H11.1451V7.026H6.76708V11.558H5.18308V7.026H0.805078V5.464H5.18308V0.909999H6.76708V5.464Z"/>
                                            </svg>
                                            <svg class="block group-[&.collapsed]:hidden" width="6" height="2" viewBox="0 0 6 2" fill="var(--lqd-heading-color)" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M0.335078 1.962V0.246H5.65908V1.962H0.335078Z"/>
                                            </svg>
                                        </span>
                                    </a>
                                </div>
                                <div id="advanced-settings" class="collapse">
                                    {{-- <div class="negative-prompt-form hidden mt-3" stable-diffusion>
                                        <label class="form-label text-heading">{{__('Negative Prompt')}}</label>
                                        <textarea class="form-control bg-[#fff] rounded-full h-[53px] text-[#000] resize-none !shadow-sm placeholder:text-black placeholder:text-opacity-50 focus:bg-white focus:border-white dark:!border-none dark:!bg-[--lqd-header-search-bg] dark:focus:!bg-[--lqd-header-search-bg] dark:placeholder:text-[#a5a9b1]" type="text" id="negative_prompt" name="negative_prompt"></textarea>
                                    </div> --}}
                                    <div class="flex flex-wrap justify-between gap-3 mt-8">
                                        @foreach(json_decode($openai->questions) as $question)
                                            @if($question->type == 'select')
                                                <div class="grow">
                                                    <label for="{{$question->name}}" class="form-label text-heading">{{__('Image resolution')}}</label>
                                                    <select class="form-control form-select bg-[#fff] placeholder:text-black" name="{{$question->name}}" id="{{$question->name}}">
                                                        {!! $question->select !!}
                                                    </select>
                                                </div>
                                            @endif
                                        @endforeach
                                            <div class="grow">
                                                <label for="image_style" class="form-label text-heading">{{__('Art Style')}}</label>
                                                <select name="image_style" id="image_style" class="form-control form-select bg-[#fff] placeholder:text-black">
                                                    <option value="" selected="selected">{{__('None')}}</option>
                                                    <option value="3d_render">{{__('3D Render')}}</option>
                                                    <option value="anime">{{__('Anime')}}</option>
                                                    <option value="ballpoint_pen">{{__('Ballpoint Pen Drawing')}}</option>
                                                    <option value="bauhaus">{{__('Bauhaus')}}</option>
                                                    <option value="cartoon">{{__('Cartoon')}}</option>
                                                    <option value="clay">{{__('Clay')}}</option>
                                                    <option value="contemporary">{{__('Contemporary')}}</option>
                                                    <option value="cubism">{{__('Cubism')}}</option>
                                                    <option value="cyberpunk">{{__('Cyberpunk')}}</option>
                                                    <option value="glitchcore">{{__('Glitchcore')}}</option>
                                                    <option value="impressionism">{{__('Impressionism')}}</option>
                                                    <option value="isometric">{{__('Isometric')}}</option>
                                                    <option value="line">{{__('Line Art')}}</option>
                                                    <option value="low_poly">{{__('Low Poly')}}</option>
                                                    <option value="minimalism">{{__('Minimalism')}}</option>
                                                    <option value="modern">{{__('Modern')}}</option>
                                                    <option value="origami">{{__('Origami')}}</option>
                                                    <option value="pencil">{{__('Pencil Drawing')}}</option>
                                                    <option value="pixel">{{__('Pixel')}}</option>
                                                    <option value="pointillism">{{__('Pointillism')}}</option>
                                                    <option value="pop">{{__('Pop')}}</option>
                                                    <option value="realistic">{{__('Realistic')}}</option>
                                                    <option value="renaissance">{{__('Renaissance')}}</option>
                                                    <option value="retro">{{__('Retro')}}</option>
                                                    <option value="steampunk">{{__('Steampunk')}}</option>
                                                    <option value="sticker">{{__('Sticker')}}</option>
                                                    <option value="ukiyo">{{__('Ukiyo')}}</option>
                                                    <option value="vaporwave">{{__('Vaporwave')}}</option>
                                                    <option value="vector">{{__('Vector')}}</option>
                                                    <option value="watercolor">{{__('Watercolor')}}</option>
                                                </select>
                                            </div>
                                            <div class="grow">
                                                <label for="image_lighting" class="form-label text-heading">{{__('Lightning Style')}}</label>
                                                <select id="image_lighting" name="image_lighting" class="form-control form-select bg-[#fff] placeholder:text-black">
                                                    <option value="" selected="selected">{{ __('None') }}</option>
                                                    <option value="ambient">{{ __('Ambient') }}</option>
                                                    <option value="backlight">{{ __('Backlight') }}</option>
                                                    <option value="blue_hour">{{ __('Blue Hour') }}</option>
                                                    <option value="cinematic">{{ __('Cinematic') }}</option>
                                                    <option value="cold">{{ __('Cold') }}</option>
                                                    <option value="dramatic">{{ __('Dramatic') }}</option>
                                                    <option value="foggy">{{ __('Foggy') }}</option>
                                                    <option value="golden_hour">{{ __('Golden Hour') }}</option>
                                                    <option value="hard">{{ __('Hard') }}</option>
                                                    <option value="natural">{{ __('Natural') }}</option>
                                                    <option value="neon">{{ __('Neon') }}</option>
                                                    <option value="studio">{{ __('Studio') }}</option>
                                                    <option value="warm">{{ __('Warm') }}</option>
                                                </select>
                                            </div>
                                            <div class="grow">
                                                <label for="image_mood" class="form-label text-heading">{{__('Mood')}}</label>
                                                <select id="image_mood" name="image_mood" class="form-control form-select bg-[#fff] placeholder:text-black">
                                                    <option value="" selected="selected">{{ __('None') }}</option>
                                                    <option value="aggressive">{{ __('Aggressive') }}</option>
                                                    <option value="angry">{{ __('Angry') }}</option>
                                                    <option value="boring">{{ __('Boring') }}</option>
                                                    <option value="bright">{{ __('Bright') }}</option>
                                                    <option value="calm">{{ __('Calm') }}</option>
                                                    <option value="cheerful">{{ __('Cheerful') }}</option>
                                                    <option value="chilling">{{ __('Chilling') }}</option>
                                                    <option value="colorful">{{ __('Colorful') }}</option>
                                                    <option value="dark">{{ __('Dark') }}</option>
                                                    <option value="neutral">{{ __('Neutral') }}</option>
                                                </select>
                                            </div>
                                            <div class="grow">
                                                <label for="image_number_of_images" class="form-label text-heading">{{__('Number of Images')}}</label>
                                                <select name="image_number_of_images" id="image_number_of_images" class="form-control form-select bg-[#fff] placeholder:text-black">
                                                    <option value="1" selected="selected">1</option>
                                                    <option value="2">2</option>
                                                    <option value="3">3</option>
                                                    <option value="4">4</option>
                                                    <option value="5">5</option>
                                                </select>
                                            </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="hidden" stable-diffusion>
                        <form id="openai_generator_form" onsubmit="return sendOpenaiGeneratorForm();">
                            
                            <ul class="nav nav-tabs nav-fill border-none mb-3 text-heading" data-bs-toggle="tabs">
                                <li class="nav-item">
                                    <a href="#tabs-text-to-image" class="nav-link active px-4 py-2 !rounded-none !rounded-l-3xl" data-bs-toggle="tab" onclick="handleTabClick('text-to-image')">{{__('Text-to-Image')}}</a>
                                </li>
                                <li class="nav-item">
                                    <a href="#tabs-image-to-image" class="nav-link !rounded-none" data-bs-toggle="tab" onclick="handleTabClick('image-to-image')">{{__('Image-to-Image')}}</a>
                                </li>
                                <li class="nav-item">
                                    <a href="#tabs-upscaling" class="nav-link !rounded-none" data-bs-toggle="tab" onclick="handleTabClick('upscale')">{{__('Upscaling')}}</a>
                                </li>
                                <li class="nav-item">
                                    <a href="#tabs-multi-prompts" class="nav-link !rounded-none !rounded-r-3xl" data-bs-toggle="tab" onclick="handleTabClick('multi-prompt')">{{__('Multi-Prompting')}}</a>
                                </li>
                            </ul>
                            <div class="flex flex-wrap justify-between mb-3">
                                <a href="#advanced-settings" class="flex items-center text-[11px] font-semibold text-heading hover:no-underline group collapsed" data-bs-toggle="collapse" data-bs-auto-close="false">
                                    {{__('Advanced Settings')}}
                                    <span class="inline-flex items-center justify-center w-[36px] h-[36px] p-0 !ms-2 bg-white !shadow-sm rounded-full dark:!bg-[--tblr-bg-surface]">
                                        <svg class="hidden group-[&.collapsed]:block" width="12" height="12" viewBox="0 0 12 12" fill="var(--lqd-heading-color)" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M6.76708 5.464H11.1451V7.026H6.76708V11.558H5.18308V7.026H0.805078V5.464H5.18308V0.909999H6.76708V5.464Z"/>
                                        </svg>
                                        <svg class="block group-[&.collapsed]:hidden" width="6" height="2" viewBox="0 0 6 2" fill="var(--lqd-heading-color)" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M0.335078 1.962V0.246H5.65908V1.962H0.335078Z"/>
                                        </svg>
                                    </span>
                                </a>
                            </div>
                            <div id="advanced-settings" class="collapse">
                                <div class="flex flex-wrap justify-between gap-3 mt-8">
                                    <div class="grow">
                                        <label for="style_preset" class="form-label text-heading">{{__('Image Style')}}</label>
                                        <select id="style_preset" name="style_preset" class="form-control form-select bg-[#fff] placeholder:text-black">
                                            <option value="" selected="selected">{{ __('None') }}</option>
                                            <option value="3d-model">{{ __('3D Model') }}</option>
                                            <option value="analog-film">{{ __('Analog Film') }}</option>
                                            <option value="anime">{{ __('Anime') }}</option>
                                            <option value="cinematic">{{ __('Cinematic') }}</option>
                                            <option value="comic-book">{{ __('Comic Book') }}</option>
                                            <option value="digital-art">{{ __('Digital Art') }}</option>
                                            <option value="enhance">{{ __('Enhance') }}</option>
                                            <option value="fantasy-art">{{ __('Fantasy Art') }}</option>
                                            <option value="isometric">{{ __('Isometric') }}</option>
                                            <option value="line-art">{{ __('Line Art') }}</option>
                                            <option value="low-poly">{{ __('Low Poly') }}</option>
                                            <option value="modeling-compound">{{ __('Modeling Compound') }}</option>
                                            <option value="neon-punk">{{ __('Neon Punk') }}</option>
                                            <option value="origami">{{ __('Origami') }}</option>
                                            <option value="photographic">{{ __('Photographic') }}</option>
                                            <option value="pixel-art">{{ __('Pixel Art') }}</option>
                                            <option value="tile-texture">{{ __('Tile Texture') }}</option>
                                        </select>
                                    </div>
                                    <div class="grow">
                                        <label for="image_mood_stable" class="form-label text-heading">{{__('Mood')}}</label>
                                        <select id="image_mood_stable" name="image_mood_stable" class="form-control form-select bg-[#fff] placeholder:text-black">
                                            <option value="" selected="selected">{{ __('None') }}</option>
                                            <option value="aggressive">{{ __('Aggressive') }}</option>
                                            <option value="angry">{{ __('Angry') }}</option>
                                            <option value="boring">{{ __('Boring') }}</option>
                                            <option value="bright">{{ __('Bright') }}</option>
                                            <option value="calm">{{ __('Calm') }}</option>
                                            <option value="cheerful">{{ __('Cheerful') }}</option>
                                            <option value="chilling">{{ __('Chilling') }}</option>
                                            <option value="colorful">{{ __('Colorful') }}</option>
                                            <option value="dark">{{ __('Dark') }}</option>
                                            <option value="neutral">{{ __('Neutral') }}</option>
                                        </select>
                                    </div>
                                    <div class="grow">
                                        <label for="sampler" class="form-label text-heading">{{__('Image Diffusion Samples')}}</label>
                                        <select id="sampler" name="sampler" class="form-control form-select bg-[#fff] placeholder:text-black">
                                            <option value="" selected="selected">{{ __('None') }}</option>
                                            <option value="DDIM">{{ __('DDIM') }}</option>
                                            <option value="DDPM">{{ __('DDPM') }}</option>
                                            <option value="K_DPMPP_2M">{{ __('K_DPMPP_2M') }}</option>
                                            <option value="K_DPM_2">{{ __('K_DPM_2') }}</option>
                                            <option value="K_DPM_2_ANCESTRAL">{{ __('K_DPM_2_ANCESTRAL') }}</option>
                                            <option value="K_EULER">{{ __('K_EULER') }}</option>
                                            <option value="K_EULER_ANCESTRAL">{{ __('K_EULER_ANCESTRAL') }}</option>
                                            <option value="K_HEUN">{{ __('K_HEUN') }}</option>
                                            <option value="K_LMS">{{ __('K_LMS') }}</option>
                                        </select>
                                    </div>
                                    <div class="grow">
                                        <label for="clip_guidance_preset" class="form-label text-heading">{{__('Clip Guidance Preset')}}</label>
                                        <select id="clip_guidance_preset" name="clip_guidance_preset" class="form-control form-select bg-[#fff] placeholder:text-black">
                                            <option value="" selected="selected">{{ __('None') }}</option>
                                            <option value="FAST_BLUE">{{ __('FAST BLUE') }}</option>
                                            <option value="FAST_GREEN">{{ __('FAST GREEN') }}</option>
                                            <option value="SIMPLE">{{ __('SIMPLE') }}</option>
                                            <option value="SLOW">{{ __('SLOW') }}</option>
                                            <option value="SLOWER">{{ __('SLOWER') }}</option>
                                            <option value="SLOWEST">{{ __('SLOWEST') }}</option>
                                        </select>
                                    </div>  
                                </div>
                                <div class="flex flex-wrap justify-between gap-3 mt-8">
                                    <div class="grow">
                                        <label for="image_resolution" class="form-label text-heading">{{__('Image Resolution')}}</label>
                                        <select id="image_resolution" name="image_resolution" class="form-control form-select bg-[#fff] placeholder:text-black">
                                            @if($settings_two->stablediffusion_default_model == "stable-diffusion-v1-5" || $settings_two->stablediffusion_default_model == "stable-diffusion-512-v2-1")
                                            <option value="896x512" selected>{{ __('896 x 512') }}</option>
                                            <option value="768x512">{{ __('768 x 512') }}</option>
                                            <option value="512x512">{{ __('512 x 512') }}</option>
                                            <option value="512x768">{{ __('512 x 768') }}</option>
                                            <option value="512x896">{{ __('512 x 896') }}</option>
                                            @else
                                            <option value="640x1536" selected>{{ __('640 x 1536') }}</option>
                                            <option value="768x1344">{{ __('768 x 1344') }}</option>
                                            <option value="832x1216">{{ __('832 x 1216') }}</option>
                                            <option value="896x1152">{{ __('896 x 1152') }}</option>
                                            <option value="1024x1024">{{ __('1024 x 1024') }}</option>
                                            <option value="1152x896">{{ __('1152 x 896') }}</option>
                                            <option value="1216x832">{{ __('1216 x 832') }}</option>
                                            <option value="1344x768">{{ __('1344 x 768') }}</option>
                                            <option value="1536x640">{{ __('1536 x 640') }}</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div class="grow">
                                        <label for="image_number_of_images_stable" class="form-label text-heading">{{__('Number of Images')}}</label>
                                        <select name="image_number_of_images_stable" id="image_number_of_images_stable" class="form-control form-select bg-[#fff] placeholder:text-black" disabled>
                                            <option value="1" selected="selected">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                            <option value="4">4</option>
                                            <option value="5">5</option>
                                        </select>
                                    </div>
                                    <div class="basis-full sm:basis-1/2">
                                        <label class="form-label text-heading">{{__('Negative Prompts')}}</label>
                                        <input type="text" class="form-control bg-white" id="negative_prompt" name="negative_prompt">
                                    </div>
                                </div>
                            </div>
                            <div class="tab-content mt-4">
                                <div class="tab-pane active show" id="tabs-text-to-image">
                                    <label for="description" class="h2 mb-3">{{__('Explain your idea')}}. | <a onclick="return fillAnExample('text-to-image-fillanexample');" class="text-success" href="">{{__('Generate example prompt')}}</a> </label>
                                    <div class="relative mb-3">
                                        @php
                                            $placeholders = [
                                                'Cityscape at sunset in retro vector illustration ',
                                                'Painting of a flower vase on a kitchen table with a window in the backdrop.',
                                                'Memphis style painting of a flower vase on a kitchen table with a window in the backdrop.',
                                                'Illustration of a cat sitting on a couch in a living room with a coffee mug in its hand.',
                                                'Delicious pizza with all the toppings.'];
                                        @endphp
                                        @foreach(json_decode($openai->questions) as $question)
                                            @if($question->type == 'textarea')
                                                <textarea class="text-to-image-fillanexample form-control bg-[#fff] rounded-full h-[53px] text-[#000] resize-none !shadow-sm placeholder:text-black placeholder:text-opacity-50 focus:bg-white focus:border-white dark:!border-none dark:!bg-[--lqd-header-search-bg] dark:focus:!bg-[--lqd-header-search-bg] dark:placeholder:text-[#a5a9b1] max-md:!min-h-[120px] max-md:rounded-md" type="text" id="txt2img_description" name="txt2img_description" placeholder="{{__($placeholders[array_rand($placeholders)])}}"></textarea>
                                            @endif
                                        @endforeach
                                        <button id="openai_generator_button" class="btn btn-primary h-[36px] absolute top-1/2 end-[1rem] -translate-y-1/2 hover:-translate-y-1/2 hover:scale-110 max-lg:relative max-lg:top-auto max-lg:right-auto max-lg:translate-y-0 max-lg:w-full max-lg:mt-2" type="submit">
                                            {{__('Generate')}}
                                            <svg class="!ms-2 rtl:-scale-x-100 translate-x-0 translate-y-0" width="14" height="13" viewBox="0 0 14 13" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M7.25 13L6.09219 11.8625L10.6422 7.3125H0.75V5.6875H10.6422L6.09219 1.1375L7.25 0L13.75 6.5L7.25 13Z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="tab-pane" id="tabs-image-to-image">
                                    <label for="img2img_description" class="h2 mb-3">{{__('Explain your idea')}}</label>
                                    <textarea class="form-control bg-[#fff] rounded-full h-[53px] text-[#000] resize-none !shadow-sm placeholder:text-black placeholder:text-opacity-50 focus:bg-white focus:border-white dark:!border-none dark:!bg-[--lqd-header-search-bg] dark:focus:!bg-[--lqd-header-search-bg] dark:placeholder:text-[#a5a9b1] max-md:!min-h-[120px] max-md:rounded-md" type="text" id="img2img_description" name="img2img_description" placeholder="{{__('Type your image title or description what you are looking for')}}"></textarea>

                                    <label class="h2 mt-4">{{__('Upload Image')}}</label>
                                    <div class="flex items-center justify-center w-full" ondrop="dropHandler(event, 'img2img_src');" ondragover="dragOverHandler(event);">
                                        <label for="img2img_src" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-bray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600">
                                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                <svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                                                </svg>
                                                <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">{{__('Drop your image here or browse')}}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 file-name">{{__('(Only jpg, png, webp will be accepted)')}}</p>
                                            </div>
                                            <input id="img2img_src" type="file" class="hidden" accept=".png, .jpg, .jpeg" onchange="handleFileSelect('img2img_src')"/>
                                        </label>
                                    </div> 
                                    <button id="openai_generator_button" class="btn btn-primary h-[36px] end-[1rem] hover:scale-110 max-lg:relative max-lg:top-auto max-lg:right-auto max-lg:translate-y-0 max-lg:w-full mt-4" type="submit">
                                        {{__('Generate')}}
                                        <svg class="!ms-2 rtl:-scale-x-100 translate-x-0 translate-y-0" width="14" height="13" viewBox="0 0 14 13" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M7.25 13L6.09219 11.8625L10.6422 7.3125H0.75V5.6875H10.6422L6.09219 1.1375L7.25 0L13.75 6.5L7.25 13Z"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="tab-pane" id="tabs-upscaling">
                                    <label for="multi_prompts_description" class="h2">{{__('Upload Image')}}</label>
                                    <div class="flex items-center justify-center w-full" ondrop="dropHandler(event, 'upscale_src');" ondragover="dragOverHandler(event);">
                                        <label for="upscale_src" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-bray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600">
                                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                <svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                                                </svg>
                                                <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">{{__('Drop your image here or browse')}}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 file-name">{{__('(Only jpg, png, webp will be accepted)')}}</p>
                                            </div>
                                            <input id="upscale_src" type="file" class="hidden" accept=".png, .jpg, .jpeg" onchange="handleFileSelect('upscale_src')"/>
                                        </label>
                                    </div> 
                                    <button id="openai_generator_button" class="btn btn-primary h-[36px] end-[1rem] hover:scale-110 max-lg:relative max-lg:top-auto max-lg:right-auto max-lg:translate-y-0 max-lg:w-full mt-4" type="submit">
                                        {{__('Generate')}}
                                        <svg class="!ms-2 rtl:-scale-x-100 translate-x-0 translate-y-0" width="14" height="13" viewBox="0 0 14 13" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M7.25 13L6.09219 11.8625L10.6422 7.3125H0.75V5.6875H10.6422L6.09219 1.1375L7.25 0L13.75 6.5L7.25 13Z"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="tab-pane" id="tabs-multi-prompts">
                                    <label for="multi_prompts_description" class="h2 mb-3">{{__('Explain your idea')}}</label>
                                    <textarea class="form-control bg-[#fff] rounded-full h-[53px] text-[#000] resize-none !shadow-sm placeholder:text-black placeholder:text-opacity-50 focus:bg-white focus:border-white dark:!border-none dark:!bg-[--lqd-header-search-bg] dark:focus:!bg-[--lqd-header-search-bg] mr-2 dark:placeholder:text-[#a5a9b1] max-md:!min-h-[120px] max-md:rounded-md multi_prompts_description" type="text" id="multi_prompts_description" name="multi_prompts_description" placeholder="{{__('Type your image title or description what you are looking for.')}}"></textarea>
                                    <div class="multi-prompts">
                                    </div>
                                    <button type="submit" class="btn btn-pill block mt-2 h-[36px] flex" onclick="return handleAddPrompt();">
                                        <div class="flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus me-1"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                            <span>{{__('Add More')}}</span>
                                        </div>
                                    </button>
                                    <button id="openai_generator_button" class="btn btn-primary h-[36px] end-[1rem] hover:scale-110 max-lg:relative max-lg:top-auto max-lg:right-auto max-lg:translate-y-0 max-lg:w-full mt-4" type="submit">
                                        {{__('Generate')}}
                                        <svg class="!ms-2 rtl:-scale-x-100 translate-x-0 translate-y-0" width="14" height="13" viewBox="0 0 14 13"              fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M7.25 13L6.09219 11.8625L10.6422 7.3125H0.75V5.6875H10.6422L6.09219 1.1375L7.25 0L13.75 6.5L7.25 13Z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <template id="prompt-template">
            <div class="each-prompt d-flex align-items-center mt-3">
                <input class="input-required form-control placeholder:text-black placeholder:text-opacity-50 focus:bg-white focus:border-white dark:!border-none dark:!bg-[--lqd-header-search-bg] dark:focus:!bg-[--lqd-header-search-bg] dark:placeholder:text-[#a5a9b1] rounded-pill bg-[#fff] text-[#000]  border border-primary multi_prompts_description" type="text" name="titles[]" placeholder="Type another title or description" required> 
                <div data-toggle="remove-parent">
                    <svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 32 32" width="24px" height="24px"><path d="M 15 4 C 14.476563 4 13.941406 4.183594 13.5625 4.5625 C 13.183594 4.941406 13 5.476563 13 6 L 13 7 L 7 7 L 7 9 L 8 9 L 8 25 C 8 26.644531 9.355469 28 11 28 L 23 28 C 24.644531 28 26 26.644531 26 25 L 26 9 L 27 9 L 27 7 L 21 7 L 21 6 C 21 5.476563 20.816406 4.941406 20.4375 4.5625 C 20.058594 4.183594 19.523438 4 19 4 Z M 15 6 L 19 6 L 19 7 L 15 7 Z M 10 9 L 24 9 L 24 25 C 24 25.554688 23.554688 26 23 26 L 11 26 C 10.445313 26 10 25.554688 10 25 Z M 12 12 L 12 23 L 14 23 L 14 12 Z M 16 12 L 16 23 L 18 23 L 18 12 Z M 20 12 L 20 23 L 22 23 L 22 12 Z"/></svg>
                </div>
            </div>
        </template>
        <div id="generator_sidebar_table">
            @include('panel.user.openai.generator_components.generator_sidebar_table')
        </div>
    </div>

    <script>
       	const imageGeneratorFields = document.querySelectorAll('.form-selectgroup-item-image-gen')
        imageGeneratorFields.forEach(field => {
            field.addEventListener('click', event => {
                if(field.getAttribute('image-generator') == "dall-e") {
                    document.querySelector('[stable-diffusion]').classList.add('hidden');
                    document.querySelector('[dall-e]').classList.remove('hidden');
                }
                else {
                    document.querySelector('[stable-diffusion]').classList.remove('hidden')
                    document.querySelector('[dall-e]').classList.add('hidden');
                }
            })
	    })

        function handleTabClick(type) {
            stablediffusionType = type;

            let imageResolution = document.getElementById("image_resolution");
            let negativePrompt = document.getElementById("negative_prompt");
            let clipGuidancePreset = document.getElementById("clip_guidance_preset");

            imageResolution.disabled = false;
            negativePrompt.disabled = false;
            clipGuidancePreset.disabled = false;
            switch(type) {
                case 'text-to-image':
                break;
                case 'image-to-image':
                    imageResolution.value="512x512";
                    imageResolution.disabled = true;
                    clipGuidancePreset.value = "";
                    clipGuidancePreset.disabled = true;
                break;
                case 'upscale':
                    imageResolution.value="512x512";
                    imageResolution.disabled = true;
                    clipGuidancePreset.value = "";
                    clipGuidancePreset.disabled = true
                break;
                case 'multi-prompt':
                    negativePrompt.disabled = true;
                break;
            }
        }

        function handleAddPrompt() {
            const mulPromptsContainer = document.querySelector('.multi-prompts')
			const promptTemplate = document.querySelector( '#prompt-template' ).content.cloneNode( true )
            const removeBtn = promptTemplate.querySelector('[data-toggle="remove-parent"]')
            removeBtn.addEventListener('click', (e) => {
                event.preventDefault();
                e.currentTarget.parentElement.remove();
            })
            mulPromptsContainer.append(promptTemplate)
        }

        function dropHandler(ev, id) {
            // Prevent default behavior (Prevent file from being opened)
            ev.preventDefault();
            $('#' + id)[0].files = ev.dataTransfer.files;
            $('#' + id).prev().find(".file-name").text(ev.dataTransfer.files[0].name);
        }

        function dragOverHandler(ev) {
            // Prevent default behavior (Prevent file from being opened)
            ev.preventDefault();
        }

        function handleFileSelect(id) {
            $('#' + id).prev().find(".file-name").text( $('#' + id)[0].files[0].name);
        }
    </script>
@endif
<!-- End image generator -->
