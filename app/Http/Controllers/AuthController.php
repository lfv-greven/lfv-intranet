<?php

namespace App\Http\Controllers;

use App\Auth\VereinsfliegerUserProvider;
use App\External\Vereinsflieger;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function vfLogin(Request $request)
    {
        $token = $request->get('accesstoken');
        $vf = app()->make(Vereinsflieger::class);
        $vfUser = $vf->IframeLogin($token);
        abort_if($vfUser == null, 403);

        $user = VereinsfliegerUserProvider::transformVfUser(
            $vfUser,
            $token,
        );

        return response()->json($user);
    }
}
