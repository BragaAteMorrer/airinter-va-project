<?php

namespace Modules\Promethee\Http;

use App\Contracts\Controller;
use App\Services\BidService;
use Illuminate\Http\Request;
use Modules\Promethee\Http\Api\AcarsSimBriefController;

class MinitelOperationsController extends Controller
{
    public function __construct(
        private readonly OperationsV1Controller $operations,
        private readonly AcarsSimBriefController $simbrief
    ) {}

    public function index(Request $request)
    {
        return $this->operations->index($request);
    }

    public function reserve(string $flight, Request $request, BidService $bids)
    {
        return $this->operations->reserveFlight($flight, $request, $bids);
    }

    public function show(string $operation, Request $request)
    {
        return $this->operations->show($operation, $request);
    }

    public function aircraft(string $operation, Request $request)
    {
        return $this->operations->aircraft($operation, $request);
    }

    public function selectAircraft(string $operation, Request $request)
    {
        return $this->operations->selectAircraft($operation, $request);
    }

    public function briefing(string $operation, Request $request)
    {
        return $this->operations->briefing($operation, $request);
    }

    public function dispatch(string $operation, Request $request)
    {
        return $this->operations->operationDispatch($operation, $request);
    }

    public function prefilePirep(string $operation, Request $request)
    {
        return $this->operations->prefilePirep($operation, $request);
    }

    public function simbriefRedirect(string $operation, Request $request)
    {
        return $this->simbrief->redirectOperation($request, $operation);
    }

    public function simbriefImportAccount(string $operation, Request $request)
    {
        return $this->simbrief->importAccountOperation($request, $operation);
    }

    public function simbriefSession(string $operation, Request $request)
    {
        return $this->simbrief->sessionOperation($request, $operation);
    }

    public function simbriefImport(string $operation, Request $request)
    {
        return $this->simbrief->importOperation($request, $operation);
    }
}
