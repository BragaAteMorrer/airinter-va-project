<?php

namespace Tests\Feature;

use App\Models\LegacyIdentity;
use App\Models\User;
use App\Services\PrometheeUserImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PrometheeUserImporterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.connections.promethee' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
            'airinter-id.legacy.pilot_id_prefix' => 'IT',
            'airinter-id.legacy.pilot_id_length' => 3,
        ]);

        DB::purge('promethee');

        Schema::connection('promethee')->create('users', function ($table) {
            $table->integer('id')->primary();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->integer('pilot_id')->nullable();
            $table->integer('airline_id')->nullable();
            $table->integer('rank_id')->nullable();
            $table->integer('state')->default(0);
            $table->string('timezone')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function test_import_preserves_existing_password_hash_and_identity_link(): void
    {
        $hash = Hash::make('airinter-secret');

        DB::connection('promethee')->table('users')->insert([
            'id' => 42,
            'name' => 'Jean Dupont',
            'email' => 'pilot@example.test',
            'password' => $hash,
            'pilot_id' => 7,
            'airline_id' => 1,
            'rank_id' => 3,
            'state' => 1,
            'timezone' => 'Europe/Paris',
            'email_verified_at' => now(),
            'deleted_at' => null,
        ]);

        $stats = app(PrometheeUserImporter::class)->import();

        $this->assertSame(1, $stats['created']);
        $user = User::where('email', 'pilot@example.test')->firstOrFail();

        $this->assertSame($hash, $user->getRawOriginal('password'));
        $this->assertTrue(Hash::check('airinter-secret', $user->password));
        $this->assertSame('active', $user->state);

        $identity = LegacyIdentity::where('provider', 'promethee')
            ->where('external_user_id', '42')
            ->firstOrFail();

        $this->assertSame($user->id, $identity->user_id);
        $this->assertSame('IT007', $identity->external_ident);
        $this->assertSame(7, $identity->metadata['pilot_id']);
    }

    public function test_soft_deleted_phpvms_user_keeps_historical_identity_without_sso_access(): void
    {
        DB::connection('promethee')->table('users')->insert([
            'id' => 99,
            'name' => 'Ancien Pilote',
            'email' => 'former@example.test',
            'password' => Hash::make('old-secret'),
            'pilot_id' => 99,
            'airline_id' => 1,
            'rank_id' => null,
            'state' => 1,
            'timezone' => 'Europe/Paris',
            'email_verified_at' => null,
            'deleted_at' => now(),
        ]);

        app(PrometheeUserImporter::class)->import();

        $user = User::where('email', 'former@example.test')->firstOrFail();
        $this->assertSame('deleted', $user->state);
        $this->assertFalse($user->canUseSso());

        $identity = LegacyIdentity::where('external_user_id', '99')->firstOrFail();
        $this->assertNotNull($identity->metadata['legacy_deleted_at']);
    }

    public function test_second_sync_does_not_replace_airinter_id_password_or_link_date(): void
    {
        DB::connection('promethee')->table('users')->insert([
            'id' => 7,
            'name' => 'Pilot One',
            'email' => 'one@example.test',
            'password' => Hash::make('legacy-pass'),
            'pilot_id' => 1,
            'airline_id' => 1,
            'rank_id' => null,
            'state' => 1,
            'timezone' => 'Europe/Paris',
            'email_verified_at' => null,
            'deleted_at' => null,
        ]);

        $importer = app(PrometheeUserImporter::class);
        $importer->import();

        $user = User::where('email', 'one@example.test')->firstOrFail();
        $newHash = Hash::make('new-airinter-id-password');
        $user->forceFill(['password' => $newHash])->save();

        $identity = LegacyIdentity::where('external_user_id', '7')->firstOrFail();
        $linkedAt = $identity->linked_at;

        DB::connection('promethee')->table('users')->where('id', 7)->update([
            'password' => Hash::make('changed-legacy-password'),
        ]);

        $importer->import();

        $user->refresh();
        $identity->refresh();

        $this->assertSame($newHash, $user->getRawOriginal('password'));
        $this->assertTrue($linkedAt->equalTo($identity->linked_at));
    }
}
