<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\HospitalResource;
use App\Models\Hospital;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HospitalController extends Controller
{
    /**
     * Hospitals that can receive a referral.
     *
     * Independent doctors' private practices are excluded here on purpose:
     * they are reachable through the doctor directory, not as facilities.
     */
    public function index(): AnonymousResourceCollection
    {
        return HospitalResource::collection(
            Hospital::hospitals()->where('is_active', true)->orderBy('name')->get()
        );
    }
}
