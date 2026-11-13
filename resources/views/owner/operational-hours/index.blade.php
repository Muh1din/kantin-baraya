<x-layouts.owner title="Jam Operasional">
    <div class="space-y-6">
        <header>
            <h1 class="text-xl font-black text-stone-900">Jam Operasional</h1>
            <p class="mt-1 text-sm text-stone-500">Atur jam buka dan tutup untuk setiap hari dalam seminggu.</p>
        </header>

        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800" role="status">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                <p class="font-bold">Jam operasional belum dapat disimpan:</p>
                <ul class="mt-1 list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('owner.operational-hours.update') }}" method="POST" class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
            @csrf
            @method('PUT')
            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px] text-left text-sm">
                    <thead class="bg-stone-50 text-xs font-semibold uppercase tracking-wider text-stone-500">
                        <tr>
                            <th scope="col" class="px-5 py-4 sm:px-6">Hari</th>
                            <th scope="col" class="px-5 py-4">Jam Buka</th>
                            <th scope="col" class="px-5 py-4">Jam Tutup</th>
                            <th scope="col" class="px-5 py-4 sm:px-6">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($days as $day => $dayLabel)
                            @php
                                $schedule = $hours->get($day);
                                $isClosed = old("hours.{$day}.is_closed", $schedule?->is_closed ?? true);
                                $openTime = old("hours.{$day}.open_time", $schedule?->open_time ? substr($schedule->open_time, 0, 5) : '');
                                $closeTime = old("hours.{$day}.close_time", $schedule?->close_time ? substr($schedule->close_time, 0, 5) : '');
                            @endphp
                            <tr class="hover:bg-stone-50/50">
                                <th scope="row" class="px-5 py-4 font-bold text-stone-900 sm:px-6">{{ $dayLabel }}</th>
                                <td class="px-5 py-4">
                                    <label class="sr-only" for="open-{{ $day }}">Jam buka {{ $dayLabel }}</label>
                                    <input id="open-{{ $day }}" name="hours[{{ $day }}][open_time]" type="time" value="{{ $openTime }}"
                                        class="rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                                    @error("hours.{$day}.open_time")
                                        <p class="mt-1 text-xs text-red-700">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-5 py-4">
                                    <label class="sr-only" for="close-{{ $day }}">Jam tutup {{ $dayLabel }}</label>
                                    <input id="close-{{ $day }}" name="hours[{{ $day }}][close_time]" type="time" value="{{ $closeTime }}"
                                        class="rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                                    @error("hours.{$day}.close_time")
                                        <p class="mt-1 text-xs text-red-700">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <label class="inline-flex items-center gap-2 font-medium text-stone-700">
                                        <input type="hidden" name="hours[{{ $day }}][is_closed]" value="0">
                                        <input type="checkbox" name="hours[{{ $day }}][is_closed]" value="1" @checked((bool) $isClosed)
                                            class="rounded border-stone-300 text-orange-600 focus:ring-orange-500">
                                        Libur
                                    </label>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-stone-100 px-5 py-4 sm:px-6">
                <button type="submit" class="rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-orange-600">
                    Simpan Jam Operasional
                </button>
            </div>
        </form>
    </div>
</x-layouts.owner>
