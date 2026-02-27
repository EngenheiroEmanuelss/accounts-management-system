<?php

namespace Feature\Api\Http\Controller;

use App\Dtos\Auth\AuthData;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $migrate = true;
    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeader('Accept', 'application/json');
    }

    #[Test]
    public function itThrowWhenUserIsNotLogged()
    {
        $this->post('api/logout')->assertStatus(401);
    }

    #[Test]
    public function itMakesLoginSuccessful(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Admin@123')
        ]);

        $payload = AuthData::validateAndCreate([
            'email'    => $user->email,
            'password' => 'Admin@123',
        ]);

        $this->post('/api/login', $payload->toArray())
            ->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'user' => [
                    "id",
                    "name",
                    "document",
                    "email"
                ]
            ]);

        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function itMakesLogoutSuccessful(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('test-token')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->post('/api/logout')
            ->assertStatus(200)
            ->assertJsonFragment([
                "message" => "Logout successful"
            ]);
    }
}
