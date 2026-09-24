<?php

namespace Modules\Promethee\Http;

use App\Contracts\Controller;
use App\Models\Bid;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Promethee\Services\DatalinkService;
use Modules\Promethee\Services\OperationIdentityService;
use RuntimeException;

class DatalinkController extends Controller
{
    private const CATEGORIES = ['OPS', 'DISPATCH', 'WEATHER', 'SYSTEM', 'CREW'];
    private const PRIORITIES = ['ROUTINE', 'ADVISORY', 'IMPORTANT', 'URGENT', 'NORMAL', 'HIGH'];

    public function __construct(
        private readonly DatalinkService $datalink,
        private readonly OperationIdentityService $operationIdentity
    ) {}

    public function index(string $operation, Request $request)
    {
        $bid = $this->pilotBid($operation, $request);

        return response()->json(['data' =>
            $this->datalink->list(
                $this->operationIdentity->id($bid),
                (int) $request->user()->id,
                'OPS_TO_COCKPIT'
            )
        ]);
    }

    public function send(string $operation, Request $request)
    {
        $bid = $this->pilotBid($operation, $request);
        $data = $request->validate([
            'body' => 'required|string|min:1|max:2000',
            'category' => ['nullable', Rule::in(self::CATEGORIES)],
            'priority' => ['nullable', Rule::in(self::PRIORITIES)],
            'requires_ack' => 'nullable|boolean',
            'client_message_id' => 'nullable|uuid',
            'reply_to' => 'nullable|uuid',
        ]);

        $user = $request->user();
        $sender = trim((string) ($user->ident ?: ($user->name_private ?? $user->name ?? 'PILOT')));
        $message = $this->datalink->send(
            $this->operationIdentity->id($bid),
            (int) $user->id,
            'COCKPIT_TO_OPS',
            $data['category'] ?? 'CREW',
            $data['priority'] ?? 'ROUTINE',
            $data['body'],
            (bool) ($data['requires_ack'] ?? false),
            $sender,
            $data['client_message_id'] ?? null,
            $data['reply_to'] ?? null
        );

        return response()->json(['data' => ['message' => $message]], 201);
    }

    public function read(string $operation, string $message, Request $request)
    {
        $bid = $this->pilotBid($operation, $request);

        try {
            $updated = $this->datalink->markRead(
                $this->operationIdentity->id($bid),
                (int) $request->user()->id,
                $message,
                'OPS_TO_COCKPIT'
            );
        } catch (RuntimeException $exception) {
            abort(409, $exception->getMessage());
        }

        return response()->json(['data' => ['message' => $updated]]);
    }

    public function acknowledge(string $operation, string $message, Request $request)
    {
        $bid = $this->pilotBid($operation, $request);

        try {
            $updated = $this->datalink->acknowledge(
                $this->operationIdentity->id($bid),
                (int) $request->user()->id,
                $message,
                'OPS_TO_COCKPIT'
            );
        } catch (RuntimeException $exception) {
            abort(409, $exception->getMessage());
        }

        return response()->json(['data' => ['message' => $updated]]);
    }

    public function adminIndex(Request $request)
    {
        $data = $request->validate(['operation' => 'required|string|max:128']);
        $bid = $this->adminBid($data['operation']);

        return response()->json(['data' =>
            $this->datalink->list(
                $this->operationIdentity->id($bid),
                (int) $bid->user_id,
                'COCKPIT_TO_OPS'
            )
        ]);
    }

    public function adminSend(Request $request)
    {
        $data = $request->validate([
            'operation' => 'required|string|max:128',
            'body' => 'required|string|min:1|max:2000',
            'category' => ['nullable', Rule::in(self::CATEGORIES)],
            'priority' => ['nullable', Rule::in(self::PRIORITIES)],
            'requires_ack' => 'nullable|boolean',
            'client_message_id' => 'nullable|uuid',
            'reply_to' => 'nullable|uuid',
            'sender_label' => 'nullable|string|max:80',
        ]);
        $bid = $this->adminBid($data['operation']);

        $message = $this->datalink->send(
            $this->operationIdentity->id($bid),
            (int) $bid->user_id,
            'OPS_TO_COCKPIT',
            $data['category'] ?? 'OPS',
            $data['priority'] ?? 'ROUTINE',
            $data['body'],
            (bool) ($data['requires_ack'] ?? true),
            $data['sender_label'] ?? 'AIR INTER OPS',
            $data['client_message_id'] ?? null,
            $data['reply_to'] ?? null
        );

        return response()->json(['data' => ['message' => $message]], 201);
    }

    public function adminRead(string $message, Request $request)
    {
        $data = $request->validate(['operation' => 'required|string|max:128']);
        $bid = $this->adminBid($data['operation']);

        try {
            $updated = $this->datalink->markRead(
                $this->operationIdentity->id($bid),
                (int) $bid->user_id,
                $message,
                'COCKPIT_TO_OPS'
            );
        } catch (RuntimeException $exception) {
            abort(409, $exception->getMessage());
        }

        return response()->json(['data' => ['message' => $updated]]);
    }

    public function adminAcknowledge(string $message, Request $request)
    {
        $data = $request->validate(['operation' => 'required|string|max:128']);
        $bid = $this->adminBid($data['operation']);

        try {
            $updated = $this->datalink->acknowledge(
                $this->operationIdentity->id($bid),
                (int) $bid->user_id,
                $message,
                'COCKPIT_TO_OPS'
            );
        } catch (RuntimeException $exception) {
            abort(409, $exception->getMessage());
        }

        return response()->json(['data' => ['message' => $updated]]);
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
