<?php

namespace App\Http\Controllers;

use App\Auth\VereinsfliegerUserProvider;
use App\Exceptions\VereinsfliegerDeferred;
use App\Exceptions\VereinsfliegerTransportException;
use App\Services\VereinsfliegerClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class AuthController extends Controller
{
    public function vfIframe(Request $request)
    {
        $token = $request->input('accesstoken');

        if (! is_string($token) || blank($token)) {
            abort(400, 'Missing access token');
        }

        try {
            $vfUser = app(VereinsfliegerClient::class)->loginIframe($token);
        } catch (VereinsfliegerDeferred|VereinsfliegerTransportException) {
            return response()->view('auth.vf-iframe', [
                'loginUrl' => null,
                'errorMessage' => 'Die Anmeldung ist vorübergehend nicht verfügbar. Bitte versuche es in wenigen Minuten erneut.',
            ], 503);
        }

        $loginUrl = url('/');
        if ($vfUser) {
            $user = VereinsfliegerUserProvider::transformVfUser($vfUser);
            $loginUrl = URL::temporarySignedRoute(
                'auth.vf-login',
                now()->addHour(),
                ['uid' => $user->id],
            );
        }

        return view('auth.vf-iframe', compact('loginUrl'));
    }

    public function vfLogin(Request $request)
    {
        abort_unless($request->hasValidSignature(), 403);

        Auth::loginUsingId($request->get('uid'));

        return redirect()->route('home');
    }
}
