<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Users;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Mail\ResetPasswordOtpMail;
use Laravel\Sanctum\PersonalAccessToken;
use Carbon\Carbon;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    /**
     * Test successful login
     */
    public function test_login_with_valid_credentials()
    {
        // Create a user
        $user = Users::factory()->create([
            'email' => 'test@example.com',
            'password_hash' => Hash::make('password123'),
            'full_name' => 'Test User'
        ]);

        // Attempt login
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'user',
                        'message',
                        'access_token',
                        'refresh_token'
                    ]
                ])
                ->assertJsonFragment([
                    'message' => 'Login success'
                ]);

        // Assert token was created
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->user_id,
            'tokenable_type' => Users::class
        ]);
    }

    /**
     * Test login with invalid credentials
     */
    public function test_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(401)
                ->assertJsonStructure([
                    'status',
                    'title',
                    'detail'
                ])
                ->assertJsonFragment([
                    'title' => 'Unauthenticated',
                    'detail' => 'Invalid email or password'
                ]);
    }

    /**
     * Test successful registration
     */
    public function test_register_with_valid_data()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'user',
                        'access_token',
                        'message',
                        'refresh_token'
                    ]
                ])
                ->assertJsonFragment([
                    'message' => 'Registration successful'
                ]);

        // Assert user was created
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'full_name' => 'John Doe'
        ]);
    }

    /**
     * Test registration with invalid data
     */
    public function test_register_with_invalid_data()
    {
        $userData = [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
            'password_confirmation' => 'different'
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(422)
                ->assertJsonStructure([
                    'status',
                    'title',
                    'detail',
                    'errors'
                ]);
    }

    /**
     * Test registration with existing email
     */
    public function test_register_with_existing_email()
    {
        // Create existing user
        Users::factory()->create(['email' => 'existing@example.com']);

        $userData = [
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(422)
                ->assertJsonStructure([
                    'status',
                    'title',
                    'detail',
                    'errors'
                ]);
    }

    /**
     * Test successful logout
     */
    public function test_logout()
    {
        $user = Users::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/users/' . $user->user_id . '/logout');

        $response->assertStatus(200)
                ->assertJsonFragment([
                    'message' => 'Successfully logged out'
                ]);
    }

    /**
     * Test reset password - OTP generation
     */
    public function test_reset_password_sends_otp()
    {
        $user = Users::factory()->create(['email' => 'test@example.com']);

        $response = $this->postJson('/api/reset-password', [
            'email' => 'test@example.com'
        ]);

        $response->assertStatus(200)
                ->assertJsonFragment([
                    'message' => 'OTP sent to your email'
                ]);

        // Assert OTP was stored
        $this->assertDatabaseHas('password_resets', [
            'email' => 'test@example.com'
        ]);

        // Assert email was sent
        Mail::assertSent(ResetPasswordOtpMail::class);
    }

    /**
     * Test reset password with non-existent email
     */
    public function test_reset_password_with_invalid_email()
    {
        $response = $this->postJson('/api/reset-password', [
            'email' => 'nonexistent@example.com'
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test OTP validation with valid OTP
     */
    public function test_validate_otp_with_valid_otp()
    {
        $user = Users::factory()->create(['email' => 'test@example.com']);
        $otp = 123456;

        // Insert OTP record
        DB::table('password_resets')->insert([
            'email' => 'test@example.com',
            'otp' => $otp,
            'created_at' => now()
        ]);

        $response = $this->postJson('/api/validate-otp', [
            'email' => 'test@example.com',
            'otp' => $otp
        ]);

        $response->assertStatus(200)
                ->assertJsonFragment([
                    'message' => 'Otp is valid. Continue to change your password.'
                ]);
    }

    /**
     * Test OTP validation with invalid OTP
     */
    public function test_validate_otp_with_invalid_otp()
    {
        $user = Users::factory()->create(['email' => 'test@example.com']);

        $response = $this->postJson('/api/validate-otp', [
            'email' => 'test@example.com',
            'otp' => 999999
        ]);

        $response->assertStatus(422)
                ->assertJsonFragment([
                    'detail' => 'Invalid or expired OTP'
                ]);
    }

    /**
     * Test OTP validation with expired OTP
     */
    public function test_validate_otp_with_expired_otp()
    {
        $user = Users::factory()->create(['email' => 'test@example.com']);
        $otp = 123456;

        // Insert expired OTP record
        DB::table('password_resets')->insert([
            'email' => 'test@example.com',
            'otp' => $otp,
            'created_at' => Carbon::now()->subMinutes(15) // Expired
        ]);

        $response = $this->postJson('/api/validate-otp', [
            'email' => 'test@example.com',
            'otp' => $otp
        ]);

        $response->assertStatus(422)
                ->assertJsonFragment([
                    'detail' => 'Invalid or expired OTP'
                ]);
    }

    /**
     * Test change password with valid OTP
     */
    public function test_change_password_with_valid_otp()
    {
        $user = Users::factory()->create(['email' => 'test@example.com']);
        $otp = 123456;

        // Insert OTP record
        DB::table('password_resets')->insert([
            'email' => 'test@example.com',
            'otp' => $otp,
            'created_at' => now()
        ]);

        $response = $this->postJson('/api/change-password', [
            'email' => 'test@example.com',
            'otp' => $otp,
            'password' => 'newpassword123'
        ]);

        $response->assertStatus(200)
                ->assertJsonFragment([
                    'message' => 'Password changed successfully. Please log in with your new password.'
                ]);

        // Assert password was changed
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password_hash));

        // Assert OTP record was deleted
        $this->assertDatabaseMissing('password_resets', [
            'email' => 'test@example.com'
        ]);
    }

    /**
     * Test change password with invalid OTP
     */
    public function test_change_password_with_invalid_otp()
    {
        $user = Users::factory()->create(['email' => 'test@example.com']);

        $response = $this->postJson('/api/change-password', [
            'email' => 'test@example.com',
            'otp' => 999999,
            'password' => 'newpassword123'
        ]);

        $response->assertStatus(422)
                ->assertJsonFragment([
                    'detail' => 'Invalid or expired OTP'
                ]);
    }

    /**
     * Test refresh token with valid refresh token
     */
    public function test_refresh_token_with_valid_token()
    {
        $user = Users::factory()->create();
        $token = $user->createToken('auth_token');
        $refreshToken = 'valid_refresh_token';

        // Update token with refresh token
        $token->accessToken->refresh_token = $refreshToken;
        $token->accessToken->refresh_token_expires_at = now()->addDays(7);
        $token->accessToken->save();

        $response = $this->withHeaders([
            'X-Refresh-Token' => $refreshToken,
        ])->postJson('/api/refresh-token');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'user',
                        'access_token',
                        'refresh_token',
                        'message'
                    ]
                ])
                ->assertJsonFragment([
                    'message' => 'Token refreshed successfully'
                ]);
    }

    /**
     * Test refresh token with missing refresh token
     */
    public function test_refresh_token_with_missing_token()
    {
        $response = $this->postJson('/api/refresh-token');

        $response->assertStatus(401)
                ->assertJsonFragment([
                    'detail' => 'Refresh token is missing'
                ]);
    }

    /**
     * Test refresh token with invalid refresh token
     */
    public function test_refresh_token_with_invalid_token()
    {
        $response = $this->withHeaders([
            'X-Refresh-Token' => 'invalid_refresh_token',
        ])->postJson('/api/refresh-token');

        $response->assertStatus(401)
                ->assertJsonFragment([
                    'detail' => 'Refresh token is invalid or expired'
                ]);
    }

    /**
     * Test refresh token with expired refresh token
     */
    public function test_refresh_token_with_expired_token()
    {
        $user = Users::factory()->create();
        $token = $user->createToken('auth_token');
        $refreshToken = 'expired_refresh_token';

        // Update token with expired refresh token
        $token->accessToken->refresh_token = $refreshToken;
        $token->accessToken->refresh_token_expires_at = now()->subDays(1); // Expired
        $token->accessToken->save();

        $response = $this->withHeaders([
            'X-Refresh-Token' => $refreshToken,
        ])->postJson('/api/refresh-token');

        $response->assertStatus(401)
                ->assertJsonFragment([
                    'detail' => 'Refresh token is invalid or expired'
                ]);
    }
}
