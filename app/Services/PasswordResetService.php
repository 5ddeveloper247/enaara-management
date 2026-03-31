<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Throwable;

class PasswordResetService
{
    public function sendResetLink(array $validated): array
    {
        try {
            $status = Password::sendResetLink([
                'email' => $validated['email'],
            ]);

            if ($status === Password::RESET_LINK_SENT) {
                return [
                    'success' => true,
                    'message' => 'A password reset link has been sent to your email.',
                ];
            }

            return [
                'success' => false,
                'message' => 'Unable to send password reset link. Please try again.',
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'Unable to send password reset link. Please try again.',
            ];
        }
    }

    public function resetPassword(array $validated): array
    {
        try {
            $status = Password::reset(
                [
                    'email' => $validated['email'],
                    'token' => $validated['token'],
                    'password' => $validated['password'],
                    'password_confirmation' => $validated['password_confirmation'],
                ],
                function (User $user, string $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                    ])->save();
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return [
                    'success' => true,
                    'message' => 'Password reset successful. Please login with your new password.',
                ];
            }

            return [
                'success' => false,
                'message' => 'Invalid or expired reset token. Please request a new link.',
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'Password reset failed. Please try again.',
            ];
        }
    }
}

