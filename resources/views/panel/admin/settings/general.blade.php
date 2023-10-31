@extends('panel.layout.app')
@section('title', __('General Settings'))

@section('additional_css')
<link rel="stylesheet" href="https://foliotek.github.io/Croppie/croppie.css" />
<style>
#upload-demo{
	width: 250px;
	height: 250px;
  	padding-bottom:25px;
	margin: 0 auto;
}

</style>
@endsection

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
					{{__('General Settings')}}
				</h2>
			</div>
		</div>
	</div>
</div>
<!-- Page body -->
<div class="page-body pt-6">
	<div class="container-xl">
		<div class="row col-md-5 mx-auto">
			<form id="settings_form" onsubmit="return generalSettingsSave();" enctype="multipart/form-data">
				<h3 class="mb-[25px] text-[20px]">{{__('Global Settings')}}</h3>
				<div class="row mb-4">

                    <div class="mb-[20px]">
                        <label class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="hosting_type" {{ $setting->hosting_type == 'low' ? 'checked' : '' }}>
                            <span class="form-check-label">{{ __('Turbo Writer') }}</span>
                            <x-info-tooltip text="{{__('Please enable this to activate turbo writer')}}" />
                        </label>
                    </div>

                    <div class="mb-[20px]">
                        <label class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="login_without_confirmation" {{ $setting->login_without_confirmation == 0 ?  'checked' : '' }}>
                            <span class="form-check-label">{{ __('Disable Login Without Confirmation') }}</span>
                            <x-info-tooltip text="{{__('If this is enabled users cannot login unless they confirm their emails.')}}" />
                        </label>
                    </div>

					<div class="col-md-12">
						<div class="mb-3">
							<label class="form-label">{{__('Site Name')}}</label>
							<input type="text" class="form-control" id="site_name" name="site_name" value="{{$setting->site_name}}">
						</div>
					</div>

					<div class="col-md-12">
						<div class="mb-3">
							<label class="form-label">{{__('Site URL')}}</label>
							<input type="text" class="form-control" id="site_url" name="site_url" value="{{$setting->site_url}}">
						</div>
					</div>

					<div class="col-md-12">
						<div class="mb-3">
							<label class="form-label">{{__('Site Email')}}</label>
							<input type="text" class="form-control" id="site_email" name="site_email" value="{{$setting->site_email}}">
						</div>
					</div>

					<div class="col-md-12">
						<div class="mb-3">
							<label class="form-label">{{__('Default Country')}}</label>
							<select class="form-select" name="default_country" id="default_country">
								@include('panel.admin.settings.countries')
							</select>
						</div>
					</div>

                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">{{__('Default Currency')}}</label>
                            <select class="form-select" name="default_currency" id="default_currency">
                                @include('panel.admin.settings.currencies')
                            </select>
                        </div>
                    </div>

					<div class="col-md-12">
						<div class="mb-3">
							<label class="form-label">{{__('Registration Active')}}</label>
							<select class="form-select" name="register_active" id="register_active">
								<option value="1" {{$setting->register_active == 1 ? 'selected' : ''}}>{{__('Active')}}</option>
								<option value="0" {{$setting->register_active == 0 ? 'selected' : ''}}>{{__('Passive')}}</option>
							</select>
						</div>
					</div>
				</div>

                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">{{__('Free Usage Upon Registration (words,images)')}}</label>
                        <input type="text" class="form-control" id="free_plan" name="free_plan" value="{{$setting->free_plan}}">
                    </div>
                </div>

				<h3 class="mb-[25px] text-[20px]">{{__('Social Login')}}</h3>
				<div class="row mb-4">
					<div class="mb-3">
						<div class="mb-4 bg-blue-100 text-blue-600 rounded-xl !p-3 !mt-2 dark:bg-blue-600/20 dark:text-blue-200">
							<a href="https://magicaidocs.liquid-themes.com/social-login" target="_blank">{{__('Check the documentation.')}}
							<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
								<path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
								<path d="M17 7l-10 10"></path>
								<path d="M8 7l9 0l0 9"></path>
							</svg>
							</a>
						</div>

						<label class="form-check form-switch">
							<input class="form-check-input" type="checkbox" id="facebook_active" {{ $setting->facebook_active ? 'checked' : '' }}>
							<span class="form-check-label">{{ __('Facebook') }}</span>
						</label>
						<label class="form-check form-switch">
							<input class="form-check-input" type="checkbox" id="google_active" {{ $setting->google_active ? 'checked' : '' }}>
							<span class="form-check-label">{{ __('Google') }}</span>
						</label>
						<label class="form-check form-switch">
							<input class="form-check-input" type="checkbox" id="github_active" {{ $setting->github_active ? 'checked' : '' }}>
							<span class="form-check-label">{{ __('Github') }}</span>
						</label>
					</div>
				</div>

				<h3 class="mb-[25px] text-[20px]">{{__('Logo Settings')}}</h3>
				<div class="row mb-4">
					<div class="col-md-12 mb-3">
						<div class="mb-4">
							<label class="form-label">{{__('Site Favicon')}}</label>
							<input type="file" class="form-control" id="favicon" name="favicon">
						</div>
						<div class="bg-blue-100 text-blue-600 rounded-xl !p-3 !mt-2 dark:bg-blue-600/20 dark:text-blue-200">
							{{__('If you will use SVG, you do not need the Retina (2x) option.')}}
						</div>
					</div>

					<div class="col-md-6">
						<h4 class="mb-3">{{__('Default Logos')}}</h4>

						<div class="mb-3">
							<label class="form-label">{{__('Site Logo')}}</label>
							<input type="file" class="form-control item-img" data-id="logo" id="logo" name="logo">
						</div>

						<div class="mb-3">
							<label class="form-label">{{__('Site Logo (Dark)')}}</label>
							<input type="file" class="form-control item-img" data-id="logo_dark" id="logo_dark" name="logo_dark">
						</div>

						<div class="mb-3">
							<label class="form-label">{{__('Site Logo Sticky')}}</label>
							<input type="file" class="form-control item-img" data-id="logo_sticky" id="logo_sticky" name="logo_sticky">
						</div>

						<div class="mb-3">
							<label class="form-label">{{__('Dashboard Logo')}}</label>
							<input type="file" class="form-control item-img" id="logo_dashboard" data-id="logo_dashboard" name="logo_dashboard">
						</div>

						<div class="mb-3">
							<label class="form-label">{{__('Dashboard Logo (Dark)')}}</label>
							<input type="file" class="form-control item-img" data-id="logo_dashboard_dark" id="logo_dashboard_dark" name="logo_dashboard_dark">
						</div>

                        <div class="mb-3">
                            <label class="form-label">{{__('Dashboard Logo Collapsed')}}</label>
                            <input type="file" class="form-control item-img" id="logo_collapsed" data-id="logo_collapsed"  name="logo_collapsed">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{__('Dashboard Logo Collapsed (Dark)')}}</label>
                            <input type="file" class="form-control item-img" id="logo_collapsed_dark" data-id="logo_collapsed_dark"  name="logo_collapsed_dark">
                        </div>

					</div>
					<div class="col-md-6">
						<h4 class="mb-3">{{__('Retina Logos (2x) - Optional')}}</h4>

						<div class="mb-3">
							<label class="form-label">{{__('Site Logo')}}</label>
							<input type="file" class="form-control item-img-x2" data-id="logo_2x" id="logo_2x" name="logo_2x">
						</div>

						<div class="mb-3">
							<label class="form-label">{{__('Site Logo (Dark)')}}</label>
							<input type="file" class="form-control item-img-x2" data-id="logo_dark_2x" id="logo_dark_2x" name="logo_dark_2x">
						</div>

						<div class="mb-3">
							<label class="form-label">{{__('Site Logo Sticky')}}</label>
							<input type="file" class="form-control item-img-x2" data-id="logo_sticky_2x" id="logo_sticky_2x" name="logo_sticky_2x">
						</div>

						<div class="mb-3">
							<label class="form-label">{{__('Dashboard Logo')}}</label>
							<input type="file" class="form-control item-img-x2" data-id="logo_dashboard_2x" id="logo_dashboard_2x" name="logo_dashboard_2x">
						</div>

						<div class="mb-3">
							<label class="form-label">{{__('Dashboard Logo (Dark)')}}</label>
							<input type="file" class="form-control item-img-x2" data-id="logo_dashboard_dark_2x" id="logo_dashboard_dark_2x" name="logo_dashboard_dark_2x">
						</div>

                        <div class="mb-3">
                            <label class="form-label">{{__('Dashboard Logo Collapsed')}}</label>
                            <input type="file" class="form-control item-img-x2" data-id="logo_collapsed_2x" id="logo_collapsed_2x" name="logo_collapsed_2x">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{__('Dashboard Logo Collapsed (Dark)')}}</label>
                            <input type="file" class="form-control item-img-x2" data-id="logo_collapsed_dark_2x" id="logo_collapsed_dark_2x" name="logo_collapsed_dark_2x">
                        </div>
					</div>
				</div>

				<h3 class="mb-[25px] text-[20px]">{{__('Seo Settings')}}</h3>
				<div class="row mb-4">
					<div class="col-md-12">
						<div class="mb-4">
							<label class="form-label">{{__('Google Analytics Tracking ID')}} (UA-1xxxxx) {{__('or')}} (G-xxxxxx)</label>
							<input type="text" class="form-control" id="google_analytics_code" name="google_analytics_code" value="{{$setting->google_analytics_code}}">
						</div>
					</div>

					<div class="col-md-12">
						<div class="mb-3">
							<div class="d-flex justify-content-between  align-items-center mb-1">
								<label class="form-label m-0">{{__('Meta Title')}}</label>
								<select class="form-control bg-[#F1EDFF] m-0 py-1" style="width: auto;" name="metaTitleLocal" id="metaTitleLocal" onchange="handleSelectChangeLang('meta_title');">
									@foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
										@if(in_array( $localeCode, explode(',', $settings_two->languages) ))
											<option value="{{$localeCode}}" class="p-0" @if( $settings_two->languages_default === $localeCode) {{'selected'}} @endif>
												<span class="text-[21px] !me-2">{{ country2flag(substr($properties['regional'], strrpos($properties['regional'], '_') + 1)) }}</span> 
												{{ucfirst($properties['native'])}} @if( $settings_two->languages_default === $localeCode)@endif
											</option>
										@endif
									@endforeach
								</select>
							</div>
							<input type="text" class="form-control" id="meta_title" name="meta_title" value="{{$setting->meta_title}}">
						</div>
					</div>

					<div class="col-md-12">
						<div class="mb-3">
							<div class="d-flex justify-content-between  align-items-center mb-1">
								<label class="form-label m-0">{{__('Meta Description')}}</label>
								<select class="form-control bg-[#F1EDFF] m-0 py-1" style="width: auto;" name="metaDescLocal" id="metaDescLocal" onchange="handleSelectChangeLang('meta_desc');">
									@foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
										@if(in_array( $localeCode, explode(',', $settings_two->languages) ))
											<option value="{{$localeCode}}" class="p-0" @if( $settings_two->languages_default === $localeCode) {{'selected'}} @endif>
												<span class="text-[21px] !me-2">{{ country2flag(substr($properties['regional'], strrpos($properties['regional'], '_') + 1)) }}</span> 
												{{ucfirst($properties['native'])}} @if( $settings_two->languages_default === $localeCode)@endif
											</option>
										@endif
									@endforeach
								</select>
							</div>
							<textarea class="form-control" id="meta_description" name="meta_description" rows="5">{{$setting->meta_description}}</textarea>
						</div>
					</div>

					<div class="col-md-12">
						<div class="mb-3">
							<label class="form-label">{{__('Meta Keywords')}}</label>
							<textarea class="form-control" id="meta_keywords" name="meta_keywords" placeholder="{{__('ChatGPT, AI Writer, AI Image Generator, AI Chat')}}" rows="3">{{$setting->meta_keywords}}</textarea>
						</div>
					</div>
				</div>

				<h3 class="mb-[25px] text-[20px]">{{__('Advanced Settings')}}</h3>
				<div class="row mb-4">
					<div class="col-md-12">
						<div class="mb-3">
							<label class="form-label">
								{{__('Code before </head> (Dashboard)')}}
								<x-info-tooltip text="{{__('Only accepts javascript code wrapped with <script> tags and HTML markup that is valid inside the </head> tag.')}}" />
							</label>
							<textarea class="form-control" id="dashboard_code_before_head" name="dashboard_code_before_head">{{$setting->dashboard_code_before_head}}</textarea>
						</div>
					</div>

					<div class="col-md-12">
						<div class="mb-3">
							<label class="form-label">
								{{__('Code before </body> (Dashboard)')}}
								<x-info-tooltip text="{{__('Only accepts javascript code wrapped with <script> tags and HTML markup that is valid inside the </body> tag.')}}" />
							</label>
							<textarea class="form-control" id="dashboard_code_before_body" name="dashboard_code_before_body">{{$setting->dashboard_code_before_body}}</textarea>
						</div>
					</div>
				</div>

				<h3 class="mb-[25px] text-[20px]">{{__('Manage the Features')}}</h3>
				<div class="row mb-4">
					<div class="mb-3">
						<div class="form-label">{{ __('Manage the features you want to activate for users.') }}</div>
						<label class="form-check form-switch">
							<input class="form-check-input" type="checkbox" id="feature_ai_writer" {{ $setting->feature_ai_writer ? 'checked' : '' }}>
							<span class="form-check-label">{{ __('AI Writer') }}</span>
						</label>
						<label class="form-check form-switch">
							<input class="form-check-input" type="checkbox" id="feature_ai_image" {{ $setting->feature_ai_image ? 'checked' : '' }}>
							<span class="form-check-label">{{ __('AI Image') }}</span>
						</label>
						<label class="form-check form-switch">
							<input class="form-check-input" type="checkbox" id="feature_ai_chat" {{ $setting->feature_ai_chat ? 'checked' : '' }}>
							<span class="form-check-label">{{ __('AI Chat') }}</span>
						</label>
						<label class="form-check form-switch">
							<input class="form-check-input" type="checkbox" id="feature_ai_code" {{ $setting->feature_ai_code ? 'checked' : '' }}>
							<span class="form-check-label">{{ __('AI Code') }}</span>
						</label>
						<label class="form-check form-switch">
							<input class="form-check-input" type="checkbox" id="feature_ai_speech_to_text" {{ $setting->feature_ai_speech_to_text ? 'checked' : '' }}>
							<span class="form-check-label">{{ __('AI Speech to Text') }}</span>
						</label>
						<label class="form-check form-switch">
							<input class="form-check-input" type="checkbox" id="feature_ai_voiceover" {{ $setting->feature_ai_voiceover ? 'checked' : '' }}>
							<span class="form-check-label">{{ __('AI Voiceover') }}</span>
						</label>
						<label class="form-check form-switch">
							<input class="form-check-input" type="checkbox" id="feature_affilates" {{ $setting->feature_affilates ? 'checked' : '' }}>
							<span class="form-check-label">{{ __('Affilates') }}</span>
						</label>
						<label class="form-check form-switch">
							<input class="form-check-input" type="checkbox" id="feature_ai_article_wizard" {{ $setting->feature_ai_article_wizard ? 'checked' : '' }}>
							<span class="form-check-label">{{ __('Article Wizard') }}</span>
						</label>
					</div>
				</div>

				<button form="settings_form" id="settings_button" class="btn btn-primary w-100">
					{{__('Save')}}
				</button>
			</form>
		</div>
	</div>
</div>


<div class="modal fade" id="cropImagePop" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
	  	<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body text-center">
				<div id="upload-demo" class="center-block"></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-bs-dismiss="modal">{{__('Close')}}</button>
				<button type="button" id="cropImageBtn" class="btn btn-primary">{{__('Crop')}}</button>
			</div>
	  	</div>
	</div>
</div>

@endsection
@section('script')
    <script src="/assets/js/panel/settings.js"></script>
	<script src="/assets/libs/ace/src-min-noconflict/ace.js" type="text/javascript" charset="utf-8"></script>
    <script src="https://foliotek.github.io/Croppie/croppie.js"></script>

	<style type="text/css" media="screen">
		.ace_editor{
			min-height: 200px;
		}
	</style>
	<script>
        var dashboard_code_before_head = ace.edit("dashboard_code_before_head");
        dashboard_code_before_head.session.setMode("ace/mode/html");

        var dashboard_code_before_body = ace.edit("dashboard_code_before_body");
        dashboard_code_before_body.session.setMode("ace/mode/html");
	</script>
	<script>
		function handleSelectChangeLang(type) {
			var selectElement = type === "meta_title" ? document.getElementById("metaTitleLocal") : document.getElementById("metaDescLocal");
			var selectedOption = selectElement.options[selectElement.selectedIndex];
			var lang = selectedOption.value;

			$.ajax({
				type: 'POST',
				url: "/dashboard/admin/settings/get-meta-content", 
				data: { type: type, lang: lang },
				success: function(response) {
					var content = response.content;
					var inputId = response.type === "meta_title" ? "meta_title" : "meta_description";
					if(content !== null){
						$("#" + inputId).val(content);
					}
					else{
						$("#" + inputId).val('');
					}
				},
				error: function ( data ) {
					var err = data.responseJSON.errors;
					$.each( err, function ( index, value ) {
						toastr.error( value );
					} );
				}
			});
		}
	</script>

	<script>
		var $uploadCrop, tempFilename, rawImg, imageId;
		var viewportWidth = 160; // Default width
   		var viewportHeight = 70; // Default height
		
		function readFile(input) {
			if (input.files && input.files[0]) {
				var reader = new FileReader();
				reader.onload = function (e) {
					$('.upload-demo').addClass('ready');
					$('#cropImagePop').modal('show');
					rawImg = e.target.result;
				}
				reader.readAsDataURL(input.files[0]);
			}
			else {
				swal("Sorry - you're browser doesn't support the FileReader API");
			}
		}

		$uploadCrop = $('#upload-demo').croppie({
			viewport: {
				width: viewportWidth,
				height: viewportHeight,
			},
			enforceBoundary: false,
			enableExif: true
		});
 
		$('#cropImagePop').on('shown.bs.modal', function(){
			$uploadCrop.croppie('bind', {
				url: rawImg
			}).then(function(){
				console.log('jQuery bind complete');
			});
		});

		$('.item-img, .item-img-x2').on('change', function () { 
			if ($(this).hasClass('item-img-x2')) {
            	viewportWidth = 320; 
            	viewportHeight = 140; 
				$uploadCrop.croppie('destroy'); // Destroy the existing croppie instance
				$uploadCrop = $('#upload-demo').croppie({ // Recreate the croppie instance with new dimensions
					viewport: {
						width: viewportWidth,
						height: viewportHeight,
					},
					enforceBoundary: false,
					enableExif: true
				});
			} else {
				viewportWidth = 160;
				viewportHeight = 70;
				$uploadCrop.croppie('destroy'); // Destroy the existing croppie instance
				$uploadCrop = $('#upload-demo').croppie({ // Recreate the croppie instance with default dimensions
					viewport: {
						width: viewportWidth,
						height: viewportHeight,
					},
					enforceBoundary: false,
					enableExif: true
				});
			}

			imageId = $(this).data('id'); 
			tempFilename = $(this).val();
			$('#cancelCropBtn').data('id', imageId); 
			readFile(this); 
		});

		$('#cropImageBtn').on('click', function (ev) {
			$uploadCrop.croppie('result', {
				type: 'blob',
				size: {width: viewportWidth, height: viewportHeight}
			}).then(function (resp) {
				var newInput = document.createElement('input');
				newInput.type = 'file';
				newInput.className = 'form-control item-img';
				newInput.setAttribute('data-id', imageId);
				newInput.id = imageId;
				newInput.name = imageId;
				var file = new File([resp], 'cropped_image.png', { type: 'image/png' });
				let container = new DataTransfer(); 
				container.items.add(file);
				newInput.files = container.files;
				$('#'+imageId).replaceWith(newInput);
				$('#cropImagePop').modal('hide');
			});
		});
	</script>	
@endsection
