<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Controller;
use App\Models\Setting;
use App\Services\FinanceService;
use Igaster\LaravelTheme\Facades\Theme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Settings whose values must never be echoed back to the browser or logs.
     * The explicit key fallback also protects upgraded installations before
     * their settings metadata has been re-synchronized.
     */
    private const SECRET_SETTING_KEYS = [
        'simbrief.api_key',
    ];

    public function __construct(
        private readonly FinanceService $financeSvc
    ) {}

    /**
     * Get a list of themes formatted for a select box
     */
    private function getThemes(): array
    {
        Theme::rebuildCache();
        $themes = Theme::all();
        $theme_list = [];
        foreach ($themes as $t) {
            if (!$t || !$t->name || $t->name === 'false') {
                continue;
            }
            $theme_list[] = $t->name;
        }

        return $theme_list;
    }

    /**
     * Return the currency list
     */
    private function getCurrencyList(): array
    {
        $curr = [];
        foreach (config('money') as $currency => $attrs) {
            $name = $attrs['name'].' ('.$attrs['symbol'].'/'.$currency.')';
            $curr[$currency] = $name;
        }

        return $curr;
    }

    /**
     * Display the settings. Group them by the setting group
     */
    public function index(): View
    {
        $settings = Setting::where('type', '!=', 'hidden')->orderBy('order')->get();
        $settings = $settings->groupBy('group');

        return view('admin.settings.index', [
            'currencies'       => $this->getCurrencyList(),
            'grouped_settings' => $settings,
            'themes'           => $this->getThemes(),
        ]);
    }

    /**
     * Update the specified setting in storage.
     */
    public function update(Request $request): RedirectResponse
    {
        $clearSecrets = (array) $request->input('_clear_secret', []);

        foreach ($request->post() as $id => $value) {
            $setting = Setting::find($id);
            if (!$setting) {
                continue;
            }

            if ($this->isSecretSetting($setting)) {
                $clearRequested = get_truth_state($clearSecrets[$setting->id] ?? false);
                $incoming = is_scalar($value) ? trim((string) $value) : '';

                // Blank means "keep the current credential". Clearing a secret
                // must always be an explicit administrator action.
                if (!$clearRequested && $incoming === '') {
                    continue;
                }

                $value = $clearRequested ? '' : $incoming;
                Log::info('Updating secret setting "'.$setting->id.'" (value redacted).');
            } else {
                if ($setting->type == 'bool' || $setting->type == 'boolean') {
                    $value = get_truth_state($value);
                }

                Log::info('Updating "'.$setting->id.'" from "'.$setting->value.'" to "'.$value.'"');
            }

            $setting->value = $value;
            $setting->save();

            $cache = config('cache.keys.SETTINGS');
            Cache::forget($cache['key'].$setting->key);
        }

        $this->financeSvc->changeJournalCurrencies();

        flash('Settings saved!');

        return redirect('/admin/settings');
    }

    private function isSecretSetting(Setting $setting): bool
    {
        return $setting->type === 'secret'
            || in_array($setting->key, self::SECRET_SETTING_KEYS, true);
    }

}
