<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\CmsContent;
use App\Models\Faq;
use Illuminate\Database\Eloquent\Collection;

class CmsService
{
    /**
     * The content blocks editable from the command centre. Fallbacks guarantee the
     * public site keeps rendering meaningful copy before an administrator saves
     * anything through the CMS.
     *
     * @return array<string, array{section: string, title: string, content: string}>
     */
    public function definitions(): array
    {
        return [
            'hero.eyebrow' => ['section' => 'hero', 'title' => 'Hero eyebrow', 'content' => 'Now connecting hospitals & care teams'],
            'hero.title' => ['section' => 'hero', 'title' => 'Hero headline (pipe-separated lines)', 'content' => 'Connecting|Hospitals,|Connecting Care'],
            'hero.subtitle' => ['section' => 'hero', 'title' => 'Hero subtitle', 'content' => 'BADBAADO is a coordinated referral and emergency pre-alert gateway. Information arrives before the patient, so the receiving team is prepared from the moment the decision to transfer is made.'],
            'hero.cta_primary' => ['section' => 'hero', 'title' => 'Primary button label', 'content' => 'Open the console'],
            'hero.cta_secondary' => ['section' => 'hero', 'title' => 'Secondary button label', 'content' => 'See how it works'],
            'sections.announcements_eyebrow' => ['section' => 'sections', 'title' => 'Announcements eyebrow', 'content' => 'Updates'],
            'sections.faq_eyebrow' => ['section' => 'sections', 'title' => 'FAQ eyebrow', 'content' => 'Common questions'],
            'sections.contact_eyebrow' => ['section' => 'sections', 'title' => 'Contact eyebrow', 'content' => 'Get in touch'],
            'stats.hospitals' => ['section' => 'stats', 'title' => 'Hospitals stat label', 'content' => 'Hospitals on the network'],
            'stats.referrals' => ['section' => 'stats', 'title' => 'Referrals stat label', 'content' => 'Referrals coordinated'],
            'stats.emergencies' => ['section' => 'stats', 'title' => 'Pre-alerts stat label', 'content' => 'Emergency pre-alerts raised'],
            'stats.transfers' => ['section' => 'stats', 'title' => 'Transfers stat label', 'content' => 'Transfers currently tracked'],
        ];
    }

    public function get(string $key, ?string $fallback = null): ?string
    {
        $definition = $this->definitions()[$key] ?? null;

        $row = CmsContent::where('key', $key)->where('is_active', true)->first();

        if ($row !== null && $row->content !== null && $row->content !== '') {
            return $row->content;
        }

        return $fallback ?? $definition['content'] ?? null;
    }

    /**
     * Seed the database so every definition is editable from day one.
     *
     * @return int number of rows created
     */
    public function seed(): int
    {
        $created = 0;

        foreach ($this->definitions() as $key => $definition) {
            if (! CmsContent::where('key', $key)->exists()) {
                CmsContent::create([
                    'key' => $key,
                    'section' => $definition['section'],
                    'title' => $definition['title'],
                    'content' => $definition['content'],
                    'is_active' => true,
                ]);
                $created++;
            }
        }

        return $created;
    }

    /**
     * @return Collection<int, CmsContent>
     */
    public function section(string $section): Collection
    {
        return CmsContent::where('section', $section)->orderBy('key')->get();
    }

    public function announcements(int $limit = 3): Collection
    {
        return Announcement::query()
            ->where('is_active', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    public function faqs(): Collection
    {
        return Faq::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * @return array<string, string|null>
     */
    public function statsLabels(): array
    {
        return [
            'hospitals' => $this->get('stats.hospitals', 'Hospitals on the network'),
            'referrals' => $this->get('stats.referrals', 'Referrals coordinated'),
            'emergencies' => $this->get('stats.emergencies', 'Emergency pre-alerts raised'),
            'transfers' => $this->get('stats.transfers', 'Transfers currently tracked'),
        ];
    }

    /**
     * @return array{email: string|null, phone: string|null, address: string|null}
     */
    public function contact(): array
    {
        $settings = app(SettingsService::class);

        return [
            'email' => $settings->getString('contact.support_email'),
            'phone' => $settings->getString('contact.support_phone'),
            'address' => $settings->getString('contact.address'),
        ];
    }
}
