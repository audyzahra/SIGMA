<?php

namespace App\Services;

use App\Mail\EmailVerificationCode;
use App\Models\EmailVerification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class EmailVerificationService
{
    public function send(User $user): void
    {
        $code = (string) random_int(100000, 999999);

        EmailVerification::where('user_id', $user->id)->delete();
        EmailVerification::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'otp_code' => $code,
            'expired_at' => now()->addMinutes(10),
        ]);

        Mail::to($user->email)->send(new EmailVerificationCode($code));
    }

    public function verify(string $email, string $code): ?User
    {
        return DB::transaction(function () use ($email, $code): ?User {
            $verification = EmailVerification::where('email', $email)
                ->where('otp_code', $code)
                ->where('expired_at', '>', now())
                ->latest()
                ->first();

            if (! $verification) {
                return null;
            }

            $user = $verification->user;
            $user->forceFill(['email_verified_at' => now()])->save();
            $verification->delete();

            return $user;
        });
    }
}
