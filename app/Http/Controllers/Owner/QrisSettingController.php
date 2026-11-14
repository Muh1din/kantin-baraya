<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class QrisSettingController extends Controller
{
    public function index(): View
    {
        return view('owner.qris.index', [
            'setting' => PaymentSetting::query()->first(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'qris_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $setting = PaymentSetting::query()->firstOrNew([]);
        $oldPath = $setting->qris_image_path;
        $newPath = $validated['qris_image']->store('payments/qris', 'public');

        $setting->qris_image_path = $newPath;
        $setting->save();

        if ($oldPath && $oldPath !== $newPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()->route('owner.qris.index')->with('status', 'Gambar QRIS berhasil disimpan.');
    }

    public function destroy(): RedirectResponse
    {
        $setting = PaymentSetting::query()->first();

        if ($setting?->qris_image_path) {
            Storage::disk('public')->delete($setting->qris_image_path);
            $setting->qris_image_path = null;
            $setting->save();
        }

        return redirect()->route('owner.qris.index')->with('status', 'Gambar QRIS berhasil dihapus.');
    }
}
