<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAdminUserRequest;
use App\Http\Requests\Api\UpdateAdminUserRequest;
use App\Http\Resources\Api\UserResource;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class AdminUserController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function index(): AnonymousResourceCollection
    {
        $user = request()->user();
        $isSystem = $user->hasRole('system_admin');

        $users = User::query()
            ->when($isSystem, fn ($query) => $query, fn ($query) => $query->where('hospital_id', $user->hospital_id))
            ->when(request('q'), function ($query, $search) {
                $query->where(function ($scope) use ($search) {
                    $scope->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->with(['role', 'hospital'])
            ->orderByDesc('id')
            ->paginate(25);

        return UserResource::collection($users);
    }

    public function store(StoreAdminUserRequest $request): UserResource
    {
        $actor = $request->user();
        $role = Role::firstOrCreate(['slug' => $request->validated('role_slug')], [
            'name' => Str::title(str_replace('_', ' ', $request->validated('role_slug'))),
        ]);

        $hospitalId = $actor->hasRole('system_admin')
            ? $request->validated('hospital_id')
            : $actor->hospital_id;

        if (! $actor->hasRole('system_admin') && $role->slug === 'system_admin') {
            abort(403, 'Hospital administrators cannot create system admins.');
        }

        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
            'title' => $request->validated('title'),
            'phone' => $request->validated('phone'),
            'role_id' => $role->id,
            'hospital_id' => $hospitalId,
            'is_active' => true,
        ]);

        $this->auditLogger->record($request->user(), 'user_created', $user, [
            'role' => $role->slug,
            'hospital_id' => $hospitalId,
        ]);

        return new UserResource($user->load(['role', 'hospital']));
    }

    public function update(UpdateAdminUserRequest $request, User $user): UserResource
    {
        $actor = $request->user();
        $isSystem = $actor->hasRole('system_admin');

        if (! $isSystem) {
            if ($user->hasRole('system_admin') || $user->hospital_id !== $actor->hospital_id) {
                abort(403, 'You can only manage users of your own hospital.');
            }
        }

        if ($user->is($actor) && $request->filled('is_active') && ! $request->boolean('is_active')) {
            abort(422, 'You cannot deactivate your own account.');
        }

        $updates = [];

        if ($request->filled('role_slug')) {
            $role = Role::firstOrCreate(['slug' => $request->validated('role_slug')], [
                'name' => Str::title(str_replace('_', ' ', $request->validated('role_slug'))),
            ]);
            if (! $isSystem && $role->slug === 'system_admin') {
                abort(403, 'Hospital administrators cannot assign the system admin role.');
            }
            $updates['role_id'] = $role->id;
        }

        if ($request->exists('hospital_id')) {
            $updates['hospital_id'] = $request->validated('hospital_id');
        }

        if ($request->exists('is_active')) {
            $updates['is_active'] = $request->boolean('is_active');
        }

        $user->update($updates);

        $this->auditLogger->record($request->user(), 'user_updated', $user, array_keys($updates));

        return new UserResource($user->fresh(['role', 'hospital']));
    }
}
