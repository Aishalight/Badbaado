<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreHospitalRequest;
use App\Http\Requests\Api\UpdateHospitalRequest;
use App\Http\Resources\Api\HospitalResource;
use App\Models\Hospital;
use App\Services\AuditLogger;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdminHospitalController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function index(): AnonymousResourceCollection
    {
        return HospitalResource::collection(
            Hospital::query()
                ->withCount('users')
                ->withCount('outgoingReferrals')
                ->orderByDesc('is_active')
                ->orderBy('name')
                ->get()
        );
    }

    public function store(StoreHospitalRequest $request): HospitalResource
    {
        $hospital = Hospital::create($request->validated());

        $this->auditLogger->record($request->user(), 'hospital_created', $hospital, [
            'code' => $hospital->code,
        ]);

        return new HospitalResource($hospital);
    }

    public function update(UpdateHospitalRequest $request, Hospital $hospital): HospitalResource
    {
        $hospital->update($request->validated());

        $this->auditLogger->record($request->user(), 'hospital_updated', $hospital, [
            'fields' => array_keys($request->validated()),
        ]);

        return new HospitalResource($hospital->fresh());
    }
}
