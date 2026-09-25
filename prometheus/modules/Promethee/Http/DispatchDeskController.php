<?php

namespace Modules\Promethee\Http;

use App\Contracts\Controller;
use Modules\Promethee\Services\BrandingService;
use Modules\Promethee\Services\DispatchDeskService;

class DispatchDeskController extends Controller
{
    public function __construct(private readonly DispatchDeskService $dispatch) {}

    public function index()
    {
        return view('promethee::admin.dispatch', [
            'branding' => app(BrandingService::class)->active(),
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
