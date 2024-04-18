<?php

namespace App\Http\Controllers;

use App\Auth\VereinsfliegerUserProvider;
use App\External\Vereinsflieger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function vfLogin(Request $request)
    {
        if (auth()->user()) {
            return redirect()->route('home', ['iframe' => true]);
        }

        $token = $request->get('accesstoken');
        $vf = app()->make(Vereinsflieger::class);
        $vfUser = $vf->IframeLogin($token);

        if (! $vfUser) {
            return redirect()->route('home', ['iframe' => true]);
        }

        $user = VereinsfliegerUserProvider::transformVfUser($vfUser);
        Auth::login($user, remember: true);

        return redirect()->route('home', ['iframe' => true]);
    }
}
