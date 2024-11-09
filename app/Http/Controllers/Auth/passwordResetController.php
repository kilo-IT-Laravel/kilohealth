<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ValidationErrorFormatter;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class passwordResetController extends Controller
{
    use ValidationErrorFormatter;

    private Request $req;

    public function __construct(Request $req)
    {
        $this->req = $req;
    }

    public function forgotPassword()
    {
        try {
            $this->req->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            $user = User::where('email', $this->req->email)->first();

            if (!$user->canRequestOTP()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please wait before requesting another OTP',
                    'retry_after' => $user->getOTPCooldownSeconds()
                ], 429);
            }

            if (!$user->sendOTP('reset')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send OTP email'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Password reset OTP has been sent to your email'
            ], 200);
        } catch (ValidationException $e) {
            $formattedErrors = $this->formatValidationError($e->errors());
            return response()->json(['success' => false, 'message' => 'Unsuccessfully', 'errors' => $formattedErrors], 422);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal server errors'], 500);
        }
    }

    public function resetPassword()
    {
        try {
            $this->req->validate([
                'otp' => 'required|string|size:6',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $user = User::where('otp', $this->req->otp)->first();

            if (!$user || !$user->verifyOTP($this->req->otp)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired OTP'
                ], 400);
            }

            $user->update([
                'password' => Hash::make($this->req->password),
            ]);
            $user->clearOTP();

            return response()->json([
                'success' => true,
                'message' => 'Successfully'
            ], 200);
        } catch (ValidationException $e) {
            $formattedErrors = $this->formatValidationError($e->errors());
            return response()->json(['success' => false, 'message' => 'Unsuccessfully', 'errors' => $formattedErrors], 422);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal server errors'], 500);
        }
    }

    public function resendResetPasswordOTP()
    {
        try {
            $this->req->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            $user = User::where('email', $this->req->email)->first();

            if (!$user->canRequestOTP()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please wait before requesting another OTP',
                    'retry_after' => $user->getOTPCooldownSeconds()
                ], 429);
            }

            if (!$user->sendOTP('reset')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send OTP email'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Password reset OTP has been sent to your email'
            ], 200);
        } catch (ValidationException $e) {
            $formattedErrors = $this->formatValidationError($e->errors());
            return response()->json(['success' => false, 'message' => 'Unsuccessfully', 'errors' => $formattedErrors], 422);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal server errors'], 500);
        }
    }
}
