<?php

namespace App\Http\Controllers;

use App\Models\ProviderApplication;
use Illuminate\View\View;

class PendingVerificationController extends Controller
{
    public function __invoke(): View
    {
        $application = ProviderApplication::query()
            ->where('user_id', auth()->user()->getKey())
            ->latest('id')
            ->first();

        return view('pending', [
            'application' => $application,
            'user' => auth()->user(),
        ]);
    }
}
