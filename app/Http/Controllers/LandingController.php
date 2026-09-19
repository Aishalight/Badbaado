<?php

namespace App\Http\Controllers;

use App\Enums\ReferralStatus;
use App\Models\Hospital;
use App\Models\Referral;
use App\Services\CmsService;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function __construct(private readonly CmsService $cms) {}

    public function __invoke(): View
    {
        $stats = [
            [
                'value' => Hospital::where('is_active', true)->count(),
                'unit' => '',
                'label' => $this->cms->get('stats.hospitals', 'Hospitals on the network'),
            ],
            [
                'value' => Referral::count(),
                'unit' => '',
                'label' => $this->cms->get('stats.referrals', 'Referrals coordinated'),
            ],
            [
                'value' => Referral::where('is_emergency', true)->count(),
                'unit' => '',
                'label' => $this->cms->get('stats.emergencies', 'Emergency pre-alerts raised'),
            ],
            [
                'value' => Referral::whereIn('status', [
                    ReferralStatus::TRANSFER_IN_PROGRESS,
                    ReferralStatus::ARRIVED,
                ])->count(),
                'unit' => '',
                'label' => $this->cms->get('stats.transfers', 'Transfers currently tracked'),
            ],
        ];

        return view('landing', [
            'stats' => $stats,
            'heroEyebrow' => $this->cms->get('hero.eyebrow'),
            'heroLines' => collect(explode('|', (string) $this->cms->get('hero.title')))->filter()->values(),
            'heroSubtitle' => $this->cms->get('hero.subtitle'),
            'ctaPrimary' => $this->cms->get('hero.cta_primary'),
            'ctaSecondary' => $this->cms->get('hero.cta_secondary'),
            'announcements' => $this->cms->announcements(),
            'faqs' => $this->cms->faqs(),
            'announcementsEyebrow' => $this->cms->get('sections.announcements_eyebrow'),
            'faqEyebrow' => $this->cms->get('sections.faq_eyebrow'),
            'contactEyebrow' => $this->cms->get('sections.contact_eyebrow'),
            'contact' => $this->cms->contact(),
        ]);
    }
}
