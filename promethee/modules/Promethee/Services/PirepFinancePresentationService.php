<?php

namespace Modules\Promethee\Services;

use App\Models\Pirep;
use App\Support\Money;

class PirepFinancePresentationService
{
    public function build(Pirep $pirep, ?int $passengerCount = null): array
    {
        $companyTransactions = $pirep->airline?->journal
            ? $pirep->airline->journal
                ->transactionsReferencingObjectQuery($pirep)
                ->orderBy('post_date')
                ->get()
            : collect();

        $pilotTransactions = $pirep->user?->journal
            ? $pirep->user->journal
                ->transactionsReferencingObjectQuery($pirep)
                ->orderBy('post_date')
                ->get()
            : collect();

        $credits = (int) $companyTransactions->sum('credit');
        $debits = (int) $companyTransactions->sum('debit');
        $net = $credits - $debits;
        $pilotNet = (int) $pilotTransactions->sum('credit') - (int) $pilotTransactions->sum('debit');

        $rows = $companyTransactions->map(fn ($transaction) => $this->presentTransaction($transaction))->values();

        $categoryOrder = [
            'Exploitation appareil',
            'Carburant',
            'Assistance au sol',
            'Maintenance',
            'Service passagers',
            'Taxes & redevances',
            'Personnel navigant',
            'Autres charges',
        ];

        $categorySummary = $rows
            ->filter(fn ($row) => $row['amount_raw'] < 0)
            ->groupBy('category')
            ->map(function ($categoryRows, $category) use ($debits, $categoryOrder) {
                $amount = abs((int) $categoryRows->sum('amount_raw'));

                return [
                    'category' => $category,
                    'amount_raw' => $amount,
                    'amount' => new Money($amount),
                    'share' => $debits > 0 ? ($amount / $debits) * 100 : 0.0,
                    'sort' => (($index = array_search($category, $categoryOrder, true)) === false) ? 999 : $index,
                ];
            })
            ->sortBy('sort')
            ->values();

        $safePassengerCount = $passengerCount !== null && $passengerCount > 0 ? $passengerCount : null;

        return [
            'company_transactions' => $companyTransactions,
            'pilot_transactions' => $pilotTransactions,
            'rows' => $rows,
            'category_summary' => $categorySummary,
            'credits' => new Money($credits),
            'debits' => new Money($debits),
            'net' => new Money($net),
            'pilot_net' => new Money($pilotNet),
            'margin' => $credits > 0 ? ($net / $credits) * 100 : null,
            'revenue_per_passenger' => $safePassengerCount ? new Money((int) round($credits / $safePassengerCount)) : null,
            'cost_per_passenger' => $safePassengerCount ? new Money((int) round($debits / $safePassengerCount)) : null,
            'net_per_passenger' => $safePassengerCount ? new Money((int) round($net / $safePassengerCount)) : null,
        ];
    }

    private function presentTransaction($transaction): array
    {
        $credit = (int) ($transaction->credit ?? 0);
        $debit = (int) ($transaction->debit ?? 0);
        $amount = $credit - $debit;
        $tags = collect((array) ($transaction->tags ?? []))
            ->map(fn ($tag) => strtolower((string) $tag));
        $group = strtolower((string) ($transaction->transaction_group ?? ''));
        $memo = strtolower((string) ($transaction->memo ?? ''));
        $search = trim($group.' '.$memo.' '.$tags->implode(' '));

        [$category, $label] = $this->classify($search, $amount, $transaction);

        return [
            'transaction' => $transaction,
            'label' => $label,
            'category' => $category,
            'credit_raw' => $credit,
            'debit_raw' => $debit,
            'amount_raw' => $amount,
            'credit' => $credit > 0 ? new Money($credit) : null,
            'debit' => $debit > 0 ? new Money($debit) : null,
            'amount' => new Money(abs($amount)),
            'is_credit' => $amount >= 0,
        ];
    }

    private function classify(string $search, int $amount, $transaction): array
    {
        $category = 'Autres charges';
        $label = $transaction->transaction_group ?: ($transaction->memo ?: 'Écriture phpVMS');

        if ($amount >= 0 || str_contains($search, 'fare')) {
            return ['Revenus commerciaux', str_contains($search, 'fare') ? 'Billetterie passagers' : $label];
        }

        if (str_contains($search, 'fuel')) {
            return ['Carburant', 'Carburant'];
        }

        if (str_contains($search, 'pilot_pay') || str_contains($search, 'pilot pay')) {
            return ['Personnel navigant', 'Rémunération pilote'];
        }

        if (
            str_contains($search, 'ground_handling')
            || str_contains($search, 'ground handling')
            || str_contains($search, 'handling')
            || str_contains($search, 'de-icing')
            || str_contains($search, 'deicing')
        ) {
            if (str_contains($search, 'departure')) {
                $label = 'Handling départ';
            } elseif (str_contains($search, 'arrival')) {
                $label = 'Handling arrivée';
            } elseif (str_contains($search, 'de-icing') || str_contains($search, 'deicing')) {
                $label = 'Assistance sol / Dégivrage';
            } elseif (str_contains($search, 'ground staff')) {
                $label = 'Personnel sol / Handling';
            }

            return ['Assistance au sol', $label];
        }

        if (str_contains($search, 'maintenance')) {
            return ['Maintenance', 'Maintenance'];
        }

        if (str_contains($search, 'catering')) {
            return ['Service passagers', 'Catering'];
        }

        if (
            str_contains($search, 'eurocontrol')
            || str_contains($search, 'navigation')
            || str_contains($search, 'landing')
            || str_contains($search, 'airport fee')
            || str_contains($search, 'airport:')
        ) {
            if (str_contains($search, 'eurocontrol') || str_contains($search, 'navigation')) {
                $label = 'Eurocontrol / Navigation';
            } elseif (str_contains($search, 'landing') || str_contains($search, 'airport fee')) {
                $label = 'Redevances aéroportuaires';
            }

            return ['Taxes & redevances', $label];
        }

        if (str_contains($search, 'subfleet') || str_contains($search, 'aircraft')) {
            return [
                'Exploitation appareil',
                str_contains($search, 'block time') ? 'Coût temps de vol appareil' : 'Coût opérationnel appareil',
            ];
        }

        return [$category, $label];
    }
}
