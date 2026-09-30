<?php

namespace Modules\Promethee\Http;

use App\Contracts\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Promethee\Services\BrandingService;
use Modules\Promethee\Services\DispatchDeskService;
use Modules\Promethee\Services\DatalinkAccessService;

class DispatchDeskController extends Controller
{
    public function __construct(
        private readonly DispatchDeskService $dispatch,
        private readonly DatalinkAccessService $datalinkAccess
    ) {}

    public function index()
    {
        $user = Auth::user();
        $canDispatchActions = $this->datalinkAccess->canOperate($user);

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
