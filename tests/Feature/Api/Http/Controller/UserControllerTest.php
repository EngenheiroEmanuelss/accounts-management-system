<?php

namespace Feature\Api\Http\Controller;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserControllerTest extends TestCase
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
        $this->get('api/user')->assertStatus(401);
    }

    #[Test]
    public function itCreatesUserSuccessful(): void
    {
        $userPayload = User::factory()->make();

        $resp = $this->post('/api/user', $userPayload->toArray());
        $resp->assertJsonStructure([
            'id',
            'name',
            'email',
            'document',
            'accounts' => [
                '*' => [
                    'id',
                    'available_balance',
                ]
            ]
        ])->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'email'    => $userPayload->email,
            'name'     => $userPayload->name,
            'document' => $userPayload->document,
        ]);
    }

    #[Test]
    public function itGetUserSuccessful(): void
    {
        $user = User::factory()->create();

        $this->assertDatabaseHas('users', [
            'email'    => $user->email,
            'name'     => $user->name,
            'document' => $user->document,
        ]);

        $this->actingAs($user);

        $this->get('/api/user')
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'document',
                'accounts' => [
                    '*' => [
                        'id',
                        'available_balance',
                    ]
                ]
            ])->assertStatus(200);
    }

    #[Test]
    public function itThrowsGetUserWhenNotLoggedUser(): void
    {
        $user = User::factory()->create();

        $this->assertDatabaseHas('users', [
            'email'    => $user->email,
            'name'     => $user->name,
            'document' => $user->document,
        ]);

        $this->get('/api/user')->assertStatus(401);
    }
}
