<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HolidayController extends Controller
{
    public function index(): View
    {
        return view('owner.holidays.index', [
            'holidays' => Holiday::query()->orderBy('holiday_date')->paginate(10),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'holiday_date' => [
                'required',
                'date',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (Holiday::query()->whereDate('holiday_date', $value)->exists()) {
                        $fail('Tanggal tersebut sudah terdaftar sebagai hari libur.');
                    }
                },
            ],
            'name' => ['required', 'string', 'max:160'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        Holiday::query()->create($validated);

        return redirect()
            ->route('owner.holidays.index')
            ->with('status', 'Hari libur berhasil ditambahkan.');
    }

    public function destroy(Holiday $holiday): RedirectResponse
    {
        $holiday->delete();

        return redirect()
            ->route('owner.holidays.index')
            ->with('status', 'Hari libur berhasil dihapus.');
    }
}
