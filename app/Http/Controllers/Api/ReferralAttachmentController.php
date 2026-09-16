<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\ReferralAttachment;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReferralAttachmentController extends Controller
{
    public function download(Referral $referral, ReferralAttachment $attachment): StreamedResponse
    {
        $this->authorize('view', $referral);

        abort_unless($attachment->referral_id === $referral->id, 404);

        $path = "referrals/{$referral->id}/{$attachment->filename}";

        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, $attachment->original_name);
    }
}
