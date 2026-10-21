<?php

namespace Database\Seeders;

use App\Enums\NotificationSeverity;
use App\Enums\ProviderApplicationStatus;
use App\Enums\ProviderApplicationType;
use App\Enums\ReferralStatus;
use App\Enums\Urgency;
use App\Enums\UserStatus;
use App\Models\AuditLog;
use App\Models\Hospital;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\ProviderApplication;
use App\Models\Referral;
use App\Models\Role;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * Builds a complete, internally consistent demonstration of the referral
 * lifecycle so the console can be shown to stakeholders without anyone
 * having to type clinical data by hand.
 *
 * Every person, patient and hospital below is fictional.
 *
 * The centrepiece is the "Hassan Ali" case: a STEMI referral travelling from
 * Al-Hilal Teaching Hospital to Shifa Community Medical College. It carries a
 * full audit trail, a two-sided message thread and the matching notifications,
 * so the activity box, the conversation panel and the notification centre all
 * have something meaningful to show.
 */
class DemoFlowSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Anchor for every timestamp in the demo data, so the whole story reads as
     * one shift rather than a scatter of "now".
     */
    private Carbon $clock;

    public function run(): void
    {
        $this->clock = Carbon::parse('2026-10-21 08:00:00');

        $roles = $this->roles();
        $specialties = $this->specialties();

        $practices = $this->seedPractices($roles, $specialties);
        $staff = $this->seedNamedStaff($roles, $specialties);
        $applications = $this->seedProviderApplications($roles);

        $this->seedFlagshipReferral($staff);
        $this->seedReferralToIndependentDoctor($staff, $practices);
        $this->seedReferralAwaitingAcknowledgement($staff);
        $this->seedReferralUnderReview($staff);
        $this->seedRejectedReferral($staff);
        $this->seedDraftReferral($staff);
        $this->seedNotifications($staff);
    }

    /**
     * @return array<string, Role>
     */
    private function roles(): array
    {
        return Role::query()->get()->keyBy('slug')->all();
    }

    /**
     * @return array<string, Specialty>
     */
    private function specialties(): array
    {
        return Specialty::query()->get()->keyBy('name')->all();
    }

    /**
     * Private practices that back verified independent doctors.
     *
     * A practice is what separates an independent doctor from staff employed
     * by a real hospital, so this is the row the referral picker keys on.
     *
     * @param  array<string, Role>  $roles
     * @param  array<string, Specialty>  $specialties
     * @return array<string, Hospital>
     */
    private function seedPractices(array $roles, array $specialties): array
    {
        $practices = [
            'SDH' => [
                'name' => "Dr. Samina Haque's Chamber",
                'short_name' => 'S. Haque Chamber',
                'kind' => 'practice',
                'location' => 'Dhanmondi, Dhaka',
                'phone' => '+880 2 9123 4477',
                'email' => 'chamber@sdh.badbaado.bd',
                'level' => 'primary',
            ],
            'IKB' => [
                'name' => "Dr. Imran Kabir's Chamber",
                'short_name' => 'I. Kabir Chamber',
                'kind' => 'practice',
                'location' => 'Uttara, Dhaka',
                'phone' => '+880 2 9123 8890',
                'email' => 'chamber@ikb.badbaado.bd',
                'level' => 'primary',
            ],
        ];

        foreach ($practices as $code => $attributes) {
            Hospital::updateOrCreate(['code' => $code], $attributes + [
                'is_active' => true,
            ]);
        }

        $doctors = [
            [
                'email' => 'dr.samina@sdh.badbaado.bd',
                'name' => 'Dr. Samina Haque',
                'title' => 'Consultant Cardiologist',
                'hospital_code' => 'SDH',
                'specialty' => 'Cardiology',
                'phone' => '+880 1712 004411',
            ],
            [
                'email' => 'dr.imran@ikb.badbaado.bd',
                'name' => 'Dr. Imran Kabir',
                'title' => 'Consultant Neurologist',
                'hospital_code' => 'IKB',
                'specialty' => 'Neurosurgery',
                'phone' => '+880 1713 009922',
            ],
        ];

        foreach ($doctors as $doctor) {
            $hospital = Hospital::where('code', $doctor['hospital_code'])->firstOrFail();

            User::updateOrCreate(['email' => $doctor['email']], [
                'name' => $doctor['name'],
                'title' => $doctor['title'],
                'password' => Hash::make('password'),
                'hospital_id' => $hospital->getKey(),
                'role_id' => $roles['healthcare_worker']->getKey(),
                'specialty_id' => $specialties[$doctor['specialty']]?->getKey(),
                'phone' => $doctor['phone'],
                'status' => UserStatus::ACTIVE,
                'email_verified_at' => $this->clock->copy(),
            ]);
        }

        return Hospital::query()->whereIn('code', array_keys($practices))->pluck('id', 'code')->all();
    }

    /**
     * Named clinicians and coordinators, so the console reads like a real
     * handover instead of a list of generic accounts.
     *
     * @param  array<string, Role>  $roles
     * @param  array<string, Specialty>  $specialties
     * @return array<string, User>
     */
    private function seedNamedStaff(array $roles, array $specialties): array
    {
        $people = [
            [
                'email' => 'dr.aminul@shm.badbaado.bd',
                'name' => 'Dr. Aminul Haque',
                'title' => 'Consultant Cardiologist',
                'hospital_code' => 'SHM',
                'role' => 'healthcare_worker',
                'specialty' => 'Cardiology',
            ],
            [
                'email' => 'coordinator.shifa@shm.badbaado.bd',
                'name' => 'Sabina Yasmin',
                'title' => 'Referral Coordinator',
                'hospital_code' => 'SHM',
                'role' => 'referral_coordinator',
                'specialty' => null,
            ],
            [
                'email' => 'dr.rehana@nsh.badbaado.bd',
                'name' => 'Dr. Rehana Chowdhury',
                'title' => 'Associate Consultant, Emergency',
                'hospital_code' => 'NSH',
                'role' => 'healthcare_worker',
                'specialty' => 'Emergency Medicine',
            ],
            [
                'email' => 'dr.shahriar@gfg.badbaado.bd',
                'name' => 'Dr. Shahriar Alam',
                'title' => 'Senior Registrar, General Surgery',
                'hospital_code' => 'GFG',
                'role' => 'healthcare_worker',
                'specialty' => 'General Surgery',
            ],
            [
                'email' => 'coordinator.rudra@rud.badbaado.bd',
                'name' => 'Mizanur Rahman',
                'title' => 'Referral Coordinator',
                'hospital_code' => 'RUD',
                'role' => 'referral_coordinator',
                'specialty' => null,
            ],
            [
                'email' => 'dr.kamrul@sdc.badbaado.bd',
                'name' => 'Dr. Kamrul Islam',
                'title' => 'Consultant, Orthopaedics',
                'hospital_code' => 'SDC',
                'role' => 'healthcare_worker',
                'specialty' => 'Orthopaedics',
            ],
            [
                'email' => 'dr.nusrat@mhb.badbaado.bd',
                'name' => 'Dr. Nusrat Jahan',
                'title' => 'Consultant, Internal Medicine',
                'hospital_code' => 'MHB',
                'role' => 'healthcare_worker',
                'specialty' => 'Internal Medicine',
            ],
            [
                'email' => 'dr.abdul@gzg.badbaado.bd',
                'name' => 'Dr. Abdul Karim',
                'title' => 'Medical Officer',
                'hospital_code' => 'GZG',
                'role' => 'healthcare_worker',
                'specialty' => 'General Medicine',
            ],
        ];

        $staff = [];

        foreach ($people as $person) {
            $hospital = Hospital::where('code', $person['hospital_code'])->firstOrFail();

            $user = User::updateOrCreate(['email' => $person['email']], [
                'name' => $person['name'],
                'title' => $person['title'],
                'password' => Hash::make('password'),
                'hospital_id' => $hospital->getKey(),
                'role_id' => $roles[$person['role']]->getKey(),
                'specialty_id' => $person['specialty'] ? ($specialties[$person['specialty']]?->getKey()) : null,
                'status' => UserStatus::ACTIVE,
                'email_verified_at' => $this->clock->copy(),
            ]);

            $staff[$person['email']] = $user;
        }

        // The Al-Hilal team already exists from UserSeeder; fetch it so the
        // flagship referral can be attached to the right author.
        $staff['dr.rahman@alhilal.bd'] = User::where('email', 'dr.rahman@alhilal.bd')->firstOrFail();
        $staff['coordinator@alhilal.bd'] = User::where('email', 'coordinator@alhilal.bd')->firstOrFail();

        return $staff;
    }

    /**
     * Applications awaiting review, so the administrator's approval queue is
     * populated the same way the referral console is.
     *
     * @param  array<string, Role>  $roles
     * @return array<string, ProviderApplication>
     */
    private function seedProviderApplications(array $roles): array
    {
        $specialties = $this->specialties();

        $pending = [
            [
                'type' => ProviderApplicationType::DOCTOR,
                'email' => 'dr.rafiqul@badbaado.bd',
                'name' => 'Dr. Rafiqul Islam',
                'title' => 'Consultant Neurologist',
                'specialty_id' => $specialties['Neurosurgery']?->getKey(),
                'license_number' => 'BMDC-N-44821',
                'phone' => '+880 1714 002233',
            ],
            [
                'type' => ProviderApplicationType::DOCTOR,
                'email' => 'dr.tamanna@badbaado.bd',
                'name' => 'Dr. Tamanna Sultana',
                'title' => 'Registrar, Gynaecology',
                'specialty_id' => $specialties['Obstetrics and Gynaecology']?->getKey(),
                'license_number' => 'BMDC-N-51903',
                'phone' => '+880 1715 004455',
            ],
            [
                'type' => ProviderApplicationType::HOSPITAL,
                'email' => 'admin@ashulia-general.bd',
                'name' => 'Ashulia General Hospital',
                'facility_name' => 'Ashulia General Hospital',
                'facility_location' => 'Ashulia, Savar',
                'facility_phone' => '+880 2 9455 1200',
                'facility_email' => 'info@ashulia-general.bd',
                'facility_level' => 'secondary',
            ],
        ];

        $applications = [];

        foreach ($pending as $index => $data) {
            $user = User::updateOrCreate(['email' => $data['email']], [
                'name' => $data['name'],
                'title' => $data['title'] ?? null,
                'password' => Hash::make('password'),
                'specialty_id' => $data['specialty_id'] ?? null,
                'phone' => $data['phone'] ?? null,
                'status' => UserStatus::PENDING,
            ]);

            $application = ProviderApplication::updateOrCreate(
                ['user_id' => $user->getKey(), 'type' => $data['type']->value],
                [
                    'status' => ProviderApplicationStatus::PENDING->value,
                    'payload' => $this->applicationPayload($data),
                ],
            );

            $applications['application_'.$index] = $application;

            $this->audit(
                null,
                'provider_application_submitted',
                $application,
                ['type' => $data['type']->value, 'email' => $data['email']],
                $this->clock->copy()->subHours(6 - $index),
            );
        }

        return $applications;
    }

    /**
     * The centrepiece: a STEMI transfer from Al-Hilal to Shifa Medical,
     * carrying every milestone of the lifecycle in its audit trail.
     *
     * @param  array<string, User>  $staff
     */
    private function seedFlagshipReferral(array $staff): void
    {
        $patient = Patient::updateOrCreate(
            ['reference' => 'PT-2026-0042'],
            ['name' => 'Hassan Ali', 'age' => 58, 'gender' => 'male', 'blood_group' => 'B+'],
        );

        $referring = $staff['dr.rahman@alhilal.bd'];
        $coordinator = $staff['coordinator@alhilal.bd'];
        $receiver = $staff['dr.aminul@shm.badbaado.bd'];

        $referral = Referral::updateOrCreate(
            ['referral_number' => 'REF-2026-HASSAN'],
            [
                'referring_hospital_id' => $referring->hospital_id,
                'receiving_hospital_id' => $receiver->hospital_id,
                'referring_user_id' => $referring->getKey(),
                'coordinator_user_id' => $coordinator->getKey(),
                'assigned_to_user_id' => $receiver->getKey(),
                'patient_id' => $patient->getKey(),
                'status' => ReferralStatus::ARRIVED,
                'urgency' => Urgency::CRITICAL,
                'is_emergency' => true,
                'department' => 'Cardiology',
                'referral_reason' => 'Confirmed STEMI on ECG with ongoing chest pain. Needs primary PCI at a cath-lab capable facility.',
                'symptoms' => 'Chest pain radiating to the left arm with diaphoresis and shortness of breath for 3 hours.',
                'vitals' => ['bp' => '92/58', 'hr' => 118, 'rr' => 24, 'spo2' => 91, 'temp' => 36.9],
                'consciousness' => 'Alert and oriented',
                'trauma_indicator' => false,
                'existing_conditions' => 'Type 2 diabetes, hypertension',
                'current_interventions' => 'Aspirin 300mg, Clopidogrel 300mg, Heparin 5000 IU IV, 2L O2 via mask',
                'notes' => 'Ambulance departed 08:12. Estimated arrival 08:34. Family informed.',
                'ai_suggestion' => [
                    'urgency' => Urgency::CRITICAL->value,
                    'confidence' => 0.94,
                    'reason' => 'ST-segment elevation with ongoing ischaemic chest pain warrants immediate reperfusion.',
                ],
            ],
        );

        $milestones = [
            ['created', $referring, 0, ['from' => null, 'to' => 'draft']],
            ['sent', $referring, 4, ['from' => 'draft', 'to' => 'sent']],
            ['received', $receiver, 11, ['from' => 'sent', 'to' => 'received']],
            ['under_review', $receiver, 16, ['from' => 'received', 'to' => 'under_review']],
            ['accepted', $receiver, 24, ['from' => 'under_review', 'to' => 'accepted']],
            ['transfer_in_progress', $coordinator, 31, ['from' => 'accepted', 'to' => 'transfer_in_progress']],
            ['arrived', $receiver, 46, ['from' => 'transfer_in_progress', 'to' => 'arrived']],
        ];

        foreach ($milestones as [$target, $actor, $minutes, $metadata]) {
            $this->audit(
                $actor,
                $target === 'created' ? 'referral_created' : 'referral_status_changed',
                $referral,
                $metadata + ['status' => $metadata['to']],
                $this->clock->copy()->addMinutes($minutes),
            );
        }

        $this->audit(
            $referring,
            'ai_urgency_suggested',
            $referral,
            ['urgency' => 'critical', 'confidence' => 0.94],
            $this->clock->copy()->addMinutes(2),
        );

        $this->audit(
            $receiver,
            'referral_assigned',
            $referral,
            ['assigned_to' => $receiver->name],
            $this->clock->copy()->addMinutes(9),
        );

        $thread = [
            [$referring, 5, 'Patient loaded into ambulance with monitor and defibrillator on board. ECG strip attached.'],
            [$receiver, 13, 'Acknowledged. Cath lab team is being alerted now — please bring the ECG and the last troponin result.'],
            [$coordinator, 22, 'Transport confirmed, leaving Al-Hilal at 08:12. Estimated 22 minutes on the road.'],
            [$receiver, 26, 'Cath lab slot reserved for 08:35. Registrar will meet the ambulance at the emergency gate.'],
            [$referring, 33, 'Copy. Patient is stable en route, SpO2 94% on 2L, no new arrhythmia.'],
            [$receiver, 45, 'Patient received at the emergency gate, vitals rechecked and handed over to the cardiology team.'],
        ];

        foreach ($thread as [$sender, $minutes, $body]) {
            Message::updateOrCreate(
                ['referral_id' => $referral->getKey(), 'body' => $body],
                [
                    'sender_user_id' => $sender->getKey(),
                    'read_at' => $this->clock->copy()->addMinutes($minutes + 2),
                ],
            );
        }
    }

    /**
     * A referral addressed straight to an independent doctor, which is the
     * destination type that only exists for verified private practitioners.
     *
     * @param  array<string, User>  $staff
     * @param  array<string, int>  $practices
     */
    private function seedReferralToIndependentDoctor(array $staff, array $practices): void
    {
        $patient = Patient::updateOrCreate(
            ['reference' => 'PT-2026-0051'],
            ['name' => 'Rokeya Khatun', 'age' => 46, 'gender' => 'female', 'blood_group' => 'O+'],
        );

        $referring = $staff['dr.rehana@nsh.badbaado.bd'];
        $doctor = User::where('email', 'dr.samina@sdh.badbaado.bd')->firstOrFail();

        $referral = Referral::updateOrCreate(
            ['referral_number' => 'REF-2026-ROKEYA'],
            [
                'referring_hospital_id' => $referring->hospital_id,
                // No receiving facility: the addressee is a person, not a place.
                'receiving_hospital_id' => null,
                'intended_user_id' => $doctor->getKey(),
                'referring_user_id' => $referring->getKey(),
                'assigned_to_user_id' => $doctor->getKey(),
                'patient_id' => $patient->getKey(),
                'status' => ReferralStatus::RECEIVED,
                'urgency' => Urgency::URGENT,
                'is_emergency' => false,
                'department' => 'Cardiology',
                'referral_reason' => 'Persistent exertional chest pain with inconclusive ECG. Wants a second opinion on whether to admit for angiography.',
                'symptoms' => 'Central chest tightness on exertion, relieved by rest, no radiation. Troponin normal so far.',
                'vitals' => ['bp' => '138/86', 'hr' => 88, 'rr' => 18, 'spo2' => 98, 'temp' => 36.7],
                'consciousness' => 'Alert',
                'trauma_indicator' => false,
                'existing_conditions' => 'Hypertension, smoker',
                'current_interventions' => 'Aspirin 75mg daily. Started on bisoprolol 2.5mg.',
                'notes' => 'Patient prefers a private specialist consultation before admission is considered.',
                'ai_suggestion' => [
                    'urgency' => Urgency::URGENT->value,
                    'confidence' => 0.71,
                    'reason' => 'Stable exertional symptoms with a normal troponin favour expedited outpatient review.',
                ],
            ],
        );

        $this->audit($referring, 'referral_created', $referral, ['to' => 'draft'], $this->clock->copy()->subHours(3));
        $this->audit($referring, 'referral_status_changed', $referral, ['from' => 'draft', 'to' => 'sent'], $this->clock->copy()->subHours(3)->addMinutes(6));
        $this->audit($doctor, 'referral_status_changed', $referral, ['from' => 'sent', 'to' => 'received'], $this->clock->copy()->subHours(2));

        Message::updateOrCreate(
            ['referral_id' => $referral->getKey(), 'body' => 'Attaching the ECG. She can be seen in the chamber tomorrow at 11:00 if that suits you.'],
            [
                'sender_user_id' => $referring->getKey(),
                'read_at' => $this->clock->copy()->subHours(2)->addMinutes(5),
            ],
        );
    }

    /**
     * A freshly sent referral that the receiving hospital has not yet
     * acknowledged — the state an on-call coordinator actually meets first.
     *
     * @param  array<string, User>  $staff
     */
    private function seedReferralAwaitingAcknowledgement(array $staff): void
    {
        $patient = Patient::updateOrCreate(
            ['reference' => 'PT-2026-0060'],
            ['name' => 'Mahbubur Rahman', 'age' => 34, 'gender' => 'male', 'blood_group' => 'A+'],
        );

        $referring = $staff['dr.shahriar@gfg.badbaado.bd'];
        $receiver = $staff['dr.kamrul@sdc.badbaado.bd'];

        $referral = Referral::updateOrCreate(
            ['referral_number' => 'REF-2026-MAHBUB'],
            [
                'referring_hospital_id' => $referring->hospital_id,
                'receiving_hospital_id' => $receiver->hospital_id,
                'referring_user_id' => $referring->getKey(),
                'assigned_to_user_id' => null,
                'patient_id' => $patient->getKey(),
                'status' => ReferralStatus::SENT,
                'urgency' => Urgency::EMERGENT,
                'is_emergency' => true,
                'department' => 'Orthopaedics',
                'referral_reason' => 'Open fracture of the left tibia. Requires debridement and theatre time.',
                'symptoms' => 'Open fracture left tibia with moderate bleeding, controlled with a pressure dressing.',
                'vitals' => ['bp' => '104/68', 'hr' => 112, 'rr' => 22, 'spo2' => 95, 'temp' => 37.2],
                'consciousness' => 'Alert, anxious',
                'trauma_indicator' => true,
                'existing_conditions' => 'None known',
                'current_interventions' => 'Splint applied, IV fluids running, tetanus cover given',
                'notes' => 'Arriving by ambulance in approximately 40 minutes.',
                'ai_suggestion' => [
                    'urgency' => Urgency::EMERGENT->value,
                    'confidence' => 0.88,
                    'reason' => 'Open fracture with ongoing bleeding needs operative management within hours.',
                ],
            ],
        );

        $this->audit($referring, 'referral_created', $referral, ['to' => 'draft'], $this->clock->copy()->subHours(5));
        $this->audit($referring, 'referral_status_changed', $referral, ['from' => 'draft', 'to' => 'sent'], $this->clock->copy()->subHours(5)->addMinutes(9));
    }

    /**
     * A referral sitting in clinical review on the receiving side.
     *
     * @param  array<string, User>  $staff
     */
    private function seedReferralUnderReview(array $staff): void
    {
        $patient = Patient::updateOrCreate(
            ['reference' => 'PT-2026-0073'],
            ['name' => 'Shahina Begum', 'age' => 61, 'gender' => 'female', 'blood_group' => 'AB+'],
        );

        $referring = $staff['coordinator.rudra@rud.badbaado.bd'];
        $receiver = $staff['dr.nusrat@mhb.badbaado.bd'];

        $referral = Referral::updateOrCreate(
            ['referral_number' => 'REF-2026-SHAHINA'],
            [
                'referring_hospital_id' => $referring->hospital_id,
                'receiving_hospital_id' => $receiver->hospital_id,
                'referring_user_id' => $referring->getKey(),
                'assigned_to_user_id' => $receiver->getKey(),
                'patient_id' => $patient->getKey(),
                'status' => ReferralStatus::UNDER_REVIEW,
                'urgency' => Urgency::URGENT,
                'is_emergency' => false,
                'department' => 'Internal Medicine',
                'referral_reason' => 'Unexplained weight loss with night sweats and a persistent productive cough for six weeks. Requesting chest imaging and a consultant opinion.',
                'symptoms' => 'Loss of 9 kg over three months, drenching night sweats, productive cough without haemoptysis.',
                'vitals' => ['bp' => '118/74', 'hr' => 96, 'rr' => 20, 'spo2' => 96, 'temp' => 37.8],
                'consciousness' => 'Alert and oriented',
                'trauma_indicator' => false,
                'existing_conditions' => 'Hypertension',
                'current_interventions' => 'Chest X-ray done and uploaded. Started on empirical antibiotics.',
                'notes' => 'Smoker for 30 years. Family anxious about the possibility of cancer.',
                'ai_suggestion' => [
                    'urgency' => Urgency::URGENT->value,
                    'confidence' => 0.79,
                    'reason' => 'Constitutional symptoms over weeks warrant expedited imaging and specialist review.',
                ],
            ],
        );

        $this->audit($referring, 'referral_created', $referral, ['to' => 'draft'], $this->clock->copy()->subHours(9));
        $this->audit($referring, 'referral_status_changed', $referral, ['from' => 'draft', 'to' => 'sent'], $this->clock->copy()->subHours(9)->addMinutes(4));
        $this->audit($receiver, 'referral_status_changed', $referral, ['from' => 'sent', 'to' => 'received'], $this->clock->copy()->subHours(8));
        $this->audit($receiver, 'referral_status_changed', $referral, ['from' => 'received', 'to' => 'under_review'], $this->clock->copy()->subHours(7));

        Message::updateOrCreate(
            ['referral_id' => $referral->getKey(), 'body' => 'Our CT service runs on alternate Tuesdays. Could you review the X-ray first and let us know whether to wait for the scan?'],
            [
                'sender_user_id' => $referring->getKey(),
                'read_at' => $this->clock->copy()->subHours(7)->addMinutes(3),
            ],
        );
    }

    /**
     * A declined referral, including the reason the receiving team gave.
     *
     * @param  array<string, User>  $staff
     */
    private function seedRejectedReferral(array $staff): void
    {
        $patient = Patient::updateOrCreate(
            ['reference' => 'PT-2026-0081'],
            ['name' => 'Jahangir Alam', 'age' => 7, 'gender' => 'male', 'blood_group' => 'O-'],
        );

        $referring = $staff['dr.abdul@gzg.badbaado.bd'];
        $receiver = $staff['dr.shahriar@gfg.badbaado.bd'];

        $referral = Referral::updateOrCreate(
            ['referral_number' => 'REF-2026-JAHANGIR'],
            [
                'referring_hospital_id' => $referring->hospital_id,
                'receiving_hospital_id' => $receiver->hospital_id,
                'referring_user_id' => $referring->getKey(),
                'assigned_to_user_id' => null,
                'patient_id' => $patient->getKey(),
                'status' => ReferralStatus::REJECTED,
                'urgency' => Urgency::URGENT,
                'is_emergency' => false,
                'department' => 'General Surgery',
                'referral_reason' => 'Acute abdominal pain in a child, query appendicitis, requesting surgical review.',
                'symptoms' => 'Right lower quadrant tenderness, low-grade fever, vomiting twice.',
                'vitals' => ['bp' => '96/62', 'hr' => 110, 'rr' => 26, 'spo2' => 97, 'temp' => 38.4],
                'consciousness' => 'Alert but irritable',
                'trauma_indicator' => false,
                'existing_conditions' => 'Asthma',
                'current_interventions' => 'Paracetamol given, kept nil by mouth',
                'notes' => 'Parents have been briefed that the child may need surgery.',
                'rejection_reason' => 'Our paediatric surgery team is on leave until Friday and we have no paediatric admission capacity. Please contact Gazipur Medical College instead.',
            ],
        );

        $this->audit($referring, 'referral_created', $referral, ['to' => 'draft'], $this->clock->copy()->subDay());
        $this->audit($referring, 'referral_status_changed', $referral, ['from' => 'draft', 'to' => 'sent'], $this->clock->copy()->subDay()->addMinutes(7));
        $this->audit($receiver, 'referral_status_changed', $referral, ['from' => 'sent', 'to' => 'received'], $this->clock->copy()->subHours(20));
        $this->audit($receiver, 'referral_status_changed', $referral, ['from' => 'received', 'to' => 'under_review'], $this->clock->copy()->subHours(19));
        $this->audit($receiver, 'referral_status_changed', $referral, ['from' => 'under_review', 'to' => 'rejected'], $this->clock->copy()->subHours(18));
    }

    /**
     * A referral still in draft, showing the first stage of the lifecycle.
     *
     * @param  array<string, User>  $staff
     */
    private function seedDraftReferral(array $staff): void
    {
        $patient = Patient::updateOrCreate(
            ['reference' => 'PT-2026-0090'],
            ['name' => 'Momena Khatun', 'age' => 52, 'gender' => 'female', 'blood_group' => 'B+'],
        );

        $referring = $staff['coordinator.shifa@shm.badbaado.bd'];
        $receiver = $staff['dr.imran@ikb.badbaado.bd'] ?? null;

        $referral = Referral::updateOrCreate(
            ['referral_number' => 'REF-2026-MOMENA'],
            [
                'referring_hospital_id' => $referring->hospital_id,
                'receiving_hospital_id' => $receiver?->hospital_id,
                'intended_user_id' => $receiver?->getKey(),
                'referring_user_id' => $referring->getKey(),
                'assigned_to_user_id' => null,
                'patient_id' => $patient->getKey(),
                'status' => ReferralStatus::DRAFT,
                'urgency' => Urgency::ROUTINE,
                'is_emergency' => false,
                'department' => 'Neurosurgery',
                'referral_reason' => 'Recurrent headache with blurred vision over three months. Requesting a specialist opinion on imaging.',
                'symptoms' => 'Frontal headache in the morning, blurred vision, no vomiting or weakness.',
                'vitals' => ['bp' => '126/80', 'hr' => 78, 'rr' => 16, 'spo2' => 99, 'temp' => 36.5],
                'consciousness' => 'Alert',
                'trauma_indicator' => false,
                'existing_conditions' => 'Hypertension',
                'current_interventions' => 'Started on paracetamol 500mg as needed',
                'notes' => 'Draft — waiting for the CT report before this is sent.',
            ],
        );

        $this->audit($referring, 'referral_created', $referral, ['to' => 'draft'], $this->clock->copy()->subMinutes(35));
    }

    /**
     * Populate the notification centre for the people involved in the demo.
     *
     * @param  array<string, User>  $staff
     */
    private function seedNotifications(array $staff): void
    {
        $flagship = Referral::where('referral_number', 'REF-2026-HASSAN')->first();
        $awaiting = Referral::where('referral_number', 'REF-2026-MAHBUB')->first();
        $rejected = Referral::where('referral_number', 'REF-2026-JAHANGIR')->first();
        $underReview = Referral::where('referral_number', 'REF-2026-SHAHINA')->first();

        if ($flagship === null || $awaiting === null) {
            return;
        }

        $items = [
            [$staff['dr.aminul@shm.badbaado.bd'], $flagship, 'patient_arrived', 'Patient arrived', $flagship->referral_number.' — patient arrived at Shifa Medical', NotificationSeverity::INFO, 46],
            [$staff['dr.rahman@alhilal.bd'], $flagship, 'referral_accepted', 'Referral accepted', $flagship->referral_number.' was accepted for admission', NotificationSeverity::INFO, 24],
            [$staff['coordinator@alhilal.bd'], $flagship, 'transfer_in_progress', 'Transfer in progress', $flagship->referral_number.' — patient transfer has started', NotificationSeverity::WARNING, 31],
            [$staff['dr.aminul@shm.badbaado.bd'], $awaiting, 'referral_sent', 'New referral received', $awaiting->referral_number.' from Greenfields General', NotificationSeverity::WARNING, 20],
            [$staff['dr.abdul@gzg.badbaado.bd'], $rejected, 'referral_rejected', 'Referral rejected', $rejected->referral_number.' was rejected', NotificationSeverity::CRITICAL, 18],
            [$staff['dr.nusrat@mhb.badbaado.bd'], $underReview, 'referral_under_review', 'Referral under review', $underReview->referral_number.' is being reviewed by Mahabub Medical', NotificationSeverity::INFO, 7],
        ];

        foreach ($items as [$user, $referral, $type, $title, $body, $severity, $minutesAgo]) {
            if ($user === null || $referral === null) {
                continue;
            }

            Notification::updateOrCreate(
                ['user_id' => $user->getKey(), 'referral_id' => $referral->getKey(), 'type' => $type],
                [
                    'title' => $title,
                    'body' => $body,
                    'severity' => $severity,
                    'read_at' => $minutesAgo > 30 ? $this->clock->copy()->addMinutes($minutesAgo) : null,
                ],
            );
        }
    }

    /**
     * The stored application payload, mirroring what the registration form
     * submits so the review screen can render it unchanged.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function applicationPayload(array $data): array
    {
        $payload = $data;
        $payload['type'] = $data['type']->value;

        return $payload;
    }

    /**
     * Write an audit-log row with an explicit timestamp.
     */
    private function audit(?User $user, string $action, ?object $entity, array $metadata, Carbon $at): void
    {
        AuditLog::create([
            'user_id' => $user?->getKey(),
            'action' => $action,
            'entity_type' => $entity !== null ? class_basename($entity) : null,
            'entity_id' => $entity !== null && method_exists($entity, 'getKey') ? $entity->getKey() : null,
            'metadata' => $metadata,
            'ip_address' => '127.0.0.1',
            'created_at' => $at,
        ]);
    }
}
