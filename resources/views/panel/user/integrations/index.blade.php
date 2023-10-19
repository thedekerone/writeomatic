@extends('panel.layout.app')
@section('title', 'Integrations')

@section('content')
    <div class="page-header">
        <div class="container-xl">
            <div class="row g-2 items-center">
                <div class="col">
					<a href="{{ LaravelLocalization::localizeUrl(route('dashboard.index')) }}" class="page-pretitle flex items-center">
						<svg class="!me-2 rtl:-scale-x-100" width="8" height="10" viewBox="0 0 6 10" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
							<path d="M4.45536 9.45539C4.52679 9.45539 4.60714 9.41968 4.66071 9.36611L5.10714 8.91968C5.16071 8.86611 5.19643 8.78575 5.19643 8.71432C5.19643 8.64289 5.16071 8.56254 5.10714 8.50896L1.59821 5.00004L5.10714 1.49111C5.16071 1.43753 5.19643 1.35718 5.19643 1.28575C5.19643 1.20539 5.16071 1.13396 5.10714 1.08039L4.66071 0.633963C4.60714 0.580392 4.52679 0.544678 4.45536 0.544678C4.38393 0.544678 4.30357 0.580392 4.25 0.633963L0.0892856 4.79468C0.0357141 4.84825 0 4.92861 0 5.00004C0 5.07146 0.0357141 5.15182 0.0892856 5.20539L4.25 9.36611C4.30357 9.41968 4.38393 9.45539 4.45536 9.45539Z"/>
						</svg>
						{{__('Back to dashboard')}}
					</a>
                    <h2 class="page-title mb-2">
                        {{__('Integrations')}}
                    </h2>
                </div>
            </div>
        </div>
    </div>
     <!-- Page body -->
    <div class="page-body pt-6">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-md-6">
                    <div class="wordpress-integration-box">
                        <img src="{{ asset('assets/img/wordpress.png') }}" alt="Wordpress" class="integration-image">
                        <div class="integration-content">
                            <h2>WordPress Integration</h2>
                            <p>Connect your WordPress account to get started.</p>
                            @if(in_array('WordPress', $integrations))
                                <span id="wp-connected" class="inline-flex items-center !ms-2 text-[var(--tblr-green)] text-[14px] bg-[rgba(var(--tblr-green-rgb),0.15)] px-[5px] py-[3px] rounded-[3px] float-right">
                                   Connected
                                </span>
                                <a href="#" id="disconnect-button" class="disconnect-button">Disconnect</a>
                            @else
                                <a href="#" id="open-popup" class="connect-button">Connect</a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="wordpress-integration-box">
                        <img src="{{ asset('assets/img/shopify.png') }}" alt="Shopify" class="integration-image">
                        <div class="integration-content">
                            <h2>Shopify Integration</h2>
                            <p>Coming Soon</p>
                            @if(in_array('Shopify', $integrations))
                                <span id="wp-connected" class="inline-flex items-center !ms-2 text-[var(--tblr-green)] text-[14px] bg-[rgba(var(--tblr-green-rgb),0.15)] px-[5px] py-[3px] rounded-[3px] float-right">
                                   Connected
                                </span>
                                <a href="#" id="disconnect-button" class="disconnect-button">Disconnect</a>
                            @else
                                <a href="#" id="open-popup" class="connect-button disabled">Connect</a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="wordpress-integration-box">
                        <img src="{{ asset('assets/img/facebook.jpg') }}" alt="Facebook" class="integration-image">
                        <div class="integration-content">
                            <h2>Facebook Integration</h2>
                            <p>Coming Soon</p>
                            @if(in_array('Facebook', $integrations))
                                <span id="wp-connected" class="inline-flex items-center !ms-2 text-[var(--tblr-green)] text-[14px] bg-[rgba(var(--tblr-green-rgb),0.15)] px-[5px] py-[3px] rounded-[3px] float-right">
                                   Connected
                                </span>
                                <a href="#" id="disconnect-button" class="disconnect-button">Disconnect</a>
                            @else
                                <a href="#" id="open-popup" class="connect-button disabled">Connect</a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="wordpress-integration-box">
                        <img src="{{ asset('assets/img/twitter.png') }}" alt="Twitter" class="integration-image">
                        <div class="integration-content">
                            <h2>Twitter Integration</h2>
                            <p>Coming Soon</p>
                            @if(in_array('Twitter', $integrations))
                                <span id="wp-connected" class="inline-flex items-center !ms-2 text-[var(--tblr-green)] text-[14px] bg-[rgba(var(--tblr-green-rgb),0.15)] px-[5px] py-[3px] rounded-[3px] float-right">
                                   Connected
                                </span>
                                <a href="#" id="disconnect-button" class="disconnect-button">Disconnect</a>
                            @else
                                <a href="#" id="open-popup" class="connect-button disabled">Connect</a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="wordpress-integration-box">
                        <img src="{{ asset('assets/img/instagram.png') }}" alt="Instagram" class="integration-image">
                        <div class="integration-content">
                            <h2>Instagram Integration</h2>
                            <p>Coming Soon</p>
                            @if(in_array('Instagram', $integrations))
                                <span id="wp-connected" class="inline-flex items-center !ms-2 text-[var(--tblr-green)] text-[14px] bg-[rgba(var(--tblr-green-rgb),0.15)] px-[5px] py-[3px] rounded-[3px] float-right">
                                   Connected
                                </span>
                                <a href="#" id="disconnect-button" class="disconnect-button">Disconnect</a>
                            @else
                                <a href="#" id="open-popup" class="connect-button disabled">Connect</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="popup" id="wordpress-popup">
        <div class="popup-body col-md-4 col-sm-12">
            <form id="wordpress_integration_form">
                <div class="popup-header">
                    <h2>Wordpress Integration</h2>
                    <span id="close-popup" class="close-popup">&times;</span>
                </div>
                <div class="popup-content">
                    <div class="mb-3 col-xs-12">
                        <label class="form-label">{{__('Website URL')}}</label>
                        <input type="text" class="form-control" id="url" name="url" maxlength="50" required="required">
                    </div>
                    <div class="mb-3 col-xs-12">
                        <label class="form-label">{{__('Username')}} <span class="tooltip-icon" data-tooltip="Wordpress username"><span>?</span></span></label>
                        <input type="text" class="form-control" id="username" name="username" maxlength="50" required="required">
                    </div>
                    <div class="mb-3 col-xs-12">
                        <label class="form-label">{{__('Password')}} <span class="tooltip-icon" data-tooltip="Application password"><span>?</span></span></label>
                        <input type="password" class="form-control" id="password" name="password" maxlength="100" required="required">
                    </div>
                </div>
                <div class="popup-footer">
                    <button id="integration_button" class="btn btn-primary w-100 py-[0.75em] flex items-center group" type="button">
    					<span class="hidden group-[.lqd-form-submitting]:inline-flex">{{__('Please wait...')}}</span>
    					<span class="group-[.lqd-form-submitting]:hidden">{{__('Submit')}}</span>
    				</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script src="/assets/js/panel/integrations.js"></script>
@endsection