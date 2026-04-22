<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthUserRequest;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdatePasswordRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Jobs\SendVerificationEmail;
use App\Jobs\SendWelcomeEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Store a newly created resource in storage.
     * Register a new user using UserStoreRequest Form Request
     * url: http://127.0.0.1:8000/api/user/register
     */
    public function register(UserStoreRequest $request)
    {
        // Validate the request using the UserStoreRequest Form Request

        $validated = $request->validated();
    
        // Hash password before creating user
        $validated['password'] = Hash::make($validated['password']);
        
        // ✅ CHANGED: Set email_verified_at to null for new registrations
        $validated['email_verified_at'] = null;
        
        $user = User::create($validated);
    
        // ✅ ADDED: Dispatch verification email job to queue
        SendVerificationEmail::dispatch($user);
    
        return response()->json([
            'message' => 'User created successfully. Please check your email to verify your account.',
            'data' => new UserResource($user), // formats the user data according to your UserResource definition.
        ], 201);
    }

    /**
     * Authorise User to Login using AuthUserRequest Form Request
     * url: http://127.0.0.1:8000/api/login
     */
    public function login(AuthUserRequest $request)
    {
        // Validate the request using the AuthUserRequest Form Request
        $validated = $request->validated();
        
        // Check if user exists and password is correct
        $user = User::where('email', $validated['email'])->first();
        
        // If user does not exist or password is incorrect, return error
        if ( ! $user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Email or password is incorrect',
            ], 401); // 401 Unauthorized status code
        }

        // create Laravel Sanctum API token for user
        $token = $user->createToken('access_token')->plainTextToken;
        
        // return response with user and token with 200 status code
        return response()->json([
            'message' => 'User logged in successfully',
            'user' => new UserResource($user), // formats the user data according to your UserResource definition.
            'access_token' => $token,
        ], 200); // 200 OK status code
    }

    /**
     * Logout User using Laravel Sanctum API token
     * url: http://127.0.0.1:8000/api/user/logout
     */
    public function logout(Request $request)
    {
        // Revoke the user's token using Laravel Sanctum API token
        $request->user()->currentAccessToken()->delete();
        
        // return response with success message and 200 status code
        return response()->json([
            'message' => 'User logged out successfully',
        ], 200); // 200 OK status code
        
    }

    /**
     * Display the specified resource.
     * url: http://127.0.0.1:8000/api/user
     * This endpoint is used to get the currently logged in user
     * It returns the user data and the access token from the request
     * The route uses auth:sanctum middleware to protect the route
     * Pass the Authorization: Bearer {token} header. No body parameters needed.
     */
    public function loggedInUser(Request $request) 
    {        
        $user = $request->user(); // get the currently logged in user, $request->user(); eturns the authenticated user
        
        // return response with user and 200 status code
        return response()->json([
            'message' => 'User retrieved successfully',
            'user' => new UserResource($user),
            'access_token' => $request->bearerToken(), // Get bearer token from request, not user
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     * url: http://127.0.0.1:8000/api/user/profile
     * This endpoint is used to update the currently logged in user's profile
     * It returns the updated user data and 200 status code
     */
    public function updateProfile(UserUpdateRequest $request)
    {
        // Validate the request using the UserUpdateRequest Form Request
        $validated = $request->validated();
        
        // if user is updating also image profile you need to use this code to store the image in the public/images/profiles folder
        $user = $request->user();

        if ($request->hasFile('profile_image')) {

            // delete existing image if it exists
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }

            // generate a unique name for the image
            $image_name = time().'_profile_image'.$user->id;
            $image_extension = $request->file('profile_image')->getClientOriginalExtension();
            // upload image to the public/images/profiles folder and get the path
            $profile_image_path = $request->file('profile_image')->storeAs('images/profiles', $image_name.'.'.$image_extension, 'public');
            // set the profile image to the validated data to be used in the update query
            $validated['profile_image'] = $profile_image_path;
        }

        // ✅ CHANGED: Partial updates (e.g. profile image only) must not null out fields omitted from the request.
        $request->user()->update([
            'name' => array_key_exists('name', $validated) ? $validated['name'] : $user->name,
            'address' => array_key_exists('address', $validated) ? $validated['address'] : $user->address,
            'city' => array_key_exists('city', $validated) ? $validated['city'] : $user->city,
            'country' => array_key_exists('country', $validated) ? $validated['country'] : $user->country,
            'zip_code' => array_key_exists('zip_code', $validated) ? $validated['zip_code'] : $user->zip_code,
            'phone_number' => array_key_exists('phone_number', $validated) ? $validated['phone_number'] : $user->phone_number,
            'profile_image' => $validated['profile_image'] ?? $user->profile_image,
            'profile_completed' => 1, // as user has completed their profile
        ]);
        
        // return response with updated user data and 200 status code
        return response()->json([
            'message' => 'User profile updated successfully',
            'user' => new UserResource($request->user()->fresh()), // fresh() is used to refresh the user data from the database
        ], 200); // 200 OK status code
    }

    /**
     * Update the user's password
     * url: http://127.0.0.1:8000/api/user/password/update
     * This endpoint is used to update the currently logged in user's password
     * It returns the updated user data and 200 status code
     */
    public function updatePassword(UserUpdatePasswordRequest $request)
    {
        // Validate the request using the UserUpdatePasswordRequest Form Request
        $validated = $request->validated();
        
        // Check if the old password is correct
        if(!Hash::check($validated['old_password'], $request->user()->password)) {
            return response()->json([
                'message' => 'Invalid old password',
            ], 401); // 401 Unauthorized status code
        }

        // Update the user's password
        $request->user()->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        // Revoke all tokens for the user to force user to login again with new password
        $request->user()->tokens()->delete();

        // return response with success message and 200 status code
        return response()->json([
            'message' => 'Password updated successfully',
            'user' => new UserResource($request->user()->fresh()), // fresh() is used to refresh the user data from the database
        ], 200); // 200 OK status code
    }
    /**
     * Remove the specified resource from storage.
     * url: http://127.0.0.1:8000/api/user
     * This endpoint is used to delete the currently logged in user
     * It returns a success message and 200 status code
     */
    public function destroy(Request $request)
    {
        // Delete the user
        $request->user()->delete();
        
        // return response with success message and 200 status code
        return response()->json([
            'message' => 'User deleted successfully',
        ], 200); // 200 OK status code
    }

    /**
     * Verify user's email address
     * url: http://127.0.0.1:8000/api/v1/email/verify
     */
    public function verifyEmail(Request $request)
    {
        // Get query parameters
        $userId = $request->query('id');
        $signature = $request->query('signature');
        $expires = $request->query('expires');

        // Check if required parameters are present
        if (!$userId || !$signature || !$expires) {
            return response()->json([
                'message' => null,
                'error' => 'Invalid verification link. Missing required parameters.',
                'data' => null,
                'status' => 400,
            ], 400);
        }

        // Check if the link has expired (using UTC timestamp - timezone independent)
        if ((int) $expires < now()->timestamp) {
            return response()->json([
                'message' => null,
                'error' => 'Verification link has expired',
                'data' => null,
                'status' => 400,
            ], 400);
        }

        if (! $request->hasValidSignature()) {
            return response()->json([
                'message' => null,
                'error' => 'Invalid or expired verification link',
                'data' => null,
                'status' => 400,
            ], 400);
        }

        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'message' => null,
                'error' => 'User not found',
                'data' => null,
                'status' => 404,
            ], 404);
        }

        // Check if email is already verified
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email is already verified',
                'data' => new UserResource($user),
                'status' => 200,
            ], 200);
        }

        // Mark email as verified
        $user->markEmailAsVerified();

        SendWelcomeEmail::dispatch($user->fresh(), 'email_verified');

        return response()->json([
            'message' => 'Email verified successfully',
            'data' => new UserResource($user->fresh()),
            'status' => 200,
        ], 200);
    }

    /**
     * Resend verification email
     * url: http://127.0.0.1:8000/api/email/verification/resend
     */
    public function resendVerificationEmail(Request $request)
    {
        $user = $request->user();

        // Check if email is already verified
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email is already verified',
                'data' => new UserResource($user),
                'status' => 200,
            ], 200);
        }

        // Dispatch verification email job to queue
        SendVerificationEmail::dispatch($user);

        return response()->json([
            'message' => 'Verification email has been sent. Please check your inbox.',
            'data' => null,
            'status' => 200,
        ], 200);
    }
}
