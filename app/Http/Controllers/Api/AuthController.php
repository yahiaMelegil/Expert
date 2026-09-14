<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register a regular user and issue an access token.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        [$user, $plainTextToken] = DB::transaction(function () use ($validated): array {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            return [
                $user,
                $user->createToken(
                    $this->tokenName($validated),
                    [User::ACCESS_ABILITY],
                )->plainTextToken,
            ];
        });

        $user->sendEmailVerificationNotification();

        return $this->authenticationResponse(
            $user,
            $plainTextToken,
            'Registration completed successfully.',
            201,
        );
    }

    /**
     * Verify credentials and issue a new access token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = User::query()->where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'The provided credentials are incorrect.',
            ], 401);
        }

        $plainTextToken = $user
            ->createToken($this->tokenName($validated), [User::ACCESS_ABILITY])
            ->plainTextToken;

        return $this->authenticationResponse(
            $user,
            $plainTextToken,
            'Authentication completed successfully.',
        );
    }

    /**
     * Return the authenticated user.
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Authenticated user retrieved successfully.',
            'data' => [
                'user' => new UserResource($request->user()),
            ],
        ]);
    }

    /**
     * Revoke the token used for the current request.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully.',
            'data' => null,
        ]);
    }

    /**
     * Revoke every access token owned by the authenticated user.
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logged out from all devices successfully.',
            'data' => null,
        ]);
    }

    /**
     * Build a successful authentication response.
     */
    private function authenticationResponse(
        User $user,
        string $plainTextToken,
        string $message,
        int $statusCode = 200,
    ): JsonResponse {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => [
                'user' => new UserResource($user),
                'token' => $plainTextToken,
                'token_type' => 'Bearer',
            ],
        ], $statusCode);
    }

    /**
     * Get a meaningful token name without requiring one from the client.
     *
     * @param  array<string, mixed>  $validated
     */
    private function tokenName(array $validated): string
    {
        return $validated['device_name'] ?? 'API Token';
    }
}
