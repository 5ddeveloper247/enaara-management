<?php

namespace App\Services;

use App\Models\Session;
use App\Models\User;
use Illuminate\Support\Str;

class UserAuthenticationInvalidationService
{
    public function invalidate(User $user, ?string $exceptSessionId = null): void
    {
        if (config('session.driver') === 'database') {
            $query = Session::query()->where('user_id', $user->id);
            if ($exceptSessionId) {
                $query->where('id', '!=', $exceptSessionId);
            }
            $query->delete();
        }

        $user->forceFill([
            'remember_token' => Str::random(60),
        ])->save();
    }
}
