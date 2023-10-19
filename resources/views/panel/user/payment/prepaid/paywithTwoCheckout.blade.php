@extends('panel.layout.app')
@section('title', __('Token Packs'))

@section('content')
<!-- Page header -->
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
                    {{__('Token Packs')}}
                </h2>
            </div>
        </div>
    </div>
</div>


<!-- Page body -->
<div class="page-body pt-6">
    <div class="container-xl">
        @if($exception != null)
            <h2 class="text-danger">{{ $exception }}</h2>
        @else
        <div class="row row-cards">
            <div class="row justify-content-md-center mt-5">
                <div class="col-12">
                  <div class="card">
                    <div class="card-body">
                      <h5 class="card-title">Basic implementation for the 2Pay.js client</h5>
                        
                      <form type="post" id="payment-form" action="{{ route('dashboard.user.payment.twocheckoutPrepaidPay') }}">
                        <div class="form-group">
                          <label for="name" class="label control-label">Name</label>
                          <input type="text" id="name" class="field form-control">
                        </div>
            
                        <div id="card-element">
                          <!-- A TCO IFRAME will be inserted here. -->
                        </div>
                        {{-- <input id="token" name="token" type="hidden" value=""> --}}
                        <button class="btn btn-primary" type="submit">Pay with 2checkout</button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            <div class="col-sm-4 col-lg-4">
                <div class="card card-md w-full bg-[#f3f5f8] text-center border-0 text-heading group-[.theme-dark]/body:!bg-[rgba(255,255,255,0.02)]">
                    @if($plan->is_featured == 1)
                    <div class="ribbon ribbon-top ribbon-bookmark bg-green">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-filled" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z" /></svg>
                    </div>
                    @endif
                    <div class="card-body flex flex-col !p-[45px_50px_50px] text-center">
                        <div class="text-heading flex items-end justify-center mt-0 mb-[15px] w-full text-[60px] leading-none">
							<small class="inline-flex mb-[0.3em] font-normal text-[0.35em]">{{currency()->symbol}}</small>
							{{$plan->price}}
							<small class="inline-flex mb-[0.3em] font-normal text-[0.35em]">/ {{__('One time')}}</small>
						</div>
						<div class="inline-flex mx-auto p-[0.85em_1.2em] bg-white rounded-full font-medium text-[15px] leading-none text-[#2D3136]">{{$plan->name}}</div>

                        <ul class="list-unstyled mt-[35px] text-[15px] mb-[25px]">
                            <li class="mb-[0.625em]">
								<span class="inline-flex items-center justify-center w-[19px] h-[19px] mr-1 bg-[rgba(28,166,133,0.15)] text-green-500 rounded-xl align-middle">
									<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
								</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1 text-success" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                                {{__('Access')}} <strong>{{$plan->plan_type}}</strong> {{__('Templates')}}
                            </li>
                            @foreach(explode(',', $plan->features) as $item)
                            <li class="mb-[0.625em]">
								<span class="inline-flex items-center justify-center w-[19px] h-[19px] mr-1 bg-[rgba(28,166,133,0.15)] text-green-500 rounded-xl align-middle">
									<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
								</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1 text-success" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                                {{$item}}
                            </li>
                            @endforeach

                            <li class="mb-[0.625em]">
								<span class="inline-flex items-center justify-center w-[19px] h-[19px] mr-1 bg-[rgba(28,166,133,0.15)] text-green-500 rounded-xl align-middle">
									<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
								</span>
                                <strong>{{number_format($plan->total_words)}}</strong> {{__('Word Tokens')}}
                            </li>
                            <li class="mb-[0.625em]">
								<span class="inline-flex items-center justify-center w-[19px] h-[19px] mr-1 bg-[rgba(28,166,133,0.15)] text-green-500 rounded-xl align-middle">
									<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
								</span>
                                <strong>{{number_format($plan->total_images)}}</strong> {{__('Image Tokens')}}
                            </li>

                        </ul>
                        <div class="text-center mt-auto">
                            <a class="btn rounded-md p-[1.15em_2.1em] w-full text-[15px] group-[.theme-dark]/body:!bg-[rgba(255,255,255,1)] group-[.theme-dark]/body:!text-[rgba(0,0,0,0.9)]" href="{{ LaravelLocalization::localizeUrl( route('dashboard.user.payment.subscription') ) }}">{{__('Change Plan')}}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
@section('script')
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script type="text/javascript" src="https://2pay-js.2checkout.com/v1/2pay.js"></script>
    <script>
        window.addEventListener('load', function() {
        // Initialize the 2Pay.js client.
        let jsPaymentClient = new TwoPayClient('{!!$merchant_code!!}');
        
        // Create the component that will hold the card fields.
        let component = jsPaymentClient.components.create('card');
        
        // Mount the card fields component in the desired HTML tag. This is where the iframe will be located.
        component.mount('#card-element');

        var myForm = document.getElementById('payment-form');

        // Handle form submission.
        document.getElementById('payment-form').addEventListener('submit', (event) => {
            event.preventDefault();
            
            // Extract the Name field value
            const billingDetails = {
                name: document.querySelector('#name').value
            };

            // Call the generate method using the component as the first parameter
            // and the billing details as the second one
            jsPaymentClient.tokens.generate(component, billingDetails).then((response) => {
                // myForm.token.value = response.token;
                console.log(response.token);
                var formData = new FormData();
                formData.append( 'token', response.token );
                formData.append( 'plan', '{!!$planId!!}' );

                $.ajax( {
                    type: "post",
                    url: "/dashboard/user/payment/twocheckout/prepaidPay",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function ( data ) {
                        console.log(data);
                        toastr.success( data.message );
                    },
                    error: function ( data ) {
                        var err = data.responseJSON.errors;
                        toastr.success( err );
                        
                    }
                } );
            }).catch((error) => {
                console.error(error);
                toastr.error(error);
            });
        });
        });
    </script>
@endsection
