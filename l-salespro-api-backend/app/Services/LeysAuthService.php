<?php

namespace App\Services;

use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Password;

class LeysAuthService
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Attempt login and generate Sanctum token
     */
    public function login(array $credentials): array
    {
        $user = $this->userRepository->findByEmail($credentials['email']);

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => ['Account is not active.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return [
            'token' => $token,
            'user'  => $user,
        ];
    }

    /**
     * Logout - revoke current token
     */
    public function logout($user): void
    {
        $user->currentAccessToken()->delete();
    }

    /**
     * Refresh token - revoke old, issue new
     */
    public function refresh($user): string
    {
        $user->currentAccessToken()->delete();
        return $user->createToken('api-token')->plainTextToken;
    }

    /**
     * Send password reset link
     */
    public function sendResetLink(array $data): string
    {
        $status = Password::sendResetLink($data);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages(['email' => [__($status)]]);
        }

        return __($status);
    }

    /**
     * Reset password with token
     */
    public function resetPassword(array $data): void
    {
        $status = Password::reset($data, function ($user, $password) {
            $this->userRepository->updatePassword($user, $password);
        });

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => [__($status)]]);
        }
    }
}