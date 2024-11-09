<?php

namespace App\Http\Controllers;

use App\CustomValidation\CustomValue;
use App\Events\UserMangement\UserLoggedIn;
use App\Events\UserMangement\UserLoggedOut;
use App\Events\UserMangement\UserRegistered;
use App\Models\User;
use App\Traits\ValidationErrorFormatter;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class Authentication extends Controller
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

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password'])
            ]);
       
            $expireDate = now()->addDays(7);
            $token = $user->createToken('my_token', expiresAt: $expireDate)->plainTextToken;

            event(new UserRegistered($user));
            return response()->json(['success' => true, 'message' => 'Successfully', 'token' => $token], 201);
        } catch (ValidationException $e) {
            $formattedErrors = $this->formatValidationError($e->errors());
            return response(['success' => false, 'message' => 'Unseccessfully', 'errors' => $formattedErrors], 422);
        } catch (Exception $e) {
            Log::error("error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Internal server errors'], 500);
        }
    }

    public function login()
    {
        try {
            $validated = Validator::make($this->req->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ], CustomValue::LoginMsg())->validate();

            $user = User::where('email', $validated['email'])->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return response(['success' => false, 'message' => "Invalid email or password"], 422);
            }

            $expireDate = now()->addDays(7);
            $token = $user->createToken('my_token', expiresAt: $expireDate)->plainTextToken;

            event(new UserLoggedIn($user));
            return response()->json(['success' => true, 'message' => 'Successfully', 'token' => $token], 200);
        } catch (ValidationException $e) {
            $formattedErrors = $this->formatValidationError($e->errors());
            return response(['success' => false, 'message' => 'Unseccessfully', 'errors' => $formattedErrors], 422);
        } catch (Exception $e) {
            Log("error: ", $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Internal server errors'], 500);
        }
    }

    public function logout()
    {
        $user = $this->req->user();
        $user->currentAccessToken()->delete();

        event(new UserLoggedOut($user));

        return response()->json(['success'=>true , 'message' => 'logged out successfully'], 200);
    }

    public function getUserDetails()
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Successfully',
                'data' => $this->req->user()
            ], 200);
        } catch (ModelNotFoundException $e) {
            Log::error('User not found: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal server errors'], 500);
        }
    }
}
