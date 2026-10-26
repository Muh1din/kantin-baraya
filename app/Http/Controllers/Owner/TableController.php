<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\Table;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TableController extends Controller
{
    public function index(): View
    {
        return view('owner.tables.index', [
            'tables' => Table::query()->orderBy('table_code')->paginate(10),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'table_code' => ['required', 'string', 'max:255', Rule::unique('tables', 'table_code')],
        ]);

        Table::query()->create([
            'table_code' => $validated['table_code'],
            'qr_token' => Str::random(40),
            'is_active' => true,
        ]);

        return redirect()->route('owner.tables.index')->with('status', 'Meja berhasil ditambahkan.');
    }

    public function update(Request $request, Table $table): RedirectResponse
    {
        $validated = $request->validate([
            'table_code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tables', 'table_code')->ignore($table->id),
            ],
        ]);

        $table->update($validated);

        return redirect()->route('owner.tables.index')->with('status', 'Meja berhasil diperbarui.');
    }

    public function toggleStatus(Table $table): RedirectResponse
    {
        $table->update(['is_active' => ! $table->is_active]);
        $status = $table->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('owner.tables.index')->with('status', "Meja berhasil {$status}.");
    }

    public function generateQr(Table $table): View
    {
        $menuUrl = route('menu.table', $table->qr_token);
        $qrSvg = QrCode::format('svg')->size(320)->margin(2)->generate($menuUrl);

        return view('owner.tables.show', [
            'table' => $table,
            'menuUrl' => $menuUrl,
            'qrImage' => 'data:image/svg+xml;base64,' . base64_encode($qrSvg),
        ]);
    }

    public function downloadQr(Table $table): StreamedResponse
    {
        $menuUrl = route('menu.table', $table->qr_token);
        $qrSvg = QrCode::format('svg')->size(640)->margin(2)->generate($menuUrl);
        $filename = 'qr-' . Str::slug($table->table_code) . '.svg';

        return response()->streamDownload(
            static fn () => print($qrSvg),
            $filename,
            ['Content-Type' => 'image/svg+xml; charset=UTF-8'],
        );
    }
}
