<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class LoginPageController extends Controller
{
    public function __invoke(): View
    {
        return view('auth.login');
    }
}
