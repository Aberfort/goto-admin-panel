<?php

namespace App\Http\Controllers;

class ConfigController extends Controller
{
    /**
     * Public, unauthenticated - lets the frontend know at runtime whether
     * to show the register flow, without needing a rebuild to flip it.
     */
    public function index()
    {
        return [
            'registration_enabled' => (bool) config('features.registration_enabled'),
        ];
    }
}
