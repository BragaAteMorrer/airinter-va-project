<?php

namespace Modules\Promethee\Http;

use App\Contracts\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Promethee\Services\BrandingService;
use Modules\Promethee\Services\DispatchDeskService;

class DispatchDeskController extends Controller
{
    public function __construct(private readonly DispatchDeskService $dispatch) {}

    public function index()
    {
        $user = Auth::user();
        $canDispatchActions = $user && (
            $user->hasRole('admin')
            || (method_exists($user, 'isAbleTo') && $user->isAbleTo('admin-access'))
        );

        return view('promethee::admin.dispatch', [
            'branding' => app(BrandingService::class)->active(),
            'canDispatchActions' => (bool) $canDispatchActions,
        ]);
    }

    public function feed()
    {
        return response()->json(['data' => $this->dispatch->board()]);
    }

    public function operation(string $operation)
    {
        return response()->json(['data' => $this->dispatch->detail($operation)]);
    }
}
