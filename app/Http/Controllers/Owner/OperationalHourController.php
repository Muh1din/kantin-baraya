<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\OperationalHour;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class OperationalHourController extends Controller
{
    private const DAYS = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        0 => 'Minggu',
    ];

    public function index(): View
    {
        return view('owner.operational-hours.index', [
            'days' => self::DAYS,
            'hours' => OperationalHour::query()->get()->keyBy('day_of_week'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = [
            'hours' => ['required', 'array:0,1,2,3,4,5,6', 'size:7'],
        ];

        foreach (array_keys(self::DAYS) as $day) {
            $rules["hours.{$day}.is_closed"] = ['required', 'boolean'];
            $rules["hours.{$day}.open_time"] = ['nullable', 'date_format:H:i'];
            $rules["hours.{$day}.close_time"] = ['nullable', 'date_format:H:i'];
        }

        $validator = Validator::make($request->all(), $rules);
        $validator->after(function ($validator) use ($request): void {
            foreach (array_keys(self::DAYS) as $day) {
                $schedule = $request->input("hours.{$day}", []);
                if (! is_array($schedule) || filter_var($schedule['is_closed'] ?? null, FILTER_VALIDATE_BOOLEAN)) {
                    continue;
                }

                $openTime = $schedule['open_time'] ?? null;
                $closeTime = $schedule['close_time'] ?? null;

                if (! $openTime || ! $closeTime) {
                    $validator->errors()->add("hours.{$day}.open_time", 'Jam buka dan tutup wajib diisi untuk hari yang beroperasi.');

                    continue;
                }

                if ($closeTime <= $openTime) {
                    $validator->errors()->add("hours.{$day}.close_time", 'Jam tutup harus lebih akhir daripada jam buka.');
                }
            }
        });

        $validated = $validator->validate();

        DB::transaction(function () use ($validated): void {
            foreach ($validated['hours'] as $day => $schedule) {
                $isClosed = (bool) $schedule['is_closed'];

                OperationalHour::query()->updateOrCreate(
                    ['day_of_week' => (int) $day],
                    [
                        'open_time' => $isClosed ? null : $schedule['open_time'],
                        'close_time' => $isClosed ? null : $schedule['close_time'],
                        'is_closed' => $isClosed,
                    ],
                );
            }
        });

        return redirect()
            ->route('owner.operational-hours.index')
            ->with('status', 'Jam operasional berhasil diperbarui.');
    }
}
