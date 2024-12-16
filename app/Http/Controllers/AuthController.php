<?php

namespace App\Http\Controllers;

use App\Auth\VereinsfliegerUserProvider;
use App\External\Vereinsflieger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class AuthController extends Controller
{
    public function vfIframe(Request $request)
    {
        $token = $request->get('accesstoken');
        $vf = app()->make(Vereinsflieger::class);
        $vfUser = $vf->IframeLogin($token);

        $loginUrl = url('/');
        if ($vfUser) {
            $user = VereinsfliegerUserProvider::transformVfUser($vfUser);
            $loginUrl = URL::temporarySignedRoute(
                'auth.vf-login',
                now()->addHour(),
                ['uid' => $user->id],
            );
        }

        $vfUrl = 'https://vereinsflieger.de';
        return response()
            ->view('auth.vf-iframe', compact('loginUrl'))
            ->header('X-Frame-Options', "ALLOW-FROM $vfUrl")
            ->header('Content-Security-Policy', "frame-ancestors 'self' $vfUrl");
    }

    public function vfLogin(Request $request)
    {
        abort_unless($request->hasValidSignature(), 403);

        Auth::loginUsingId($request->get('uid'));

        return redirect()->route('home');
    }
}
