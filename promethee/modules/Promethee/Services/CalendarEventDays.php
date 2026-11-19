<?php
namespace Modules\Promethee\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Index UTC event intervals by their local calendar days.
 * Events use half-open intervals [starts_at, ends_at): midnight belongs to the next day.
 */
final class CalendarEventDays
{
    public static function group(iterable $events, CarbonImmutable $gridStart, int $numberOfDays = 42): array
    {
        $first = $gridStart->setTimezone('Europe/Paris')->startOfDay();
        $limit = $first->addDays($numberOfDays);
        $indexed = [];

        foreach ($events as $event) {
            $start = CarbonImmutable::parse($event->starts_at, 'UTC')->setTimezone('Europe/Paris');
            $end = CarbonImmutable::parse($event->ends_at, 'UTC')->setTimezone('Europe/Paris');
            if ($end->lessThanOrEqualTo($start)) {
                continue;
            }

            $firstDay = $start->startOfDay()->max($first);
            $lastDay = $end->subMicrosecond()->startOfDay();
            for ($day = $firstDay; $day->lessThanOrEqualTo($lastDay) && $day->lessThan($limit); $day = $day->addDay()) {
                $indexed[$day->toDateString()][] = $event;
            }
        }
        return $indexed;
    }
}
