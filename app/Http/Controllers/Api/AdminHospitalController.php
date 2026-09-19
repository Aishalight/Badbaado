<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreHospitalRequest;
use App\Http\Requests\Api\UpdateHospitalRequest;
use App\Http\Resources\Api\HospitalResource;
use App\Models\Hospital;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;

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

        if ($request->filled('admin_name') && $request->filled('admin_email') && $request->filled('admin_password')) {
            $hospitalAdminRole = Role::where('slug', 'hospital_admin')->firstOrFail();

            User::create([
                'name' => $request->validated('admin_name'),
                'email' => $request->validated('admin_email'),
                'password' => Hash::make($request->validated('admin_password')),
                'title' => 'Hospital Administrator',
                'hospital_id' => $hospital->id,
                'role_id' => $hospitalAdminRole->id,
                'is_active' => true,
            ]);
        }

        $this->auditLogger->record($request->user(), 'hospital_created', $hospital, [
            'code' => $hospital->code,
        ]);

        return new HospitalResource($hospital);
    }

    public function destroy(Hospital $hospital): JsonResponse
    {
        $this->auditLogger->record(request()->user(), 'hospital_deleted', $hospital, ['code' => $hospital->code]);
        $hospital->delete();

        return response()->json(['message' => 'Hospital deleted successfully']);
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
