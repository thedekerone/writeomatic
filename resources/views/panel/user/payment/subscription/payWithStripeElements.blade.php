@extends('panel.layout.app')
@section('title', __('Subscription Payment'))

@section('additional_css')
<style>

#payment-form {
  width: 30vw;
  min-width: 500px;
  align-self: center;
  box-shadow: 0px 0px 0px 0.5px rgba(50, 50, 93, 0.1),
    0px 2px 5px 0px rgba(50, 50, 93, 0.1), 0px 1px 1.5px 0px rgba(0, 0, 0, 0.07);
  border-radius: 7px;
  padding: 40px;
}

.hidden {
  display: none;
}

#payment-message {
  color: rgb(105, 115, 134);
  font-size: 16px;
  line-height: 20px;
  padding-top: 12px;
  text-align: center;
}

#payment-element {
  margin-bottom: 24px;
}

/* Buttons and links */
button {
  background: #5469d4;
  font-family: Arial, sans-serif;
  color: #ffffff;
  border-radius: 4px;
  border: 0;
  padding: 12px 16px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  display: block;
  transition: all 0.2s ease;
  box-shadow: 0px 4px 5.5px 0px rgba(0, 0, 0, 0.07);
  width: 100%;
}
button:hover {
  filter: contrast(115%);
}
button:disabled {
  opacity: 0.5;
  cursor: default;
}

/* spinner/processing state, errors */
.spinner,
.spinner:before,
.spinner:after {
  border-radius: 50%;
}
.spinner {
  color: #ffffff;
  font-size: 22px;
  text-indent: -99999px;
  margin: 0px auto;
  position: relative;
  width: 20px;
  height: 20px;
  box-shadow: inset 0 0 0 2px;
  -webkit-transform: translateZ(0);
  -ms-transform: translateZ(0);
  transform: translateZ(0);
}
.spinner:before,
.spinner:after {
  position: absolute;
  content: "";
}
.spinner:before {
  width: 10.4px;
  height: 20.4px;
  background: #5469d4;
  border-radius: 20.4px 0 0 20.4px;
  top: -0.2px;
  left: -0.2px;
  -webkit-transform-origin: 10.4px 10.2px;
  transform-origin: 10.4px 10.2px;
  -webkit-animation: loading 2s infinite ease 1.5s;
  animation: loading 2s infinite ease 1.5s;
}
.spinner:after {
  width: 10.4px;
  height: 10.2px;
  background: #5469d4;
  border-radius: 0 10.2px 10.2px 0;
  top: -0.1px;
  left: 10.2px;
  -webkit-transform-origin: 0px 10.2px;
  transform-origin: 0px 10.2px;
  -webkit-animation: loading 2s infinite ease;
  animation: loading 2s infinite ease;
}

@-webkit-keyframes loading {
  0% {
    -webkit-transform: rotate(0deg);
    transform: rotate(0deg);
  }
  100% {
    -webkit-transform: rotate(360deg);
    transform: rotate(360deg);
  }
}
@keyframes loading {
  0% {
    -webkit-transform: rotate(0deg);
    transform: rotate(0deg);
  }
  100% {
    -webkit-transform: rotate(360deg);
    transform: rotate(360deg);
  }
}

@media only screen and (max-width: 600px) {
  form {
    width: 80vw;
    min-width: initial;
  }
}
</style>
@endsection

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
                    {{__('Subscription Payment')}}
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

            <div class="col-sm-8 col-lg-8">
				<form id="payment-form" action="{{ route('dashboard.user.payment.stripeSubscribePay') }}" method="post" >
					@csrf
					<input type="hidden" name="plan" id="plan" value="{{ $plan->id }}">
					<input type="hidden" name="payment_method" class="payment-method">
					<div class="row">
						<div class="col-md-12 col-xl-12">

                            <div id="link-authentication-element">
                                <!--Stripe.js injects the Link Authentication Element-->
                            </div>

                            <div id="payment-element">
                                <!--Stripe.js injects the Payment Element-->
                                
                            </div>
                            
                            <button id="submit">
                                <div class="spinner hidden" id="spinner"></div>
                                <span id="button-text">{{__('Pay')}} 
                                @if(currencyShouldDisplayOnRight(currency()->symbol))
                                    {{$plan->price}}{{ currency()->symbol }}
                                @else
                                    {{currency()->symbol}}{{$plan->price}} 
                                @endif
                                {{__('with')}}<img src="/images/payment/stripe.svg" height="29px" alt="Stripe"></span>
                            </button>
                            <div id="payment-message" class="hidden"></div>

						</div>
					</div>
				</form>
        <p></p>
				<p>{{__('By purchase you confirm our')}} <a href="{{ url('/').'/terms' }}">{{__('Terms and Conditions')}}</a> </p>
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
                        @if(currencyShouldDisplayOnRight(currency()->symbol))
                        {{$plan->price}}
                        <small class="inline-flex mb-[0.3em] font-normal text-[0.35em]">{{currency()->symbol}}</small>
                        @else
                        <small class="inline-flex mb-[0.3em] font-normal text-[0.35em]">{{currency()->symbol}}</small>
                        {{$plan->price}}
                        @endif

                        
							<small class="inline-flex mb-[0.3em] font-normal text-[0.35em]">/ {{$plan->frequency}}</small>
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
                @if((int)$plan->total_words >= 0)
                <strong>{{number_format($plan->total_words)}}</strong> {{__('Word Tokens')}}
            @else
                <strong>{{__('Unlimited')}}</strong> {{__('Word Tokens')}}
            @endif
                            </li>
                            <li class="mb-[0.625em]">
								<span class="inline-flex items-center justify-center w-[19px] h-[19px] mr-1 bg-[rgba(28,166,133,0.15)] text-green-500 rounded-xl align-middle">
									<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
								</span>
                @if((int)$plan->total_images >= 0)
                <strong>{{number_format($plan->total_images)}}</strong> {{__('Image Tokens')}}
            @else
                <strong>{{__('Unlimited')}}</strong> {{__('Image Tokens')}}
            @endif
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
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        (() => {
            "use strict";

            const stripe = Stripe("{{ $gateway->mode == 'live' ? $gateway->live_client_id : $gateway->sandbox_client_id }}");

            let elements;

            initialize();
            if("{{ $paymentIntent['client_secret'] }}".startsWith("set") == false){
            checkStatus();
            }

            document
            .querySelector("#payment-form")
            .addEventListener("submit", handleSubmit);

            let emailAddress = "{{ $email }}";
            
            async function initialize() {

              const clientSecret = {'clientSecret': "{{ $paymentIntent['client_secret'] }}"};  

              if("{{ $paymentIntent['client_secret'] }}".startsWith("set") == true){
                var options = {
                  mode: 'setup',
                  curreny: "{{ $paymentIntent['currency'] }}",
                  amount: "{{ $paymentIntent['amount'] }}",
                };
              }else{
                var options = {
                  mode: 'subscription',
                  curreny: "{{ $paymentIntent['currency'] }}",
                  amount: "{{ $paymentIntent['amount'] }}",
                };
              }

              elements = stripe.elements(clientSecret);

              const linkAuthenticationElement = elements.create("linkAuthentication");
              linkAuthenticationElement.mount("#link-authentication-element");

              const paymentElementOptions = {
                  layout: {
                      type: 'accordion',
                      defaultCollapsed: false,
                      radios: true,
                      spacedAccordionItems: false
                  },
              };

              const paymentElement = elements.create("payment", paymentElementOptions);
              paymentElement.mount("#payment-element");

            }

            async function handleSubmit(e) {
              e.preventDefault();
              setLoading(true);

              const secret = "{{ $paymentIntent['client_secret'] }}";

              var error;

              if(secret.startsWith("set") == false){

                error = await stripe.confirmPayment({
                    elements,
                    confirmParams: {
                    return_url: "{{ route('dashboard.user.payment.stripeSubscribePay') }}",
                    receipt_email: emailAddress,
                    },
                });

              }else{
                var paymentForm = document.getElementById('payment-form');
                const receiptEmailField = paymentForm.querySelector('[name="receipt_email"]');
                if (receiptEmailField) {
                    paymentForm.removeChild(receiptEmailField);
                }else{
                  const receiptEmailField = document.getElementById('receipt_email');
                  if (receiptEmailField) {
                    paymentForm.removeChild(receiptEmailField);
                  }
                }

                error = await stripe.confirmSetup({
                    elements,
                    confirmParams: {
                    return_url: "{{ route('dashboard.user.payment.stripeSubscribePay') }}",
                    },
                });
              }

              if (error.type === "card_error" || error.type === "validation_error") {
                  showMessage(error.message);
              } else {
                  showMessage("An unexpected error occurred.");
              }

              setLoading(false);
            }


            async function checkStatus() {

                const clientSecret = "{{ $paymentIntent['client_secret'] }}"; 

                if (!clientSecret) {
                    return;
                }

                const { paymentIntent } = await stripe.retrievePaymentIntent(clientSecret);

                switch (paymentIntent.status) {
                    case "succeeded":
                    showMessage("Payment succeeded!");
                    break;
                    case "processing":
                    showMessage("Your payment is processing.");
                    break;
                    case "requires_payment_method":
                    showMessage("Your payment was not successful, please try again.");
                    break;
                    default:
                    showMessage("Something went wrong.");
                    break;
                }
            }

            // ------- UI helpers -------

            function showMessage(messageText) {
            const messageContainer = document.querySelector("#payment-message");

            messageContainer.classList.remove("hidden");
            messageContainer.textContent = messageText;

            setTimeout(function () {
                messageContainer.classList.add("hidden");
                messageContainer.textContent = "";
            }, 4000);
            }

            // Show a spinner on payment submission
            function setLoading(isLoading) {
            if (isLoading) {
                // Disable the button and show a spinner
                document.querySelector("#submit").disabled = true;
                document.querySelector("#spinner").classList.remove("hidden");
                document.querySelector("#button-text").classList.add("hidden");
            } else {
                document.querySelector("#submit").disabled = false;
                document.querySelector("#spinner").classList.add("hidden");
                document.querySelector("#button-text").classList.remove("hidden");
            }
            }

        })();
    </script>
@endsection
