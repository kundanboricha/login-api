<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Traits\GenerateApiTokenTrait;
use App\Traits\ApiResponserTrait;

class AuthenticationAPisController extends Controller
{
    use ApiResponserTrait;

    public function register(Request $request)
    {
        // dd($request->all());
        try {
            $validator = validator::make($request->all(), [
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'password' => 'required',
                'status' => 'required|numeric|digits:1|in:1,2',
            ]);
            if ($validator->fails()) {
                $errorMessages = [];
                foreach ($validator->errors()->messages() as $field => $messages) {
                    $errorMessages[$field] = $messages[0];
                }
                return $this->validationErrorResponse($errorMessages, 'Validation failed', 422);
            }

            $existingUser = User::withTrashed()->where('email', $request->email)->first();
            if ($existingUser) {
                return $this->conflictResponse('A user with this email address already existsss.');
            }

            $otp = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $user = new User;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = \Hash::make($request->password);
            $user->status = $request->status;
            $user->created_at = now();
            $user->updated_at = now();
            $hashedOtp = \Hash::make($otp);
            $user->otp = $hashedOtp;
            $user->save();

            Mail::to($request->email)->queue(new OtpMail($otp));
            return $this->successResponse(new UserResource($user), 'User has been registered successfully', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred while registering the user: ' . $e->getMessage(), 500);
        }
    }

    // public function login(Request $request)
    // {
    //     try {
    //         $credentials = [
    //             'email' => $request->input('email'),
    //             'password' => $request->input('password')
    //         ];
        
    //         if (auth()->attempt($credentials)) 
    //         {
    //             $user = auth()->user();

    //             if (empty($user->email_verified_at)) {
                    
    //                 if (!empty($user->otp) && $user->otp === $request->input('otp')) {
    //                     $user->email_verified_at = now();
    //                     $user->otp = null;
    //                     $user->save();
    //                 } else {
    //                     return response()->json([
    //                         'error' => 'Invalid OTP',
    //                         'message' => 'The OTP you provided is incorrect.'
    //                     ], 401);
    //                 }

    //             }
    //             $token = $request->user()->createToken('token');
    //             $token = $token->plainTextToken;
    
    //             return response()->json([
    //                 'message' => 'Login successful ' . $user->name,
    //                 'token' => $token
    //             ], 200);
    //         } 
    //         else {
    //             return response()->json(['error' => 'Unauthorized'], 401);
    //         }
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => 'An error occurred'], 500);
    //     }
    // }
    
    // public function login(Request $request)
    // {
    //     $credentials = $request->only('email', 'password');
    
    //     if (auth()->attempt($credentials)) {
    //         $user = auth()->user();

    //         if ($user->email_verified_at === null) {
                
    //             auth()->logout(); // Log the user out if email is not verified
    //             return response()->json([
    //                 'error' => 'Email Verification Needed',
    //                 'message' => 'Email verification is required before logging in.'
    //             ], 401);
    //         }
    //         // User is logged in and email is verified, you can add additional logic here if needed.
    
    //         // $token = $request->user()->createToken('token');
    //         // $token = $token->plainTextToken;

    //         // return response()->json([
    //         //     'message' => 'Login successful ' . $user->name,
    //         //     'token' => $token
    //         // ], 200);
    //         return response()->json([
    //             'message' => 'Successfully logged in.',
    //             'data' => new UserResource($user),
    //             'token' =>$user->generateApiToken()
    //         ], 200);
    //     }
    
    //     return response()->json([
    //         'error' => 'Unauthorized',
    //         'message' => 'Invalid credentials.'
    //     ], 401);
    // }
    
    public function login(Request $request)
    {
            $validator = validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ]);
            if ($validator->fails()) {
                $errorMessages = [];
                foreach ($validator->errors()->messages() as $field => $messages) {
                    $errorMessages[$field] = $messages[0];
                }
                return $this->validationErrorResponse($errorMessages, 'Validation failed', 422);
            }

            $credentials = $request->only('email', 'password');
            
            if (Auth::attempt($credentials))
             {
                $user = Auth::user();
                if ($user->email_verified_at === null) {
                    $otp = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
                    $hashedOtp = \Hash::make($otp);
                    $user->otp = $hashedOtp;
                    $user->save();
                    Mail::to($user->email)->queue(new OtpMail($otp));
                    return $this->conflictResponse('Email Verification Needed An OTP has been sent to your email. Please check your inbox.', 422);
                }
                $token = $request->user()->createToken('token');
                $token = $token->plainTextToken;

                return $this->successResponse([
                    'message' => 'User Login Successfully.',
                    'data' => new UserResource($user),
                    'token' => $token,
                ], 200);
            }
            return $this->conflictResponse('Invalid credentials.', 401);
        }
    
        public function verifyOTP(Request $request)
        {
            try {
                // Validate the request
                $validator = Validator::make($request->all(), [
                    'email' => 'required|email',
                    'otp' => 'required|numeric',
                ]);
        
                if ($validator->fails()) {
                    $errorMessages = [];
                    foreach ($validator->errors()->messages() as $field => $messages) {
                        $errorMessages[$field] = $messages[0];
                    }
        
                    return $this->validationErrorResponse($errorMessages, 'Validation failed', 422);
                }
        
                $user = User::where('email', $request->input('email'))->first();
        
                if (!$user) {
                    return $this->conflictResponse('No user with this email address exists.', 422);
                }
        
                $hashedOtp = $user->otp;
        
                if (Hash::check($request->otp, $hashedOtp)) {
                    $user->email_verified_at = now();
                    $user->otp = null;
                    $user->save();
                    $token = $user->generateApiToken();
        
                    return $this->successResponse([
                        'message' => 'Login successful, ' . $user->name,
                        'token' => $token,
                        'data' => new UserResource($user),
                    ], 200);
                } else {
                    return $this->errorResponse('The OTP you provided is incorrect.', 401);
                }
            } catch (\Exception $e) {
                return $this->errorResponse('An error occurred while verifying OTP: ' . $e->getMessage(), 500);
            }
        }
        

        public function getUserData(Request $request)
        {
            try {
                $user = $request->user();
        
                return $this->successResponse([
                    'message' => 'User Fetched Successfully',
                    'data' => new UserResource($user),
                ], 200);
            } catch (\Exception $e) {
                return $this->errorResponse('Internal Server Error', 'An error occurred while fetching user data.', 500);
            }
        }
    

    public function update(Request $request)
    {
        try {
            $user = Auth::user();
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'status'=>'required|numeric|digits:1|in:1,2',
            ]);

            if ($validator->fails()) {
                $errorMessages = [];
                foreach ($validator->errors()->messages() as $field => $messages) {
                    $errorMessages[$field] = $messages[0];
                }
                return $this->validationErrorResponse($errorMessages, 'Validation failed', 422);
            }
            
            $user->name = $request->name;
            $user->email = $request->email;
            $user->status = $request->status;
            $user->save();
            return $this->successResponse(new UserResource($user), 'User has been Updated successfully', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred while updating the profile: ' . $e->getMessage(), 500);
        }
    }

    public function deleteUser()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response([
                    'message' => 'User not found.',
                    'status' => false,
                ], 500);
            }
            $user->email .= '_del_' . $user->id;
            $user->save();
            $user->delete();
            return $this->delmessage('User has been deleted successfully.', 422);
        } catch (\Exception $e) {
            return $this->errorResponse('Something went wrong: ' . $e->getMessage(), 500);
        }
    }

    //only for addmin
    public function getAllUsers()
    {
        try {
            $users = User::all();

            return response()->json([
                'message' => 'Users retrieved successfully.',
                'data' => $users,
                'status' => true,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Internal Server Error',
                'message' => 'An error occurred while fetching user data.'
            ], 500);
        }
    }
    
    public function logout()
    {
        try {
            Auth::user()->tokens->each(function ($token) {
                $token->delete();
            });
            return $this->delmessage('successfully logged out.', 200);

        } catch (\Exception $e) {

            return $this->errorResponse('Internal Server Error: ' . $e->getMessage(), 500);
        }
    }

    public function forgotPassword(Request $request)
    {
        try {
            $user = User::where('email', $request->email)->first();
            if (!$user) {
            return $this->conflictResponse('Email not found.', 200);
            }

            $otp = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $hashedOtp = \Hash::make($otp);
            $user->otp = $hashedOtp;
            $user->save();
            // Send OTP email
            Mail::to($user->email)->queue(new OtpMail($otp));
            return $this->delmessage('OTP sent to your email.', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Internal Server Error: ' . $e->getMessage(), 500);
        }
    }
    
    public function resetPasswordWithOTP(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
                'otp' => 'required|digits:4',
                'password' => 'required|confirmed|min:8',
            ]);
            if ($validator->fails()) {
                $errorMessages = [];
                foreach ($validator->errors()->messages() as $field => $messages) {
                    $errorMessages[$field] = $messages[0];
                }
                return $this->validationErrorResponse($errorMessages, 'Validation failed', 422);
            }

            $user = User::where('email', $request->email)->first();
            if (!$user) {

                return $this->conflictResponse('Email Not Found.', 404);
            }

            if (!empty($user->otp) && Hash::check($request->otp, $user->otp)) {
                $user->otp = null;
                $user->password = Hash::make($request->password);
                $user->save();

            return $this->delmessage('Password reset successfully.', 200);
            }
             else {
                return $this->conflictResponse('The OTP you provided is incorrect.', 401);
            }
        } catch (\Exception $e) {
            return $this->errorResponse('Internal Server Error: ' . $e->getMessage(), 500);
        }
    }

    public function changePassword(Request $request)
    {
        try {
            $validator = validator::make($request->all(), [
                'current_password' => 'required',
                'new_password' => 'required|min:6|different:current_password',
                'confirm_password' => 'required|same:new_password',
            ]);
    
           if ($validator->fails()) {
            $errorMessages = [];
            foreach ($validator->errors()->messages() as $field => $messages) {
                $errorMessages[$field] = $messages[0];
            }
            return $this->validationErrorResponse($errorMessages, 'Validation failed', 422);
            }

            $user = auth()->user();
            if (!Hash::check($request->current_password, $user->password)) {

                return $this->conflictResponse('Current password is incorrect.', 422);
            }

            $user->password = Hash::make($request->new_password);
            $user->save();

            return $this->successResponse(new UserResource($user), 'Password changed successfully.', 200);
        } 
        catch (\Exception $e) {
            return $this->errorResponse('Internal Server Error: ' . $e->getMessage(), 500);
        }
    }
}
