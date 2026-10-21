<?php

namespace App\Http\Controllers;

use App\Enums\ReferralStatus;
use App\Http\Requests\Api\StoreMessageRequest;
use App\Http\Requests\Api\StoreReferralRequest;
use App\Models\AuditLog;
use App\Models\Hospital;
use App\Models\Message;
use App\Models\Referral;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\ReferralNotifier;
use App\Services\ReferralWorkflowService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReferralPageController extends Controller
{
    public function __construct(
        private readonly ReferralWorkflowService $workflow,
        private readonly AuditLogger $auditLogger,
        private readonly ReferralNotifier $notifier,
    ) {}

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
            ->hospitals()
            ->where('is_active', true)
            ->when($me->hospital_id, fn (Builder $query) => $query->whereKeyNot($me->hospital_id))
            ->orderBy('name')
            ->get();

        // Independent doctors (private practices) are valid destinations when
        // the referral is addressed to a specific clinician rather than a facility.
        $doctors = User::independentDoctors()
            ->with('hospital')
            ->orderBy('name')
            ->get();

        return view('referrals.create', [
            'hospitals' => $hospitals,
            'doctors' => $doctors,
        ]);
    }

    public function store(StoreReferralRequest $request): RedirectResponse
    {
        try {
            $referral = $this->workflow->createReferral($request->user(), $request->validated());

            return redirect()->route('referrals.show', $referral)
                ->with('status', 'Referral created and sent to the receiving team.');
        } catch (\Throwable $e) {
            return back()->withInput()
                ->withErrors(['referral' => 'Could not create referral: '.$e->getMessage()]);
        }
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

    public function transition(Request $request, Referral $referral): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'string'],
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $target = ReferralStatus::from($request->string('status')->toString());

        $this->authorize('transition', [$referral, $target]);

        try {
            $this->workflow->transition(
                $request->user(),
                $referral,
                $target,
                $request->string('rejection_reason')->toString() ?: null,
            );

            return back()->with('status', 'Referral status updated to '.str_replace('_', ' ', ucfirst($target->value)).'.');
        } catch (\Throwable $e) {
            return back()->withErrors(['transition' => $e->getMessage()]);
        }
    }

    public function storeMessage(StoreMessageRequest $request, Referral $referral): RedirectResponse
    {
        $this->authorize('view', $referral);

        $message = Message::create([
            'referral_id' => $referral->id,
            'sender_user_id' => $request->user()->id,
            'body' => $request->validated('body'),
        ]);

        $this->auditLogger->record($request->user(), 'message_sent', $message, [
            'referral_id' => $referral->id,
        ]);
        $this->notifier->notifyOnMessage($referral, $request->user());

        return back()->with('status', 'Message sent.');
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
