<?php

namespace App\Http\Controllers;

use App\Enums\ReferralStatus;
use App\Models\AuditLog;
use App\Models\Hospital;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ReferralPageController extends Controller
{
    public function index(Request $request): View
    {
        $referrals = Referral::visibleTo(auth()->user())
            ->when($request->string('status')->toString(), fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($request->string('urgency')->toString(), fn (Builder $query, string $urgency) => $query->where('urgency', $urgency))
            ->with([
                'referringHospital',
                'receivingHospital',
                'referringUser:id,name,title',
                'intendedUser:id,name,title,email',
                'assignedTo:id,name,title',
                'coordinator:id,name,title',
                'patient',
            ])
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('referrals.index', [
            'referrals' => $referrals,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Referral::class);

        $me = auth()->user();
        $hospitals = Hospital::query()
            ->where('is_active', true)
            ->when($me->hospital_id, fn (Builder $query) => $query->whereKeyNot($me->hospital_id))
            ->with(['referralStaff' => fn ($query) => $query->get([
                'users.id', 'users.name', 'users.title', 'users.avatar_path',
            ])])
            ->orderBy('name')
            ->get();

        return view('referrals.create', [
            'hospitals' => $hospitals,
            'hospitalDirectory' => $this->hospitalDirectory($hospitals),
        ]);
    }

    /**
     * Destination options for the dependent doctor picker, keyed by hospital.
     *
     * @param  Collection<int, Hospital>  $hospitals
     * @return array<int, array{id: int, name: string, logo_url: string|null, doctors: array<int, array{id: int, name: string, title: string|null, avatar_url: string|null}>}>
     */
    private function hospitalDirectory(Collection $hospitals): array
    {
        return $hospitals->map(fn (Hospital $hospital): array => [
            'id' => $hospital->getKey(),
            'name' => $hospital->name,
            'logo_url' => $hospital->logo_url,
            'doctors' => $hospital->referralStaff->map(fn (User $doctor): array => [
                'id' => $doctor->getKey(),
                'name' => $doctor->name,
                'title' => $doctor->title,
                'avatar_url' => $doctor->avatar_url,
            ])->all(),
        ])->all();
    }

    public function show(Referral $referral): View
    {
        $this->authorize('view', $referral);

        $referral->load([
            'referringHospital',
            'receivingHospital',
            'referringUser:id,name,title,email',
            'intendedUser:id,name,title,email,avatar_path',
            'assignedTo:id,name,title,email',
            'coordinator:id,name,title,email',
            'patient',
            'messages.sender:id,name,title',
            'attachments',
        ]);

        $messages = $referral->messages()->with('sender:id,name,title')->orderByDesc('created_at')->get();
        $timeline = AuditLog::query()
            ->where('entity_type', 'Referral')
            ->where('entity_id', $referral->id)
            ->with('user:id,name,title')
            ->orderBy('created_at')
            ->get();

        return view('referrals.show', [
            'referral' => $referral,
            'messages' => $messages,
            'timeline' => $timeline,
            'actions' => $this->availableActions($referral, auth()->user()),
        ]);
    }

    /**
     * @return array<int, array{target: string, label: string, tone: string}>
     */
    private function availableActions(Referral $referral, User $user): array
    {
        $candidates = [
            'draft' => [
                ['target' => 'sent', 'label' => 'Send referral', 'tone' => 'primary'],
                ['target' => 'cancelled', 'label' => 'Cancel', 'tone' => 'secondary'],
            ],
            'sent' => [
                ['target' => 'received', 'label' => 'Acknowledge', 'tone' => 'primary'],
            ],
            'received' => [
                ['target' => 'under_review', 'label' => 'Start review', 'tone' => 'primary'],
            ],
            'under_review' => [
                ['target' => 'accepted', 'label' => 'Accept referral', 'tone' => 'primary'],
                ['target' => 'rejected', 'label' => 'Decline', 'tone' => 'danger'],
            ],
            'accepted' => [
                ['target' => 'transfer_in_progress', 'label' => 'Transfer in progress', 'tone' => 'primary'],
            ],
            'transfer_in_progress' => [
                ['target' => 'arrived', 'label' => 'Patient arrived', 'tone' => 'orange'],
            ],
            'arrived' => [
                ['target' => 'completed', 'label' => 'Mark completed', 'tone' => 'primary'],
            ],
        ];

        return array_values(array_filter(
            $candidates[$referral->status->value] ?? [],
            fn (array $action): bool => $user->can('transition', [$referral, ReferralStatus::from($action['target'])]),
        ));
    }
}
