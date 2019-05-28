<?php

namespace RemoteControl\Tests\Feature;

use Mockery as m;
use RemoteControl\Remote;
use RemoteControl\Tests\TestCase;
use Illuminate\Foundation\Auth\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Orchestra\Testbench\Concerns\WithLaravelMigrations;

class CreateRemoteAccessTest extends TestCase
{
    use WithLaravelMigrations;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations('testing');
        $this->artisan('migrate');
    }

    /** @test */
    public function it_can_create_remote_access()
    {
        $user = factory(User::class)->create();

        $accessToken = Remote::create($user, 'crynobone@katsana.com');

        $this->assertSame($user->getKey(), $accessToken->getUserId());
        $this->assertSame('crynobone@katsana.com', $accessToken->getEmail());

        $this->assertDatabaseHas('user_remote_controls', [
            'user_id' => $user->getKey(),
            'email' => 'crynobone@katsana.com',
            'verification_code' => $accessToken->getVerificationCode(),
        ]);
    }

    /** @test */
    public function it_can_authenticate_remote_access()
    {
        Remote::route('test');

        $user = factory(User::class)->create();

        $accessToken = Remote::create($user, 'crynobone@katsana.com');

        $this->assertGuest();

        $this->call('GET', $accessToken->getUrl(false))
            ->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
    }
}