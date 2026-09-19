<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Resources\Api\UserResource;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\SettingsService;
use App\Support\AuditActions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly SettingsService $settingsService,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        if (! $this->settingsService->get('auth.registration_enabled', true)) {
            throw ValidationException::withMessages([
                'email' => ['Registration is currently disabled on the platform.'],
            ]);
        }

        $role = Role::where('slug', 'healthcare_worker')->firstOrFail();

        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user->load(['role', 'hospital'])),
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::query()
            ->where('email', $request->validated('email'))
            ->with(['role', 'hospital'])
            ->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            $this->auditLogger->record(null, AuditActions::AUTH_LOGIN_FAILED, null, [
                'email' => $request->validated('email'),
                'reason' => 'invalid_credentials',
                'guard' => 'sanctum',
            ]);

            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->is_active) {
            $this->auditLogger->record($user, AuditActions::AUTH_INACTIVE_ACCOUNT, $user, [
                'email' => $user->email,
                'guard' => 'sanctum',
            ]);

            throw ValidationException::withMessages([
                'email' => ['This account is disabled. Contact your administrator.'],
            ]);
        }

        $token = $user->createToken('auth')->plainTextToken;

        $this->auditLogger->record($user, AuditActions::AUTH_LOGIN, $user, [
            'guard' => 'sanctum',
            'email' => $user->email,
        ]);

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();

        $this->auditLogger->record($user, AuditActions::AUTH_LOGOUT, $user, [
            'guard' => 'sanctum',
            'email' => $user->email,
        ]);

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user()->load(['role', 'hospital']));
    }
}
