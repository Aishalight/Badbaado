<?php

namespace App\Http\Controllers\Api;

use App\Enums\ReferralStatus;
use App\Exceptions\InvalidReferralTransitionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreReferralRequest;
use App\Http\Requests\Api\TransitionReferralRequest;
use App\Http\Resources\Api\ReferralResource;
use App\Models\Referral;
use App\Services\AuditLogger;
use App\Services\ReferralWorkflowService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReferralController extends Controller
{
    public function __construct(
        private readonly ReferralWorkflowService $workflow,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $user = request()->user();

        $referrals = Referral::query()
            ->when($user->hasRole('system_admin'), function ($query) {
                return $query;
            }, function ($query) use ($user) {
                $query->where(function ($sub) use ($user) {
                    $sub->where('referring_hospital_id', $user->hospital_id)
                        ->orWhere('receiving_hospital_id', $user->hospital_id);
                });
            })
            ->with([
                'referringHospital',
                'receivingHospital',
                'referringUser:id,name,title',
                'coordinator:id,name,title',
                'patient',
            ])
            ->orderByDesc('created_at')
            ->paginate(25);

        return ReferralResource::collection($referrals);
    }

    public function store(StoreReferralRequest $request): ReferralResource
    {
        $referral = $this->workflow->createReferral($request->user(), $request->validated());

        return new ReferralResource($referral->load([
            'referringHospital',
            'receivingHospital',
            'referringUser',
            'patient',
        ]));
    }

    public function show(Referral $referral): ReferralResource
    {
        $this->authorize('view', $referral);

        return new ReferralResource($referral->load([
            'referringHospital',
            'receivingHospital',
            'referringUser:id,name,title,email',
            'coordinator:id,name,title,email',
            'patient',
            'messages.sender:id,name,title',
        ]));
    }

    public function transition(TransitionReferralRequest $request, Referral $referral): ReferralResource
    {
        $this->authorize('transition', [$referral, $request->enum('status', ReferralStatus::class)]);

        try {
            $referral = $this->workflow->transition(
                $request->user(),
                $referral,
                ReferralStatus::from($request->validated('status')),
                $request->validated('rejection_reason'),
            );
        } catch (InvalidReferralTransitionException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return new ReferralResource($referral->load([
            'referringHospital',
            'receivingHospital',
            'referringUser',
            'coordinator',
            'patient',
        ]));
    }
}
