<?php
namespace Tests;

use Carbon\CarbonImmutable;
use Modules\Promethee\Services\CalendarEventDays;
use PHPUnit\Framework\TestCase;

final class PrometheeCalendarEventDaysTest extends TestCase
{
    public function test_multiday_event_is_visible_on_each_local_day(): void
    {
        $event = (object) [
            'starts_at' => '2026-11-14 19:00:00',
            'ends_at' => '2026-11-16 09:00:00',
        ];
        $days = CalendarEventDays::group([$event], CarbonImmutable::parse('2026-11-09', 'Europe/Paris'));
        self::assertSame(['2026-11-14', '2026-11-15', '2026-11-16'], array_keys($days));
    }

    public function test_end_at_midnight_does_not_occupy_next_day(): void
    {
        $event = (object) [
            'starts_at' => '2026-11-14 19:00:00',
            'ends_at' => '2026-11-14 23:00:00', // Midnight in Paris (UTC+1)
        ];
        $days = CalendarEventDays::group([$event], CarbonImmutable::parse('2026-11-09', 'Europe/Paris'));
        self::assertSame(['2026-11-14'], array_keys($days));
    }

    public function test_grid_is_clipped_to_42_days(): void
    {
        $event = (object) [
            'starts_at' => '2026-11-01 00:00:00',
            'ends_at' => '2027-02-01 00:00:00',
        ];
        $days = CalendarEventDays::group([$event], CarbonImmutable::parse('2026-10-26', 'Europe/Paris'));
        self::assertCount(42, $days);
        self::assertArrayHasKey('2026-12-06', $days);
        self::assertArrayNotHasKey('2026-12-07', $days);
    }

    public function test_empty_and_inverted_intervals_are_ignored(): void
    {
        $events = [(object)['starts_at'=>'2026-11-15 12:00:00', 'ends_at'=>'2026-11-15 12:00:00']];
        self::assertSame([], CalendarEventDays::group($events, CarbonImmutable::parse('2026-11-09', 'Europe/Paris')));
    }
}
