<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreHospitalRequest;
use App\Http\Requests\Api\UpdateHospitalRequest;
use App\Http\Resources\Api\HospitalResource;
use App\Models\Hospital;
use App\Models\Referral;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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
        $hospital = Hospital::create($this->hospitalAttributes($request));

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
        $referralCount = Referral::where('referring_hospital_id', $hospital->id)
            ->orWhere('receiving_hospital_id', $hospital->id)
            ->count();

        if ($referralCount > 0) {
            abort(422, 'This hospital has referral history and cannot be deleted. Deactivate it instead.');
        }

        $this->auditLogger->record(request()->user(), 'hospital_deleted', $hospital, ['code' => $hospital->code]);

        if ($hospital->logo_path !== null) {
            Storage::disk('public')->delete($hospital->logo_path);
        }

        $hospital->delete();

        return response()->json(['message' => 'Hospital deleted successfully']);
    }

    public function update(UpdateHospitalRequest $request, Hospital $hospital): HospitalResource
    {
        $hospital->update($this->hospitalAttributes($request, $hospital));

        $this->auditLogger->record($request->user(), 'hospital_updated', $hospital, [
            'fields' => array_keys($request->validated()),
        ]);

        return new HospitalResource($hospital->fresh());
    }

    /**
     * Hospital columns safe to mass-assign, including a freshly uploaded logo.
     *
     * @return array<string, mixed>
     */
    private function hospitalAttributes(StoreHospitalRequest|UpdateHospitalRequest $request, ?Hospital $hospital = null): array
    {
        $attributes = $request->safe()->except(['logo', 'admin_name', 'admin_email', 'admin_password']);

        $logo = $request->file('logo');

        if ($logo instanceof UploadedFile) {
            if ($hospital?->logo_path !== null) {
                Storage::disk('public')->delete($hospital->logo_path);
            }

            $attributes['logo_path'] = $logo->store('hospital-logos', 'public') ?: null;
        }

        return $attributes;
    }
}
