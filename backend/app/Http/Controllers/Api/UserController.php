<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthUserRequest;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdatePasswordRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
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
        
        $user = User::create($validated);
    
        return response()->json([
            'message' => 'User created successfully',
            'user' => new UserResource($user), // formats the user data according to your UserResource definition.
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
        if($request->hasFile('profile_image')) {

            // delete existing image if it exists
            if($request->user()->profile_image) {
                Storage::disk('public')->delete($request->user()->profile_image);
            }

            // generate a unique name for the image
            $image_name = time().'_profile_image'.$request->user()->id;
            $image_extension = $request->file('profile_image')->getClientOriginalExtension();
            // upload image to the public/images/profiles folder and get the path
            $profile_image_path = $request->file('profile_image')->storeAs('images/profiles', $image_name.'.'.$image_extension, 'public');
            // set the profile image to the validated data to be used in the update query
            $validated['profile_image'] = $profile_image_path;
        }
        
        // Update the user - Added profile_image to update array
        $request->user()->update([
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'country' => $validated['country'] ?? null,
            'zip_code' => $validated['zip_code'] ?? null,
            'phone_number' => $validated['phone_number'] ?? null,
            'profile_image' => $validated['profile_image'] ?? $request->user()->profile_image, 
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
}
