<?php
namespace Modules\Promethee\Http;

use App\Contracts\Controller;
use App\Models\{Aircraft, Airline, Award, Rank, User};
use App\Models\Enums\UserState;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File as Filesystem;
use Modules\Promethee\Services\{BrandingService, ProgressionService};

/**
 * Badge/rank progression administration extracted from PortalController.
 *
 * Route names and request/response contracts remain unchanged.
 */
class AutomationController extends Controller
{
    private function page(string $name, array $data = [])
    {
        return view('promethee::'.$name, $data + ['branding' => app(BrandingService::class)->active()]);
    }

    public function automation()
    {
        $badgeRules = DB::table('promethee_badge_rules')->orderByDesc('updated_at')->get();
        $rankRules = DB::table('promethee_rank_rules')->orderByDesc('updated_at')->get();

        return $this->page('admin.automation', [
            'badgeRules' => $badgeRules,
            'rankRules' => $rankRules,
            'badgeRuleData' => $badgeRules->groupBy('award_id')->map(fn ($rules) => $this->rulePayload($rules->first())),
            'rankRuleData' => $rankRules->groupBy('rank_id')->map(fn ($rules) => $this->rulePayload($rules->first())),
            'awards' => Award::orderBy('name')->get(),
            'ranks' => Rank::orderBy('hours')->get(),
            'awardData' => Award::orderBy('name')->get()->mapWithKeys(fn ($award) => [$award->id => [
                'name' => $award->name,
                'description' => $award->description,
                'image_url' => $award->image_url,
            ]]),
            'rankData' => Rank::orderBy('hours')->get()->mapWithKeys(fn ($rank) => [$rank->id => [
                'name' => $rank->name,
                'hours' => $rank->hours,
                'image_url' => $rank->image_url,
            ]]),
            'airlines' => Airline::orderBy('name')->get(['id', 'name', 'icao']),
            'aircraftTypes' => Aircraft::whereNotNull('icao')->where('icao', '!=', '')->distinct()->orderBy('icao')->pluck('icao'),
            'events' => DB::table('promethee_events')->orderByDesc('starts_at')->get(['id', 'title', 'starts_at']),
            'history' => DB::table('promethee_progression_history')->latest()->limit(30)->get(),
        ]);
    }

    private function rulePayload(object $rule): array
    {
        return [
            'id' => $rule->id,
            'operator' => $rule->operator,
            'criteria' => json_decode($rule->criteria, true) ?: [],
            'active' => (bool) $rule->active,
            'allow_demotion' => (bool) ($rule->allow_demotion ?? false),
        ];
    }

    public function automationRule(string $kind, int $id)
    {
        $table = $kind === 'badge' ? 'promethee_badge_rules' : 'promethee_rank_rules';
        $column = $kind === 'badge' ? 'award_id' : 'rank_id';
        $rule = DB::table($table)->where($column, $id)->orderByDesc('updated_at')->first();

        return response()->json($rule ? $this->rulePayload($rule) : null);
    }

    public function recalculateAutomation(Request $request, ProgressionService $progression)
    {
        $result = $progression->recalculate(null, 'manual');

        return back()->with('success', $result['awards'].' badge(s) et '.$result['promotions'].' promotion(s) attribué(e)(s).');
    }

    private function automationCriteria(Request $request): array
    {
        $data = $request->validate([
            'operator' => 'required|in:and,or',
            'criteria' => 'required|array|min:1|max:12',
            'criteria.*.metric' => 'required|string',
            'criteria.*.value' => 'nullable',
            'criteria.*.text' => 'nullable|string|max:30',
        ]);
        $criteria = $data['criteria'];
        $metrics = [
            'validated_flights', 'flight_minutes', 'total_distance', 'visited_airports',
            'visited_countries', 'seniority_days', 'required_badge', 'route', 'airline',
            'aircraft_icao', 'event_completed', 'night_flights',
        ];

        foreach ($criteria as &$criterion) {
            abort_unless(is_array($criterion) && in_array($criterion['metric'] ?? null, $metrics, true), 422, 'Critère non valide.');
            $criterion['value'] = isset($criterion['value']) ? (float) $criterion['value'] : 0;
            $criterion['route'] = $criterion['metric'] === 'route'
                ? strtoupper(substr((string) ($criterion['text'] ?? ''), 0, 30))
                : null;
            $criterion['text'] = $criterion['metric'] === 'aircraft_icao'
                ? strtoupper(substr((string) ($criterion['text'] ?? ''), 0, 30))
                : null;
        }
        unset($criterion);

        return [$data['operator'], $criteria];
    }

    private function distinctionImage(Request $request): ?string
    {
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $directory = storage_path('app/promethee-distinctions');
            Filesystem::ensureDirectoryExists($directory);
            $name = uniqid('distinction_', true).'.'.$file->extension();
            $file->move($directory, $name);

            return '/promethee-assets/distinctions/'.$name;
        }

        return $request->filled('image_url') ? $request->string('image_url')->toString() : null;
    }

    public function createAutomationAward(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'description' => 'nullable|string|max:1000',
            'image_url' => 'nullable|url|max:2000',
            'image' => 'nullable|image|max:4096',
        ]);
        $data['image_url'] = $this->distinctionImage($request);
        unset($data['image']);
        Award::create($data + ['active' => true]);

        return back()->with('success', 'Badge ajouté au catalogue.');
    }

    public function createAutomationRank(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:50|unique:ranks,name',
            'hours' => 'required|integer|min:0',
            'image_url' => 'nullable|url|max:2000',
            'image' => 'nullable|image|max:4096',
        ]);
        $data['image_url'] = $this->distinctionImage($request);
        unset($data['image']);
        Rank::create($data);

        return back()->with('success', 'Grade ajouté au catalogue.');
    }

    public function updateAutomationAward(Request $request, Award $award)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'description' => 'nullable|string|max:1000',
            'image_url' => 'nullable|url|max:2000',
            'image' => 'nullable|image|max:4096',
        ]);
        $image = $request->hasFile('image') || $request->filled('image_url')
            ? $this->distinctionImage($request)
            : $award->image_url;
        $award->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'image_url' => $image,
        ]);

        return back()->with('success', 'Badge mis à jour.');
    }

    public function updateAutomationRank(Request $request, Rank $rank)
    {
        $data = $request->validate([
            'name' => 'required|string|max:50|unique:ranks,name,'.$rank->id,
            'hours' => 'required|integer|min:0',
            'image_url' => 'nullable|url|max:2000',
            'image' => 'nullable|image|max:4096',
        ]);
        $image = $request->hasFile('image') || $request->filled('image_url')
            ? $this->distinctionImage($request)
            : $rank->image_url;
        $rank->update([
            'name' => $data['name'],
            'hours' => $data['hours'],
            'image_url' => $image,
        ]);

        return back()->with('success', 'Grade mis à jour.');
    }

    public function previewAutomation(Request $request, ProgressionService $progression)
    {
        [$operator, $criteria] = $this->automationCriteria($request);
        $rule = ['operator' => $operator, 'criteria' => $criteria];
        $pilots = User::where('state', UserState::ACTIVE)
            ->get()
            ->filter(fn ($user) => $progression->eligible($user, $rule))
            ->take(100)
            ->values();

        return back()->with('automation_preview', [
            'count' => $pilots->count(),
            'pilots' => $pilots->map(fn ($pilot) => $pilot->pilot_id.' · '.$pilot->name)->all(),
        ]);
    }

    public function saveBadgeRule(Request $request, ProgressionService $progression)
    {
        $data = $request->validate([
            'award_id' => 'required|integer|exists:awards,id',
            'rule_id' => 'nullable|integer|exists:promethee_badge_rules,id',
            'active' => 'nullable|boolean',
        ]);
        [$operator, $criteria] = $this->automationCriteria($request);
        $payload = [
            'award_id' => $data['award_id'],
            'operator' => $operator,
            'criteria' => json_encode($criteria),
            'active' => $request->boolean('active'),
            'updated_at' => now(),
        ];

        if (!empty($data['rule_id'])) {
            DB::table('promethee_badge_rules')->where('id', $data['rule_id'])->update($payload);
        } else {
            DB::table('promethee_badge_rules')->insert($payload + ['created_at' => now()]);
        }

        $result = $progression->recalculate(null, 'rule:badge_saved');

        return back()->with('success', 'Règle de badge enregistrée · '.$result['awards'].' badge(s) attribué(s) automatiquement.');
    }

    public function saveRankRule(Request $request, ProgressionService $progression)
    {
        $data = $request->validate([
            'rank_id' => 'required|integer|exists:ranks,id',
            'rule_id' => 'nullable|integer|exists:promethee_rank_rules,id',
            'active' => 'nullable|boolean',
            'allow_demotion' => 'nullable|boolean',
        ]);
        [$operator, $criteria] = $this->automationCriteria($request);
        $payload = [
            'rank_id' => $data['rank_id'],
            'operator' => $operator,
            'criteria' => json_encode($criteria),
            'active' => $request->boolean('active'),
            'allow_demotion' => $request->boolean('allow_demotion'),
            'updated_at' => now(),
        ];

        if (!empty($data['rule_id'])) {
            DB::table('promethee_rank_rules')->where('id', $data['rule_id'])->update($payload);
        } else {
            DB::table('promethee_rank_rules')->insert($payload + ['created_at' => now()]);
        }

        $result = $progression->recalculate(null, 'rule:rank_saved');

        return back()->with('success', 'Règle de grade enregistrée · '.$result['promotions'].' promotion(s) appliquée(s) automatiquement.');
    }
}
