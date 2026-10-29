<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AvatarController extends Controller
{
    public function __invoke(string $filename): StreamedResponse
    {
        // Never serve arbitrary storage files: only explicitly registered
        // public profile images may be fetched via this endpoint.
        $path = 'avatars/'.$filename;
        abort_unless(User::query()->where('avatar_path', $path)->exists(), 404);
        $disk = Storage::disk('public');
        abort_unless($disk->exists($path), 404);

        return $disk->response($path, $filename, [
            'Cache-Control' => 'public, max-age=300, must-revalidate',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
