<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreMessageRequest;
use App\Http\Resources\Api\MessageResource;
use App\Models\Message;
use App\Models\Referral;
use App\Services\AuditLogger;
use App\Services\ReferralNotifier;
use App\Support\AuditActions;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly ReferralNotifier $referralNotifier,
    ) {}

    public function index(Request $request, Referral $referral): AnonymousResourceCollection
    {
        $this->authorize('view', $referral);

        $afterId = $request->integer('after_id');
        $query = $referral->messages()->with('sender:id,name,title');

        if ($afterId > 0) {
            return MessageResource::collection(
                $query->where('id', '>', $afterId)->oldest()->limit(50)->get()
            );
        }

        return MessageResource::collection($query->latest()->paginate(50));
    }

    public function store(StoreMessageRequest $request, Referral $referral): MessageResource
    {
        $this->authorize('view', $referral);

        $message = DB::transaction(function () use ($request, $referral) {
            $message = Message::create([
                'referral_id' => $referral->id,
                'sender_user_id' => $request->user()->id,
                'body' => $request->validated('body'),
            ]);

            $this->auditLogger->record($request->user(), AuditActions::MESSAGE_SENT, $message, [
                'referral_id' => $referral->id,
            ]);
            $this->referralNotifier->notifyOnMessage($referral, $request->user());

            return $message;
        });

        return new MessageResource($message->load('sender:id,name,title'));
    }
}
