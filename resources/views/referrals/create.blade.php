@extends('layouts.console')
@section('title', 'New referral')
@section('heading', 'New referral')
@section('content')
<div class="mx-auto max-w-6xl">

    <!-- Page header -->
    <div class="section-header mb-8">
        <div class="section-header__group">
            <p class="section-header__eyebrow">Fast clinical handover</p>
            <h2 class="section-header__title">Send a referral</h2>
        </div>
        <span class="badge bg-red-50 text-red-700">Emergency-ready</span>
    </div>

    <form id="referral-form" class="space-y-8" enctype="multipart/form-data" method="POST" action="{{ route('referrals.store') }}">
        @csrf

        <!-- Section 1: Destination -->
        <section class="form-section form-section--emergency">
            <div class="form-section__header">
                <div class="flex items-start gap-3">
                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                        <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4">
                            <path d="M10 4v6m0 3v.5M3 16h14L10 3 3 16Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="form-section__title">Send the essentials</h3>
                        <p class="form-section__subtitle">The receiving hospital will be notified and a care-team owner assigned as soon as you send.</p>
                    </div>
                </div>
            </div>
            <div class="form-section__body">
                <div class="grid gap-4 sm:grid-cols-2">
                    <fieldset class="sm:col-span-2">
                        <legend class="form-field__label">Destination</legend>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <label class="cursor-pointer">
                                <input class="peer sr-only" type="radio" name="destination_type" value="hospital" checked>
                                <span class="btn-secondary inline-block peer-checked:bg-brand-700 peer-checked:text-white peer-checked:border-brand-700">Receiving hospital</span>
                            </label>
                            <label class="cursor-pointer">
                                <input class="peer sr-only" type="radio" name="destination_type" value="doctor">
                                <span class="btn-secondary inline-block peer-checked:bg-brand-700 peer-checked:text-white peer-checked:border-brand-700">Independent doctor</span>
                            </label>
                        </div>
                    </fieldset>
                    <div id="hospital-target">
                        <label class="form-field__label" for="hospital">Receiving hospital</label>
                        <select class="input mt-1" id="hospital" name="receiving_hospital_id" required>
                            <option value="">Choose hospital</option>
                            @foreach($hospitals as $hospital)
                            <option value="{{ $hospital->id }}">{{ $hospital->name }}</option>
                            @endforeach
                        </select>
                        <p class="form-field__helper">Select the hospital that will receive this patient.</p>
                    </div>
                    <div id="doctor-target" class="hidden">
                        <label class="form-field__label" for="doctor-search">Find an independent doctor</label>
                        <input class="input mt-1" id="doctor-search" type="search" placeholder="Search by name or specialty">
                        <select class="input mt-2" id="intended-doctor" name="intended_user_id" disabled>
                            <option value="">Choose doctor</option>
                        </select>
                        <p class="form-field__helper">Only active doctors at active private practices appear here.</p>
                    </div>
                    <div>
                        <label class="form-field__label" for="department">Receiving department</label>
                        <input class="input mt-1" id="department" name="department" placeholder="Emergency, ICU, Cardiology" required>
                        <p class="form-field__helper">Where the patient will be admitted.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 2: Patient -->
        <section class="form-section">
            <div class="form-section__header">
                <h3 class="form-section__title">Patient</h3>
                <p class="form-section__subtitle">Minimal patient information required for coordination.</p>
            </div>
            <div class="form-section__body">
                <div class="form-row">
                    <div class="form-field">
                        <label class="form-field__label--required" for="patient-name">Patient name</label>
                        <input class="input mt-1" id="patient-name" name="patient[name]" placeholder="Full name" required>
                    </div>
                    <div class="form-field">
                        <label class="form-field__label" for="patient-age">Age</label>
                        <input class="input mt-1" id="patient-age" name="patient[age]" type="number" min="0" max="130" placeholder="Years">
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 3: Clinical picture -->
        <section class="form-section">
            <div class="form-section__header flex items-center justify-between">
                <div>
                    <h3 class="form-section__title">Clinical picture</h3>
                    <p class="form-section__subtitle">What the receiving team needs to know right now.</p>
                </div>
                <label class="flex items-center gap-2 text-sm font-semibold text-red-700">
                    <input class="h-4 w-4 accent-red-600" id="is-emergency" name="is_emergency" type="checkbox" value="1">
                    Emergency transfer
                </label>
            </div>
            <div class="form-section__body">
                <div class="form-row">
                    <div class="form-field">
                        <label class="form-field__label" for="urgency">Urgency</label>
                        <select class="input mt-1" id="urgency" name="urgency">
                            @foreach(\App\Support\ReferralFormat::urgencyOptions(null) as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="form-field__label" for="vitals-bp">Blood pressure <span class="font-normal text-slate-400">(optional)</span></label>
                        <input class="input mt-1" id="vitals-bp" name="vitals[bp]" placeholder="120/80">
                    </div>
                </div>
                <div class="form-field">
                    <label class="form-field__label--required" for="referral-reason">Reason for referral</label>
                    <textarea class="input mt-1 min-h-24" id="referral-reason" name="referral_reason" placeholder="Diagnosis, concern, or reason for transfer" required></textarea>
                    <p class="form-field__helper">Diagnosis, concern, or reason for transfer.</p>
                </div>
                <div class="form-field">
                    <label class="form-field__label" for="symptoms">Current symptoms <span class="font-normal text-slate-400">(optional)</span></label>
                    <textarea class="input mt-1 min-h-20" id="symptoms" name="symptoms" placeholder="What is happening now?"></textarea>
                </div>
            </div>
        </section>

        <!-- Section 4: Additional clinical details (collapsible) -->
        <details class="form-section group">
            <summary class="flex cursor-pointer list-none items-center justify-between p-5 font-semibold text-brand-950 border-b border-slate-200/80">
                <span>Additional clinical details</span>
                <i data-lucide="plus" class="text-slate-400 transition group-open:rotate-45" aria-hidden="true"></i>
            </summary>
            <div class="form-section__body">
                <div class="grid gap-4 border-t border-slate-100 pt-5 sm:grid-cols-2">
                    <div class="form-field">
                        <label class="form-field__label" for="vitals-hr">Heart rate</label>
                        <input class="input mt-1" id="vitals-hr" name="vitals[hr]" type="number">
                    </div>
                    <div class="form-field">
                        <label class="form-field__label" for="vitals-spo2">SpO₂</label>
                        <input class="input mt-1" id="vitals-spo2" name="vitals[spo2]" type="number" min="0" max="100">
                    </div>
                    <div class="form-field">
                        <label class="form-field__label" for="consciousness">Consciousness</label>
                        <input class="input mt-1" id="consciousness" name="consciousness" placeholder="Alert, drowsy...">
                    </div>
                    <div class="form-field">
                        <label class="form-field__label" for="blood-group">Blood group</label>
                        <input class="input mt-1" id="blood-group" name="patient[blood_group]">
                    </div>
                    <div class="form-field sm:col-span-2">
                        <label class="form-field__label" for="existing-conditions">Existing conditions</label>
                        <textarea class="input mt-1 min-h-20" id="existing-conditions" name="existing_conditions"></textarea>
                    </div>
                    <div class="form-field sm:col-span-2">
                        <label class="form-field__label" for="current-interventions">Current interventions</label>
                        <textarea class="input mt-1 min-h-20" id="current-interventions" name="current_interventions"></textarea>
                    </div>
                    <div class="form-field sm:col-span-2">
                        <label class="form-field__label" for="attachments">Attachments</label>
                        <input class="input mt-1 py-2" id="attachments" name="attachments[]" type="file" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.csv,.txt">
                        <p class="form-field__helper">Clinical documents, lab results, imaging reports.</p>
                    </div>
                </div>
            </div>
        </details>

        <!-- Error banner -->
        <div id="referral-error" class="hidden rounded-[10px] border border-red-200 bg-red-50 p-4 text-sm text-red-800"></div>

        <!-- Actions -->
        <div class="flex flex-wrap items-center justify-between gap-3 pt-6 border-t border-slate-100">
            <a href="{{ route('referrals.index') }}" class="btn-ghost px-5 py-2.5">Cancel</a>
            <button id="referral-submit" type="submit" class="btn-accent px-8 py-3">Send referral</button>
        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons({ icons: lucide.icons });

        // Populate independent doctor dropdown
        var doctorSelect = document.getElementById('intended-doctor');
        var doctors = @json($doctors ?? []);
        if (doctorSelect && doctors.length) {
            doctors.forEach(function (doctor) {
                var option = document.createElement('option');
                option.value = doctor.id;
                option.textContent = doctor.name + (doctor.hospital ? ' · ' + doctor.hospital.name : '');
                doctorSelect.appendChild(option);
            });
        }

        // Toggle destination sections
        var hospitalRadio = document.querySelector('input[name="destination_type"][value="hospital"]');
        var doctorRadio = document.querySelector('input[name="destination_type"][value="doctor"]');
        var hospitalSection = document.getElementById('hospital-target');
        var doctorSection = document.getElementById('doctor-target');
        var hospitalSelect = document.getElementById('hospital');
        var doctorSelectEl = document.getElementById('intended-doctor');

        function syncDestination() {
            if (doctorRadio.checked) {
                hospitalSection.classList.add('hidden');
                doctorSection.classList.remove('hidden');
                hospitalSelect.disabled = true;
                hospitalSelect.required = false;
                doctorSelectEl.disabled = false;
                doctorSelectEl.required = true;
            } else {
                hospitalSection.classList.remove('hidden');
                doctorSection.classList.add('hidden');
                hospitalSelect.disabled = false;
                hospitalSelect.required = true;
                doctorSelectEl.disabled = true;
                doctorSelectEl.required = false;
            }
        }

        hospitalRadio.addEventListener('change', syncDestination);
        doctorRadio.addEventListener('change', syncDestination);
        syncDestination();
    });
</script>
@endpush