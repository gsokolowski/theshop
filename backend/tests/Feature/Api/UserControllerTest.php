<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Feature tests for Api\UserController. Covers register, login, logout, loggedInUser,
 * updateProfile, updatePassword, destroy, verifyEmail, resendVerificationEmail.
 */
class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Register creates user and returns 201.
     */
    public function test_register_creates_user_successfully(): void
    {
        $response = $this->postJson(route('user.register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'confirm_password' => 'password123',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('message', 'User created successfully. Please check your email to verify your account.');
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    /**
     * Register fails validation when email already exists.
     */
    public function test_register_fails_when_email_exists(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->postJson(route('user.register'), [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'confirm_password' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    /**
     * Login returns token with valid credentials.
     */
    public function test_login_returns_token_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson(route('user.login'), [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'User logged in successfully');
        $response->assertJsonStructure(['access_token', 'user']);
    }

    /**
     * Login returns 401 with invalid credentials.
     */
    public function test_login_returns_401_with_invalid_credentials(): void
    {
        User::factory()->create(['email' => 'user@example.com']);

        $response = $this->postJson(route('user.login'), [
            'email' => 'user@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
        $response->assertJsonPath('message', 'Email or password is incorrect');
    }

    /**
     * LoggedInUser returns authenticated user.
     */
    public function test_logged_in_user_returns_authenticated_user(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson(route('user.loggedInUser'));

        $response->assertStatus(200);
        $response->assertJsonPath('user.email', $user->email);
    }

    /**
     * Logout revokes token.
     */
    public function test_logout_revokes_token(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson(route('user.logout'));

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'User logged out successfully');
    }

    /**
     * UpdateProfile updates user data.
     */
    public function test_update_profile_updates_user(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->putJson(route('user.profile.update'), [
            'name' => 'Updated Name',
            'address' => '123 Main St',
            'city' => 'New York',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('user.name', 'Updated Name');
        $response->assertJsonPath('user.address', '123 Main St');
    }

    /**
     * UpdatePassword updates password when old password is correct.
     */
    public function test_update_password_succeeds_with_valid_old_password(): void
    {
        $user = User::factory()->create(['password' => 'oldpassword123']);
        Sanctum::actingAs($user);

        $response = $this->putJson(route('user.password.update'), [
            'old_password' => 'oldpassword123',
            'new_password' => 'newpassword456',
            'new_password_confirmation' => 'newpassword456',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Password updated successfully');
    }

    /**
     * UpdatePassword returns 401 with invalid old password.
     */
    public function test_update_password_fails_with_invalid_old_password(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->putJson(route('user.password.update'), [
            'old_password' => 'wrongpassword',
            'new_password' => 'newpassword456',
            'new_password_confirmation' => 'newpassword456',
        ]);

        $response->assertStatus(401);
        $response->assertJsonPath('message', 'Invalid old password');
    }

    /**
     * Destroy soft-deletes the user.
     */
    public function test_destroy_soft_deletes_user(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->deleteJson(route('user.destroy'));

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'User deleted successfully');
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    /**
     * VerifyEmail returns 400 when parameters are missing.
     */
    public function test_verify_email_returns_400_when_params_missing(): void
    {
        $response = $this->getJson(route('email.verify'));

        $response->assertStatus(400);
        $response->assertJsonPath('error', 'Invalid verification link. Missing required parameters.');
    }

    /**
     * ResendVerificationEmail returns message when not verified.
     */
    public function test_resend_verification_email_sends_when_unverified(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);
        Sanctum::actingAs($user);

        $response = $this->postJson(route('email.verification.resend'));

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Verification email has been sent. Please check your inbox.');
    }

    /**
     * ResendVerificationEmail returns already verified when user is verified.
     */
    public function test_resend_verification_email_returns_already_verified(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        Sanctum::actingAs($user);

        $response = $this->postJson(route('email.verification.resend'));

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Email is already verified');
    }

    /**
     * VerifyEmail returns 400 when link has expired.
     */
    public function test_verify_email_returns_400_when_expired(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $response = $this->getJson(route('email.verify') . '?id=' . $user->id . '&expires=1&signature=invalid');

        $response->assertStatus(400);
        $response->assertJsonPath('error', 'Verification link has expired');
    }

    /**
     * VerifyEmail returns 400 when signature is invalid.
     */
    public function test_verify_email_returns_400_when_signature_invalid(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);
        $futureExpires = (string) (now()->addHour()->timestamp);

        $response = $this->getJson(route('email.verify') . '?id=' . $user->id . '&expires=' . $futureExpires . '&signature=invalid');

        $response->assertStatus(400);
        $response->assertJsonPath('error', 'Invalid or expired verification link');
    }

    /**
     * UpdateProfile updates user with profile image.
     */
    public function test_update_profile_with_image(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->image('avatar.jpg', 100, 100);

        $response = $this->put(route('user.profile.update'), [
            'name' => 'Updated Name',
            'profile_image' => $file,
        ], ['Accept' => 'application/json']);

        $response->assertStatus(200);
        $response->assertJsonPath('user.name', 'Updated Name');
        $user->refresh();
        $this->assertNotNull($user->profile_image);
        Storage::disk('public')->assertExists($user->profile_image);
    }
}
