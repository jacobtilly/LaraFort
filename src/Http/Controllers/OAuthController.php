<?php

namespace JacobTilly\LaraFort\Http\Controllers;

use Illuminate\Http\Request;

class OAuthController
{
    public function callback(Request $request)
    {
        if (!$request->has('code')) {
            return 'Authorization failed. No code received.';
        }

        return "Code received: <code style='color:red;'>{$request->code}</code><br><br>Paste this into your installation terminal window to continue.";

    }
}
