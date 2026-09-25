<?php

namespace Tests;

use App\Models\Airline;
use App\Models\Airport;
use App\Models\Enums\UserState;
use App\Models\Invite;
use App\Models\User;
use App\Notifications\Messages\AdminUserRegistered;
use App\Services\UserService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class RegistrationTest extends TestCase
{
    private Airline $airInter;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a default user to prevent redirection to the installer
        User::factory()->create();

        $this->airInter = Airline::factory()->create([
            'icao' => 'ITF',
            'iata' => 'IT',
            'name' => 'Air Inter',
            'active' => true,
        ]);
    }

    /**
     * A basic test example.
     *
     * @throws \Exception
     */
    public function test_registration(): void
    {
        $admin = $this->createAdminUser(['name' => 'testRegistration Admin']);

        /** @var UserService $userSvc */
        $userSvc = app(UserService::class);

        $this->updateSetting('pilots.auto_accept', true);

        $attrs = User::factory()->make()->makeVisible(['api_key', 'name', 'email'])->toArray();
        $attrs['password'] = Hash::make('secret');
        $attrs['flights'] = 0;
        $user = $userSvc->createUser($attrs);

        $this->assertEquals(UserState::ACTIVE, $user->state);

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        Notification::assertSentTo([$admin], AdminUserRegistered::class);
        Notification::assertNotSentTo([$user], AdminUserRegistered::class);
    }

    protected function getUserData(): array
    {
        $home = Airport::factory()->create(['hub' => true]);

        return [
            'name'                  => 'Test User',
            'email'                 => 'test@phpvms.net',
            'airline_id'            => $this->airInter->id,
            'home_airport_id'       => $home->id,
            'timezone'              => 'Europe/Paris',
            'password'              => 'secret',
            'password_confirmation' => 'secret',
            'toc_accepted'          => true,
        ];
    }

    public function test_access_to_registration_when_registration_enabled(): void
    {
        $this->updateSetting('general.disable_registrations', false);
        $this->updateSetting('general.invite_only_registrations', false);

        $this->get('/register')
            ->assertOk();

        $this->post('/register', $this->getUserData())
            ->assertRedirect('/dashboard');
    }

    public function test_registration_forces_air_inter_and_normalizes_invalid_timezone(): void
    {
        $this->updateSetting('general.disable_registrations', false);
        $this->updateSetting('general.invite_only_registrations', false);
        $this->updateSetting('pilots.auto_accept', true);

        $otherAirline = Airline::factory()->create([
            'icao' => 'ACF',
            'name' => 'Another Carrier',
            'active' => true,
        ]);

        $data = $this->getUserData();
        $data['email'] = 'airinter-only@example.test';
        $data['airline_id'] = $otherAirline->id;
        $data['timezone'] = 'Browser/Invalid-Alias';

        $this->post('/register', $data)->assertRedirect('/dashboard');

        $user = User::query()->where('email', 'airinter-only@example.test')->firstOrFail();
        $this->assertSame($this->airInter->id, $user->airline_id);
        $this->assertSame('Europe/Paris', $user->timezone);
    }

    public function test_registration_preserves_valid_iana_timezone(): void
    {
        $this->updateSetting('general.disable_registrations', false);
        $this->updateSetting('general.invite_only_registrations', false);
        $this->updateSetting('pilots.auto_accept', true);

        $data = $this->getUserData();
        $data['email'] = 'montreal-time@example.test';
        $data['timezone'] = 'America/Toronto';

        $this->post('/register', $data)->assertRedirect('/dashboard');

        $user = User::query()->where('email', 'montreal-time@example.test')->firstOrFail();
        $this->assertSame('America/Toronto', $user->timezone);
        $this->assertSame($this->airInter->id, $user->airline_id);
    }

    public function test_access_to_registration_when_registration_disabled(): void
    {
        $this->updateSetting('general.disable_registrations', true);

        $this->expectException(HttpException::class);

        $this->get('/register')
            ->assertForbidden();

        $this->post('/register', $this->getUserData())
            ->assertForbidden();
    }

    public function test_access_without_invite(): void
    {
        $this->updateSetting('general.disable_registrations', false);
        $this->updateSetting('general.invite_only_registrations', true);

        $this->expectException(HttpException::class);

        $this->get('/register')
            ->assertForbidden();

        $this->post('/register', $this->getUserData())
            ->assertForbidden();
    }

    public function test_access_with_valid_invite(): void
    {
        $this->updateSetting('general.disable_registrations', false);
        $this->updateSetting('general.invite_only_registrations', true);

        $invite = Invite::create([
            'token' => 'test',
        ]);

        $this->get($invite->link)
            ->assertOk();

        $userData = array_merge($this->getUserData(), [
            'invite'       => $invite->id,
            'invite_token' => base64_encode($invite->token),
        ]);

        $this->post('/register', $userData)
            ->assertRedirect('/dashboard');
    }

    public function test_access_with_invalid_invite(): void
    {
        $this->updateSetting('general.disable_registrations', false);
        $this->updateSetting('general.invite_only_registrations', true);

        $this->expectException(HttpException::class);

        // Expired invite
        $expiredInvite = Invite::create([
            'token'      => 'test',
            'expires_at' => now()->subDay(),
        ]);

        $expiredUserData = array_merge($this->getUserData(), [
            'invite'       => $expiredInvite->id,
            'invite_token' => base64_encode($expiredInvite->token),
        ]);

        $this->get($expiredInvite->link)
            ->assertForbidden();

        $this->post('/register', $expiredUserData)
            ->assertForbidden();

        // Invalid token
        $invalidUserData = array_merge($this->getUserData(), [
            'invite'       => 1,
            'invite_token' => 'invalid',
        ]);

        $this->get('/register?invite=1&invite_token=invalid')
            ->assertForbidden();

        $this->post('/register', $invalidUserData)
            ->assertForbidden();

        // Invite used too many times
        $tooUsedInvite = Invite::create([
            'token'       => 'test',
            'usage_count' => 1,
            'usage_limit' => 1,
        ]);

        $tooUsedUserData = array_merge($this->getUserData(), [
            'invite'       => $tooUsedInvite->id,
            'invite_token' => base64_encode($tooUsedInvite->token),
        ]);

        $this->get($tooUsedInvite->link)
            ->assertForbidden();

        $this->post('/register', $tooUsedUserData)
            ->assertForbidden();
    }

    public function test_with_invalid_email(): void
    {
        $this->updateSetting('general.disable_registrations', false);
        $this->updateSetting('general.invite_only_registrations', true);

        $this->expectException(HttpException::class);
        $invite = Invite::create([
            'email' => 'invited_email@phpvms.net',
            'token' => 'test',
        ]);

        $userData = array_merge($this->getUserData(), [
            'invite'       => $invite->id,
            'invite_token' => base64_encode($invite->token),
        ]);

        $this->get($invite->link)
            ->assertOk();

        $this->post('/register', $userData)
            ->assertForbidden();
    }
}
