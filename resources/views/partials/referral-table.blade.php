<div class="overflow-x-auto">
    <table class="data-table">
        <thead>
        <tr>
            <th class="w-28">Referral</th>
            <th>Route</th>
            <th class="w-24 text-center">Urgency</th>
            <th class="w-28 text-center">Status</th>
            <th class="w-24 text-right">Action</th>
        </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">@forelse($referrals as $referral)
            <tr class="group">
                <td class="px-5 py-4">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('referrals.show', $referral) }}" class="font-mono text-xs text-slate-400 group-hover:text-brand-600">{{ $referral->referral_number }}</a>
                        @if($referral->is_emergency)
                        <span class="badge bg-red-50 text-red-600 text-[10px] px-2 py-1 ml-1">Pre-alert</span>
                        @endif
                    </div>
                    <p class="mt-1 text-xs text-slate-500">{{ $referral->patient?->full_name ?? 'Patient record' }}</p>
                </td>
                <td class="px-5 py-4 text-xs text-slate-600">
                    {{ $referral->referringHospital?->short_name ?? $referral->referringHospital?->name }} <span class="inline-flex text-accent-500"><i data-lucide="arrow-right" aria-hidden="true"></i></span> {{ $referral->receivingHospital?->short_name ?? $referral->receivingHospital?->name ?? ('Dr. ' . ($referral->intendedUser?->name ?? 'direct referral')) }}
                </td>
                <td class="px-5 py-4 text-center">{!! \App\Support\ReferralFormat::urgencyPill($referral->urgency?->value) !!}</td>
                <td class="px-5 py-4 text-center">{!! \App\Support\ReferralFormat::statusPill($referral->status?->value) !!}</td>
                <td class="px-5 py-4 text-right"><a href="{{ route('referrals.show', $referral) }}" class="font-semibold text-brand-600 hover:text-brand-500">View</a></td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="px-5 py-16 text-center text-sm text-slate-500">No referrals in this workspace yet.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>