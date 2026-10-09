<?php

namespace App\Services;

use App\Enums\ProviderApplicationStatus;
use App\Enums\ProviderApplicationType;
use App\Enums\UserStatus;
use App\Models\Hospital;
use App\Models\ProviderApplication;
use App\Models\Role;
use App\Models\User;
use App\Support\AuditActions;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProviderRegistrationService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * Record a signup as a pending account plus a reviewable application.
     * The user row exists from the start so the password is reserved and the
     * applicant can sign in to check their own status, but it carries no role
     * and no facility until a system administrator approves it.
     *
     * @param  array<string, mixed>  $payload
     */
    public function submit(ProviderApplicationType $type, array $payload): ProviderApplication
    {
        return DB::transaction(function () use ($type, $payload): ProviderApplication {
            $user = User::create([
                'name' => $payload['name'],
                'email' => $payload['email'],
                'password' => $payload['password'],
                'phone' => $payload['phone'] ?? null,
                'title' => $payload['title'] ?? null,
                'specialty_id' => $type === ProviderApplicationType::DOCTOR ? $payload['specialty_id'] : null,
                'status' => UserStatus::PENDING,
            ]);

            $application = ProviderApplication::create([
                'type' => $type,
                'status' => ProviderApplicationStatus::PENDING,
                'user_id' => $user->getKey(),
                'payload' => Arr::except($payload, ['password', 'password_confirmation']),
            ]);

            $this->auditLogger->record(null, AuditActions::PROVIDER_APPLICATION_SUBMITTED, $application, [
                'type' => $type->value,
                'email' => $user->email,
            ]);

            return $application;
        });
    }

    /**
     * Grant console access: create the facility, assign the role, activate.
     */
    public function approve(ProviderApplication $application, User $reviewer): ProviderApplication
    {
        if (! $application->status->isPending()) {
            return $application;
        }

        DB::transaction(function () use ($application, $reviewer): void {
            $user = $application->user;
            $hospital = $this->createFacility($application);

            // firstOrCreate, so a missing role row can never leave an active
            // account that silently fails every role check.
            $role = Role::firstOrCreate(
                ['slug' => $this->roleSlugFor($application->type)],
                ['name' => Str::title(str_replace('_', ' ', $this->roleSlugFor($application->type)))],
            );

            $user->update([
                'hospital_id' => $hospital->getKey(),
                'role_id' => $role->getKey(),
                'status' => UserStatus::ACTIVE,
            ]);

            $application->update([
                'status' => ProviderApplicationStatus::APPROVED,
                'hospital_id' => $hospital->getKey(),
                'reviewed_by' => $reviewer->getKey(),
                'reviewed_at' => now(),
            ]);
        });

        $this->auditLogger->record($reviewer, AuditActions::PROVIDER_APPLICATION_APPROVED, $application, [
            'type' => $application->type->value,
            'user_id' => $application->user_id,
            'hospital_id' => $application->hospital_id,
        ]);

        return $application->refresh();
    }

    /**
     * Refuse the application and close the account.
     */
    public function reject(ProviderApplication $application, User $reviewer, ?string $reason): ProviderApplication
    {
        if (! $application->status->isPending()) {
            return $application;
        }

        DB::transaction(function () use ($application, $reviewer, $reason): void {
            $application->user?->update(['status' => UserStatus::REJECTED]);

            $application->update([
                'status' => ProviderApplicationStatus::REJECTED,
                'reviewed_by' => $reviewer->getKey(),
                'reviewed_at' => now(),
                'rejection_reason' => $reason,
            ]);
        });

        $this->auditLogger->record($reviewer, AuditActions::PROVIDER_APPLICATION_REJECTED, $application, [
            'type' => $application->type->value,
            'user_id' => $application->user_id,
        ]);

        return $application->refresh();
    }

    /**
     * The console role granted to an approved application of this type.
     */
    public function roleSlugFor(ProviderApplicationType $type): string
    {
        return $type === ProviderApplicationType::HOSPITAL
            ? 'hospital_admin'
            : 'healthcare_worker';
    }

    /**
     * A hospital is a referral facility in its own right; a doctor gets a
     * private practice so the existing hospital tenancy keeps working.
     */
    private function createFacility(ProviderApplication $application): Hospital
    {
        $payload = $application->payload;
        $user = $application->user;

        $name = $application->type === ProviderApplicationType::HOSPITAL
            ? $payload['facility_name']
            : sprintf('%s — Private Practice', $user->name);

        [$shortName, $code] = $this->uniqueCodes($name, $application->type);

        return Hospital::create([
            'name' => $name,
            'short_name' => $shortName,
            'code' => $code,
            'kind' => $application->type->facilityKind(),
            'location' => $payload['location'] ?? null,
            'phone' => $payload['phone'] ?? null,
            'email' => $user->email,
            'level' => $payload['level'] ?? 'secondary',
            'is_active' => true,
        ]);
    }

    /**
     * @return array{0: string, 1: string} short name and facility code
     */
    private function uniqueCodes(string $name, ProviderApplicationType $type): array
    {
        $base = Str::upper(Str::substr(preg_replace('/[^A-Za-z0-9]+/', '', $name) ?: 'BADBAADO', 0, 4));
        $suffix = Str::upper(Str::random(4));

        return [
            $type === ProviderApplicationType::HOSPITAL ? "HSP-{$suffix}" : "PRC-{$suffix}",
            "{$base}-{$suffix}",
        ];
    }
}
