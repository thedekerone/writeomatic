<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Integration;

class IntegrationController extends Controller {
    public function index() {
        $integrations = Integration::where('user_id', Auth::id())->pluck('name')->toArray();
        return view('panel.user.integrations.index', compact('integrations'));
    }
    
    public function add(Request $request) {
        $url = "";
        if(strcmp($request->name, "WordPress") === 0) {
            $url = rtrim($request->url, '/') . "/wp-json/wp/v2/posts";
        }
        Integration::create([
            'name' => $request->name,
            'url' => $url,
            'username' => $request->username,
            'password' => encrypt($request->password),
            'user_id' => Auth::id()
        ]);
        return response()->json(['success' => 'Integration successful'], 200);
    }
    
    public function remove($name) {
        $integration = Integration::where('name', $name)->where('user_id', Auth::id())->first();
        $integration->delete();
        return response()->json(['success' => 'WordPress removed successfully'], 200);
    }
}