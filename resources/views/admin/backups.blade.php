@extends('layouts.console')

@php
    use App\Support\ReferralFormat;
@endphp

@section('title', 'Backups & Data')
@section('heading', 'Backups & Data')
@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="section-eyebrow">Continuity</p>
            <h2 class="page-heading mt-2 text-3xl">Backups & data management.</h2>
            <p class="mt-2 max-w-2xl text-sm text-slate-500">On-demand and scheduled archives of the database and private storage, bounded by the retention window. Restore remains a manual, documented engineering operation.</p>
        </div>
        <button class="btn-accent" type="button" data-create-backup>Create backup now</button>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="tile tile--blue"><p class="tile__label">Archives stored</p><p class="tile__value">{{ $backups->where('status', 'completed')->count() }}</p></div>
        <div class="tile"><p class="tile__label">Retention window</p><p class="tile__value">{{ $retention_days }}<span class="tile__label text-base"> days</span></p></div>
        <div class="tile tile--amber"><p class="tile__label">Auto-backup</p><p class="tile__value text-lg">Daily 02:00</p><p class="faint mt-1 text-xs">Prunes archived beyond retention.</p></div>
    </div>

    <section class="card p-6">
        <div class="flex items-center justify-between"><div><p class="section-eyebrow">Archive</p><h3 class="dashboard-section-title mt-1.5">Backup history</h3></div>
            <form class="filter-bar">
                <input class="input" name="notes_prefill" placeholder="Add a note to logs (optional)">
            </form>
        </div>
        <div class="overflow-x-auto mt-4">
            <table class="data-table">
                <thead><tr><th>Archive</th><th>Size</th><th>Status</th><th>Created by</th><th>Created</th><th>Notes</th><th></th></tr></thead>
                <tbody>
                    @forelse ($backups as $backup)
                        <tr>
                            <td class="mono word">{{ $backup->filename }}</td>
                            <td class="soft mono">{{ $backup->size ? ReferralFormat::bytes($backup->size) : '—' }}</td>
                            <td>
                                <span class="badge {{ $backup->status === 'completed' ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' }}">{{ $backup->status }}</span>
                            </td>
                            <td class="soft">{{ $backup->creator?->name ?? 'Scheduler' }}</td>
                            <td class="faint">{{ $backup->created_at?->format('d M Y, H:i') }}</td>
                            <td class="faint max-w-[14rem] truncate">{{ $backup->notes ?? '—' }}</td>
                            <td class="text-right">
                                <div class="flex justify-end gap-2">
                                    @if ($backup->status === 'completed')
                                        <a href="{{ route('admin.backups.download', $backup) }}" class="btn-secondary btn-sm">Download</a>
                                    @endif
                                    <button class="btn-danger btn-sm" data-delete-backup="{{ $backup->id }}" type="button">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="empty-state"><div class="empty-state__icon">⛁</div><p>No backups yet. Create the first one above or let the nightly schedule run.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="card p-6">
        <div><p class="section-eyebrow">Procedure</p><h3 class="dashboard-section-title mt-1.5">Restoring an archive</h3></div>
        <div class="mt-3 space-y-2 text-sm text-slate-600">
            <p>1. Pull the archive from the backups directory.</p>
            <p>2. Extract the database file and swap it into <span class="mono">database/</span>.</p>
            <p>3. Restore <span class="mono">storage/app/private</span> and <span class="mono">storage/app/public</span> contents.</p>
            <p>4. Run <span class="mono">php artisan migrate --force</span> and clear the application cache.</p>
        </div>
    </section>
</div>
@endsection