<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ValidationErrorFormatter;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class verificationController extends Controller
{
    use ValidationErrorFormatter;

    private Request $req;

    public function __construct(Request $req)
    {
        $this->req = $req;
    }

    public function verifyEmail()
    {
        try {
            $this->req->validate([
                'otp' => 'required|string|size:6',
            ]);

            $user = User::where('otp', $this->req->otp)
                ->where('otp_expires_at', '>', Carbon::now())
                ->whereNull('email_verified_at')
                ->first();

            if (!$user || !$user->verifyOTP($this->req->otp)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired OTP'
                ], 400);
            }

            $user->update(['email_verified_at' => Carbon::now()]);
            $user->clearOTP();

            return response()->json([
                'success' => true,
                'message' => 'Email verified successfully'
            ], 200);
        } catch (ValidationException $e) {
            $formattedErrors = $this->formatValidationError($e->errors());
            return response()->json(['success' => false, 'message' => 'Unseccessfully', 'errors' => $formattedErrors], 422);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal server errors'], 500);
        }
    }

    public function resendVerificationOTP()
    {
        try {
            $this->req->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            $user = User::where('email', $this->req->email)->first();

            if ($user->email_verified_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email is already verified'
                ], 400);
            }

            if (!$user->canRequestOTP()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please wait before requesting another OTP',
                    'retry_after' => $user->getOTPCooldownSeconds()
                ], 429);
            }

            if (!$user->sendOTP('verification')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send OTP email'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Verification OTP has been resent to your email'
            ], 200);
        } catch (ValidationException $e) {
            $formattedErrors = $this->formatValidationError($e->errors());
            return response()->json(['success' => false, 'message' => 'Unseccessfully', 'errors' => $formattedErrors], 422);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal server errors'], 500);
        }
    }
}
