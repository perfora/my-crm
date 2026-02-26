<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    public function finans()
    {
        return view('finans');
    }

    public function home(Request $request)
    {
        $userAgent = $request->header('User-Agent');
        $isMobile = preg_match('/(android|iphone|ipad|mobile)/i', $userAgent);

        if ($isMobile) {
            return redirect('/mobile');
        }

        return view('pages.dashboard');
    }

    public function dashboard()
    {
        return view('pages.dashboard');
    }
}
