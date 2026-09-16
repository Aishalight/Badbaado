<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\HospitalResource;
use App\Models\Hospital;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HospitalController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return HospitalResource::collection(Hospital::where('is_active', true)->orderBy('name')->get());
    }
}
