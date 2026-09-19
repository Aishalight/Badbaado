<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreMessageRequest;
use App\Http\Resources\Api\MessageResource;
use App\Models\Message;
use App\Models\Referral;
use App\Services\AuditLogger;
use App\Support\AuditActions;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MessageController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function index(Referral $referral): AnonymousResourceCollection
    {
        $this->authorize('view', $referral);

        return MessageResource::collection(
            $referral->messages()->with('sender:id,name,title')->latest()->paginate(50)
        );
    }

    public function store(StoreMessageRequest $request, Referral $referral): MessageResource
    {
        $this->authorize('view', $referral);

        $message = Message::create([
            'referral_id' => $referral->id,
            'sender_user_id' => $request->user()->id,
            'body' => $request->validated('body'),
        ]);

        $this->auditLogger->record($request->user(), AuditActions::MESSAGE_SENT, $message, [
            'referral_id' => $referral->id,
        ]);

        return new MessageResource($message->load('sender:id,name,title'));
    }
}
