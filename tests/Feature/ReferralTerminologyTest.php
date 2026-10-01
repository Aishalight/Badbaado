<?php

namespace Tests\Feature;

use App\Enums\ReferralStatus;
use App\Enums\Urgency;
use App\Support\ReferralFormat;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class ReferralTerminologyTest extends TestCase
{
    public function test_urgency_labels_match_the_enum_values(): void
    {
        foreach (Urgency::cases() as $case) {
            $this->assertSame(
                $case->label(),
                ReferralFormat::urgencyLabel($case->value),
                "Urgency '{$case->value}' renders as a different word than the enum label."
            );
        }
    }

    public function test_urgency_options_cover_every_enum_case(): void
    {
        $options = ReferralFormat::urgencyOptions();

        $this->assertSame('All urgencies', $options['']);
        $this->assertSame([], array_diff(array_column(Urgency::cases(), 'value'), array_keys($options)));
    }

    public function test_omitting_the_blank_label_leaves_no_empty_option(): void
    {
        $this->assertArrayNotHasKey('', ReferralFormat::urgencyOptions(null));
        $this->assertArrayNotHasKey('', ReferralFormat::statusOptions(null));
        $this->assertCount(count(Urgency::cases()), ReferralFormat::urgencyOptions(null));
    }

    public function test_status_options_cover_every_referral_status(): void
    {
        $options = ReferralFormat::statusOptions();

        $this->assertSame('All statuses', $options['']);

        foreach (ReferralStatus::cases() as $case) {
            $this->assertArrayHasKey(
                $case->value,
                $options,
                "Status '{$case->value}' is missing from the filter options."
            );
        }
    }

    public function test_urgency_pill_uses_the_same_word_as_the_label_helper(): void
    {
        $this->assertStringContainsString(
            ReferralFormat::urgencyLabel('emergent'),
            ReferralFormat::urgencyPill('emergent')
        );
    }

    public function test_referral_form_urgency_select_uses_shared_labels(): void
    {
        $rendered = Blade::render(
            '@foreach(\App\Support\ReferralFormat::urgencyOptions(null) as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach'
        );

        foreach (Urgency::cases() as $case) {
            $this->assertStringContainsString('>'.$case->label().'</option>', $rendered);
        }
    }
}
