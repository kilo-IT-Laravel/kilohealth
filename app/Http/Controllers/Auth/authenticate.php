<?php

namespace App\Http\Controllers\Auth;

use App\CustomValidation\CustomValue;
use App\Events\UserMangement\UserInfoUpdated;
use App\Http\Controllers\Controller;
use App\Http\Resources\auth_user;
use App\Models\Role;
use App\Models\User;
use App\Traits\ValidationErrorFormatter;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class authenticate extends Controller
{
    use ValidationErrorFormatter;

    private Request $req;
    public function __construct(Request $req)
    {
        $this->req = $req;
    }
    public function register()
    {
        try {
            $validated = Validator::make($this->req->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed'
            ])->validate();

            $defaultId = Role::where('name', 'user')->first()->id;

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role_id' => $defaultId
            ]);

            if (!$user->sendOTP('verification')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send OTP email'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Register successfully please go your email to verify your account',
            ], 201);
        } catch (ValidationException $e) {

            $formattedErrors = $this->formatValidationError($e->errors());

            return response(['success' => false, 'message' => 'Unsuccessfully', 'errors' => $formattedErrors], 422);
        } catch (Exception $e) {
            Log::error("error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => "Internal server errors"], 500);
        }
    }

    public function login()
    {
        try {
            $validated = Validator::make($this->req->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ])->validate();

            $user = User::where('email', $validated['email'])->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return response(['success' => false, 'message' => "Invalid email or password"], 422);
            }

            if (!$user->email_verified_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please verify your email first'
                ], 403);
            }

            $expireDate = now()->addDays(7);
            $token = $user->createToken('my_token', expiresAt: $expireDate)->plainTextToken;

            return response()->json(['success' => true, 'message' => 'Successfully', 'token' => $token], 200);
        } catch (ValidationException $e) {

            $formattedErrors = $this->formatValidationError($e->errors());

            return response(['success' => false, 'message' => 'Unsuccessfully', 'errors' => $formattedErrors], 422);
        } catch (Exception $e) {
            Log("error: ", $e->getMessage());
            return response()->json(['success' => false, 'message' => "Internal server errors"], 500);
        }
    }

    public function getUser()
    {
        try {
            return response()->json(['success' => true, 'message' => 'Successfully', 'data' => new auth_user($this->req->user())], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'User not found'], 500);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Unsuccessfully'], 500);
        }
    }

    public function logout()
    {

        $this->req->user()->currentAccessToken()->delete();

        return response()->json(['success' => true, 'message' => 'Logged out successfully!'], 200);
    }

    public function updateUserInfo()
    {
        try {
            $this->req->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|string|email',
                'avatar' => 'sometimes|file|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $user = User::findOrFail($this->req->user()->id);

            $data = $this->req->only(['name', 'email']);

            if ($this->req->hasFile('avatar')) {
                if ($user->avatar) {
                    Storage::disk('s3')->delete($user->avatar);
                }
                $user->avatar = $this->req->file('avatar')->store('avatar', 's3');
            }

            $user->update($data);

            event(new UserInfoUpdated($user));

            return response()->json(['success' => true, 'message' => 'Successfully', 'data' => $user], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => "Internal server errors"], 500);
        }
    }

    public function changePassword()
    {
        $this->req->validate([
            'current_password' => 'required',
            'new_password' => 'required|confirmed|min:8',
        ]);
        $user = $this->req->user();
        try {
            if (!Hash::check($this->req->current_password, $user->password)) {
                return response()->json(['success' => false, 'message' => 'Your current password does not match our records.'], 422);
            }

            $user->password = Hash::make($this->req->new_password);

            $user->save();

            return response()->json(['success' => true, 'message' => 'Successfully'], 200);
        } catch (ValidationException $e) {
            $formattedErrors = $this->formatValidationError($e->errors());
            return response()->json(['success' => false, 'message' => 'Unsuccessfully', 'errors' => $formattedErrors], 422);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Unsuccessfully'], 500);
        }
    }
}
