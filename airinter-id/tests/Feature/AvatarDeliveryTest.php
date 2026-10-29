<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AvatarDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_registered_avatar_is_accessible_without_public_storage_symlink(): void
    {
        Storage::fake('public');
        $filename = '2755c039-9eb7-4b15-9002-699576b0108e.jpg';
        Storage::disk('public')->put('avatars/'.$filename, 'test-image-bytes');

        User::query()->create([
            'subject' => '2755c039-9eb7-4b15-9002-699576b0108e',
            'display_name' => 'Test pilot',
            'email' => 'test-avatar@example.test',
            'password' => bcrypt('test-password'),
            'avatar_path' => 'avatars/'.$filename,
            'state' => 'active',
        ]);

        $this->get('/avatars/'.$filename)->assertOk()->assertSee('test-image-bytes');
        $this->get('/storage/avatars/'.$filename)->assertOk();
    }

    public function test_unregistered_avatar_is_not_exposed_even_if_file_exists(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('avatars/unregistered.jpg', 'unlisted');
        $this->get('/avatars/unregistered.jpg')->assertNotFound();
        $this->get('/avatars/..%2Fsecret.txt')->assertNotFound();
    }

    public function test_missing_file_returns_404_instead_of_broken_image_response(): void
    {
        Storage::fake('public');
        User::query()->create([
            'subject' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
            'display_name' => 'Test pilot 2',
            'email' => 'missing-avatar@example.test',
            'password' => bcrypt('test-password'),
            'avatar_path' => 'avatars/missing.jpg',
            'state' => 'active',
        ]);
        $this->get('/avatars/missing.jpg')->assertNotFound();
    }
}
