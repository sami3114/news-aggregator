<?php

namespace App\Http\Controllers\Api;

use App\Contracts\UserPreferenceRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Response\ResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        protected UserPreferenceRepositoryInterface $userPreferenceRepository
    ){}

    /**
     * Register a new user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return ResponseService::successResponse(
            'User registered successfully',
            null,
            201
        );
    }

    /**
     * Login user and create token
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return ResponseService::successResponse(
            'Login successful',
            [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'token' => $token,
            ]
        );
    }

    /**
     * Logout user (revoke token)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return ResponseService::successResponse('Logged out successfully');
    }

    /**
     * Get authenticated user with preferences
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        $preference = $this->userPreferenceRepository->findByUserId($user->id);

        return ResponseService::successResponse(
            'User retrieved successfully',
            [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'preferences' => [
                    'preferred_sources' => $preference?->preferred_sources ?? [],
                    'preferred_categories' => $preference?->preferred_categories ?? [],
                    'preferred_authors' => $preference?->preferred_authors ?? [],
                ],
            ]
        );
    }
}
