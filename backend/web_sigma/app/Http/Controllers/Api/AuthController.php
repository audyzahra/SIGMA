<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CreatePetugasRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResendVerificationCodeRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Models\User;
use App\Services\EmailVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(private readonly EmailVerificationService $verificationService) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = DB::transaction(function () use ($request): User {
            $user = User::create($request->safe()->only(['name', 'email', 'password']));
            $user->assignRole($request->string('role')->toString());
            $this->verificationService->send($user);

            return $user;
        });

        return response()->json([
            'message' => 'Registrasi berhasil. Silakan cek email untuk kode verifikasi.',
            'user' => $user,
        ], 201);
    }

    public function verifyEmail(VerifyEmailRequest $request): JsonResponse
    {
        $user = $this->verificationService->verify(
            $request->string('email')->toString(),
            $request->string('otp_code')->toString(),
        );

        if (! $user) {
            return response()->json(['message' => 'Kode verifikasi salah atau sudah kedaluwarsa.'], 422);
        }

        return response()->json(['message' => 'Email berhasil diverifikasi.']);
    }

    public function createPetugas(CreatePetugasRequest $request): JsonResponse
    {
        $user = DB::transaction(function () use ($request): User {
            $user = User::create($request->safe()->only(['name', 'email', 'password']));
            $user->assignRole('officer');
            $this->verificationService->send($user);

            return $user;
        });

        return response()->json([
            'message' => 'Akun petugas dibuat. Kode verifikasi telah dikirim ke email.',
            'user' => $user,
        ], 201);
    }

    public function resendCode(ResendVerificationCodeRequest $request): JsonResponse
    {
        $user = User::where('email', $request->string('email')->toString())->firstOrFail();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email sudah diverifikasi.'], 422);
        }

        $this->verificationService->send($user);

        return response()->json(['message' => 'Kode verifikasi baru telah dikirim.']);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->string('email')->toString())->first();

        if (! $user || ! Hash::check($request->string('password')->toString(), $user->password)) {
            return response()->json(['message' => 'Email atau password salah.'], 401);
        }

        if (! $user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email belum diverifikasi.'], 403);
        }

        $requestedRole = $request->string('role')->toString();
        if (! $user->hasRole($requestedRole)) {
            return response()->json(['message' => 'Akun tidak memiliki akses sebagai role tersebut.'], 403);
        }

        $role = $requestedRole !== '' ? $requestedRole : $user->getRoleNames()->first();

        return response()->json([
            'message' => 'Login berhasil.',
            'token' => $user->createToken('sigma-token')->plainTextToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $requestedRole,
            ],
            'role' => $role,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logout berhasil.']);
    }
}
