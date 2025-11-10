<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\OtpCode;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Services\SemaphoreService;
use Illuminate\Support\Facades\Hash;

class ForgotPasswordController extends Controller
{
    protected $semaphore;

    public function __construct(SemaphoreService $semaphore)
    {
        $this->semaphore = $semaphore;
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $user = User::where('phone', $request->phone)->first();
        if (!$user) {
            return response()->json(['message' => 'No user found with that phone number.'], 404);
        }

        $otp = rand(100000, 999999);
        OtpCode::updateOrCreate(
            ['phone' => $request->phone],
            ['otp' => $otp, 'expires_at' => Carbon::now()->addMinutes(5)]
        );

        $message = "Your CPMS OTP is: $otp. It will expire in 5 minutes.";
        $response = $this->semaphore->sendSMS($request->phone, $message);

        return response()->json(['message' => 'OTP sent successfully.']);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'otp' => 'required|string',
            'password' => 'required|string|min:8',
        ]);

        $otpRecord = OtpCode::where('phone', $request->phone)
            ->where('otp', $request->otp)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$otpRecord) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 400);
        }

        $user = User::where('phone', $request->phone)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        $otpRecord->delete();

        return response()->json(['message' => 'Password has been reset successfully.']);
    }
}
