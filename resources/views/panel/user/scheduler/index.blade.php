@extends('panel.layout.app')
@section('title', 'My Scheduled Documents')

@section('content')
    <div class="unlock-wrapper" id="unlockWrapper">
        <div class="unlock-content dark:bg-black">
            <h2>Premium feature locked</h2>
            <p>Activate your premium subscription to unlock this feature</p>
            <a class="btn btn-primary" href="{{route('dashboard.user.payment.subscription')}}">
                <svg class="md:me-2 max-lg:w-[20px] max-lg:h-[20px]" width="11" height="15" viewBox="0 0 11 15" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path d="M6.6 0L0 9.375H4.4V15L11 5.625H6.6V0Z" />
                </svg>
                <span class="max-lg:hidden">{{__('Upgrade')}}</span>
            </a>
        </div>
    </div>
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
                        {{__('Scheduled Documents')}}
                    </h2>
                </div>
            </div>
        </div>
    </div>
    <!-- Page body -->
    <div class="page-body pt-6">
        <div class="container-xl">
			<div class="card">
				<div id="table-default" class="card-table table-responsive">
					<table class="table">
						<thead>
						<tr>
							<th>{{__('Type')}}</th>
							<th>{{__('Category')}}</th>
							<th class="max-sm:min-w-[250px]">{{__('Output')}}</th>
							<th>{{__('Platform')}}</th>
							<th>{{__('Date/time')}}</th>
							<th>{{__('Status')}}</th>
							<th class="!text-end">{{__('Actions')}}</th>
						</tr>
						</thead>
						<tbody class="table-tbody align-middle text-heading">
						@foreach($scheduled_docs as $entry)
                            @if($entry->document->generator != null)
                                @if($entry->document->generator->type != 'image')
                                    <tr class="relative transition-colors hover:bg-black hover:bg-opacity-[0.03] dark:hover:bg-white dark:hover:bg-opacity-[0.03]">
                                        <td class="sort-type text-capitalize">
        									<span class="avatar w-[43px] h-[43px] [&_svg]:w-[20px] [&_svg]:h-[20px]" style="background: {{$entry->document->generator->color}}">
        										@if ( $entry->document->generator->image !== 'none' )
                                                    {!! html_entity_decode($entry->document->generator->image) !!}
                                                @endif
        									</span>
                                        </td>
                                        <td class="sort-category">{{$entry->document->generator->title}}</td>
                                        @if($entry->document->generator->type == 'text')
                                            <td class="max-sm:min-w-[250px]">
                                                {{\Illuminate\Support\Str::limit(strip_tags($entry->document->output), 200)}}
                                            </td>
                                        @elseif($entry->document->generator->type == 'audio')
                                            <td class="max-sm:min-w-[250px]">
                                                {!!  \Illuminate\Support\Str::limit($entry->document->output, 200) !!}
                                            </td>
                                        @elseif($entry->document->generator->type == 'code')
                                            <td class="max-sm:min-w-[250px]">
                                                {{\Illuminate\Support\Str::limit(strip_tags($entry->document->output), 200)}}
                                            </td>
                                        @elseif($entry->document->generator->type == 'voiceover')
                                            <td class="max-sm:min-w-[250px] data-audio" data-audio="/uploads/{{$entry->document->output}}">
												<div class="audio-preview"></div>
                                            </td>
                                        @else
                                            <td class="max-sm:min-w-[250px]">
                                                <a href="{{$entry->document->output}}" target="_blank"><img src="{{$entry->document->output}}" class="img-fluid" alt=""></a>
                                            </td>
                                        @endif
                                        <td class="sort-platform">
                                            {{$entry->account->name}}
                                        </td>
                                        <td class="sort-date" data-date="{{strtotime($entry->run_at)}}">
                                            <p class="m-0">{{date("j.n.Y", strtotime($entry->run_at))}}</p>
                                            <p class="m-0 text-muted">{{date("H:i:s", strtotime($entry->run_at))}}</p>
                                        </td>
                                        <td class="sort-status">{{!$entry->is_executed ? 'Scheduled' : 'Published'}}</td>
                                        <td class="!text-end whitespace-nowrap">
                                            <a href="#" id="reschedule_doc" data-account="{{$entry->account->name}}" data-title="{{$entry->document->generator->title}}" data-time="{{$entry->run_at}}" class="btn relative z-10 w-[36px] h-[36px] p-0 border hover:bg-[var(--tblr-primary)] hover:text-white" title="{{__('Edit')}}">
                                                <svg width="13" height="12" viewBox="0 0 16 15" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M9.3125 2.55064L12.8125 5.94302M11.5 12.3038H15M4.5 14L13.6875 5.09498C13.9173 4.87223 14.0996 4.60779 14.224 4.31676C14.3484 4.02572 14.4124 3.71379 14.4124 3.39878C14.4124 3.08377 14.3484 2.77184 14.224 2.48081C14.0996 2.18977 13.9173 1.92533 13.6875 1.70259C13.4577 1.47984 13.1849 1.30315 12.8846 1.1826C12.5843 1.06205 12.2625 1 11.9375 1C11.6125 1 11.2907 1.06205 10.9904 1.1826C10.6901 1.30315 10.4173 1.47984 10.1875 1.70259L1 10.6076V14H4.5Z" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </a>
                                            <a href="{{ LaravelLocalization::localizeUrl( route('dashboard.user.scheduler.delete', $entry->id)) }}" onclick="return confirm('Are you sure you want to delete this scheduled document?')" class="btn relative z-10 p-0 border w-[36px] h-[36px] hover:bg-red-600 hover:text-white" title="{{__('Delete')}}">
                                                <svg width="10" height="10" viewBox="0 0 10 10" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M9.08789 1.74609L5.80664 5L9.08789 8.25391L8.26758 9.07422L4.98633 5.82031L1.73242 9.07422L0.912109 8.25391L4.16602 5L0.912109 1.74609L1.73242 0.925781L4.98633 4.17969L8.26758 0.925781L9.08789 1.74609Z"/>
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                @endif
                            @endif
						@endforeach
						</tbody>
					</table>
				</div>
			</div>
        </div>
    </div>

    <div class="popup" id="reschedule_popup">
        <div class="popup-body col-lg-4 col-md-4 col-sm-12">
            <form id="reschedule_form">
                <div class="popup-header">
                    <h2>Update Scheduled Date/time</h2>
                    <span id="close_popup" class="close-popup">&times;</span>
                </div>
                <div class="popup-content">
                    <div class="mb-3 col-xs-12">
                        <label class="form-label">{{__('Selected Account: ')}}<span id="selected_acc"></span></label>
                        <label class="form-label">{{__('Scheduled Document: ')}}<span id="selected_doc"></span></label>
                    </div>
                    <div class="mb-3 col-xs-12">
                        <label class="form-label">{{__('Date/time:')}}</label>
                        <div id="datetimepicker"></div>
                        <label id="rescheduled-time" class="form-label mt-3" style="display:none;">{{__('The document will be re-scheduled for ')}}<span id="datetimevalue"></span></label>
                    </div>
                </div>
                <div class="popup-footer">
                    <button id="reschedule_confirm" class="btn btn-primary w-100 py-[0.75em] flex items-center group" type="button">
    					<span class="hidden group-[.lqd-form-submitting]:inline-flex">{{__('Please wait...')}}</span>
    					<span class="group-[.lqd-form-submitting]:hidden">{{__('Re-schedule')}}</span>
    				</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script src="/assets/libs/flatpickr/flatpickr.js"></script>
    <script>
        const rescheduleBtn = document.getElementById('reschedule_doc');
        if (rescheduleBtn) {
            rescheduleBtn.addEventListener('click', function(e){
                e.preventDefault();
                const popup = document.getElementById("reschedule_popup");
                popup.style.display = "flex";
                const closeButton = document.getElementById("close_popup");
                closeButton.addEventListener("click", function() {
                    popup.style.display = "none";
                });
                document.getElementById("selected_acc").innerText = event.target.getAttribute('data-account');
                document.getElementById("selected_doc").innerText = event.target.getAttribute('data-title');
                const selectedDate = event.target.getAttribute('data-time');
                flatpickr("#datetimepicker", {
                    inline: true,
                    enableTime: true,
                    minDate: "today",
                    defaultDate: selectedDate,
                    onChange: function(selectedDates, dateStr, instance) {
                        if(new Date(dateStr).getTime() !== new Date(selectedDate).getTime()) {
                        document.getElementById("rescheduled-time").style.display = "block";
                        document.getElementById("datetimevalue").innerText = dateStr;
                        } else 
                            document.getElementById("rescheduled-time").style.display = "none";
                    }
                });
            // document.getElementById("reschedule_confirm").addEventListener("click", ConfirmSchedule);
            });
        }
    </script>
@endsection

@push('css')
   <link rel="stylesheet" href="/assets/libs/flatpickr/flatpickr.min.css">
   <style>
        .page-wrapper {
            position: relative;
        }
        .navbar .dropdown-menu {
            z-index: 99999;
        }
        .unlock-wrapper {
            position: absolute;
            top: -10%;
            left: -20%;
            width: 100vw;
            height: 100vh;
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px); 
            background-color: rgba(255, 255, 255, 0.6);
            display: flex;
            text-align: center;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        .unlock-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }
    </style>
@endpush