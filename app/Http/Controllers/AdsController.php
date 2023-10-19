<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ad;
use DataTables;

class AdsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Ad::all();
            return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('actions', function($row){
                $actionBtn = '<div>  
                                        <a class="btn w-[36px] h-[36px] p-0 border hover:bg-[var(--tblr-primary)] hover:text-white edit-button" href="'. route("dashboard.admin.ads.edit", $row["id"] ). '">
											<svg width="13" height="12" viewBox="0 0 16 15" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
												<path d="M9.3125 2.55064L12.8125 5.94302M11.5 12.3038H15M4.5 14L13.6875 5.09498C13.9173 4.87223 14.0996 4.60779 14.224 4.31676C14.3484 4.02572 14.4124 3.71379 14.4124 3.39878C14.4124 3.08377 14.3484 2.77184 14.224 2.48081C14.0996 2.18977 13.9173 1.92533 13.6875 1.70259C13.4577 1.47984 13.1849 1.30315 12.8846 1.1826C12.5843 1.06205 12.2625 1 11.9375 1C11.6125 1 11.2907 1.06205 10.9904 1.1826C10.6901 1.30315 10.4173 1.47984 10.1875 1.70259L1 10.6076V14H4.5Z" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
											</svg>
										</a>                                          
                                </div>';
                return $actionBtn;
            })
            ->addColumn('updated-on', function($row){
                $created_on = '<span>'.date_format($row["updated_at"], 'd M Y H:i A').'</span>';
                return $created_on;
            })
            ->addColumn('custom-type', function($row){
                $type = '<span class="font-weight-bold">'.$row["type"].'</span>';
                return $type;
            })
            ->addColumn('custom-status', function($row){
                $status = ($row['status']) ? 'Activated' : 'Deactivated';
                $custom_status = '<span class="cell-box  adsense-'.strtolower($status).'">'. $status .'</span>';
                return $custom_status;
            })
            ->rawColumns(['actions', 'custom-status', 'updated-on', 'custom-type'])
            ->make(true);
        }
        return view('panel.admin.adsense.index');
    }

    public function store(Request $request)
    {
        request()->validate([
            'code' => 'required',
        ]);
        
        $new_ad = new Ad();
        $new_ad->code =  request('code');
        $new_ad->status = request('status') == "on"? 1 : 0;
        $new_ad->save();

        return redirect()->route('dashboard.admin.ads.index')->with(['message' => __('Ad created successfully.'), 'type' => 'success']);
    }

    public function edit(Ad $id)
    {
        return view('panel.admin.adsense.edit', compact('id'));
    }

    public function update(Request $request, $id)
    {
        request()->validate([
            'code' => 'required',
        ]);

        $ads = Ad::where('id', $id)->firstOrFail();
        $ads->code = request('code');
        $ads->status = request('status') == "on"? 1 : 0;
        $ads->save();    

        return redirect()->route('dashboard.admin.ads.index')->with(['message' => __('Ad updated successfully.'), 'type' => 'success']);
    }

    public function destroy($id)
    {
        $ads = Ad::where('id', $id)->firstOrFail();
        $ads->delete();
        return redirect()->route('dashboard.admin.ads.index')->with(['message' => __('Ad deleted successfully.'), 'type' => 'success']);
    }
}
