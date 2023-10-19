@extends('panel.layout.app')
@section('title', __('Google Adsense'))

@section('additional_css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Data Table CSS -->
	<link href="{{URL::asset('assets/plugins/datatable/datatables.min.css')}}" rel="stylesheet" />
    <style>
        .cell-box {
        border-radius: 5px;
        padding: 3px 10px;
        color: #fff;
        font-weight: 700;
        }
        .adsense-deactivated{
        background: #FFE2E5;
        color: #ff0000;
        font-size: 12px;
        }
        .adsense-activated{
        background: rgba(0, 188, 126, 0.1);
        color: #00bc7e;
        font-size: 12px;
        }
        .edit-action-button:hover, .edit-action-button:focus {
        background: #FFF4DE;
        color: #FFA800;
        }
        .table-action-buttons {
        background: #f5f9fc;
        border: 1px solid white;
        border-radius: 0.42rem;
        line-height: 2.2;
        font-size: 14px;
        color: #67748E;
        width: 33px;
        height: 33px;
        text-align: center;
        transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, -webkit-text-decoration-color;
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        transition-duration: 0.15s;
        }


    </style>
@endsection

@section('content')
    <div class="page-header">
        <div class="container-xl">
            <div class="row g-2 items-center">
                <div class="col">
                    <div class="hstack gap-1">
                        <a href="{{ LaravelLocalization::localizeUrl( route('dashboard.index') ) }}" class="page-pretitle flex items-center">
                            <svg class="!me-2 rtl:-scale-x-100" width="8" height="10" viewBox="0 0 6 10" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.45536 9.45539C4.52679 9.45539 4.60714 9.41968 4.66071 9.36611L5.10714 8.91968C5.16071 8.86611 5.19643 8.78575 5.19643 8.71432C5.19643 8.64289 5.16071 8.56254 5.10714 8.50896L1.59821 5.00004L5.10714 1.49111C5.16071 1.43753 5.19643 1.35718 5.19643 1.28575C5.19643 1.20539 5.16071 1.13396 5.10714 1.08039L4.66071 0.633963C4.60714 0.580392 4.52679 0.544678 4.45536 0.544678C4.38393 0.544678 4.30357 0.580392 4.25 0.633963L0.0892856 4.79468C0.0357141 4.84825 0 4.92861 0 5.00004C0 5.07146 0.0357141 5.15182 0.0892856 5.20539L4.25 9.36611C4.30357 9.41968 4.38393 9.45539 4.45536 9.45539Z"/>
                            </svg>
                            {{__('Back to dashboard')}}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Page body -->
    <div class="page-body pt-6">
        <div class="container-xl">
			<div class="row">
				<div class="col-md-12 mx-auto">
					<div class="card border-0">
                        <div class="card-header">
                            <h3 class="card-title">{{ __('Google Adsense List') }} <x-info-tooltip text="{{__('Activate header section to view ads')}}" /></h3>
                        </div>
                        <div class="card-body pt-2">
                            <table id='adsTable' class='table' width='100%'>
                                    <thead>
                                        <tr>									
                                            <th width="20%">{{ __('Adsense Type') }}</th>	
                                            <th width="20%">{{ __('Status') }}</th>	
                                            <th width="20%">{{ __('Updated On') }}</th>																		
                                            <th width="5%">{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                            </table>
                        </div>
                    </div>

				</div>
			</div>
        </div>
    </div>
@endsection

@section('script')
    <!-- Data Tables JS -->
	<script src="{{URL::asset('assets/plugins/datatable/datatables.min.js')}}"></script>
	<script type="text/javascript">
		$(function () {
			"use strict";
			var table = $('#adsTable').DataTable({
				"lengthMenu": [[25, 50, 100, -1], [25, 50, 100, "All"]],
				responsive: true,
				colReorder: true,
				language: {
					"emptyTable": "<div>No ads created yet</div>",
					search: "<i class='fa fa-search search-icon'></i>",
					lengthMenu: '_MENU_ ',
					paginate : {
						first    : '<i class="fa fa-angle-double-left"></i>',
						last     : '<i class="fa fa-angle-double-right"></i>',
						previous : '<i class="fa fa-angle-left"></i>',
						next     : '<i class="fa fa-angle-right"></i>'
					}
				},
				pagingType : 'full_numbers',
				processing: true,
				serverSide: true,
				ajax: "{{ route('dashboard.admin.ads.index') }}",
				columns: [
					{
						data: 'custom-type',
						name: 'custom-type',
						orderable: false,
						searchable: true
					},					
					{
						data: 'custom-status',
						name: 'custom-status',
						orderable: true,
						searchable: true
					},
					{
						data: 'updated-on',
						name: 'updated-on',
						orderable: false,
						searchable: true
					},									
					{
						data: 'actions',
						name: 'actions',
						orderable: false,
						searchable: false
					},
				]
			});

		});
	</script>
@endsection

