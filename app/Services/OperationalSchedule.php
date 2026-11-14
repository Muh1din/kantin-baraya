<?php

namespace App\Services;

use App\Models\OperationalHour;
use App\Models\Holiday;
use Illuminate\Support\Carbon;

class OperationalSchedule
{
    public static function isClosedToday(): bool
    {
        if (self::holidayToday()) {
            return true;
        }

        $dayOfWeek = Carbon::now(config('app.timezone'))->dayOfWeek;
        $schedule = OperationalHour::query()->where('day_of_week', $dayOfWeek)->first();

        // A missing schedule is treated as closed, matching the owner form default.
        return ! $schedule || $schedule->is_closed;
    }

    public static function holidayToday(): ?Holiday
    {
        return Holiday::query()
            ->whereDate('holiday_date', Carbon::now(config('app.timezone'))->toDateString())
            ->first();
    }

    public static function nextHoliday(): ?Holiday
    {
        return Holiday::query()
            ->whereDate('holiday_date', '>=', Carbon::now(config('app.timezone'))->toDateString())
            ->orderBy('holiday_date')
            ->first();
    }
}
