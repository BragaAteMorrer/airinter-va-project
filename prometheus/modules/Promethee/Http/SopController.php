<?php

namespace Modules\Promethee\Http;

use App\Contracts\Controller;
use App\Models\Bid;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Promethee\Services\BrandingService;
use Modules\Promethee\Services\OperationIdentityService;
use Modules\Promethee\Services\SopEngineService;
use RuntimeException;

class SopController extends Controller
{
    private const OPERATORS = ['exists', 'gt', 'gte', 'lt', 'lte', 'eq', 'neq'];
    private const SEVERITIES = ['INFO', 'ADVISORY', 'WARNING'];

    public function __construct(
        private readonly SopEngineService $sop,
        private readonly OperationIdentityService $operationIdentity
    ) {}

    public function index(string $operation, Request $request)
    {
        $bid = $this->pilotBid($operation, $request);

        return response()->json(['data' =>
            $this->sop->operation($this->operationIdentity->id($bid), (int) $request->user()->id)
        ]);
    }

    public function ingest(string $operation, Request $request)
    {
        $bid = $this->pilotBid($operation, $request);
        $data = $request->validate([
            'facts' => 'required|array|min:1|max:100',
            'facts.*.fact_id' => 'required|uuid',
            'facts.*.code' => ['required','string','max:80','regex:/^[A-Z0-9_]+$/'],
            'facts.*.category' => 'nullable|string|max:64',
            'facts.*.occurred_at' => 'required|date',
            'facts.*.message' => 'nullable|string|max:1000',
            'facts.*.source_severity' => 'nullable|string|max:32',
            'facts.*.value' => 'nullable|numeric',
            'facts.*.unit' => 'nullable|string|max:32',
            'facts.*.phase' => 'nullable|string|max:32',
            'facts.*.status' => 'nullable|string|max:32',
        ]);

        try {
            $result = $this->sop->ingest(
                $this->operationIdentity->id($bid),
                (int) $request->user()->id,
                $data['facts']
            );
        } catch (RuntimeException $exception) {
            abort(409, $exception->getMessage());
        }

        return response()->json(['data' => $result]);
    }

    public function review(string $operation, string $evaluation, Request $request)
    {
        $bid = $this->pilotBid($operation, $request);
        try {
            $item = $this->sop->pilotReview(
                $this->operationIdentity->id($bid),
                (int) $request->user()->id,
                $evaluation
            );
        } catch (RuntimeException $exception) {
            abort(409, $exception->getMessage());
        }

        return response()->json(['data' => ['evaluation' => $item]]);
    }

    public function admin()
    {
        return view('promethee::admin.sop', [
            'branding' => app(BrandingService::class)->active(),
            'rules' => $this->sop->rules(),
            'alerts' => $this->sop->recentDispatchAlerts(100),
            'operators' => self::OPERATORS,
            'severities' => self::SEVERITIES,
        ]);
    }

    public function saveRule(Request $request, ?string $rule = null)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'fact_code' => ['required','string','max:80','regex:/^[A-Z0-9_]+$/'],
            'operator' => ['required', Rule::in(self::OPERATORS)],
            'threshold' => 'nullable|numeric',
            'phases' => 'nullable|string|max:255',
            'severity' => ['required', Rule::in(self::SEVERITIES)],
            'message' => 'nullable|string|max:500',
            'enabled' => 'nullable|boolean',
            'pilot_review' => 'nullable|boolean',
            'dispatch_alert' => 'nullable|boolean',
        ]);

        $data['enabled'] = $request->boolean('enabled');
        $data['pilot_review'] = $request->boolean('pilot_review');
        $data['dispatch_alert'] = $request->boolean('dispatch_alert');
        $data['phases'] = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) ($data['phases'] ?? ''))
        )));

        try {
            $this->sop->upsertRule($data, $rule);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['sop' => $exception->getMessage()])->withInput();
        }

        return back()->with('success', $rule ? 'Règle SOP mise à jour.' : 'Règle SOP créée.');
    }

    public function deleteRule(string $rule)
    {
        $this->sop->deleteRule($rule);
        return back()->with('success', 'Règle SOP supprimée.');
    }

    public function acknowledgeAlert(string $evaluation, Request $request)
    {
        $data = $request->validate([
            'operation' => 'required|string|max:128',
        ]);
        $bid = $this->adminBid($data['operation']);

        try {
            $this->sop->dispatchAcknowledge(
                $this->operationIdentity->id($bid),
                (int) $bid->user_id,
                $evaluation
            );
        } catch (RuntimeException $exception) {
            return back()->withErrors(['sop' => $exception->getMessage()]);
        }

        return back()->with('success', 'Alerte Dispatch acquittée.');
    }

    private function pilotBid(string $reference, Request $request): Bid
    {
        $bid = $this->operationIdentity->resolveBid($reference, (int) $request->user()->id);
        abort_if(!$bid, 404, 'Opération introuvable.');
        return $bid;
    }

    private function adminBid(string $reference): Bid
    {
        $id = str_starts_with($reference, 'op_') ? substr($reference, 3) : $reference;
        abort_if($id === '', 404, 'Opération introuvable.');
        return Bid::query()->findOrFail($id);
    }
}
