<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\ReferralAttachment;
use App\Services\AuditLogger;
use App\Support\AuditActions;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReferralAttachmentController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function download(Referral $referral, ReferralAttachment $attachment): StreamedResponse
    {
        $this->authorize('view', $referral);

        abort_unless($attachment->referral_id === $referral->id, 404);

        $path = "referrals/{$referral->id}/{$attachment->filename}";

        abort_unless(Storage::disk('local')->exists($path), 404);

        $this->auditLogger->record(request()->user(), AuditActions::ATTACHMENT_DOWNLOADED, $attachment, [
            'referral_id' => $referral->id,
            'original_name' => $attachment->original_name,
        ]);

        return Storage::disk('local')->download($path, $attachment->original_name);
    }
}
