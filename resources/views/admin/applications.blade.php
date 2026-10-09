@extends('layouts.console')

@section('title', 'Applications: BADBAADO')

@section('heading', 'Provider applications')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Onboarding</p>
            <h2 class="mt-1 text-2xl font-bold text-slate-900">Registrations awaiting a decision</h2>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.applications', ['status' => null]) }}"
                class="badge {{ $status === null ? 'bg-brand-50 text-brand-700' : 'bg-slate-50 text-slate-500' }}">All</a>
            <a href="{{ route('admin.applications', ['status' => 'pending']) }}"
                class="badge {{ $status === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-slate-50 text-slate-500' }}">Pending
                ({{ $counts['pending'] }})</a>
            <a href="{{ route('admin.applications', ['status' => 'approved']) }}"
                class="badge {{ $status === 'approved' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-50 text-slate-500' }}">Approved
                ({{ $counts['approved'] }})</a>
            <a href="{{ route('admin.applications', ['status' => 'rejected']) }}"
                class="badge {{ $status === 'rejected' ? 'bg-red-50 text-red-700' : 'bg-slate-50 text-slate-500' }}">Rejected
                ({{ $counts['rejected'] }})</a>
        </div>
    </div>

    @if (session('status'))
        <div class="card mt-6 border-emerald-200 bg-emerald-50 text-sm text-emerald-800" role="status">
            {{ session('status') }}
        </div>
    @endif

    <div class="card mt-6 overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500">
                    <th>Applicant</th>
                    <th>Type</th>
                    <th>Details</th>
                    <th>Status</th>
                    <th>Reviewed</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($applications as $application)
                    @php
                        $payload = $application->payload;
                        $detail = $application->type === \App\Enums\ProviderApplicationType::DOCTOR
                            ? trim(($payload['title'] ?? '').' · '.($payload['license_number'] ?? ''), ' ·')
                            : ($payload['facility_name'] ?? '');
                    @endphp
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                @include('partials.avatar', ['user' => $application->user, 'size' => 'h-10 w-10'])
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-slate-900">
                                        {{ $application->user?->name ?? 'Deleted account' }}
                                    </p>
                                    <p class="truncate text-xs text-slate-500">{{ $application->user?->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge bg-slate-100 text-slate-600">{{ $application->type->label() }}</span></td>
                        <td class="text-slate-600">{{ $detail ?: '—' }}</td>
                        <td>
                            <span
                                class="badge {{ match ($application->status->value) {
                                    'approved' => 'bg-emerald-50 text-emerald-700',
                                    'rejected' => 'bg-red-50 text-red-700',
                                    default => 'bg-amber-50 text-amber-700',
                                } }}">{{ $application->status->label() }}</span>
                        </td>
                        <td class="text-xs text-slate-500">
                            {{ $application->reviewed_at?->format('d M Y') ?? '—' }}
                            @if ($application->reviewer)
                                <span class="block text-slate-400">by {{ $application->reviewer->name }}</span>
                            @endif
                        </td>
                        <td class="text-right">
                            @if ($application->status->isPending())
                                <div class="flex justify-end gap-2">
                                    <form method="POST" action="{{ route('admin.applications.approve', $application) }}">
                                        @csrf
                                        <button type="submit" class="btn-primary btn-sm">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.applications.reject', $application) }}"
                                        class="flex items-center gap-2">
                                        @csrf
                                        <input type="text" name="rejection_reason" placeholder="Reason"
                                            aria-label="Rejection reason for {{ $application->user?->name }}"
                                            class="input py-1 text-xs" required>
                                        <button type="submit" class="btn-danger btn-sm">Reject</button>
                                    </form>
                                </div>
                            @else
                                @if ($application->hospital)
                                    <a href="{{ route('admin.hospitals') }}"
                                        class="text-xs font-semibold text-brand-700 hover:underline">View facility</a>
                                @endif
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-sm text-slate-500">
                            No {{ $status ? e($status) : '' }} applications yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @include('partials.pager', ['pager' => $applications])
    </div>
@endsection