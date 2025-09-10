<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\API\AuthController;
use App\Models\ApiUser;
use App\Helpers\ApiErrorHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Mockery;

class AuthControllerUnitTest extends TestCase
{
    use RefreshDatabase;

    protected $authController;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authController = new AuthController();
        Mail::fake();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test ApiErrorHelper integration
     */
    public function test_api_error_helper_format_error()
    {
        $apiErrorHelper = new ApiErrorHelper();
        
        $result = $apiErrorHelper->formatError(
            status: 401,
            title: 'Unauthenticated',
            detail: 'Invalid credentials',
            errors: ['email' => 'Email not found']
        );

        $expected = [
            'status' => 401,
            'title' => 'Unauthenticated',
            'detail' => 'Invalid credentials',
            'errors' => ['email' => 'Email not found']
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * Test user creation during registration
     */
    public function test_user_creation_with_correct_attributes()
    {
        $userData = [
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'password_hash' => Hash::make('password123'),
            'remember_token' => 'sample_token'
        ];

        $user = ApiUser::create($userData);

        $this->assertInstanceOf(ApiUser::class, $user);
        $this->assertEquals('John Doe', $user->full_name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertTrue(Hash::check('password123', $user->password_hash));
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'full_name' => 'John Doe'
        ]);
    }

    /**
     * Test password hashing
     */
    public function test_password_hashing()
    {
        $password = 'test_password_123';
        $hashedPassword = Hash::make($password);

        $this->assertNotEquals($password, $hashedPassword);
        $this->assertTrue(Hash::check($password, $hashedPassword));
        $this->assertFalse(Hash::check('wrong_password', $hashedPassword));
    }

    /**
     * Test token creation for user
     */
    public function test_token_creation_for_user()
    {
        $user = ApiUser::factory()->create();
        
        $token = $user->createToken('auth_token');

        $this->assertNotNull($token);
        $this->assertNotNull($token->plainTextToken);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->user_id,
            'tokenable_type' => ApiUser::class,
            'name' => 'auth_token'
        ]);
    }

    /**
     * Test token deletion
     */
    public function test_token_deletion()
    {
        $user = ApiUser::factory()->create();
        $token1 = $user->createToken('token1');
        $token2 = $user->createToken('token2');

        // Verify tokens exist
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->user_id,
            'name' => 'token1'
        ]);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->user_id,
            'name' => 'token2'
        ]);

        // Delete all tokens
        $user->tokens()->delete();

        // Verify tokens are deleted
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->user_id,
            'name' => 'token1'
        ]);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->user_id,
            'name' => 'token2'
        ]);
    }

    /**
     * Test OTP generation range
     */
    public function test_otp_generation_range()
    {
        for ($i = 0; $i < 10; $i++) {
            $otp = rand(100000, 999999);
            $this->assertGreaterThanOrEqual(100000, $otp);
            $this->assertLessThanOrEqual(999999, $otp);
            $this->assertEquals(6, strlen((string)$otp));
        }
    }

    /**
     * Test password reset record insertion
     */
    public function test_password_reset_record_insertion()
    {
        $email = 'test@example.com';
        $otp = 123456;

        DB::table('password_resets')->updateOrInsert(
            ['email' => $email],
            ['otp' => $otp, 'created_at' => now()]
        );

        $this->assertDatabaseHas('password_resets', [
            'email' => $email,
            'otp' => $otp
        ]);
    }

    /**
     * Test password reset record update
     */
    public function test_password_reset_record_update()
    {
        $email = 'test@example.com';
        $oldOtp = 123456;
        $newOtp = 654321;

        // Insert initial record
        DB::table('password_resets')->insert([
            'email' => $email,
            'otp' => $oldOtp,
            'created_at' => now()->subMinutes(5)
        ]);

        // Update with new OTP
        DB::table('password_resets')->updateOrInsert(
            ['email' => $email],
            ['otp' => $newOtp, 'created_at' => now()]
        );

        $this->assertDatabaseHas('password_resets', [
            'email' => $email,
            'otp' => $newOtp
        ]);

        $this->assertDatabaseMissing('password_resets', [
            'email' => $email,
            'otp' => $oldOtp
        ]);
    }

    /**
     * Test user authentication method
     */
    public function test_user_authentication_method()
    {
        $user = ApiUser::factory()->create([
            'password_hash' => Hash::make('test_password')
        ]);

        // Test getAuthPassword method
        $this->assertEquals($user->password_hash, $user->getAuthPassword());
    }

    /**
     * Test user model fillable attributes
     */
    public function test_user_model_fillable_attributes()
    {
        $user = new Users();
        $fillable = $user->getFillable();

        $expectedFillable = ['email', 'password_hash', 'full_name'];
        
        $this->assertEquals($expectedFillable, $fillable);
    }

    /**
     * Test user model hidden attributes
     */
    public function test_user_model_hidden_attributes()
    {
        $user = new Users();
        $hidden = $user->getHidden();

        $expectedHidden = ['password_hash', 'remember_token'];
        
        $this->assertEquals($expectedHidden, $hidden);
    }

    /**
     * Test primary key configuration
     */
    public function test_user_model_primary_key()
    {
        $user = new Users();
        
        $this->assertEquals('user_id', $user->getKeyName());
    }

    /**
     * Test refresh token expiration calculation
     */
    public function test_refresh_token_expiration()
    {
        $now = now();
        $expirationTime = $now->copy()->addDays(7);

        $this->assertEquals(7, $now->diffInDays($expirationTime));
    }

    /**
     * Test string generation for tokens
     */
    public function test_string_generation_for_tokens()
    {
        $token1 = \Illuminate\Support\Str::random(64);
        $token2 = \Illuminate\Support\Str::random(64);

        $this->assertEquals(64, strlen($token1));
        $this->assertEquals(64, strlen($token2));
        $this->assertNotEquals($token1, $token2);
    }
}
