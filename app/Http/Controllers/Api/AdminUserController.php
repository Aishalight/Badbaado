<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAdminUserRequest;
use App\Http\Requests\Api\UpdateAdminUserRequest;
use App\Http\Resources\Api\UserResource;
use App\Models\Message;
use App\Models\Referral;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class AdminUserController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function index(): AnonymousResourceCollection
    {
        $user = request()->user();
        $isSystem = $user->hasRole('system_admin');
        $hospitalAdminRoleId = Role::where('slug', 'hospital_admin')->value('id');

        $users = User::query()
            ->when($isSystem, fn ($query) => $query->where('role_id', $hospitalAdminRoleId), fn ($query) => $query->where('hospital_id', $user->hospital_id)->whereNot('role_id', $hospitalAdminRoleId))
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
        $roleSlug = $request->validated('role_slug');
        $isSystem = $actor->hasRole('system_admin');
        $hospitalAdminRoleId = Role::where('slug', 'hospital_admin')->value('id');

        $role = Role::firstOrCreate(['slug' => $roleSlug], [
            'name' => Str::title(str_replace('_', ' ', $roleSlug)),
        ]);

        if ($isSystem && $roleSlug !== 'hospital_admin') {
            abort(403, 'System administrators can only create hospital administrators.');
        }
        if (! $isSystem && $roleSlug === 'hospital_admin') {
            abort(403, 'Hospital administrators cannot create other administrators.');
        }

        $hospitalId = $actor->hasRole('system_admin')
            ? $request->validated('hospital_id')
            : $actor->hospital_id;

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

    public function destroy(User $user): JsonResponse
    {
        $actor = request()->user();
        $isSystem = $actor->hasRole('system_admin');
        $hospitalAdminRoleId = Role::where('slug', 'hospital_admin')->value('id');

        if (! $isSystem && ($user->hasRole('system_admin') || $user->hospital_id !== $actor->hospital_id || $user->role_id === $hospitalAdminRoleId)) {
            abort(403, 'You can only delete users of your own hospital.');
        }

        if ($user->is($actor)) {
            abort(422, 'You cannot delete your own account.');
        }

        $historyCount = Referral::where('referring_user_id', $user->id)
            ->orWhere('coordinator_user_id', $user->id)
            ->count()
            + Message::where('sender_user_id', $user->id)->count();

        if ($historyCount > 0) {
            abort(422, 'This user has referral or message history and cannot be deleted. Deactivate them instead.');
        }

        $this->auditLogger->record($actor, 'user_deleted', $user, ['email' => $user->email]);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    public function update(UpdateAdminUserRequest $request, User $user): UserResource
    {
        $actor = $request->user();
        $isSystem = $actor->hasRole('system_admin');
        $hospitalAdminRoleId = Role::where('slug', 'hospital_admin')->value('id');

        if (! $isSystem) {
            if ($user->hasRole('system_admin') || $user->hospital_id !== $actor->hospital_id || $user->role_id === $hospitalAdminRoleId) {
                abort(403, 'You can only manage users of your own hospital.');
            }
        }

        if ($user->is($actor) && $request->filled('is_active') && ! $request->boolean('is_active')) {
            abort(422, 'You cannot deactivate your own account.');
        }

        $updates = [];

        if ($request->filled('name')) {
            $updates['name'] = $request->validated('name');
        }
        if ($request->filled('email')) {
            $updates['email'] = $request->validated('email');
        }
        if ($request->filled('title')) {
            $updates['title'] = $request->validated('title');
        }
        if ($request->filled('phone')) {
            $updates['phone'] = $request->validated('phone');
        }

        if ($request->filled('role_slug')) {
            $roleSlug = $request->validated('role_slug');
            $role = Role::firstOrCreate(['slug' => $roleSlug], [
                'name' => Str::title(str_replace('_', ' ', $roleSlug)),
            ]);
            if ($isSystem && $roleSlug !== 'hospital_admin') {
                abort(403, 'System administrators can only assign the hospital administrator role.');
            }
            if (! $isSystem && ($roleSlug === 'hospital_admin' || $roleSlug === 'system_admin')) {
                abort(403, 'You cannot assign administrator roles.');
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
