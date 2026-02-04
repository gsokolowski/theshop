<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    /**
     * Redirect to Google OAuth
     * url: http://127.0.0.1:8000/api/auth/google
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback
     * url: http://127.0.0.1:8000/api/auth/google/callback
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Check if user exists
            $user = User::where('email', $googleUser->email)->first();
            
            if ($user) {
                // User exists - log them in
                // If user was created via OAuth and has no password, update Google avatar if needed
                if (!$user->password && $googleUser->avatar && !$user->profile_image) {
                    $user->update([
                        'profile_image' => $this->storeGoogleAvatar($googleUser->avatar, $user->id),
                    ]);
                }
            } else {
                // Create new user from Google data
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'password' => null, // OAuth users don't have passwords
                    'profile_image' => $this->storeGoogleAvatar($googleUser->avatar ?? null, null),
                    'email_verified_at' => now(), // Google emails are verified
                    'profile_completed' => false, // User must complete profile
                ]);
            }
            
            // Create Sanctum token
            $token = $user->createToken('access_token')->plainTextToken;
            
            // Redirect to frontend with token
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            return redirect($frontendUrl . '/auth/google/callback?token=' . $token . '&user=' . base64_encode(json_encode(new UserResource($user))));
            
        } catch (\Exception $e) {
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            return redirect($frontendUrl . '/login?error=' . urlencode('Failed to authenticate with Google: ' . $e->getMessage()));
        }
    }

    /**
     * Download and store Google profile image
     */
    private function storeGoogleAvatar($avatarUrl, $userId = null)
    {
        if (!$avatarUrl) {
            return null;
        }
        
        try {
            $imageData = file_get_contents($avatarUrl);
            $imageName = ($userId ?? time()) . '_google_avatar.jpg';
            $path = 'images/profiles/' . $imageName;
            
            Storage::disk('public')->put($path, $imageData);
            
            return $path;
        } catch (\Exception $e) {
            // If avatar download fails, return null
            return null;
        }
    }
}
